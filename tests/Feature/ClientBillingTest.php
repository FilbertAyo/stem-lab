<?php

use App\Models\SubscriptionOrder;
use App\Models\SubscriptionPackage;
use App\Models\TeacherClub;
use App\Models\User;
use Database\Seeders\SubscriptionPackageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

require_once __DIR__.'/../Helpers/CompetitionTestHelper.php';

test('legacy trial backfill marks existing free users and skips club access', function () {
    $legacy = User::factory()->create([
        'role' => 'teacher',
        'created_at' => now()->subMonths(2),
        'trial_used_at' => null,
        'subscription_tier' => null,
    ]);

    $prepaid = User::factory()->create([
        'role' => 'teacher',
        'trial_used_at' => null,
        'subscription_tier' => 'individual',
        'subscription_expires_at' => now()->addDays(10),
    ]);

    ['club' => $club, 'school' => $school] = createCompetitionFixture();
    $clubTeacher = User::factory()->create([
        'role' => 'teacher',
        'subscription_tier' => 'public_school',
        'trial_used_at' => null,
    ]);
    TeacherClub::query()->create([
        'user_id' => $clubTeacher->id,
        'club_id' => $club->id,
        'school_id' => $school->id,
        'status' => 'approved',
        'is_leader' => true,
    ]);

    $this->artisan('subscriptions:mark-legacy-trials-used')
        ->assertSuccessful();

    expect($legacy->fresh()->trial_used_at)->not->toBeNull()
        ->and($legacy->fresh()->trial_used_at->equalTo($legacy->created_at))->toBeTrue()
        ->and($prepaid->fresh()->trial_used_at)->toBeNull()
        ->and($clubTeacher->fresh()->trial_used_at)->toBeNull();
});

test('client billing page shows current plan options', function () {
    $this->seed(SubscriptionPackageSeeder::class);

    $teacher = User::factory()->create(['role' => 'teacher']);

    $this->actingAs($teacher)
        ->get(route('client.billing.index'))
        ->assertOk()
        ->assertSee('My plan')
        ->assertSee('Pay with mobile money')
        ->assertSee('Billing history');
});

test('client transactions page lists only the authenticated user orders', function () {
    $this->seed(SubscriptionPackageSeeder::class);

    $package = SubscriptionPackage::query()->where('slug', 'individual')->firstOrFail();
    $owner = User::factory()->create(['role' => 'teacher']);
    $other = User::factory()->create(['role' => 'teacher']);

    SubscriptionOrder::query()->create([
        'user_id' => $owner->id,
        'subscription_package_id' => $package->id,
        'package_slug' => 'individual',
        'status' => SubscriptionOrder::STATUS_FULFILLED,
        'amount_tzs' => 1000,
        'currency' => 'TZS',
        'period_days' => 30,
        'snippe_payment_reference' => 'pi_owner_visible',
        'idempotency_key' => Str::lower(Str::random(30)),
        'paid_at' => now(),
        'fulfilled_at' => now(),
    ]);

    SubscriptionOrder::query()->create([
        'user_id' => $other->id,
        'subscription_package_id' => $package->id,
        'package_slug' => 'individual',
        'status' => SubscriptionOrder::STATUS_FULFILLED,
        'amount_tzs' => 10000,
        'currency' => 'TZS',
        'period_days' => 365,
        'snippe_payment_reference' => 'pi_other_secret',
        'idempotency_key' => Str::lower(Str::random(30)),
        'paid_at' => now(),
        'fulfilled_at' => now(),
    ]);

    $this->actingAs($owner)
        ->get(route('client.billing.transactions'))
        ->assertOk()
        ->assertSee('pi_owner_visible')
        ->assertDontSee('pi_other_secret');
});

test('transactions empty state is shown when the user has no orders', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);

    $this->actingAs($teacher)
        ->get(route('client.billing.transactions'))
        ->assertOk()
        ->assertSee('No payments yet');
});

test('paywall page is shown for unpaid users redirected from learning', function () {
    config(['subscriptions.payments_enabled' => true]);
    $this->seed(SubscriptionPackageSeeder::class);

    $teacher = User::factory()->create([
        'role' => 'teacher',
        'trial_used_at' => now()->subDay(),
        'subscription_tier' => null,
    ]);

    $this->actingAs($teacher)
        ->get(route('client.billing.index', ['paywall' => 1]))
        ->assertOk()
        ->assertSee('Your trial has ended')
        ->assertSee('Pay with mobile money')
        ->assertSee('Get free school access');
});

test('club teachers still reach competitions when payments are enabled', function () {
    config(['subscriptions.payments_enabled' => true]);
    $this->seed(SubscriptionPackageSeeder::class);
    ['club' => $club, 'school' => $school] = createCompetitionFixture();

    $teacher = User::factory()->create([
        'role' => 'teacher',
        'subscription_tier' => 'public_school',
    ]);

    TeacherClub::query()->create([
        'user_id' => $teacher->id,
        'club_id' => $club->id,
        'school_id' => $school->id,
        'status' => 'approved',
        'is_leader' => true,
    ]);

    $this->actingAs($teacher)
        ->get(route('client.competitions.index'))
        ->assertOk();
});

test('unpaid teachers can still open club join when payments are enabled', function () {
    config(['subscriptions.payments_enabled' => true]);
    ['club' => $club] = createCompetitionFixture();

    $teacher = User::factory()->create([
        'role' => 'teacher',
        'trial_used_at' => now()->subDay(),
    ]);

    $this->actingAs($teacher)
        ->get(route('client.clubs.join.form', $club))
        ->assertOk();
});

test('navigation includes my plan and billing history links', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);

    $this->actingAs($teacher)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('My plan')
        ->assertSee('Billing history');
});
