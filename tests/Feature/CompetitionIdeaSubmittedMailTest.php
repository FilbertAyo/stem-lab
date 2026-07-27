<?php

use App\Mail\CompetitionIdeaSubmittedMail;
use App\Models\CompetitionPhase;
use App\Models\SchoolClass;
use App\Services\CompetitionParticipantRegistrationService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

require_once __DIR__.'/../Helpers/CompetitionTestHelper.php';

test('final idea submit sends congratulation email to teacher', function () {
    Mail::fake();

    ['school' => $school, 'competition' => $competition] = createCompetitionFixture([
        'min_members' => 3,
        'representative_count' => 3,
    ]);
    ['region' => $region, 'district' => $district] = createRegionAndDistrict();

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
        'email' => 'teacher-idea@example.com',
        'name' => 'Idea Teacher',
    ]);

    $class = SchoolClass::query()->create([
        'name' => 'Form 1 '.Str::random(4),
        'is_active' => true,
    ]);

    $girls = collect([
        $teacherClub->students()->create([
            'name' => 'Amina',
            'gender' => 'female',
            'school_class_id' => $class->id,
            'age' => 14,
        ]),
        $teacherClub->students()->create([
            'name' => 'Neema',
            'gender' => 'female',
            'school_class_id' => $class->id,
            'age' => 14,
        ]),
        $teacherClub->students()->create([
            'name' => 'Fatuma',
            'gender' => 'female',
            'school_class_id' => $class->id,
            'age' => 15,
        ]),
    ]);

    $registration = app(CompetitionParticipantRegistrationService::class)
        ->ensureDraftRegistration($teacherClub, $competition);

    $registration->update([
        'status' => 'approved',
        'submitted_at' => now()->subDay(),
        'approved_at' => now()->subHour(),
        'teacher_email' => 'teacher-idea@example.com',
        'teacher_name' => 'Idea Teacher',
        'school_name' => $school->name,
        'eligibility_met' => true,
        'student_count_snapshot' => 3,
        'girls_count_snapshot' => 3,
        'girls_ratio_snapshot' => 100,
    ]);

    $response = $this->actingAs($teacherClub->user)
        ->post(route('client.competitions.idea.store', $competition), [
            'problem_title' => 'Clean water access for nearby villages',
            'problem_statement' => 'Many households lack reliable clean water.',
            'proposed_solution' => 'A low-cost filtration and monitoring prototype.',
            'stem_category' => 'Health & Biotechnology',
            'community_region_id' => $region->id,
            'community_district_id' => $district->id,
            'supporting_notes' => 'Pilot with three households.',
            'representative_student_ids' => $girls->pluck('id')->all(),
            'sync_representatives' => '1',
            'submit_final' => '1',
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('status');

    $idea = $registration->fresh()->idea;
    expect($idea)->not->toBeNull()
        ->and($idea->status)->toBe('submitted')
        ->and($idea->stem_category)->toBe('Health & Biotechnology')
        ->and((int) $idea->community_region_id)->toBe($region->id)
        ->and((int) $idea->community_district_id)->toBe($district->id)
        ->and($idea->community_location)->toBe($district->name);

    Mail::assertSent(CompetitionIdeaSubmittedMail::class, function (CompetitionIdeaSubmittedMail $mail) use ($registration, $competition) {
        return $mail->hasTo('teacher-idea@example.com')
            && $mail->registration->is($registration)
            && $mail->competition->is($competition)
            && str_contains($mail->envelope()->subject, 'Congratulations');
    });
});

test('submitted idea cannot be edited again', function () {
    Mail::fake();

    ['school' => $school, 'competition' => $competition] = createCompetitionFixture([
        'min_members' => 3,
        'representative_count' => 3,
    ]);

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
        'email' => 'teacher-idea-2@example.com',
    ]);

    $class = SchoolClass::query()->create([
        'name' => 'Form 2 '.Str::random(4),
        'is_active' => true,
    ]);

    $girls = collect([
        $teacherClub->students()->create(['name' => 'Amina', 'gender' => 'female', 'school_class_id' => $class->id, 'age' => 14]),
        $teacherClub->students()->create(['name' => 'Neema', 'gender' => 'female', 'school_class_id' => $class->id, 'age' => 14]),
        $teacherClub->students()->create(['name' => 'Fatuma', 'gender' => 'female', 'school_class_id' => $class->id, 'age' => 15]),
    ]);

    $registration = app(CompetitionParticipantRegistrationService::class)
        ->ensureDraftRegistration($teacherClub, $competition);

    $registration->update([
        'status' => 'approved',
        'submitted_at' => now()->subDay(),
        'approved_at' => now()->subHour(),
        'teacher_email' => 'teacher-idea-2@example.com',
        'eligibility_met' => true,
        'student_count_snapshot' => 3,
        'girls_count_snapshot' => 3,
        'girls_ratio_snapshot' => 100,
    ]);

    $payload = [
        'problem_title' => 'Solar lighting for evening study',
        'problem_statement' => 'Students lack lighting after dark.',
        'proposed_solution' => 'Shared solar lantern stations.',
        'stem_category' => 'Robotics',
        'representative_student_ids' => $girls->pluck('id')->all(),
        'sync_representatives' => '1',
        'submit_final' => '1',
    ];

    $this->actingAs($teacherClub->user)
        ->post(route('client.competitions.idea.store', $competition), $payload)
        ->assertRedirect();

    Mail::assertSent(CompetitionIdeaSubmittedMail::class, 1);

    $this->actingAs($teacherClub->user)
        ->post(route('client.competitions.idea.store', $competition), array_merge($payload, [
            'problem_title' => 'Updated solar lighting idea',
        ]))
        ->assertRedirect(route('client.competitions.show', $competition).'#idea-submission')
        ->assertSessionHas('error', 'Your idea has already been submitted and can no longer be edited.');

    expect($registration->fresh()->idea->problem_title)->toBe('Solar lighting for evening study');

    Mail::assertSent(CompetitionIdeaSubmittedMail::class, 1);
});
