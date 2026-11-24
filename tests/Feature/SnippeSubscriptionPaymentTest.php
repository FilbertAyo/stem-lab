<?php

use App\Models\SubscriptionOrder;
use App\Models\User;
use Database\Seeders\SubscriptionPackageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

require_once __DIR__.'/../Helpers/CompetitionTestHelper.php';

function snippeSignature(string $payload, string $secret, ?int $timestamp = null): array
{
    $timestamp ??= time();
    $signature = hash_hmac('sha256', $timestamp.'.'.$payload, $secret);

    return [
        'X-Webhook-Timestamp' => (string) $timestamp,
        'X-Webhook-Signature' => $signature,
    ];
}

beforeEach(function () {
    config([
        'subscriptions.payments_enabled' => true,
        'snippe.api_key' => 'snp_test_key',
        'snippe.webhook_secret' => 'whsec_test_secret',
        'snippe.base_url' => 'https://api.snippe.test',
    ]);

    $this->seed(SubscriptionPackageSeeder::class);
});

test('free trial activates prepaid access for seven days', function () {
    Mail::fake();

    $teacher = User::factory()->create(['role' => 'teacher']);

    $response = $this->actingAs($teacher)
        ->from(route('dashboard'))
        ->post(route('client.subscription-packages.select'), [
            'package' => 'free-trial',
        ]);

    $response->assertRedirect(route('dashboard').'#platform-subscription')
        ->assertSessionHas('status');

    $teacher->refresh();

    expect($teacher->subscription_tier)->toBe('trial')
        ->and($teacher->trial_used_at)->not->toBeNull()
        ->and($teacher->subscription_expires_at)->not->toBeNull()
        ->and($teacher->hasActivePrepaidAccess())->toBeTrue()
        ->and($teacher->hasPlatformAccess())->toBeTrue();
});

test('free trial can only be used once', function () {
    $teacher = User::factory()->create([
        'role' => 'teacher',
        'trial_used_at' => now()->subDay(),
        'subscription_tier' => null,
    ]);

    $response = $this->actingAs($teacher)
        ->from(route('dashboard'))
        ->post(route('client.subscription-packages.select'), [
            'package' => 'free-trial',
        ]);

    $response->assertRedirect(route('dashboard').'#platform-subscription')
        ->assertSessionHas('error');
});

test('yearly individual checkout charges yearly amount for 365 days', function () {
    Http::fake([
        'api.snippe.test/api/v1/sessions' => Http::response([
            'code' => 201,
            'data' => [
                'reference' => 'sess_yearly123',
                'checkout_url' => 'https://snippe.me/checkout/yearlytoken',
                'short_code' => 'Yr12Cd',
                'expires_at' => now()->addHour()->toIso8601String(),
            ],
        ], 201),
    ]);

    $teacher = User::factory()->create([
        'role' => 'teacher',
        'phone' => '+255712345678',
    ]);

    $response = $this->actingAs($teacher)
        ->post(route('client.subscription-packages.select'), [
            'package' => 'individual-yearly',
        ]);

    $response->assertRedirect('https://snippe.me/checkout/yearlytoken');

    $order = SubscriptionOrder::query()->first();

    expect($order)->not->toBeNull()
        ->and($order->package_slug)->toBe('individual-yearly')
        ->and($order->amount_tzs)->toBe(10000)
        ->and($order->period_days)->toBe(365);
});

test('individual checkout redirects to snippe hosted session', function () {
    Http::fake([
        'api.snippe.test/api/v1/sessions' => Http::response([
            'code' => 201,
            'data' => [
                'reference' => 'sess_test123',
                'checkout_url' => 'https://snippe.me/checkout/testtoken',
                'short_code' => 'Ab12Cd',
                'expires_at' => now()->addHour()->toIso8601String(),
            ],
        ], 201),
    ]);

    $teacher = User::factory()->create([
        'role' => 'teacher',
        'phone' => '+255712345678',
    ]);

    $response = $this->actingAs($teacher)
        ->post(route('client.subscription-packages.select'), [
            'package' => 'individual',
        ]);

    $response->assertRedirect('https://snippe.me/checkout/testtoken');

    $order = SubscriptionOrder::query()->first();

    expect($order)->not->toBeNull()
        ->and($order->status)->toBe(SubscriptionOrder::STATUS_CHECKOUT_CREATED)
        ->and($order->snippe_session_reference)->toBe('sess_test123')
        ->and($order->amount_tzs)->toBe(1000)
        ->and($order->period_days)->toBe(30);
});

test('snippe payment completed webhook fulfills order and grants access', function () {
    Mail::fake();

    $teacher = User::factory()->create(['role' => 'teacher']);

    $order = SubscriptionOrder::query()->create([
        'user_id' => $teacher->id,
        'subscription_package_id' => \App\Models\SubscriptionPackage::query()->where('slug', 'individual')->value('id'),
        'package_slug' => 'individual',
        'status' => SubscriptionOrder::STATUS_CHECKOUT_CREATED,
        'amount_tzs' => 1000,
        'currency' => 'TZS',
        'period_days' => 30,
        'snippe_session_reference' => 'sess_abc',
        'checkout_url' => 'https://snippe.me/checkout/abc',
        'idempotency_key' => 'idempotencykey123456789012345',
        'metadata' => ['order_ulid' => 'will-be-set'],
    ]);

    $order->forceFill([
        'metadata' => ['order_ulid' => $order->ulid],
    ])->save();

    $payload = json_encode([
        'id' => 'evt_test_completed_1',
        'type' => 'payment.completed',
        'api_version' => '2026-01-25',
        'created_at' => now()->toIso8601String(),
        'data' => [
            'reference' => 'pi_test_pay_1',
            'session_reference' => 'sess_abc',
            'status' => 'completed',
            'amount' => [
                'value' => 1000,
                'currency' => 'TZS',
            ],
            'metadata' => [
                'order_ulid' => $order->ulid,
                'user_id' => (string) $teacher->id,
                'package_slug' => 'individual',
            ],
            'completed_at' => now()->toIso8601String(),
        ],
    ], JSON_THROW_ON_ERROR);

    $headers = snippeSignature($payload, 'whsec_test_secret');

    $response = $this->call(
        'POST',
        route('webhooks.snippe'),
        [],
        [],
        [],
        [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_WEBHOOK_TIMESTAMP' => $headers['X-Webhook-Timestamp'],
            'HTTP_X_WEBHOOK_SIGNATURE' => $headers['X-Webhook-Signature'],
        ],
        $payload,
    );

    $response->assertOk();

    $order->refresh();
    $teacher->refresh();

    expect($order->status)->toBe(SubscriptionOrder::STATUS_FULFILLED)
        ->and($order->snippe_payment_reference)->toBe('pi_test_pay_1')
        ->and($teacher->subscription_tier)->toBe('individual')
        ->and($teacher->hasPlatformAccess())->toBeTrue();

    // Duplicate delivery is ignored.
    $duplicate = $this->call(
        'POST',
        route('webhooks.snippe'),
        [],
        [],
        [],
        [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_WEBHOOK_TIMESTAMP' => $headers['X-Webhook-Timestamp'],
            'HTTP_X_WEBHOOK_SIGNATURE' => $headers['X-Webhook-Signature'],
        ],
        $payload,
    );

    $duplicate->assertOk();
    expect(SubscriptionOrder::query()->where('status', SubscriptionOrder::STATUS_FULFILLED)->count())->toBe(1);
});

test('invalid snippe webhook signature is rejected', function () {
    $payload = json_encode([
        'id' => 'evt_bad',
        'type' => 'payment.completed',
        'data' => [],
    ], JSON_THROW_ON_ERROR);

    $response = $this->call(
        'POST',
        route('webhooks.snippe'),
        [],
        [],
        [],
        [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_WEBHOOK_TIMESTAMP' => (string) time(),
            'HTTP_X_WEBHOOK_SIGNATURE' => 'invalid',
        ],
        $payload,
    );

    $response->assertStatus(400);
});

test('unpaid users can browse catalogs and are asked for a package when opening an item', function () {
    $teacher = User::factory()->create([
        'role' => 'teacher',
        'subscription_tier' => null,
        'trial_used_at' => now()->subDay(),
    ]);

    $this->actingAs($teacher)
        ->get(route('client.models.3d.index'))
        ->assertOk()
        ->assertSee('Choose a package to continue')
        ->assertSee('id="access-packages-modal"', false);

    $this->actingAs($teacher)
        ->get(route('client.vr-experiments.math'))
        ->assertRedirect(route('client.vr-experiments.index'))
        ->assertSessionHas('open_packages', true);
});

test('unpaid teachers can browse competitions and weekly sessions until they open one', function () {
    ['competition' => $competition] = createCompetitionFixture();

    $teacher = User::factory()->create([
        'role' => 'teacher',
        'subscription_tier' => null,
        'trial_used_at' => now()->subDay(),
    ]);

    $this->actingAs($teacher)
        ->get(route('client.competitions.index'))
        ->assertOk()
        ->assertSee('id="access-packages-modal"', false);

    $this->actingAs($teacher)
        ->get(route('client.competitions.show', $competition))
        ->assertRedirect(route('client.competitions.index'))
        ->assertSessionHas('open_packages', true);

    $this->actingAs($teacher)
        ->get(route('client.weekly-sessions.index'))
        ->assertOk();
});

test('public school club teachers keep free learning access', function () {
    ['club' => $club, 'school' => $school] = createCompetitionFixture();

    $teacher = User::factory()->create([
        'role' => 'teacher',
        'subscription_tier' => 'public_school',
    ]);

    \App\Models\TeacherClub::query()->create([
        'user_id' => $teacher->id,
        'club_id' => $club->id,
        'school_id' => $school->id,
        'status' => 'approved',
        'is_leader' => true,
    ]);

    $response = $this->actingAs($teacher)->get(route('client.models.3d.index'));

    $response->assertOk();
});

test('learning remains open when payments are disabled', function () {
    config(['subscriptions.payments_enabled' => false]);

    $teacher = User::factory()->create([
        'role' => 'teacher',
        'subscription_tier' => null,
    ]);

    $response = $this->actingAs($teacher)->get(route('client.models.3d.index'));

    $response->assertOk();
});
