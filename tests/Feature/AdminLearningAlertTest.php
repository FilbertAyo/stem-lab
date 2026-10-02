<?php

use App\Mail\AdminActivityMail;
use App\Mail\AdminAttentionDigestMail;
use App\Models\Subject;
use App\Models\User;
use App\Models\VrAssessmentAttempt;
use App\Models\VrExperiment;
use App\Models\VrQuestionBank;
use App\Services\AdminActivityNotifier;
use App\Services\AdminAttentionDigestService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

require_once __DIR__.'/../Helpers/CompetitionTestHelper.php';

function createLearningAlertQuestionBank(string $title = 'Cell Quiz'): VrQuestionBank
{
    $subject = Subject::query()->create([
        'subject_name' => 'Science '.Str::random(4),
        'description' => 'Test',
        'display_order' => 1,
        'is_active' => true,
    ]);

    $experiment = VrExperiment::query()->create([
        'name' => 'VR Lab '.Str::random(4),
        'description' => 'Explore science',
        'external_url' => 'https://example.com/vr',
        'subject_id' => $subject->id,
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

function createCompletedVrAttempt(User $user, VrQuestionBank $bank, bool $passed, float $score = 40): VrAssessmentAttempt
{
    return VrAssessmentAttempt::query()->create([
        'user_id' => $user->id,
        'question_bank_id' => $bank->id,
        'started_at' => now()->subMinutes(20),
        'completed_at' => now()->subMinutes(5),
        'score' => $score,
        'correct_answers' => $passed ? 8 : 3,
        'total_questions' => 10,
        'passed' => $passed,
    ]);
}

test('learning category appears on admin users page', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'email' => 'learning.viewer@example.com',
        'is_active' => true,
        'admin_alert_categories' => ['learning'],
    ]);
    $host = adminRequestHost();

    $response = $this->actingAs($admin)
        ->withServerVariables(['HTTP_HOST' => $host])
        ->get('http://'.$host.'/users');

    $response->assertOk();
    $response->assertSee('Student learning');
});

test('repeated vr quiz failure emails only learning admins', function () {
    Mail::fake();

    $learningAdmin = User::factory()->create([
        'role' => 'admin',
        'email' => 'learning@example.com',
        'is_active' => true,
        'admin_alert_categories' => ['learning'],
    ]);
    $competitionAdmin = User::factory()->create([
        'role' => 'admin',
        'email' => 'comp@example.com',
        'is_active' => true,
        'admin_alert_categories' => ['competition'],
    ]);

    $student = User::factory()->create([
        'role' => 'student',
        'name' => 'Struggling Student',
        'email' => 'struggle@example.com',
    ]);
    $bank = createLearningAlertQuestionBank('Photosynthesis Quiz');

    createCompletedVrAttempt($student, $bank, false, 30);
    $latest = createCompletedVrAttempt($student, $bank, false, 35);

    app(AdminActivityNotifier::class)->vrQuizCompleted($latest);

    Mail::assertSent(AdminActivityMail::class, function (AdminActivityMail $mail) use ($learningAdmin, $competitionAdmin) {
        return $mail->headline === 'Repeated VR quiz failure'
            && $mail->hasTo($learningAdmin->email)
            && $mail->hasTo('support@adilishastemlabs.com')
            && ! $mail->hasTo($competitionAdmin->email)
            && str_contains($mail->summary, 'Struggling Student');
    });
});

test('first fail does not send repeated failure alert', function () {
    Mail::fake();

    User::factory()->create([
        'role' => 'admin',
        'email' => 'learning.once@example.com',
        'is_active' => true,
        'admin_alert_categories' => ['learning'],
    ]);

    $student = User::factory()->create(['role' => 'student', 'name' => 'First Fail']);
    $bank = createLearningAlertQuestionBank();
    $attempt = createCompletedVrAttempt($student, $bank, false, 40);

    app(AdminActivityNotifier::class)->vrQuizCompleted($attempt);

    Mail::assertNotSent(AdminActivityMail::class);
});

test('pass after earlier fails emails learning admins', function () {
    Mail::fake();

    $learningAdmin = User::factory()->create([
        'role' => 'admin',
        'email' => 'learning.win@example.com',
        'is_active' => true,
        'admin_alert_categories' => ['learning'],
    ]);

    $student = User::factory()->create([
        'role' => 'student',
        'name' => 'Comeback Student',
        'email' => 'comeback@example.com',
    ]);
    $bank = createLearningAlertQuestionBank('Forces Quiz');

    createCompletedVrAttempt($student, $bank, false, 40);
    $pass = createCompletedVrAttempt($student, $bank, true, 85);

    app(AdminActivityNotifier::class)->vrQuizCompleted($pass);

    Mail::assertSent(AdminActivityMail::class, function (AdminActivityMail $mail) use ($learningAdmin) {
        return $mail->headline === 'Student passed after earlier fails'
            && $mail->hasTo($learningAdmin->email)
            && str_contains($mail->summary, 'Comeback Student');
    });
});

test('learning digest includes recent fails and routes to learning admins', function () {
    Mail::fake();

    $learningAdmin = User::factory()->create([
        'role' => 'admin',
        'email' => 'digest.learning@example.com',
        'is_active' => true,
        'admin_alert_categories' => ['learning'],
    ]);
    $contentAdmin = User::factory()->create([
        'role' => 'admin',
        'email' => 'digest.content@example.com',
        'is_active' => true,
        'admin_alert_categories' => ['content'],
    ]);

    $student = User::factory()->create([
        'role' => 'student',
        'name' => 'Digest Fail Student',
    ]);
    $bank = createLearningAlertQuestionBank('Digest Quiz');
    createCompletedVrAttempt($student, $bank, false, 25);
    createCompletedVrAttempt($student, $bank, false, 28);

    Artisan::call('admin:send-attention-digest');

    Mail::assertSent(AdminAttentionDigestMail::class, function (AdminAttentionDigestMail $mail) use ($learningAdmin, $contentAdmin) {
        return $mail->categoryKey === 'learning'
            && $mail->hasTo($learningAdmin->email)
            && $mail->hasTo('support@adilishastemlabs.com')
            && ! $mail->hasTo($contentAdmin->email)
            && collect($mail->sections)->contains(fn (array $section) => str_contains($section['title'], 'VR quiz fails'));
    });
});

test('learning digest sections build when there is quiz backlog', function () {
    $student = User::factory()->create(['role' => 'student', 'name' => 'Section Student']);
    $bank = createLearningAlertQuestionBank('Section Quiz');
    createCompletedVrAttempt($student, $bank, false, 20);
    createCompletedVrAttempt($student, $bank, false, 22);

    $sections = app(AdminAttentionDigestService::class)->buildSectionsByCategory();

    expect($sections)->toHaveKey('learning');
    expect(collect($sections['learning'])->pluck('title')->implode(' '))
        ->toContain('VR quiz fails')
        ->toContain('2+ fails');
});
