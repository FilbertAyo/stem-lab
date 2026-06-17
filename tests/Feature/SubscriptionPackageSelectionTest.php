<?php

use App\Models\TeacherClub;
use App\Models\User;
use Database\Seeders\SubscriptionPackageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

require_once __DIR__.'/../Helpers/CompetitionTestHelper.php';

test('teacher with approved club membership is redirected from join form instead of 403', function () {
    ['club' => $club, 'school' => $school] = createCompetitionFixture();

    $teacher = User::factory()->create(['role' => 'teacher']);

    TeacherClub::query()->create([
        'user_id' => $teacher->id,
        'club_id' => $club->id,
        'school_id' => $school->id,
        'status' => 'approved',
        'is_leader' => true,
    ]);

    $membership = TeacherClub::query()->where('user_id', $teacher->id)->firstOrFail();

    $response = $this->actingAs($teacher)->get(route('client.clubs.join.form', $club));

    $response->assertRedirect(route('client.clubs.show', $membership));
});

test('paid package selection is blocked while payments are disabled', function () {
    $this->seed(SubscriptionPackageSeeder::class);

    $teacher = User::factory()->create(['role' => 'teacher']);

    $response = $this->actingAs($teacher)
        ->from(route('dashboard'))
        ->post(route('client.subscription-packages.select'), [
            'package' => 'free-trial',
        ]);

    $response->assertRedirect(route('dashboard').'#platform-subscription')
        ->assertSessionHas('error');

    expect($teacher->fresh()->subscription_tier)->toBeNull();
});

test('club member teacher cannot select paid package even when payments enabled', function () {
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

    $response = $this->actingAs($teacher)
        ->from(route('dashboard'))
        ->post(route('client.subscription-packages.select'), [
            'package' => 'individual',
        ]);

    $response->assertRedirect(route('dashboard').'#platform-subscription')
        ->assertSessionHas('error');

    expect($teacher->fresh()->subscription_tier)->toBe('public_school');
});

test('teacher club join sets public school subscription tier when approved', function () {
    ['club' => $club, 'school' => $school] = createCompetitionFixture();

    $teacher = User::factory()->create(['role' => 'teacher']);

    $this->actingAs($teacher)->post(route('client.clubs.join', $club), [
        'school_id' => $school->id,
    ]);

    expect($teacher->fresh()->subscription_tier)->toBe('public_school');
});

test('dashboard shows coming soon on paid packages when payments disabled', function () {
    $this->seed(SubscriptionPackageSeeder::class);

    $teacher = User::factory()->create(['role' => 'teacher']);

    $response = $this->actingAs($teacher)->get(route('dashboard'));

    $response->assertOk()
        ->assertSee('Coming soon');
});

test('dashboard marks public school package for approved teacher and disables other packages', function () {
    $this->seed(SubscriptionPackageSeeder::class);
    ['club' => $club, 'school' => $school] = createCompetitionFixture();

    $teacher = User::factory()->create([
        'role' => 'teacher',
        'subscription_tier' => 'individual',
    ]);

    TeacherClub::query()->create([
        'user_id' => $teacher->id,
        'club_id' => $club->id,
        'school_id' => $school->id,
        'status' => 'approved',
        'is_leader' => true,
    ]);

    $response = $this->actingAs($teacher)->get(route('dashboard'));

    $response->assertOk()
        ->assertSee('Your plan')
        ->assertSee('Current plan')
        ->assertSee('Included in your plan')
        ->assertSee('Free');
});

test('nav shows free plan for club teacher even if individual tier was set earlier', function () {
    ['club' => $club, 'school' => $school] = createCompetitionFixture();

    $teacher = User::factory()->create([
        'role' => 'teacher',
        'subscription_tier' => 'individual',
    ]);

    TeacherClub::query()->create([
        'user_id' => $teacher->id,
        'club_id' => $club->id,
        'school_id' => $school->id,
        'status' => 'approved',
        'is_leader' => true,
    ]);

    $response = $this->actingAs($teacher)->get(route('dashboard'));

    $response->assertOk()
        ->assertSee('Free', false);
});
