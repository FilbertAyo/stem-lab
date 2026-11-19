<?php

use App\Models\SubscriptionPackage;
use App\Models\User;
use Database\Seeders\SubscriptionPackageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

require_once __DIR__.'/../Helpers/CompetitionTestHelper.php';

function adminSubscriptionPackagesUrl(string $path = '/'): string
{
    $host = parse_url(config('app.admin_url'), PHP_URL_HOST) ?: 'admin.test';

    return 'http://'.$host.'/subscription-packages'.$path;
}

test('subscription package seeder creates dashboard packages plus yearly checkout sku', function () {
    $this->seed(SubscriptionPackageSeeder::class);

    expect(SubscriptionPackage::query()->count())->toBe(4)
        ->and(SubscriptionPackage::query()->pluck('slug')->all())->toContain(
            'free-trial',
            'individual',
            'individual-yearly',
            'public-school',
        );

    $yearly = SubscriptionPackage::query()->where('slug', 'individual-yearly')->first();

    expect($yearly)->not->toBeNull()
        ->and($yearly->tier_key)->toBe('individual')
        ->and($yearly->price_amount_tzs)->toBe(10000)
        ->and($yearly->billing_period_days)->toBe(365);
});

test('admin can view subscription packages index', function () {
    $this->seed(SubscriptionPackageSeeder::class);

    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)
        ->withServerVariables(['HTTP_HOST' => adminRequestHost()])
        ->get(adminSubscriptionPackagesUrl());

    $response->assertOk()
        ->assertSee('Subscription Packages')
        ->assertSee('Free trial')
        ->assertSee('Individual')
        ->assertSee('Individual yearly')
        ->assertSee('Yearly checkout')
        ->assertSee('Public secondary school');
});

test('admin can open subscription package edit form', function () {
    $this->seed(SubscriptionPackageSeeder::class);

    $admin = User::factory()->create(['role' => 'admin']);
    $package = SubscriptionPackage::query()->where('slug', 'individual')->firstOrFail();

    $response = $this->actingAs($admin)
        ->withServerVariables(['HTTP_HOST' => adminRequestHost()])
        ->get(adminSubscriptionPackagesUrl('/'.$package->id.'/edit'));

    $response->assertOk()
        ->assertSee('Edit Subscription Package')
        ->assertSee('individual')
        ->assertSee('Most popular');
});

test('client dashboard includes seeded package name', function () {
    $this->seed(SubscriptionPackageSeeder::class);

    $teacher = User::factory()->create(['role' => 'teacher']);

    $response = $this->actingAs($teacher)->get(route('dashboard'));

    $response->assertOk()
        ->assertSee('Platform subscription')
        ->assertSee('Free trial')
        ->assertDontSee('Individual yearly');
});
