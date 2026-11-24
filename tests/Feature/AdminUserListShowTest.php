<?php

use App\Models\TeacherClub;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

require_once __DIR__.'/../Helpers/CompetitionTestHelper.php';

function actingAsAdmin(): User
{
    $admin = User::factory()->create([
        'role' => 'admin',
        'is_active' => true,
    ]);

    test()->actingAs($admin);

    return $admin;
}

test('teachers list shows school and plan instead of role and created at', function () {
    actingAsAdmin();
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

    $response = $this->get(route('admin.users.teachers'));

    $response->assertOk()
        ->assertSee('School')
        ->assertSee('Plan')
        ->assertSee($school->name)
        ->assertSee('Free (public school)')
        ->assertDontSee('>Role<', false)
        ->assertDontSee('Created At');
});

test('parents list is available under user management', function () {
    actingAsAdmin();

    $parent = User::factory()->create([
        'role' => 'parent',
        'subscription_tier' => 'individual',
        'subscription_expires_at' => now()->addDays(10),
    ]);

    $this->get(route('admin.users.parents'))
        ->assertOk()
        ->assertSee('Parents Management')
        ->assertSee($parent->name)
        ->assertSee(route('admin.users.show', $parent), false)
        ->assertSee('Individual');

    $this->get(route('admin.users.show', $parent))
        ->assertOk()
        ->assertSee('Parent')
        ->assertSee('Manage plan');
});

test('students list links view to a dedicated show page with plan details', function () {
    actingAsAdmin();

    $student = User::factory()->create([
        'role' => 'student',
        'subscription_tier' => 'individual',
        'subscription_expires_at' => now()->addDays(10),
    ]);

    $list = $this->get(route('admin.users.students'));
    $list->assertOk()
        ->assertSee(route('admin.users.show', $student), false)
        ->assertSee('Individual')
        ->assertDontSee('Created At');

    $show = $this->get(route('admin.users.show', $student));
    $show->assertOk()
        ->assertSee($student->name)
        ->assertSee('Subscription plan')
        ->assertSee('Manage plan')
        ->assertSee('Individual');
});

test('admin can assign and clear a user subscription plan from the show page', function () {
    actingAsAdmin();
    $this->seed(\Database\Seeders\SubscriptionPackageSeeder::class);

    $student = User::factory()->create([
        'role' => 'student',
        'subscription_tier' => null,
        'subscription_expires_at' => null,
        'student_onboarding_completed_at' => now(),
    ]);

    $assign = $this->put(route('admin.users.subscription.update', $student), [
        'plan' => 'individual',
        'expires_at' => now()->addDays(20)->format('Y-m-d'),
    ]);

    $assign->assertRedirect(route('admin.users.show', $student));
    $student->refresh();

    expect($student->subscription_tier)->toBe('individual')
        ->and($student->subscription_expires_at)->not->toBeNull()
        ->and($student->hasActivePrepaidAccess())->toBeTrue()
        ->and($student->hasPlatformAccess())->toBeTrue()
        ->and($student->trial_used_at)->not->toBeNull();

    $clear = $this->put(route('admin.users.subscription.update', $student), [
        'plan' => 'none',
    ]);

    $clear->assertRedirect(route('admin.users.show', $student));
    $student->refresh();

    expect($student->subscription_tier)->toBeNull()
        ->and($student->subscription_expires_at)->toBeNull()
        ->and($student->adminPlanLabel())->toBe('No plan');
});

test('admin-granted individual plan unlocks labs when payments are enabled', function () {
    config(['subscriptions.payments_enabled' => true]);
    actingAsAdmin();
    $this->seed(\Database\Seeders\SubscriptionPackageSeeder::class);

    $student = User::factory()->create([
        'role' => 'student',
        'subscription_tier' => null,
        'subscription_expires_at' => null,
        'trial_used_at' => now()->subDay(),
        'student_onboarding_completed_at' => now(),
        'gender' => 'female',
        'date_of_birth' => '2010-01-01',
    ]);

    $this->put(route('admin.users.subscription.update', $student), [
        'plan' => 'individual',
        'expires_at' => now()->addDays(30)->format('Y-m-d'),
    ])->assertRedirect(route('admin.users.show', $student));

    $student = $student->fresh();
    expect($student->hasPlatformAccess())->toBeTrue();

    // Hit learning on the client host (admin domain uses the same path for ops routes).
    $this->actingAs($student)
        ->get('http://localhost/vr-experiments')
        ->assertOk();

    $this->actingAs($student)
        ->get('http://localhost/billing?paywall=1')
        ->assertOk()
        ->assertDontSee('Your trial has ended')
        ->assertDontSee('Access paused');
});

test('admin can assign the yearly individual plan', function () {
    actingAsAdmin();
    $this->seed(\Database\Seeders\SubscriptionPackageSeeder::class);

    $student = User::factory()->create([
        'role' => 'student',
        'subscription_tier' => null,
        'subscription_expires_at' => null,
    ]);

    $this->get(route('admin.users.show', $student))
        ->assertOk()
        ->assertSee('Individual yearly');

    $this->put(route('admin.users.subscription.update', $student), [
        'plan' => 'individual-yearly',
    ])->assertRedirect(route('admin.users.show', $student));

    $student->refresh();

    expect($student->subscription_tier)->toBe('individual')
        ->and($student->subscription_expires_at?->isAfter(now()->addDays(360)))->toBeTrue();
});
