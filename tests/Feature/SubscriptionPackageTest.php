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

test('subscription package seeder creates three packages with expected slugs', function () {
    $this->seed(SubscriptionPackageSeeder::class);

    expect(SubscriptionPackage::query()->count())->toBe(3)
        ->and(SubscriptionPackage::query()->pluck('slug')->all())->toBe([
            'free-trial',
            'individual',
            'public-school',
        ]);
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
        ->assertSee('Free trial');
});
