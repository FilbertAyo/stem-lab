<?php

use App\Models\Club;
use App\Models\Model3d;
use App\Models\Project;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use App\Models\VrExperiment;
use App\Models\VrQuestionBank;
use App\Models\WeeklySession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

require_once __DIR__.'/../Helpers/CompetitionTestHelper.php';

function ownershipAdminUrl(string $path = '/'): string
{
    $host = parse_url(config('app.admin_url'), PHP_URL_HOST) ?: 'admin.test';

    return 'http://'.$host.$path;
}

function createOwnershipSubject(): Subject
{
    return Subject::query()->create([
        'subject_name' => 'Physics '.Str::random(4),
        'is_active' => true,
        'display_order' => 1,
    ]);
}

test('authenticated admin create and update set ownership on schools', function () {
    $creator = User::factory()->create(['role' => 'admin', 'name' => 'Creator Admin']);
    $updater = User::factory()->create(['role' => 'admin', 'name' => 'Updater Admin']);

    $this->actingAs($creator)
        ->withServerVariables(['HTTP_HOST' => adminRequestHost()])
        ->post(ownershipAdminUrl('/schools'), [
            'name' => 'Owned School '.Str::random(6),
            'is_active' => '1',
        ])
        ->assertRedirect();

    $school = School::query()->latest('id')->first();

    expect($school)->not->toBeNull()
        ->and($school->created_by)->toBe($creator->id)
        ->and($school->updated_by)->toBe($creator->id);

    $updatedName = $school->name.' Updated';

    $this->actingAs($updater)
        ->withServerVariables(['HTTP_HOST' => adminRequestHost()])
        ->put(ownershipAdminUrl('/schools/'.$school->getRouteKey()), [
            'name' => $updatedName,
            'is_active' => '1',
        ])
        ->assertRedirect();

    $school->refresh();

    expect($school->created_by)->toBe($creator->id)
        ->and($school->updated_by)->toBe($updater->id)
        ->and($school->name)->toBe($updatedName);
});

test('legacy ownership remains null and displays fallback label', function () {
    $admin = User::factory()->create(['role' => 'admin', 'name' => 'Viewer Admin']);

    $school = School::query()->create([
        'name' => 'Legacy School '.Str::random(6),
        'is_active' => true,
        'admin_reviewed_at' => now(),
        'created_by' => null,
        'updated_by' => null,
    ]);

    expect($school->createdByName())->toBe('Legacy / unavailable')
        ->and($school->updatedByName())->toBe('Legacy / unavailable');

    $response = $this->actingAs($admin)
        ->withServerVariables(['HTTP_HOST' => adminRequestHost()])
        ->get(ownershipAdminUrl('/schools/'.$school->getRouteKey()));

    $response->assertOk();
    $response->assertSee('Created by: Legacy / unavailable', false);
    $response->assertSee('Last updated by: Legacy / unavailable', false);
});

test('deleted owner user falls back to legacy unavailable label', function () {
    $owner = User::factory()->create(['role' => 'admin', 'name' => 'Deleted Owner']);
    $viewer = User::factory()->create(['role' => 'admin', 'name' => 'Viewer Admin']);

    $school = School::query()->create([
        'name' => 'Orphaned School '.Str::random(6),
        'is_active' => true,
        'admin_reviewed_at' => now(),
        'created_by' => $owner->id,
        'updated_by' => $owner->id,
    ]);

    $owner->delete();
    $school->refresh();

    expect($school->createdBy)->toBeNull()
        ->and($school->createdByName())->toBe('Legacy / unavailable')
        ->and($school->updatedByName())->toBe('Legacy / unavailable');

    $response = $this->actingAs($viewer)
        ->withServerVariables(['HTTP_HOST' => adminRequestHost()])
        ->get(ownershipAdminUrl('/schools/'.$school->getRouteKey()));

    $response->assertOk();
    $response->assertSee('Created by: Legacy / unavailable', false);
});

test('bulk mark reviewed sets updated_by for matching schools', function () {
    $admin = User::factory()->create(['role' => 'admin', 'name' => 'Bulk Reviewer']);
    ['region' => $region, 'district' => $district] = createRegionAndDistrict('Mara', 'Musoma');

    $pending = School::query()->create([
        'name' => 'Bulk Pending '.Str::random(6),
        'is_active' => true,
        'region_id' => $region->id,
        'district_id' => $district->id,
        'district' => $district->name,
        'admin_reviewed_at' => null,
        'created_by' => null,
        'updated_by' => null,
    ]);

    $this->actingAs($admin)
        ->withServerVariables(['HTTP_HOST' => adminRequestHost()])
        ->post(ownershipAdminUrl('/schools/mark-all-reviewed'), [
            'review' => 'pending',
            'district_id' => $district->id,
        ])
        ->assertRedirect();

    $pending->refresh();

    expect($pending->admin_reviewed_at)->not->toBeNull()
        ->and($pending->updated_by)->toBe($admin->id);
});

test('model create and update capture authenticated ownership', function () {
    $creator = User::factory()->create(['role' => 'admin', 'name' => 'Model Creator']);
    $updater = User::factory()->create(['role' => 'admin', 'name' => 'Model Updater']);
    $subject = createOwnershipSubject();

    $this->actingAs($creator);

    $model = Model3d::query()->create([
        'subject_id' => $subject->id,
        'name' => 'Owned Model '.Str::random(4),
        'embed_url' => 'https://sketchfab.com/models/example',
        'is_active' => true,
    ]);

    expect($model->created_by)->toBe($creator->id)
        ->and($model->updated_by)->toBe($creator->id);

    $this->actingAs($updater);
    $model->update(['name' => 'Updated Model '.Str::random(4)]);

    expect($model->fresh()->created_by)->toBe($creator->id)
        ->and($model->fresh()->updated_by)->toBe($updater->id);
});

test('vr experiment and question bank ownership are tracked automatically', function () {
    $admin = User::factory()->create(['role' => 'admin', 'name' => 'VR Admin']);
    $subject = createOwnershipSubject();

    $this->actingAs($admin);

    $experiment = VrExperiment::query()->create([
        'name' => 'Owned Experiment '.Str::random(4),
        'external_url' => 'https://example.com/sim',
        'subject_id' => $subject->id,
        'is_active' => true,
    ]);

    $bank = VrQuestionBank::query()->create([
        'vr_experiment_id' => $experiment->id,
        'title' => 'Owned Bank '.Str::random(4),
        'passing_score' => 60,
        'is_active' => true,
        'display_order' => 1,
    ]);

    expect($experiment->created_by)->toBe($admin->id)
        ->and($experiment->updated_by)->toBe($admin->id)
        ->and($bank->created_by)->toBe($admin->id)
        ->and($bank->updated_by)->toBe($admin->id);

    $response = $this->withServerVariables(['HTTP_HOST' => adminRequestHost()])
        ->get(ownershipAdminUrl('/vr-assessments/question-banks/'.$bank->public_id));

    $response->assertOk();
    $response->assertSee('Created by: VR Admin', false);
    $response->assertSee('Last updated by: VR Admin', false);
});

test('project keeps existing created_by and records updated_by on update', function () {
    $creator = User::factory()->create(['role' => 'admin', 'name' => 'Project Author']);
    $updater = User::factory()->create(['role' => 'admin', 'name' => 'Project Editor']);
    $class = SchoolClass::query()->create([
        'name' => 'Form 1 '.Str::random(4),
        'is_active' => true,
    ]);

    $this->actingAs($creator);

    $project = Project::query()->create([
        'school_class_id' => $class->id,
        'created_by' => $creator->id,
        'name' => 'Owned Project '.Str::random(4),
        'slug' => 'owned-project-'.Str::lower(Str::random(6)),
        'difficulty' => 'beginner',
        'is_active' => true,
    ]);

    expect($project->created_by)->toBe($creator->id)
        ->and($project->updated_by)->toBe($creator->id)
        ->and($project->author?->name)->toBe('Project Author')
        ->and($project->createdByName())->toBe('Project Author');

    $this->actingAs($updater);
    $project->update(['name' => 'Edited Project '.Str::random(4)]);

    $project->refresh();

    expect($project->created_by)->toBe($creator->id)
        ->and($project->updated_by)->toBe($updater->id)
        ->and($project->updatedByName())->toBe('Project Editor');

    $response = $this->withServerVariables(['HTTP_HOST' => adminRequestHost()])
        ->get(ownershipAdminUrl('/projects/'.$project->ulid));

    $response->assertOk();
    $response->assertSee('Created by: Project Author', false);
    $response->assertSee('Last updated by: Project Editor', false);
});

test('weekly session tracks creator and last updater automatically', function () {
    $creator = User::factory()->create(['role' => 'admin', 'name' => 'Session Creator']);
    $updater = User::factory()->create(['role' => 'admin', 'name' => 'Session Updater']);

    $club = Club::query()->create([
        'name' => 'Session Club '.Str::random(6),
        'is_active' => true,
    ]);

    $this->actingAs($creator);

    $session = WeeklySession::query()->create([
        'club_id' => $club->id,
        'title' => 'Owned Session '.Str::random(4),
        'session_date' => now()->toDateString(),
        'session_end_date' => now()->addDays(3)->toDateString(),
        'status' => 'draft',
    ]);

    expect($session->created_by)->toBe($creator->id)
        ->and($session->updated_by)->toBe($creator->id)
        ->and($session->createdByName())->toBe('Session Creator');

    $this->actingAs($updater);
    $session->update(['status' => 'scheduled']);

    $session->refresh();

    expect($session->created_by)->toBe($creator->id)
        ->and($session->updated_by)->toBe($updater->id)
        ->and($session->updatedByName())->toBe('Session Updater');

    $response = $this->withServerVariables(['HTTP_HOST' => adminRequestHost()])
        ->get(ownershipAdminUrl('/weekly-sessions/'.$session->ulid));

    $response->assertOk();
    $response->assertSee('Session Creator', false);
    $response->assertSee('Session Updater', false);
});

test('admin school create response includes ownership labels on index', function () {
    $admin = User::factory()->create(['role' => 'admin', 'name' => 'Index Admin']);

    $this->actingAs($admin)
        ->withServerVariables(['HTTP_HOST' => adminRequestHost()])
        ->post(ownershipAdminUrl('/schools'), [
            'name' => 'Indexed School '.Str::random(6),
            'is_active' => '1',
        ])
        ->assertRedirect();

    $response = $this->actingAs($admin)
        ->withServerVariables(['HTTP_HOST' => adminRequestHost()])
        ->get(ownershipAdminUrl('/schools'));

    $response->assertOk();
    $response->assertSee('Created by: Index Admin', false);
    $response->assertSee('Last updated by: Index Admin', false);
});
