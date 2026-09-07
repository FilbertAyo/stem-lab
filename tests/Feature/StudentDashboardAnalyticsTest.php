<?php

use App\Enums\LearningEventType;
use App\Models\Model3d;
use App\Models\ModelQuestion;
use App\Models\ModelQuestionAnswer;
use App\Models\StudentLearningEvent;
use App\Models\Subject;
use App\Models\User;
use App\Models\VrAssessmentAttempt;
use App\Models\VrExperiment;
use App\Models\VrQuestionBank;
use Illuminate\Support\Str;

function createDashboardStudent(array $overrides = []): User
{
    return User::factory()->create(array_merge([
        'name' => 'Amina Hassan',
        'role' => 'student',
        'gender' => 'female',
        'date_of_birth' => '2011-05-20',
        'student_onboarding_completed_at' => now(),
        'phone' => '0712345678',
    ], $overrides));
}

function createDashboardSubject(): Subject
{
    return Subject::query()->create([
        'subject_name' => 'Biology '.Str::random(4),
        'is_active' => true,
        'display_order' => 1,
    ]);
}

function createDashboardModel(string $name = 'Heart Model'): Model3d
{
    return Model3d::query()->create([
        'subject_id' => createDashboardSubject()->id,
        'name' => $name,
        'is_active' => true,
    ]);
}

function createDashboardQuestion(Model3d $model, int $order = 1): ModelQuestion
{
    return ModelQuestion::query()->create([
        'model_id' => $model->id,
        'question_text' => 'Question '.$order,
        'question_type' => 'mcq',
        'options' => ['A', 'B'],
        'correct_answer' => 'A',
        'order' => $order,
    ]);
}

function createDashboardQuestionBank(string $title = 'Cell Quiz'): VrQuestionBank
{
    $experiment = VrExperiment::query()->create([
        'name' => 'VR Lab '.Str::random(4),
        'description' => 'Explore science',
        'external_url' => 'https://example.com/vr',
        'subject_id' => createDashboardSubject()->id,
        'is_active' => true,
    ]);

    return VrQuestionBank::query()->create([
        'vr_experiment_id' => $experiment->id,
        'title' => $title,
        'description' => 'Assessment',
        'time_limit' => 10,
        'passing_score' => 70,
        'is_active' => true,
        'display_order' => 1,
    ]);
}

test('student dashboard shows progress analytics even with no activity', function () {
    $student = createDashboardStudent();

    $this->actingAs($student)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Your progress')
        ->assertSee('Hello Amina')
        ->assertSee('Lessons completed')
        ->assertSee('Quiz accuracy')
        ->assertSee('VR average')
        ->assertSee('This week')
        ->assertSee('3D lesson scores')
        ->assertSee('VR quiz results')
        ->assertSee('Recent activity')
        ->assertDontSee('Registered students');
});

test('student dashboard shows lesson scores quizzes and recent activity', function () {
    $student = createDashboardStudent();
    $model = createDashboardModel('Heart Model');
    $question = createDashboardQuestion($model);
    $bank = createDashboardQuestionBank('Cell Quiz');

    ModelQuestionAnswer::query()->create([
        'user_id' => $student->id,
        'model_id' => $model->id,
        'question_id' => $question->id,
        'student_answer' => 'A',
        'is_correct' => true,
        'score' => 1,
    ]);

    VrAssessmentAttempt::query()->create([
        'user_id' => $student->id,
        'question_bank_id' => $bank->id,
        'started_at' => now()->subMinutes(12),
        'completed_at' => now()->subMinutes(4),
        'score' => 80,
        'correct_answers' => 8,
        'total_questions' => 10,
        'passed' => true,
    ]);

    StudentLearningEvent::query()->create([
        'user_id' => $student->id,
        'event_type' => LearningEventType::ModelCompleted,
        'metadata' => ['label' => 'Heart Model'],
        'occurred_at' => now()->subHour(),
    ]);

    $this->actingAs($student)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Heart Model')
        ->assertSee('100%')
        ->assertSee('Cell Quiz')
        ->assertSee('80%')
        ->assertSee('Passed')
        ->assertSee('Finished a 3D lesson')
        ->assertSee('1/1 correct');
});

test('teacher dashboard does not show student progress analytics', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);

    $this->actingAs($teacher)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertDontSee('Your progress')
        ->assertDontSee('3D lesson scores');
});
