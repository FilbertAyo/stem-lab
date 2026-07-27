<?php

use App\Models\CompetitionPhase;
use App\Models\SchoolClass;
use App\Services\CompetitionParticipantRegistrationService;
use Illuminate\Support\Str;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

require_once __DIR__.'/../Helpers/CompetitionTestHelper.php';

test('admin-overridden registration can submit idea without meeting min members pool', function () {
    ['school' => $school, 'competition' => $competition] = createCompetitionFixture([
        'min_members' => 75,
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
        'email' => 'override-idea@example.com',
        'name' => 'Override Teacher',
    ]);

    $class = SchoolClass::query()->create([
        'name' => 'Form 1 '.Str::random(4),
        'is_active' => true,
    ]);

    // Below competition min_members (75), as expected for admin-override schools.
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
        'teacher_email' => 'override-idea@example.com',
        'teacher_name' => 'Override Teacher',
        'school_name' => $school->name,
        'eligibility_met' => false,
        'student_count_snapshot' => 3,
        'girls_count_snapshot' => 3,
        'girls_ratio_snapshot' => 100,
    ]);

    expect($registration->passedViaEligibilityOverride())->toBeTrue();

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
    $response->assertSessionMissing('errors');

    $idea = $registration->fresh()->idea;
    expect($idea)->not->toBeNull()
        ->and($idea->status)->toBe('submitted');
});

test('approved registration that met eligibility still blocked when pool drops below min members', function () {
    ['school' => $school, 'competition' => $competition] = createCompetitionFixture([
        'min_members' => 75,
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

    $teacherClub = createTeacherClubForCompetition($competition, $school);

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
        'eligibility_met' => true,
        'student_count_snapshot' => 75,
        'girls_count_snapshot' => 53,
        'girls_ratio_snapshot' => 70.67,
    ]);

    $response = $this->actingAs($teacherClub->user)
        ->post(route('client.competitions.idea.store', $competition), [
            'problem_title' => 'Clean water access',
            'problem_statement' => 'Problem statement',
            'proposed_solution' => 'Proposed solution',
            'stem_category' => 'Science',
            'community_region_id' => $region->id,
            'community_district_id' => $district->id,
            'representative_student_ids' => $girls->pluck('id')->all(),
            'sync_representatives' => '1',
            'submit_final' => '1',
        ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors('representative_student_ids');
    expect(session('errors')->first('representative_student_ids'))
        ->toContain('Eligibility gate failed')
        ->toContain('at least 75 students');
});
