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
    ]);

    $assign = $this->put(route('admin.users.subscription.update', $student), [
        'plan' => 'individual',
        'expires_at' => now()->addDays(20)->format('Y-m-d'),
    ]);

    $assign->assertRedirect(route('admin.users.show', $student));
    $student->refresh();

    expect($student->subscription_tier)->toBe('individual')
        ->and($student->subscription_expires_at)->not->toBeNull()
        ->and($student->hasActivePrepaidAccess())->toBeTrue();

    $clear = $this->put(route('admin.users.subscription.update', $student), [
        'plan' => 'none',
    ]);

    $clear->assertRedirect(route('admin.users.show', $student));
    $student->refresh();

    expect($student->subscription_tier)->toBeNull()
        ->and($student->subscription_expires_at)->toBeNull()
        ->and($student->adminPlanLabel())->toBe('No plan');
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
