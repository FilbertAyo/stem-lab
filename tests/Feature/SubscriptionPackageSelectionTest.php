<?php

use App\Models\Club;
use App\Models\SubscriptionPackage;
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

test('teacher can select free trial package', function () {
    $this->seed(SubscriptionPackageSeeder::class);

    $teacher = User::factory()->create(['role' => 'teacher']);

    $response = $this->actingAs($teacher)
        ->from(route('dashboard'))
        ->post(route('client.subscription-packages.select'), [
            'package' => 'free-trial',
        ]);

    $response->assertRedirect(route('dashboard').'#platform-subscription');

    expect($teacher->fresh()->subscription_tier)->toBe('trial');
});

test('student can select individual package', function () {
    $this->seed(SubscriptionPackageSeeder::class);

    $student = User::factory()->create([
        'role' => 'student',
        'gender' => 'female',
        'date_of_birth' => '2010-01-01',
        'student_onboarding_completed_at' => now(),
    ]);

    $response = $this->actingAs($student)
        ->from(route('dashboard'))
        ->post(route('client.subscription-packages.select'), [
            'package' => 'individual',
        ]);

    $response->assertRedirect(route('dashboard').'#platform-subscription');

    expect($student->fresh()->subscription_tier)->toBe('individual');
});

test('teacher club join sets public school subscription tier when approved', function () {
    ['club' => $club, 'school' => $school] = createCompetitionFixture();

    $teacher = User::factory()->create(['role' => 'teacher']);

    $this->actingAs($teacher)->post(route('client.clubs.join', $club), [
        'school_id' => $school->id,
    ]);

    expect($teacher->fresh()->subscription_tier)->toBe('public_school');
});

test('dashboard marks active package for user on trial', function () {
    $this->seed(SubscriptionPackageSeeder::class);

    $teacher = User::factory()->create([
        'role' => 'teacher',
        'subscription_tier' => 'trial',
    ]);

    $response = $this->actingAs($teacher)->get(route('dashboard'));

    $response->assertOk()
        ->assertSee('Your plan')
        ->assertSee('Current plan');
});

test('dashboard marks public school package for approved teacher', function () {
    $this->seed(SubscriptionPackageSeeder::class);
    ['club' => $club, 'school' => $school] = createCompetitionFixture();

    $teacher = User::factory()->create(['role' => 'teacher']);

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
        ->assertSee('Current plan');
});
