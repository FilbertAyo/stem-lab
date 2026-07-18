<?php

use App\Models\Club;
use App\Models\Competition;
use App\Models\Subject;
use App\Models\User;
use App\Models\VrAssessmentAttempt;
use App\Models\VrExperiment;
use App\Models\VrQuestionBank;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

require_once __DIR__.'/../Helpers/CompetitionTestHelper.php';

function createPublicIdSubject(): Subject
{
    return Subject::query()->create([
        'subject_name' => 'Physics '.Str::random(4),
        'is_active' => true,
        'display_order' => 1,
    ]);
}

function createPublicIdVrExperiment(?Subject $subject = null): VrExperiment
{
    $subject ??= createPublicIdSubject();

    return VrExperiment::query()->create([
        'name' => 'VR Lab '.Str::random(4),
        'description' => 'Explore science',
        'external_url' => 'https://example.com/vr',
        'subject_id' => $subject->id,
        'is_active' => true,
    ])->fresh();
}

function createPublicIdQuestionBank(?VrExperiment $experiment = null): VrQuestionBank
{
    $experiment ??= createPublicIdVrExperiment();

    return VrQuestionBank::query()->create([
        'vr_experiment_id' => $experiment->id,
        'title' => 'Quiz '.Str::random(4),
        'description' => 'Assessment',
        'time_limit' => 10,
        'passing_score' => 70,
        'is_active' => true,
        'display_order' => 1,
    ])->fresh();
}

function adminHostUrl(string $path = '/'): string
{
    $host = parse_url(config('app.admin_url'), PHP_URL_HOST) ?: 'admin.test';

    return 'http://'.$host.$path;
}

test('vr experiments, banks, attempts, subjects, clubs, and users receive short public ids', function () {
    $subject = createPublicIdSubject();
    $experiment = createPublicIdVrExperiment($subject);
    $bank = createPublicIdQuestionBank($experiment);
    $user = User::factory()->create(['role' => 'student']);
    $attempt = VrAssessmentAttempt::query()->create([
        'user_id' => $user->id,
        'question_bank_id' => $bank->id,
        'started_at' => now(),
        'completed_at' => now(),
        'score' => 80,
        'correct_answers' => 8,
        'total_questions' => 10,
        'passed' => true,
    ])->fresh();
    $club = Club::query()->create([
        'name' => 'Club '.Str::random(4),
        'is_active' => true,
    ])->fresh();

    foreach ([$subject, $experiment, $bank, $attempt, $club, $user->fresh()] as $model) {
        expect($model->public_id)
            ->toBeString()
            ->toHaveLength(6)
            ->toMatch('/^[a-z0-9]{6}$/')
            ->toMatch('/[a-z]/')
            ->and($model->ulid)->toBeString()->toHaveLength(26)
            ->and($model->getRouteKey())->toBe($model->public_id);
    }
});

test('generated routes use short public ids for scoped modules', function () {
    $subject = createPublicIdSubject();
    $experiment = createPublicIdVrExperiment($subject);
    $bank = createPublicIdQuestionBank($experiment);
    $user = User::factory()->create(['role' => 'admin']);
    $club = Club::query()->create(['name' => 'Club '.Str::random(4), 'is_active' => true])->fresh();

    expect(route('subjects.show', $subject, false))->toEndWith('/subjects/'.$subject->public_id)
        ->and(route('admin.vr-experiments.edit', $experiment, false))->toEndWith('/vr-experiments/'.$experiment->public_id.'/edit')
        ->and(route('admin.vr-assessments.question-banks.show', $bank, false))->toEndWith('/vr-assessments/question-banks/'.$bank->public_id)
        ->and(route('admin.clubs.show', $club, false))->toEndWith('/clubs/'.$club->public_id)
        ->and(route('admin.progress.student', $user, false))->toEndWith('/progress/student/'.$user->public_id)
        ->and(route('client.vr-experiments.show', $experiment, false))->toBe('/vr-experiments/'.$experiment->public_id)
        ->and(route('client.vr-assessments.show', $bank, false))->toBe('/vr-quizzes/'.$bank->public_id)
        ->and(route('client.clubs.join.form', $club, false))->toBe('/clubs/join/'.$club->public_id);
});

test('legacy ulid get urls redirect for subjects clubs and vr modules', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $subject = createPublicIdSubject();
    $experiment = createPublicIdVrExperiment($subject);
    $bank = createPublicIdQuestionBank($experiment);
    $club = Club::query()->create(['name' => 'Club '.Str::random(4), 'is_active' => true])->fresh();

    $this->actingAs($admin)
        ->withServerVariables(['HTTP_HOST' => adminRequestHost()])
        ->get(adminHostUrl('/subjects/'.$subject->ulid))
        ->assertRedirect(adminHostUrl('/subjects/'.$subject->public_id))
        ->assertStatus(301);

    $this->actingAs($admin)
        ->withServerVariables(['HTTP_HOST' => adminRequestHost()])
        ->get(adminHostUrl('/vr-experiments/'.$experiment->ulid.'/edit'))
        ->assertRedirect(adminHostUrl('/vr-experiments/'.$experiment->public_id.'/edit'))
        ->assertStatus(301);

    $this->actingAs($admin)
        ->withServerVariables(['HTTP_HOST' => adminRequestHost()])
        ->get(adminHostUrl('/vr-assessments/question-banks/'.$bank->ulid))
        ->assertRedirect(adminHostUrl('/vr-assessments/question-banks/'.$bank->public_id))
        ->assertStatus(301);

    $this->actingAs($admin)
        ->withServerVariables(['HTTP_HOST' => adminRequestHost()])
        ->get(adminHostUrl('/clubs/'.$club->ulid))
        ->assertRedirect(adminHostUrl('/clubs/'.$club->public_id))
        ->assertStatus(301);
});

test('legacy numeric get urls redirect for users and assessment results', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $student = User::factory()->create(['role' => 'student']);
    $bank = createPublicIdQuestionBank();
    $attempt = VrAssessmentAttempt::query()->create([
        'user_id' => $student->id,
        'question_bank_id' => $bank->id,
        'started_at' => now(),
        'completed_at' => now(),
        'score' => 90,
        'correct_answers' => 9,
        'total_questions' => 10,
        'passed' => true,
    ])->fresh();

    $this->actingAs($admin)
        ->withServerVariables(['HTTP_HOST' => adminRequestHost()])
        ->get(adminHostUrl('/progress/student/'.$student->id))
        ->assertRedirect(adminHostUrl('/progress/student/'.$student->public_id))
        ->assertStatus(301);

    $this->actingAs($admin)
        ->withServerVariables(['HTTP_HOST' => adminRequestHost()])
        ->get(adminHostUrl('/vr-assessments/results/'.$attempt->id))
        ->assertRedirect(adminHostUrl('/vr-assessments/results/'.$attempt->public_id))
        ->assertStatus(301);
});

test('mutating legacy user routes resolve without redirect', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $target = User::factory()->create([
        'role' => 'admin',
        'name' => 'Original Admin',
        'email' => 'original-admin@example.com',
    ]);

    $response = $this->actingAs($admin)
        ->withServerVariables(['HTTP_HOST' => adminRequestHost()])
        ->from(adminHostUrl('/users'))
        ->put(adminHostUrl('/users/'.$target->ulid), [
            'name' => 'Updated Admin',
            'email' => 'updated-admin@example.com',
        ]);

    expect($response->status())->not->toBe(301);
    $response->assertRedirect();

    expect($target->fresh()->name)->toBe('Updated Admin');
});

test('public ids backfill command is idempotent across resources', function () {
    $subject = createPublicIdSubject();
    $subject->forceFill(['public_id' => null])->saveQuietly();
    $originalUlid = $subject->ulid;
    $originalId = $subject->id;

    Artisan::call('public-ids:backfill', ['resource' => 'subjects']);
    $subject->refresh();

    expect($subject->public_id)->toBeString()->toHaveLength(6)
        ->and($subject->ulid)->toBe($originalUlid)
        ->and($subject->id)->toBe($originalId);

    $first = $subject->public_id;
    Artisan::call('public-ids:backfill', ['resource' => 'subjects', '--verify' => true]);
    expect($subject->fresh()->public_id)->toBe($first);
});

test('competition urls still use ulids and are unchanged', function () {
    ['competition' => $competition] = createCompetitionFixture();

    expect($competition->public_id ?? null)->toBeNull()
        ->and($competition->getRouteKey())->toBe($competition->ulid)
        ->and(route('admin.competitions.show', $competition, false))->toEndWith('/competitions/'.$competition->ulid)
        ->and(schemaHasNoPublicIdOnCompetitions())->toBeTrue();
});

function schemaHasNoPublicIdOnCompetitions(): bool
{
    return ! \Illuminate\Support\Facades\Schema::hasColumn('competitions', 'public_id');
}

test('reserved public ids are never generated', function () {
    $candidates = collect(range(1, 50))->map(fn () => Subject::generatePublicIdCandidate());

    expect($candidates->intersect(['create', 'export', 'delete', 'update', 'search', 'import', 'status', 'result', 'report'])->count())
        ->toBe(0);
});
