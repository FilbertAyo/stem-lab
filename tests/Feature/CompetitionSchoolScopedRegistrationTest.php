<?php

use App\Models\CompetitionPhase;
use App\Models\CompetitionRegistration;
use App\Models\SchoolClass;
use App\Models\TeacherClub;
use App\Models\User;
use App\Services\CompetitionEligibilityPoolService;
use App\Services\CompetitionParticipantRegistrationService;
use App\Services\TeacherClubJoinService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

require_once __DIR__.'/../Helpers/CompetitionTestHelper.php';

test('second co-teacher join does not create duplicate school registration', function () {
    ['club' => $club, 'school' => $school, 'competition' => $competition] = createCompetitionFixture();

    $leader = createTeacherClubForCompetition($competition, $school);
    app(CompetitionParticipantRegistrationService::class)
        ->ensureDraftRegistration($leader, $competition);

    $coTeacher = User::factory()->create(['role' => 'teacher']);
    $join = app(TeacherClubJoinService::class)->join(
        $coTeacher,
        $club,
        Request::create('/', 'POST', ['school_id' => $school->id]),
        $competition,
    );

    expect($join['ok'])->toBeTrue();

    $count = CompetitionRegistration::query()
        ->where('competition_id', $competition->id)
        ->where('school_id', $school->id)
        ->count();

    expect($count)->toBe(1);
});

test('co-teacher cannot store registration when school already pending', function () {
    ['school' => $school, 'competition' => $competition] = createCompetitionFixture([
        'min_members' => 2,
    ]);

    $leaderClub = createTeacherClubForCompetition($competition, $school);
    $class = SchoolClass::query()->create(['name' => 'Form '.Str::random(4), 'is_active' => true]);

    $registration = app(CompetitionParticipantRegistrationService::class)
        ->ensureDraftRegistration($leaderClub, $competition);

    $leaderClub->students()->create(['name' => 'A', 'gender' => 'female', 'age' => 14, 'school_class_id' => $class->id]);
    $leaderClub->students()->create(['name' => 'B', 'gender' => 'female', 'age' => 14, 'school_class_id' => $class->id]);

    $registration->update([
        'status' => 'pending',
        'eligibility_met' => true,
        'student_count_snapshot' => 2,
        'girls_count_snapshot' => 2,
        'girls_ratio_snapshot' => 100,
        'submitted_at' => now(),
    ]);

    $coTeacher = User::factory()->create(['role' => 'teacher']);
    TeacherClub::query()->create([
        'user_id' => $coTeacher->id,
        'club_id' => $competition->club_id,
        'school_id' => $school->id,
        'status' => 'approved',
        'is_leader' => false,
    ]);

    $response = $this->actingAs($coTeacher)->post(
        route('client.competitions.register.store', $competition),
        [
            'school_name' => $school->name,
            'school_district_id' => $school->district_id,
            'school_ward' => null,
        ],
    );

    $response->assertRedirect(route('client.competitions.show', $competition));

    expect(
        CompetitionRegistration::query()
            ->where('competition_id', $competition->id)
            ->where('school_id', $school->id)
            ->count()
    )->toBe(1);
});

test('admin registrations index shows one row per school with multiple teachers', function () {
    ['school' => $school, 'competition' => $competition] = createCompetitionFixture();

    $leader = createTeacherClubForCompetition($competition, $school);
    $co = createTeacherClubForCompetition($competition, $school, [
        'email' => 'co.'.uniqid().'@school.test',
    ]);

    $service = app(CompetitionParticipantRegistrationService::class);
    $service->ensureDraftRegistration($leader, $competition);
    $service->ensureDraftRegistration($co, $competition);

    expect(
        CompetitionRegistration::query()
            ->where('competition_id', $competition->id)
            ->where('school_id', $school->id)
            ->count()
    )->toBe(1);

    $admin = User::factory()->create(['role' => 'admin']);
    $host = adminRequestHost();

    $response = $this->actingAs($admin)
        ->withServerVariables(['HTTP_HOST' => $host])
        ->get('http://'.$host.'/competitions/'.$competition->ulid.'/registrations');

    $response->assertOk();
    $response->assertSee($school->name);
    // School name should appear once in the table body (plus maybe elsewhere).
    expect(substr_count($response->getContent(), '>'.$school->name.'<'))->toBeLessThanOrEqual(2);
});

test('eligibility pool includes co-member teacher students', function () {
    ['school' => $school, 'competition' => $competition] = createCompetitionFixture([
        'min_members' => 2,
    ]);

    $leader = createTeacherClubForCompetition($competition, $school);
    $co = createTeacherClubForCompetition($competition, $school, [
        'email' => 'poolco.'.uniqid().'@school.test',
    ]);
    $co->update(['status' => 'approved', 'is_leader' => false]);

    $class = SchoolClass::query()->create(['name' => 'Shared '.Str::random(4), 'is_active' => true]);

    $leader->students()->create(['name' => 'Leader Student', 'gender' => 'female', 'age' => 14, 'school_class_id' => $class->id]);
    $co->students()->create(['name' => 'Co Student', 'gender' => 'female', 'age' => 15, 'school_class_id' => $class->id]);

    $pool = app(CompetitionEligibilityPoolService::class)->computePool($competition, $leader);

    expect($pool['count'])->toBe(2)
        ->and($pool['girls_count'])->toBe(2)
        ->and($pool['eligible'])->toBeTrue();
});

test('co-teacher index sees school registration status', function () {
    ['school' => $school, 'competition' => $competition] = createCompetitionFixture();

    $leader = createTeacherClubForCompetition($competition, $school);
    $registration = app(CompetitionParticipantRegistrationService::class)
        ->ensureDraftRegistration($leader, $competition);
    $registration->update(['status' => 'pending', 'submitted_at' => now(), 'eligibility_met' => true]);

    $coTeacher = User::factory()->create(['role' => 'teacher']);
    TeacherClub::query()->create([
        'user_id' => $coTeacher->id,
        'club_id' => $competition->club_id,
        'school_id' => $school->id,
        'status' => 'approved',
        'is_leader' => false,
    ]);

    $response = $this->actingAs($coTeacher)->get(route('client.competitions.index'));

    $response->assertOk();
    // Pending badge / registration surfaced for school-scoped lookup
    expect(
        app(CompetitionParticipantRegistrationService::class)
            ->registrationsForTeacher(...$coTeacher->teacherClubs()->get()->all())
            ->get($competition->id)
            ?->status
    )->toBe('pending');
});

test('co-teacher sees approved not not-registered after admin eligibility override', function () {
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

    $leader = createTeacherClubForCompetition($competition, $school);
    $registration = app(CompetitionParticipantRegistrationService::class)
        ->ensureDraftRegistration($leader, $competition);

    $registration->update([
        'status' => 'approved',
        'submitted_at' => now()->subDay(),
        'approved_at' => now()->subHour(),
        'eligibility_met' => false,
        'teacher_user_id' => $leader->user_id,
        'teacher_club_id' => $leader->id,
        'school_id' => $school->id,
    ]);

    $coTeacher = User::factory()->create(['role' => 'teacher']);
    TeacherClub::query()->create([
        'user_id' => $coTeacher->id,
        'club_id' => $competition->club_id,
        'school_id' => $school->id,
        'status' => 'approved',
        'is_leader' => false,
    ]);

    $index = $this->actingAs($coTeacher)->get(route('client.competitions.index'));
    $index->assertOk();
    $index->assertDontSee('Not registered');
    $index->assertSee('Approved');

    $show = $this->actingAs($coTeacher)->get(route('client.competitions.show', $competition));
    $show->assertOk();
    $show->assertSee('Problem title');
    $show->assertSee('Save draft');
    $show->assertDontSee('Complete and submit your participant registration');
});

test('stale draft does not hide an approved school registration for the same teacher', function () {
    ['school' => $school, 'competition' => $competition] = createCompetitionFixture();

    $otherSchool = \App\Models\School::query()->create([
        'name' => 'Other School '.Str::random(6),
        'is_active' => true,
    ]);

    $teacherClub = createTeacherClubForCompetition($competition, $school);
    $service = app(CompetitionParticipantRegistrationService::class);

    $approved = $service->ensureDraftRegistration($teacherClub, $competition);
    $approved->update([
        'status' => 'approved',
        'submitted_at' => now(),
        'approved_at' => now(),
        'eligibility_met' => false,
        'school_id' => $school->id,
    ]);

    CompetitionRegistration::query()->create([
        'competition_id' => $competition->id,
        'teacher_club_id' => $teacherClub->id,
        'teacher_user_id' => $teacherClub->user_id,
        'school_id' => $otherSchool->id,
        'status' => 'draft',
        'teacher_name' => 'Stale Draft',
        'teacher_email' => 'stale@example.com',
        'school_name' => $otherSchool->name,
        'eligibility_met' => false,
        'student_count_snapshot' => 0,
        'girls_count_snapshot' => 0,
        'girls_ratio_snapshot' => 0,
    ]);

    expect($service->findForTeacherClub($competition, $teacherClub)?->status)->toBe('approved')
        ->and($service->registrationsForTeacher($teacherClub)->get($competition->id)?->status)->toBe('approved');

    $this->actingAs($teacherClub->user)
        ->get(route('client.competitions.index'))
        ->assertOk()
        ->assertSee('Approved')
        ->assertDontSee('Not registered');
});
