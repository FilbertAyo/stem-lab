<?php

use App\Models\Project;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

require_once __DIR__.'/../Helpers/CompetitionTestHelper.php';

function projectAdminUrl(string $path = ''): string
{
    $host = parse_url(config('app.admin_url'), PHP_URL_HOST) ?: 'admin.test';

    return 'http://'.$host.'/projects'.$path;
}

test('admin project crud uses the active status', function () {
    $schoolClass = SchoolClass::query()->create([
        'name' => 'Project class '.Str::random(6),
        'is_active' => true,
    ]);

    $admin = User::factory()->create(['role' => 'admin']);
    $projectName = 'Visibility project '.Str::random(6);

    $this->actingAs($admin)
        ->withServerVariables(['HTTP_HOST' => adminRequestHost()])
        ->post(projectAdminUrl(), [
            'name' => $projectName,
            'difficulty' => 'beginner',
            'school_class_id' => $schoolClass->id,
            'is_active' => '1',
        ])
        ->assertRedirect();

    $project = Project::query()->where('name', $projectName)->firstOrFail();

    expect($project->is_active)->toBeTrue();

    $this->actingAs($admin)
        ->withServerVariables(['HTTP_HOST' => adminRequestHost()])
        ->get(projectAdminUrl('?status=active'))
        ->assertOk()
        ->assertSee($projectName);

    $this->actingAs($admin)
        ->withServerVariables(['HTTP_HOST' => adminRequestHost()])
        ->put(projectAdminUrl('/'.$project->ulid), [
            'name' => $projectName,
            'difficulty' => 'beginner',
            'school_class_id' => $schoolClass->id,
            'is_active' => '0',
        ])
        ->assertRedirect();

    expect($project->fresh()->is_active)->toBeFalse();

    $this->actingAs($admin)
        ->withServerVariables(['HTTP_HOST' => adminRequestHost()])
        ->get(projectAdminUrl('?status=active'))
        ->assertOk()
        ->assertDontSee($projectName);

    $this->actingAs($admin)
        ->withServerVariables(['HTTP_HOST' => adminRequestHost()])
        ->get(projectAdminUrl('?status=inactive'))
        ->assertOk()
        ->assertSee($projectName);
});

test('active projects are visible to clients and inactive projects are hidden', function () {
    $schoolClass = SchoolClass::query()->create([
        'name' => 'Client project class '.Str::random(6),
        'is_active' => true,
    ]);

    $student = User::factory()->create([
        'role' => 'student',
        'gender' => 'female',
        'date_of_birth' => '2011-05-20',
        'school_class_id' => $schoolClass->id,
        'student_onboarding_completed_at' => now(),
    ]);

    $project = Project::query()->create([
        'name' => 'Client visibility project '.Str::random(6),
        'slug' => 'client-visibility-project-'.Str::lower(Str::random(6)),
        'difficulty' => 'beginner',
        'school_class_id' => $schoolClass->id,
        'is_active' => true,
    ]);

    $this->actingAs($student)
        ->get(route('client.projects.index'))
        ->assertOk()
        ->assertSee($project->name);

    $this->actingAs($student)
        ->get(route('client.projects.show', $project))
        ->assertOk();

    $project->update(['is_active' => false]);

    $this->actingAs($student)
        ->get(route('client.projects.index'))
        ->assertOk()
        ->assertDontSee($project->name);

    $this->actingAs($student)
        ->get(route('client.projects.show', $project))
        ->assertNotFound();
});
