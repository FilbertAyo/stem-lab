<?php

use App\Mail\CompetitionIdeaSubmissionReminderMail;
use App\Models\CompetitionIdea;
use App\Models\CompetitionRegistration;
use App\Models\User;
use App\Services\CompetitionParticipantRegistrationService;
use Illuminate\Support\Facades\Mail;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

require_once __DIR__.'/../Helpers/CompetitionTestHelper.php';

function createApprovedRegistrationWithoutIdea(): array
{
    ['school' => $school, 'competition' => $competition] = createCompetitionFixture();
    $teacherClub = createTeacherClubForCompetition($competition, $school);

    $registration = app(CompetitionParticipantRegistrationService::class)
        ->ensureDraftRegistration($teacherClub, $competition);

    $registration->update([
        'status' => 'approved',
        'submitted_at' => now(),
        'approved_at' => now(),
        'eligibility_met' => true,
    ]);

    return compact('school', 'competition', 'teacherClub', 'registration');
}

test('ideas index shows submitted and pending tabs', function () {
    ['competition' => $competition, 'registration' => $registration] = createApprovedRegistrationWithoutIdea();

    CompetitionIdea::query()->create([
        'competition_id' => $competition->id,
        'competition_registration_id' => $registration->id,
        'status' => 'draft',
        'problem_title' => 'Draft only',
    ]);

    $admin = User::factory()->create(['role' => 'admin']);
    $host = adminRequestHost();

    $submitted = $this->actingAs($admin)
        ->withServerVariables(['HTTP_HOST' => $host])
        ->get('http://'.$host.'/competitions/'.$competition->ulid.'/ideas');

    $submitted->assertOk();
    $submitted->assertSee('Submitted ideas');
    $submitted->assertSee('Not submitted');
    $submitted->assertDontSee('Draft only');

    $pending = $this->actingAs($admin)
        ->withServerVariables(['HTTP_HOST' => $host])
        ->get('http://'.$host.'/competitions/'.$competition->ulid.'/ideas?tab=pending');

    $pending->assertOk();
    $pending->assertSee($registration->resolvedSchoolName());
    $pending->assertSee('Draft saved');
});

test('admin can send idea submission reminder for approved school without idea', function () {
    Mail::fake();

    ['competition' => $competition, 'registration' => $registration] = createApprovedRegistrationWithoutIdea();

    $admin = User::factory()->create(['role' => 'admin']);
    $host = adminRequestHost();

    $response = $this->actingAs($admin)
        ->withServerVariables(['HTTP_HOST' => $host])
        ->post(
            'http://'.$host.'/competitions/'.$competition->ulid.'/ideas/pending/'.$registration->ulid.'/reminder',
            [
                'send_sms' => '0',
                'sms_message' => 'unused',
                'sms_sender_id' => 'ADILISHA',
            ]
        );

    $response->assertRedirect();
    $response->assertSessionHas('status');

    $registration->refresh();
    expect($registration->idea_submission_reminder_count)->toBe(1)
        ->and($registration->last_idea_submission_reminder_sent_at)->not->toBeNull();

    $clientBase = rtrim((string) config('app.url'), '/');
    $adminHost = parse_url((string) config('app.admin_url'), PHP_URL_HOST);

    Mail::assertSent(CompetitionIdeaSubmissionReminderMail::class, function (CompetitionIdeaSubmissionReminderMail $mail) use ($registration, $clientBase, $adminHost, $competition) {
        return $mail->registration->is($registration)
            && str_starts_with($mail->competitionUrl, $clientBase.'/competitions/'.$competition->ulid)
            && str_contains($mail->competitionUrl, '#idea-submission')
            && ! str_contains($mail->competitionUrl, (string) $adminHost);
    });
});

test('idea submission reminder is blocked after max sends', function () {
    Mail::fake();

    ['competition' => $competition, 'registration' => $registration] = createApprovedRegistrationWithoutIdea();

    $max = CompetitionRegistration::maxIdeaSubmissionReminders();
    $registration->update([
        'idea_submission_reminder_count' => $max,
        'last_idea_submission_reminder_sent_at' => now(),
    ]);

    $admin = User::factory()->create(['role' => 'admin']);
    $host = adminRequestHost();

    $response = $this->actingAs($admin)
        ->withServerVariables(['HTTP_HOST' => $host])
        ->post(
            'http://'.$host.'/competitions/'.$competition->ulid.'/ideas/pending/'.$registration->ulid.'/reminder',
            [
                'send_sms' => '0',
                'sms_message' => 'unused',
                'sms_sender_id' => 'ADILISHA',
            ]
        );

    $response->assertRedirect();
    $response->assertSessionHas('error');

    Mail::assertNothingSent();
});

test('idea show page does not show the pending schools banner', function () {
    ['school' => $school, 'competition' => $competition] = createCompetitionFixture();
    $submittedClub = createTeacherClubForCompetition($competition, $school);
    $submittedRegistration = app(CompetitionParticipantRegistrationService::class)
        ->ensureDraftRegistration($submittedClub, $competition);
    $submittedRegistration->update([
        'status' => 'approved',
        'submitted_at' => now(),
        'approved_at' => now(),
        'eligibility_met' => true,
    ]);

    $idea = CompetitionIdea::query()->create([
        'competition_id' => $competition->id,
        'competition_registration_id' => $submittedRegistration->id,
        'status' => 'submitted',
        'problem_title' => 'Submitted idea',
        'submitted_at' => now(),
    ]);

    $pendingSchool = \App\Models\School::query()->create([
        'name' => 'Pending School '.uniqid(),
        'is_active' => true,
    ]);
    $pendingClub = createTeacherClubForCompetition($competition, $pendingSchool);
    $pendingRegistration = app(CompetitionParticipantRegistrationService::class)
        ->ensureDraftRegistration($pendingClub, $competition);
    $pendingRegistration->update([
        'status' => 'approved',
        'submitted_at' => now(),
        'approved_at' => now(),
        'eligibility_met' => true,
    ]);

    $admin = User::factory()->create(['role' => 'admin']);
    $host = adminRequestHost();

    $response = $this->actingAs($admin)
        ->withServerVariables(['HTTP_HOST' => $host])
        ->get('http://'.$host.'/competitions/'.$competition->ulid.'/ideas/'.$idea->ulid);

    $response->assertOk();
    $response->assertDontSee('not submitted an idea yet');
    $response->assertDontSee('View & remind');
});
