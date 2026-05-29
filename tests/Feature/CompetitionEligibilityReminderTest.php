<?php

use App\Mail\CompetitionEligibilityReminderMail;
use App\Models\CompetitionRegistration;
use App\Models\User;
use App\Services\CompetitionParticipantRegistrationService;
use Illuminate\Support\Facades\Mail;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

require_once __DIR__.'/../Helpers/CompetitionTestHelper.php';

test('admin can send eligibility reminder for draft registration', function () {
    Mail::fake();

    ['school' => $school, 'competition' => $competition] = createCompetitionFixture();
    $teacherClub = createTeacherClubForCompetition($competition, $school);

    $registration = app(CompetitionParticipantRegistrationService::class)
        ->ensureDraftRegistration($teacherClub, $competition);

    $admin = User::factory()->create(['role' => 'admin']);
    $host = adminRequestHost();

    $response = $this->actingAs($admin)
        ->withServerVariables(['HTTP_HOST' => $host])
        ->post(
            'http://'.$host.'/competitions/'.$competition->ulid.'/registrations/'.$registration->ulid.'/eligibility-reminder'
        );

    $response->assertRedirect();
    $response->assertSessionHas('status');

    $registration->refresh();
    expect($registration->eligibility_reminder_count)->toBe(1)
        ->and($registration->last_eligibility_reminder_sent_at)->not->toBeNull();

    $clientBase = rtrim((string) config('app.url'), '/');
    $adminHost = parse_url((string) config('app.admin_url'), PHP_URL_HOST);

    Mail::assertSent(CompetitionEligibilityReminderMail::class, function (CompetitionEligibilityReminderMail $mail) use ($registration, $clientBase, $adminHost, $competition) {
        return $mail->registration->is($registration)
            && str_starts_with($mail->registerUrl, $clientBase.'/competitions/'.$competition->ulid.'/register')
            && ! str_contains($mail->registerUrl, (string) $adminHost);
    });
});

test('eligibility reminder is blocked after max sends', function () {
    Mail::fake();

    ['school' => $school, 'competition' => $competition] = createCompetitionFixture();
    $teacherClub = createTeacherClubForCompetition($competition, $school);

    $registration = app(CompetitionParticipantRegistrationService::class)
        ->ensureDraftRegistration($teacherClub, $competition);

    $max = CompetitionRegistration::maxEligibilityReminders();
    $registration->update([
        'eligibility_reminder_count' => $max,
        'last_eligibility_reminder_sent_at' => now(),
    ]);

    $admin = User::factory()->create(['role' => 'admin']);
    $host = adminRequestHost();

    $response = $this->actingAs($admin)
        ->withServerVariables(['HTTP_HOST' => $host])
        ->post(
            'http://'.$host.'/competitions/'.$competition->ulid.'/registrations/'.$registration->ulid.'/eligibility-reminder'
        );

    $response->assertRedirect();
    $response->assertSessionHas('error');

    Mail::assertNothingSent();
});
