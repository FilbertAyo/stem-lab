<?php

use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\TeacherClub;
use App\Models\User;
use App\Notifications\StudentJoinedClubNotification;
use App\Services\Learning\StudentProgressSummaryService;
use App\Services\StudentClubLinkService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

require_once __DIR__.'/../Helpers/CompetitionTestHelper.php';

function createTeacherUser(): User
{
    return User::factory()->create(['role' => 'teacher']);
}

function createRosterStudentForHub(TeacherClub $teacherClub, array $overrides = []): Student
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

test('teacher can view my students index for their club roster', function () {
    ['school' => $school, 'competition' => $competition] = createCompetitionFixture();
    $teacherClub = createTeacherClubForCompetition($competition, $school);
    $teacher = $teacherClub->user;
    createRosterStudentForHub($teacherClub);

    $response = $this->actingAs($teacher)->get(route('client.my-students.index'));

    $response->assertOk();
    $response->assertSee('My Students');
    $response->assertSee('Amina Hassan');
});

test('teacher cannot view student progress from another school club', function () {
    ['school' => $schoolA, 'competition' => $competitionA] = createCompetitionFixture();
    $teacherClubA = createTeacherClubForCompetition($competitionA, $schoolA);
    $teacherA = $teacherClubA->user;

    ['school' => $schoolB, 'competition' => $competitionB] = createCompetitionFixture();
    $teacherClubB = createTeacherClubForCompetition($competitionB, $schoolB);
    $studentB = createRosterStudentForHub($teacherClubB);

    $this->actingAs($teacherA)
        ->get(route('client.my-students.show', $studentB))
        ->assertForbidden();
});

test('progress summary is recomputed from learning activity data', function () {
    ['school' => $school, 'competition' => $competition] = createCompetitionFixture();
    $teacherClub = createTeacherClubForCompetition($competition, $school);
    $studentUser = User::factory()->create([
        'role' => 'student',
        'gender' => 'female',
        'date_of_birth' => '2011-05-20',
        'student_onboarding_completed_at' => now(),
        'subscription_tier' => 'public_school',
        'club_linked_at' => now(),
    ]);
    $student = createRosterStudentForHub($teacherClub, [
        'user_id' => $studentUser->id,
        'linked_at' => now(),
    ]);

    $summary = app(StudentProgressSummaryService::class)->recomputeForStudent($student);

    expect($summary->student_id)->toBe($student->id);
    expect($summary->attention_flags)->toContain('inactive');
});

test('student link notifies co-teachers in app and mail channels', function () {
    Notification::fake();

    ['school' => $school, 'competition' => $competition] = createCompetitionFixture();
    $teacherClub = createTeacherClubForCompetition($competition, $school);
    $coTeacher = User::factory()->create(['role' => 'teacher']);
    TeacherClub::query()->create([
        'user_id' => $coTeacher->id,
        'club_id' => $teacherClub->club_id,
        'school_id' => $school->id,
        'status' => 'approved',
        'is_leader' => false,
    ]);

    $rosterStudent = createRosterStudentForHub($teacherClub);
    $studentUser = User::factory()->create([
        'role' => 'student',
        'gender' => 'female',
        'date_of_birth' => '2011-05-20',
        'student_onboarding_completed_at' => now(),
    ]);

    $this->actingAs($studentUser)->post(route('client.student-club.lookup'), [
        'admission_number' => $rosterStudent->admission_number,
        'club_code' => $teacherClub->club_code,
    ]);

    $this->actingAs($studentUser)->post(route('client.student-club.link'));

    Notification::assertSentTo(
        $teacherClub->fresh()->user,
        StudentJoinedClubNotification::class,
    );
    Notification::assertSentTo(
        $coTeacher,
        StudentJoinedClubNotification::class,
    );
});

test('my students filters registered students only', function () {
    ['school' => $school, 'competition' => $competition] = createCompetitionFixture();
    $teacherClub = createTeacherClubForCompetition($competition, $school);
    $teacher = $teacherClub->user;

    createRosterStudentForHub($teacherClub, ['name' => 'Registered Student', 'user_id' => User::factory()->create(['role' => 'student'])->id, 'linked_at' => now()]);
    createRosterStudentForHub($teacherClub, ['name' => 'Pending Student']);

    $response = $this->actingAs($teacher)->get(route('client.my-students.index', [
        'registration' => 'registered',
    ]));

    $response->assertOk();
    $response->assertSee('Registered Student');
    $response->assertDontSee('Pending Student');
});

test('unlinked public school student is blocked from learning routes', function () {
    $student = User::factory()->create([
        'role' => 'student',
        'gender' => 'female',
        'date_of_birth' => '2011-05-20',
        'student_onboarding_completed_at' => now(),
        'subscription_tier' => 'public_school',
        'club_linked_at' => null,
    ]);

    $this->actingAs($student)
        ->get(route('client.models.3d.index'))
        ->assertRedirect(route('client.student-club.index'));
});

test('linked public school student can access learning routes', function () {
    ['school' => $school, 'competition' => $competition] = createCompetitionFixture();
    $teacherClub = createTeacherClubForCompetition($competition, $school);
    $studentUser = User::factory()->create([
        'role' => 'student',
        'gender' => 'female',
        'date_of_birth' => '2011-05-20',
        'subscription_tier' => 'public_school',
        'club_linked_at' => now(),
        'student_onboarding_completed_at' => now(),
    ]);
    createRosterStudentForHub($teacherClub, [
        'user_id' => $studentUser->id,
        'linked_at' => now(),
    ]);

    $this->actingAs($studentUser)
        ->get(route('client.models.3d.index'))
        ->assertOk();
});
