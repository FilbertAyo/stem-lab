<?php

use App\Mail\CompetitionRegistrationApprovedMail;
use App\Models\CompetitionPhase;
use App\Models\User;
use App\Services\CompetitionParticipantRegistrationService;
use Illuminate\Support\Facades\Mail;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

require_once __DIR__.'/../Helpers/CompetitionTestHelper.php';

test('admin approve registration sends congratulation email to teacher', function () {
    Mail::fake();

    ['school' => $school, 'competition' => $competition] = createCompetitionFixture();
    CompetitionPhase::query()->create([
        'competition_id' => $competition->id,
        'phase_key' => 'idea_submission',
        'name' => 'Idea submission',
        'sequence' => 2,
        'status' => 'open',
        'opens_at' => now()->subDay(),
        'closes_at' => now()->addMonth(),
        'is_required_action' => true,
        'action_type' => 'idea',
    ]);

    $teacherClub = createTeacherClubForCompetition($competition, $school, [
        'email' => 'teacher-approve@example.com',
        'name' => 'Amina Teacher',
    ]);

    $registration = app(CompetitionParticipantRegistrationService::class)
        ->ensureDraftRegistration($teacherClub, $competition);

    $registration->update([
        'status' => 'pending',
        'submitted_at' => now(),
        'teacher_email' => 'teacher-approve@example.com',
        'teacher_name' => 'Amina Teacher',
        'school_name' => $school->name,
    ]);

    $admin = User::factory()->create(['role' => 'admin']);
    $host = adminRequestHost();

    $response = $this->actingAs($admin)
        ->withServerVariables(['HTTP_HOST' => $host])
        ->post(
            'http://'.$host.'/competitions/'.$competition->ulid.'/registrations/'.$registration->ulid.'/approve'
        );

    $response->assertRedirect();
    $response->assertSessionHas('status');

    $registration->refresh();
    expect($registration->status)->toBe('approved')
        ->and($registration->approved_at)->not->toBeNull();

    $clientBase = rtrim((string) config('app.url'), '/');

    Mail::assertSent(CompetitionRegistrationApprovedMail::class, function (CompetitionRegistrationApprovedMail $mail) use ($registration, $clientBase, $competition) {
        return $mail->hasTo('teacher-approve@example.com')
            && $mail->registration->is($registration)
            && str_starts_with($mail->competitionUrl, $clientBase.'/competitions/'.$competition->ulid);
    });
});
