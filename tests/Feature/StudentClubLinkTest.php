<?php

use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\TeacherClub;
use App\Models\User;
use App\Services\StudentClubLinkService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

require_once __DIR__.'/../Helpers/CompetitionTestHelper.php';

function createOnboardedStudent(array $overrides = []): User
{
    return User::factory()->create(array_merge([
        'role' => 'student',
        'gender' => 'female',
        'date_of_birth' => '2011-05-20',
        'student_onboarding_completed_at' => now(),
    ], $overrides));
}

function createRosterStudent(TeacherClub $teacherClub, array $overrides = []): Student
{
    $schoolClass = SchoolClass::query()->create([
        'name' => 'Form 2 '.Str::random(6),
        'is_active' => true,
    ]);

    return $teacherClub->students()->create(array_merge([
        'name' => 'Amina Hassan',
        'gender' => 'female',
        'age' => 14,
        'date_of_birth' => '2011-05-20',
        'school_class_id' => $schoolClass->id,
    ], $overrides));
}

test('student can look up club with valid admission number and club code', function () {
    ['school' => $school, 'competition' => $competition] = createCompetitionFixture();
    $teacherClub = createTeacherClubForCompetition($competition, $school);
    $rosterStudent = createRosterStudent($teacherClub);
    $studentUser = createOnboardedStudent();

    $response = $this->actingAs($studentUser)->post(route('client.student-club.lookup'), [
        'admission_number' => $rosterStudent->admission_number,
        'club_code' => $teacherClub->club_code,
    ]);

    $response->assertRedirect(route('client.student-club.confirm'));
    $response->assertSessionHas('student_club_pending_link');
});

test('lookup fails with wrong club code and shows generic error', function () {
    ['school' => $school, 'competition' => $competition] = createCompetitionFixture();
    $teacherClub = createTeacherClubForCompetition($competition, $school);
    $rosterStudent = createRosterStudent($teacherClub);
    $studentUser = createOnboardedStudent();

    $response = $this->actingAs($studentUser)->post(route('client.student-club.lookup'), [
        'admission_number' => $rosterStudent->admission_number,
        'club_code' => 'ZZZZZZ',
    ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors('admission_number');
    expect(session('errors')->get('admission_number')[0])
        ->toBe(StudentClubLinkService::GENERIC_NOT_FOUND_MESSAGE);
});

test('lookup fails when teacher club is pending', function () {
    ['school' => $school, 'competition' => $competition] = createCompetitionFixture();
    $teacherClub = createTeacherClubForCompetition($competition, $school);
    $teacherClub->update(['status' => 'pending']);
    $rosterStudent = createRosterStudent($teacherClub);
    $studentUser = createOnboardedStudent();

    $response = $this->actingAs($studentUser)->post(route('client.student-club.lookup'), [
        'admission_number' => $rosterStudent->admission_number,
        'club_code' => $teacherClub->club_code,
    ]);

    $response->assertSessionHasErrors('admission_number');
});

test('student can link account and receive public school subscription tier', function () {
    ['school' => $school, 'competition' => $competition] = createCompetitionFixture();
    $teacherClub = createTeacherClubForCompetition($competition, $school);
    $rosterStudent = createRosterStudent($teacherClub);
    $studentUser = createOnboardedStudent();

    $this->actingAs($studentUser)->post(route('client.student-club.lookup'), [
        'admission_number' => $rosterStudent->admission_number,
        'club_code' => $teacherClub->club_code,
    ]);

    $response = $this->actingAs($studentUser)->post(route('client.student-club.link'));

    $response->assertRedirect(route('client.student-club.index'));
    $response->assertSessionHas('status');

    $rosterStudent->refresh();
    $studentUser->refresh();

    expect($rosterStudent->user_id)->toBe($studentUser->id)
        ->and($rosterStudent->linked_at)->not->toBeNull()
        ->and($studentUser->subscription_tier)->toBe('public_school')
        ->and($studentUser->club_linked_at)->not->toBeNull()
        ->and($studentUser->hasPublicSchoolAccess())->toBeTrue();
});

test('link prevents double linking for roster student', function () {
    ['school' => $school, 'competition' => $competition] = createCompetitionFixture();
    $teacherClub = createTeacherClubForCompetition($competition, $school);
    $rosterStudent = createRosterStudent($teacherClub);

    $firstStudent = createOnboardedStudent(['email' => 'first@example.com']);
    $secondStudent = createOnboardedStudent(['email' => 'second@example.com']);

    app(StudentClubLinkService::class)->link(
        $firstStudent,
        $rosterStudent->admission_number,
        $teacherClub->club_code,
    );

    expect(fn () => app(StudentClubLinkService::class)->link(
        $secondStudent,
        $rosterStudent->admission_number,
        $teacherClub->club_code,
    ))->toThrow(\Illuminate\Validation\ValidationException::class);
});

test('link prevents user from linking twice', function () {
    ['school' => $school, 'competition' => $competition] = createCompetitionFixture();
    $teacherClub = createTeacherClubForCompetition($competition, $school);
    $rosterStudent = createRosterStudent($teacherClub);
    $studentUser = createOnboardedStudent();

    app(StudentClubLinkService::class)->link(
        $studentUser,
        $rosterStudent->admission_number,
        $teacherClub->club_code,
    );

    $otherClub = createTeacherClubForCompetition($competition, $school);
    $otherRosterStudent = createRosterStudent($otherClub);

    expect(fn () => app(StudentClubLinkService::class)->link(
        $studentUser,
        $otherRosterStudent->admission_number,
        $otherClub->club_code,
    ))->toThrow(\Illuminate\Validation\ValidationException::class);
});

test('student club routes return forbidden for teachers', function () {
    ['school' => $school, 'competition' => $competition] = createCompetitionFixture();
    $teacherClub = createTeacherClubForCompetition($competition, $school);
    $teacher = $teacherClub->user;

    $this->actingAs($teacher)->get(route('client.student-club.index'))->assertForbidden();
    $this->actingAs($teacher)->post(route('client.student-club.lookup'), [
        'admission_number' => 'AAAAAA',
        'club_code' => $teacherClub->club_code,
    ])->assertForbidden();
});

test('linked student sees read only club details on my club page', function () {
    ['school' => $school, 'competition' => $competition] = createCompetitionFixture();
    $teacherClub = createTeacherClubForCompetition($competition, $school);
    $rosterStudent = createRosterStudent($teacherClub);
    $studentUser = createOnboardedStudent();

    app(StudentClubLinkService::class)->link(
        $studentUser,
        $rosterStudent->admission_number,
        $teacherClub->club_code,
    );

    $response = $this->actingAs($studentUser)->get(route('client.student-club.index'));

    $response->assertOk();
    $response->assertSee('Public secondary school — free access');
    $response->assertSee($competition->club->name);
    $response->assertSee($school->name);
    $response->assertSee($teacherClub->user->name);
    $response->assertSee('Club management is handled by your teacher');
    $response->assertDontSee('Find my club');
});

test('unlinked student sees lookup form on my club page', function () {
    $studentUser = createOnboardedStudent();

    $response = $this->actingAs($studentUser)->get(route('client.student-club.index'));

    $response->assertOk();
    $response->assertSee('Find my club');
    $response->assertSee('Admission number');
    $response->assertSee('Club code');
});
