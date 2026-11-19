<?php

use App\Mail\SubscriptionActivatedMail;
use App\Mail\SubscriptionPaymentDueMail;
use App\Mail\SubscriptionPaymentSucceededMail;
use App\Mail\SubscriptionRenewalReminderMail;
use App\Models\SubscriptionOrder;
use App\Models\User;
use App\Services\SubscriptionFulfillmentService;
use Database\Seeders\SubscriptionPackageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(function () {
    config([
        'subscriptions.payments_enabled' => true,
        'snippe.api_key' => 'snp_test_key',
        'snippe.webhook_secret' => 'whsec_test_secret',
        'snippe.base_url' => 'https://api.snippe.test',
    ]);

    $this->seed(SubscriptionPackageSeeder::class);
});

test('free trial activation emails the client', function () {
    Mail::fake();

    $teacher = User::factory()->create(['role' => 'teacher']);

    $this->actingAs($teacher)
        ->post(route('client.subscription-packages.select'), [
            'package' => 'free-trial',
        ])
        ->assertRedirect();

    Mail::assertSent(SubscriptionActivatedMail::class, function (SubscriptionActivatedMail $mail) use ($teacher) {
        return $mail->user->is($teacher) && $mail->packageName !== '';
    });
});

test('checkout creation emails a payment due notice', function () {
    Mail::fake();

    Http::fake([
        'api.snippe.test/api/v1/sessions' => Http::response([
            'code' => 201,
            'data' => [
                'reference' => 'sess_mail123',
                'checkout_url' => 'https://snippe.me/checkout/mailtoken',
                'short_code' => 'Ml12Cd',
                'expires_at' => now()->addHour()->toIso8601String(),
            ],
        ], 201),
    ]);

    $teacher = User::factory()->create([
        'role' => 'teacher',
        'phone' => '+255712345678',
    ]);

    $this->actingAs($teacher)
        ->post(route('client.subscription-packages.select'), [
            'package' => 'individual',
        ])
        ->assertRedirect('https://snippe.me/checkout/mailtoken');

    Mail::assertSent(SubscriptionPaymentDueMail::class);

    $order = SubscriptionOrder::query()->first();
    expect(data_get($order?->metadata, 'payment_due_notified_at'))->not->toBeNull();
});

test('fulfillment emails payment confirmation', function () {
    Mail::fake();

    $teacher = User::factory()->create(['role' => 'teacher']);
    $packageId = \App\Models\SubscriptionPackage::query()->where('slug', 'individual')->value('id');

    $order = SubscriptionOrder::query()->create([
        'user_id' => $teacher->id,
        'subscription_package_id' => $packageId,
        'package_slug' => 'individual',
        'status' => SubscriptionOrder::STATUS_CHECKOUT_CREATED,
        'amount_tzs' => 1000,
        'currency' => 'TZS',
        'period_days' => 30,
        'snippe_session_reference' => 'sess_fulfill_mail',
        'checkout_url' => 'https://snippe.me/checkout/fulfill',
        'idempotency_key' => 'idempotencykeymailfulfill0001',
        'metadata' => [],
    ]);

    $service = app(SubscriptionFulfillmentService::class);
    $fulfilled = $service->fulfillPaidOrder($order, 'pi_mail_1');
    $service->notifyFulfillment($fulfilled);

    Mail::assertSent(SubscriptionPaymentSucceededMail::class);
});

test('renewal reminder command emails users before expiry', function () {
    Mail::fake();

    $teacher = User::factory()->create([
        'role' => 'teacher',
        'subscription_tier' => 'individual',
        'subscription_expires_at' => now()->addDays(2),
        'subscription_renewal_reminded_at' => null,
    ]);

    $this->artisan('subscriptions:send-renewal-reminders')->assertSuccessful();

    Mail::assertSent(SubscriptionRenewalReminderMail::class, function (SubscriptionRenewalReminderMail $mail) use ($teacher) {
        return $mail->user->is($teacher);
    });
});

test('payment reminder command emails open checkouts near expiry', function () {
    Mail::fake();

    $teacher = User::factory()->create(['role' => 'teacher']);
    $packageId = \App\Models\SubscriptionPackage::query()->where('slug', 'individual')->value('id');

    SubscriptionOrder::query()->create([
        'user_id' => $teacher->id,
        'subscription_package_id' => $packageId,
        'package_slug' => 'individual',
        'status' => SubscriptionOrder::STATUS_CHECKOUT_CREATED,
        'amount_tzs' => 1000,
        'currency' => 'TZS',
        'period_days' => 30,
        'snippe_session_reference' => 'sess_remind_mail',
        'checkout_url' => 'https://snippe.me/checkout/remind',
        'idempotency_key' => 'idempotencykeymailremind00001',
        'expires_at' => now()->addHours(2),
        'metadata' => ['payment_due_notified_at' => now()->subHour()->toIso8601String()],
    ]);

    $this->artisan('subscriptions:send-payment-reminders')->assertSuccessful();

    Mail::assertSent(SubscriptionPaymentDueMail::class);

    $order = SubscriptionOrder::query()->first();
    expect(data_get($order?->metadata, 'payment_reminded_at'))->not->toBeNull();
});

test('admin payments sidebar routes resolve', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $host = parse_url((string) config('app.admin_url'), PHP_URL_HOST) ?: 'admin.test';

    $this->actingAs($admin)
        ->withServerVariables(['HTTP_HOST' => $host])
        ->get(route('admin.subscription-packages.index'))
        ->assertOk();

    $this->actingAs($admin)
        ->withServerVariables(['HTTP_HOST' => $host])
        ->get(route('admin.subscription-orders.index'))
        ->assertOk();

    $this->actingAs($admin)
        ->withServerVariables(['HTTP_HOST' => $host])
        ->get(route('admin.subscription-issues.index'))
        ->assertOk()
        ->assertSee('Subscription Issues');
});
