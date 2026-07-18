<?php

use App\Models\Model3d;
use App\Models\ModelQuestion;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

require_once __DIR__.'/../Helpers/CompetitionTestHelper.php';

function createModel3dSubject(): Subject
{
    return Subject::query()->create([
        'subject_name' => 'Biology '.Str::random(4),
        'is_active' => true,
        'display_order' => 1,
    ]);
}

function createActiveModel3d(?SchoolClass $schoolClass = null): Model3d
{
    $subject = createModel3dSubject();

    $model = Model3d::query()->create([
        'subject_id' => $subject->id,
        'name' => 'Heart Model '.Str::random(4),
        'is_active' => true,
    ]);

    if ($schoolClass) {
        $model->schoolClasses()->attach($schoolClass->id);
    }

    return $model->fresh();
}

function adminModelsUrl(string $path = ''): string
{
    $host = parse_url(config('app.admin_url'), PHP_URL_HOST) ?: 'admin.test';

    return 'http://'.$host.'/models/3d'.$path;
}

test('newly created 3d models receive a six-character lowercase public_id', function () {
    $model = createActiveModel3d();

    expect($model->public_id)
        ->toBeString()
        ->toHaveLength(6)
        ->toMatch('/^[a-z0-9]{6}$/')
        ->toMatch('/[a-z]/')
        ->and($model->ulid)->toBeString()->toHaveLength(26);
});

test('generated model routes use the short public_id', function () {
    $model = createActiveModel3d();

    expect(route('client.models.3d.show', $model, false))
        ->toBe('/models/3d/'.$model->public_id)
        ->and(route('admin.models.3d.show', $model, false))
        ->toEndWith('/models/3d/'.$model->public_id);
});

test('short public_id resolves on client show route', function () {
    $schoolClass = SchoolClass::query()->create([
        'name' => 'Form 2 '.Str::random(4),
        'is_active' => true,
    ]);

    $student = User::factory()->create([
        'role' => 'student',
        'gender' => 'female',
        'date_of_birth' => '2011-05-20',
        'school_class_id' => $schoolClass->id,
        'student_onboarding_completed_at' => now(),
    ]);

    $model = createActiveModel3d($schoolClass);

    $this->actingAs($student)
        ->get(route('client.models.3d.show', $model))
        ->assertOk()
        ->assertSee($model->name);
});

test('legacy ulid get urls redirect to the short public_id url', function () {
    $schoolClass = SchoolClass::query()->create([
        'name' => 'Form 2 '.Str::random(4),
        'is_active' => true,
    ]);

    $student = User::factory()->create([
        'role' => 'student',
        'gender' => 'female',
        'date_of_birth' => '2011-05-20',
        'school_class_id' => $schoolClass->id,
        'student_onboarding_completed_at' => now(),
    ]);

    $model = createActiveModel3d($schoolClass);

    $this->actingAs($student)
        ->get('/models/3d/'.$model->ulid)
        ->assertRedirect('/models/3d/'.$model->public_id)
        ->assertStatus(301);
});

test('legacy numeric get urls redirect to the short public_id url', function () {
    $schoolClass = SchoolClass::query()->create([
        'name' => 'Form 2 '.Str::random(4),
        'is_active' => true,
    ]);

    $student = User::factory()->create([
        'role' => 'student',
        'gender' => 'female',
        'date_of_birth' => '2011-05-20',
        'school_class_id' => $schoolClass->id,
        'student_onboarding_completed_at' => now(),
    ]);

    $model = createActiveModel3d($schoolClass);

    $this->actingAs($student)
        ->get('/models/3d/'.$model->id)
        ->assertRedirect('/models/3d/'.$model->public_id)
        ->assertStatus(301);
});

test('admin legacy ulid get urls redirect to the short public_id url', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $model = createActiveModel3d();

    $this->actingAs($admin)
        ->withServerVariables(['HTTP_HOST' => adminRequestHost()])
        ->get(adminModelsUrl('/'.$model->ulid))
        ->assertRedirect(adminModelsUrl('/'.$model->public_id))
        ->assertStatus(301);
});

test('admin short public_id resolves nested question routes', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $model = createActiveModel3d();

    $question = ModelQuestion::query()->create([
        'model_id' => $model->id,
        'question_text' => 'What organ is this?',
        'question_type' => 'mcq',
        'options' => ['Heart', 'Liver'],
        'correct_answer' => 'Heart',
        'order' => 1,
    ]);

    $this->actingAs($admin)
        ->withServerVariables(['HTTP_HOST' => adminRequestHost()])
        ->get(adminModelsUrl('/'.$model->public_id.'/questions/'.$question->id.'/edit'))
        ->assertOk();
});

test('mutating legacy model routes resolve without redirect', function () {
    $schoolClass = SchoolClass::query()->create([
        'name' => 'Form 2 '.Str::random(4),
        'is_active' => true,
    ]);

    $student = User::factory()->create([
        'role' => 'student',
        'gender' => 'female',
        'date_of_birth' => '2011-05-20',
        'school_class_id' => $schoolClass->id,
        'student_onboarding_completed_at' => now(),
    ]);

    $model = createActiveModel3d($schoolClass);

    $response = $this->actingAs($student)
        ->postJson('/models/3d/'.$model->ulid.'/questions/submit', [
            'answers' => [],
        ]);

    expect($response->status())->not->toBe(301);
});

test('backfill command assigns public_ids idempotently and preserves ulids', function () {
    $model = createActiveModel3d();
    $originalUlid = $model->ulid;
    $originalId = $model->id;
    $originalPublicId = $model->public_id;

    $model->forceFill(['public_id' => null])->saveQuietly();
    expect($model->fresh()->public_id)->toBeNull();

    Artisan::call('public-ids:backfill', ['resource' => 'model3ds']);
    $model->refresh();

    expect($model->public_id)
        ->toBeString()
        ->toHaveLength(6)
        ->toMatch('/^[a-z0-9]{6}$/')
        ->and($model->ulid)->toBe($originalUlid)
        ->and($model->id)->toBe($originalId);

    $firstBackfill = $model->public_id;

    Artisan::call('public-ids:backfill', ['resource' => 'model3ds', '--verify' => true]);
    $model->refresh();

    expect($model->public_id)->toBe($firstBackfill)
        ->and($model->public_id)->not->toBe($originalPublicId);
});

test('models without public_id still generate ulid route keys', function () {
    $model = createActiveModel3d();
    $model->forceFill(['public_id' => null])->saveQuietly();
    $model->refresh();

    expect($model->getRouteKey())->toBe($model->ulid)
        ->and(route('client.models.3d.show', $model, false))->toBe('/models/3d/'.$model->ulid);
});
