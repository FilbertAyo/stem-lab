<?php

use App\Models\Model3d;
use App\Models\Project;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use App\Models\VrExperiment;
use App\Models\VrQuestionBank;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

function createStudentWithClass(?SchoolClass $schoolClass = null): array
{
    $schoolClass ??= SchoolClass::query()->create([
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

    return compact('student', 'schoolClass');
}

function createSubject(): Subject
{
    return Subject::query()->create([
        'subject_name' => 'Biology '.Str::random(4),
        'is_active' => true,
        'display_order' => 1,
    ]);
}

test('student with a class only sees matching stem resources', function () {
    ['student' => $student, 'schoolClass' => $formTwo] = createStudentWithClass();

    $formThree = SchoolClass::query()->create([
        'name' => 'Form 3 '.Str::random(4),
        'is_active' => true,
    ]);

    $subject = createSubject();

    $matchingModel = Model3d::query()->create([
        'subject_id' => $subject->id,
        'name' => 'Heart Model',
        'is_active' => true,
    ]);
    $matchingModel->schoolClasses()->attach($formTwo->id);

    $otherModel = Model3d::query()->create([
        'subject_id' => $subject->id,
        'name' => 'Brain Model',
        'is_active' => true,
    ]);
    $otherModel->schoolClasses()->attach($formThree->id);

    $matchingExperiment = VrExperiment::query()->create([
        'name' => 'Cell VR',
        'description' => 'Explore cells',
        'external_url' => 'https://example.com/cell',
        'school_class_id' => $formTwo->id,
        'subject_id' => $subject->id,
        'is_active' => true,
    ]);

    VrExperiment::query()->create([
        'name' => 'Physics VR',
        'description' => 'Explore forces',
        'external_url' => 'https://example.com/physics',
        'school_class_id' => $formThree->id,
        'subject_id' => $subject->id,
        'is_active' => true,
    ]);

    $matchingProject = Project::query()->create([
        'name' => 'LED Blink',
        'slug' => 'led-blink-'.Str::random(6),
        'difficulty' => 'beginner',
        'school_class_id' => $formTwo->id,
        'is_active' => true,
        'is_published' => true,
        'published_at' => now(),
    ]);

    Project::query()->create([
        'name' => 'Robot Arm',
        'slug' => 'robot-arm-'.Str::random(6),
        'difficulty' => 'advanced',
        'school_class_id' => $formThree->id,
        'is_active' => true,
        'is_published' => true,
        'published_at' => now(),
    ]);

    $questionBank = VrQuestionBank::query()->create([
        'vr_experiment_id' => $matchingExperiment->id,
        'title' => 'Cell Quiz',
        'is_active' => true,
    ]);

    VrQuestionBank::query()->create([
        'vr_experiment_id' => VrExperiment::query()->where('name', 'Physics VR')->value('id'),
        'title' => 'Physics Quiz',
        'is_active' => true,
    ]);

    $this->actingAs($student)
        ->get(route('client.models.3d.index'))
        ->assertOk()
        ->assertSee($matchingModel->name)
        ->assertDontSee('Brain Model');

    $this->actingAs($student)
        ->get(route('client.vr-experiments.index'))
        ->assertOk()
        ->assertSee($matchingExperiment->name)
        ->assertDontSee('Physics VR');

    $this->actingAs($student)
        ->get(route('client.projects.index'))
        ->assertOk()
        ->assertSee($matchingProject->name)
        ->assertDontSee('Robot Arm');

    $this->actingAs($student)
        ->get(route('client.vr-assessments.index'))
        ->assertOk()
        ->assertSee($questionBank->title)
        ->assertDontSee('Physics Quiz');
});

test('student without a class sees all published stem resources', function () {
    $student = User::factory()->create([
        'role' => 'student',
        'gender' => 'male',
        'date_of_birth' => '2011-01-01',
        'school_class_id' => null,
        'student_onboarding_completed_at' => now(),
    ]);

    $formTwo = SchoolClass::query()->create([
        'name' => 'Form 2 '.Str::random(4),
        'is_active' => true,
    ]);

    $formThree = SchoolClass::query()->create([
        'name' => 'Form 3 '.Str::random(4),
        'is_active' => true,
    ]);

    $subject = createSubject();

    $heartModel = Model3d::query()->create([
        'subject_id' => $subject->id,
        'name' => 'Heart Model',
        'is_active' => true,
    ]);
    $heartModel->schoolClasses()->attach($formTwo->id);

    $brainModel = Model3d::query()->create([
        'subject_id' => $subject->id,
        'name' => 'Brain Model',
        'is_active' => true,
    ]);
    $brainModel->schoolClasses()->attach($formThree->id);

    $this->actingAs($student)
        ->get(route('client.models.3d.index'))
        ->assertOk()
        ->assertSee('Heart Model')
        ->assertSee('Brain Model');
});

test('student cannot open a model assigned to another class', function () {
    ['student' => $student] = createStudentWithClass();

    $otherClass = SchoolClass::query()->create([
        'name' => 'Form 4 '.Str::random(4),
        'is_active' => true,
    ]);

    $subject = createSubject();

    $foreignModel = Model3d::query()->create([
        'subject_id' => $subject->id,
        'name' => 'Foreign Model',
        'is_active' => true,
    ]);
    $foreignModel->schoolClasses()->attach($otherClass->id);

    $this->actingAs($student)
        ->get(route('client.models.3d.show', $foreignModel))
        ->assertNotFound();
});

test('student sees a model assigned to multiple classes when their class is included', function () {
    ['student' => $student, 'schoolClass' => $formTwo] = createStudentWithClass();

    $formThree = SchoolClass::query()->create([
        'name' => 'Form 3 '.Str::random(4),
        'is_active' => true,
    ]);

    $subject = createSubject();

    $sharedModel = Model3d::query()->create([
        'subject_id' => $subject->id,
        'name' => 'Shared Skeleton Model',
        'is_active' => true,
    ]);
    $sharedModel->schoolClasses()->attach([$formTwo->id, $formThree->id]);

    $this->actingAs($student)
        ->get(route('client.models.3d.index'))
        ->assertOk()
        ->assertSee('Shared Skeleton Model');
});
