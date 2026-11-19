<?php

use App\Mail\CompetitionIdeaShortlistedMail;
use App\Models\CompetitionIdea;
use App\Models\CompetitionShortlist;
use App\Models\User;
use App\Services\CompetitionParticipantRegistrationService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

require_once __DIR__.'/../Helpers/CompetitionTestHelper.php';

function shortlistFixture(): array
{
    ['school' => $school, 'competition' => $competition] = createCompetitionFixture();
    $teacherClub = createTeacherClubForCompetition($competition, $school, [
        'email' => 'teacher-shortlist@example.com',
        'name' => 'Shortlist Teacher',
    ]);

    $registration = app(CompetitionParticipantRegistrationService::class)
        ->ensureDraftRegistration($teacherClub, $competition);

    $registration->update([
        'status' => 'approved',
        'teacher_email' => 'teacher-shortlist@example.com',
        'teacher_name' => 'Shortlist Teacher',
        'teacher_phone' => '0712345678',
        'school_name' => $school->name,
    ]);

    $idea = CompetitionIdea::query()->create([
        'competition_id' => $competition->id,
        'competition_registration_id' => $registration->id,
        'status' => 'submitted',
        'problem_title' => 'Biodegradable bags',
        'problem_statement' => 'Plastic bags remain in the soil.',
        'proposed_solution' => 'Bags made from root and tuber waste.',
        'stem_category' => 'Environment',
        'submitted_at' => now(),
    ]);

    $admin = User::factory()->create(['role' => 'admin']);

    return compact('competition', 'registration', 'idea', 'admin');
}

function postShortlist($test, array $fixture, array $payload)
{
    $host = adminRequestHost();

    return $test->actingAs($fixture['admin'])
        ->withServerVariables(['HTTP_HOST' => $host])
        ->post(
            'http://'.$host.'/competitions/'.$fixture['competition']->ulid.'/ideas/'.$fixture['idea']->ulid.'/shortlist',
            $payload
        );
}

test('first shortlist sends email and sms once and a later save does not', function () {
    Mail::fake();
    Http::fake([
        'https://sms.soscom.co.tz/*' => Http::response(['ok' => true], 200),
    ]);
    config([
        'sms.enabled' => true,
        'sms.api_key' => 'test-key',
        'sms.base_url' => 'https://sms.soscom.co.tz/api/v1',
    ]);

    $fixture = shortlistFixture();
    $message = 'Hi teacher, you are shortlisted. Continue: '.$fixture['competition']->smsUrl();

    $first = postShortlist($this, $fixture, [
        'status' => 'shortlisted',
        'send_sms' => '1',
        'sms_message' => $message,
        'sms_sender_id' => 'ADILISHA',
    ]);

    $first->assertRedirect();
    $first->assertSessionHas('status');

    $shortlist = CompetitionShortlist::query()->where('competition_idea_id', $fixture['idea']->id)->first();
    expect($shortlist)->not->toBeNull()
        ->and($shortlist->notified_at)->not->toBeNull()
        ->and($fixture['idea']->fresh()->status)->toBe('shortlisted');

    Mail::assertSent(CompetitionIdeaShortlistedMail::class, 1);
    Http::assertSentCount(1);

    $second = postShortlist($this, $fixture, [
        'status' => 'shortlisted',
        'rank' => 2,
        'send_sms' => '1',
        'sms_message' => $message,
        'sms_sender_id' => 'ADILISHA',
    ]);

    $second->assertRedirect();
    $second->assertSessionHas('status');
    Mail::assertSent(CompetitionIdeaShortlistedMail::class, 1);
    Http::assertSentCount(1);
    expect($shortlist->fresh()->reminder_count)->toBe(0);

    $rejected = postShortlist($this, $fixture, [
        'status' => 'rejected',
        'send_sms' => '1',
        'sms_message' => $message,
        'sms_sender_id' => 'ADILISHA',
    ]);
    $rejected->assertRedirect();

    $again = postShortlist($this, $fixture, [
        'status' => 'shortlisted',
        'send_sms' => '1',
        'sms_message' => $message,
        'sms_sender_id' => 'ADILISHA',
    ]);
    $again->assertRedirect();
    Mail::assertSent(CompetitionIdeaShortlistedMail::class, 1);
    Http::assertSentCount(1);
});

test('shortlist reminder is the explicit second send', function () {
    Mail::fake();
    config([
        'sms.enabled' => false,
        'sms.api_key' => null,
    ]);

    $fixture = shortlistFixture();

    postShortlist($this, $fixture, [
        'status' => 'shortlisted',
        'send_sms' => '0',
        'sms_message' => 'unused',
        'sms_sender_id' => 'ADILISHA',
    ])->assertRedirect();

    Mail::assertSent(CompetitionIdeaShortlistedMail::class, 1);

    $host = adminRequestHost();
    $reminder = $this->actingAs($fixture['admin'])
        ->withServerVariables(['HTTP_HOST' => $host])
        ->post(
            'http://'.$host.'/competitions/'.$fixture['competition']->ulid.'/ideas/'.$fixture['idea']->ulid.'/shortlist-reminder',
            [
                'send_sms' => '0',
                'sms_message' => 'Reminder text',
                'sms_sender_id' => 'ADILISHA',
            ]
        );

    $reminder->assertRedirect();
    $reminder->assertSessionHas('status');
    Mail::assertSent(CompetitionIdeaShortlistedMail::class, function (CompetitionIdeaShortlistedMail $mail) {
        return $mail->isReminder === true;
    });
    Mail::assertSent(CompetitionIdeaShortlistedMail::class, 2);

    $shortlist = CompetitionShortlist::query()->where('competition_idea_id', $fixture['idea']->id)->first();
    expect($shortlist->reminder_count)->toBe(1)
        ->and($shortlist->last_reminder_sent_at)->not->toBeNull();
});

test('shortlist still emails when the teacher phone is missing', function () {
    Mail::fake();
    config(['sms.enabled' => true, 'sms.api_key' => 'test-key']);

    $fixture = shortlistFixture();
    $fixture['registration']->update(['teacher_phone' => null]);

    $response = postShortlist($this, $fixture, [
        'status' => 'shortlisted',
        'send_sms' => '1',
        'sms_message' => 'Hello from shortlist',
        'sms_sender_id' => 'ADILISHA',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('status');
    Mail::assertSent(CompetitionIdeaShortlistedMail::class, 1);

    $shortlist = CompetitionShortlist::query()->where('competition_idea_id', $fixture['idea']->id)->first();
    expect($shortlist->notified_at)->not->toBeNull();
    expect(session('status'))->not->toContain('email was skipped')
        ->and(session('status'))->toContain('SMS skipped');
});

test('registration page shows the sms credit sentence before send', function () {
    ['school' => $school, 'competition' => $competition] = createCompetitionFixture();
    $teacherClub = createTeacherClubForCompetition($competition, $school);
    $registration = app(CompetitionParticipantRegistrationService::class)
        ->ensureDraftRegistration($teacherClub, $competition);
    $registration->update(['teacher_phone' => '0712345678']);

    $admin = User::factory()->create(['role' => 'admin']);
    $host = adminRequestHost();

    $response = $this->actingAs($admin)
        ->withServerVariables(['HTTP_HOST' => $host])
        ->get('http://'.$host.'/competitions/'.$competition->ulid.'/registrations/'.$registration->ulid);

    $response->assertOk();
    $response->assertSee('Credits to be deducted', false);
    $response->assertSee('data-sms-credit-root', false);
});
