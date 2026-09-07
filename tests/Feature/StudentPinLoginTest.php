<?php

use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\TeacherClub;
use App\Models\User;
use App\Services\StudentAccountProvisionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

require_once __DIR__.'/../Helpers/CompetitionTestHelper.php';

function createRosterStudentForPin(TeacherClub $teacherClub, array $overrides = []): Student
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

test('teacher can provision an unlinked roster student', function () {
    Notification::fake();

    ['school' => $school, 'competition' => $competition] = createCompetitionFixture();
    $teacherClub = createTeacherClubForCompetition($competition, $school);
    $teacher = $teacherClub->user;
    $rosterStudent = createRosterStudentForPin($teacherClub);

    $response = $this->actingAs($teacher)->post(route('client.students.credentials.store', $rosterStudent));

    $response->assertRedirect(route('client.clubs.show', $teacherClub, absolute: false));
    $response->assertSessionHas('provisioned_credentials');

    $credentials = session('provisioned_credentials');
    expect($credentials)->toHaveCount(1)
        ->and($credentials[0]['login_id'])->toBe($rosterStudent->admission_number)
        ->and($credentials[0]['pin'])->toHaveLength(6);

    $rosterStudent->refresh();
    $user = $rosterStudent->user;

    expect($rosterStudent->isLinked())->toBeTrue()
        ->and($user)->not->toBeNull()
        ->and($user->login_id)->toBe($rosterStudent->admission_number)
        ->and($user->uses_pin)->toBeTrue()
        ->and($user->email)->toBeNull()
        ->and($user->subscription_tier)->toBe('public_school')
        ->and($user->club_linked_at)->not->toBeNull()
        ->and($user->student_onboarding_completed_at)->not->toBeNull()
        ->and($user->needsStudentOnboarding())->toBeFalse();
});

test('admin can provision a student on their school', function () {
    Notification::fake();

    ['school' => $school, 'competition' => $competition] = createCompetitionFixture();
    $teacherClub = createTeacherClubForCompetition($competition, $school);
    $rosterStudent = createRosterStudentForPin($teacherClub);
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->withServerVariables(['HTTP_HOST' => adminRequestHost()])
        ->post(route('admin.schools.students.credentials.store', [$school, $rosterStudent]))
        ->assertRedirect(route('admin.schools.show', ['school' => $school, 'tab' => 'students']))
        ->assertSessionHas('provisioned_credentials');

    expect($rosterStudent->fresh()->isLinked())->toBeTrue();
});

test('admin cannot provision a student from another school', function () {
    Notification::fake();

    ['school' => $schoolA, 'competition' => $competitionA] = createCompetitionFixture();
    createTeacherClubForCompetition($competitionA, $schoolA);

    ['school' => $schoolB, 'competition' => $competitionB] = createCompetitionFixture();
    $teacherClubB = createTeacherClubForCompetition($competitionB, $schoolB);
    $studentB = createRosterStudentForPin($teacherClubB);

    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->withServerVariables(['HTTP_HOST' => adminRequestHost()])
        ->post(route('admin.schools.students.credentials.store', [$schoolA, $studentB]))
        ->assertNotFound();
});

test('cannot provision an already-linked student', function () {
    Notification::fake();

    ['school' => $school, 'competition' => $competition] = createCompetitionFixture();
    $teacherClub = createTeacherClubForCompetition($competition, $school);
    $teacher = $teacherClub->user;
    $linkedUser = User::factory()->create(['role' => 'student']);
    $rosterStudent = createRosterStudentForPin($teacherClub, [
        'user_id' => $linkedUser->id,
        'linked_at' => now(),
    ]);

    $this->actingAs($teacher)
        ->from(route('client.clubs.show', $teacherClub))
        ->post(route('client.students.credentials.store', $rosterStudent))
        ->assertRedirect(route('client.clubs.show', $teacherClub))
        ->assertSessionHasErrors('student');
});

test('bulk provision skips linked rows and reports counts', function () {
    Notification::fake();

    ['school' => $school, 'competition' => $competition] = createCompetitionFixture();
    $teacherClub = createTeacherClubForCompetition($competition, $school);
    $teacher = $teacherClub->user;
    $pending = createRosterStudentForPin($teacherClub, ['name' => 'Pending Student']);
    $linkedUser = User::factory()->create(['role' => 'student']);
    $linked = createRosterStudentForPin($teacherClub, [
        'name' => 'Linked Student',
        'user_id' => $linkedUser->id,
        'linked_at' => now(),
    ]);

    $response = $this->actingAs($teacher)->post(route('client.students.credentials.bulk'), [
        'teacher_club_id' => $teacherClub->id,
        'student_ids' => [$pending->id, $linked->id],
    ]);

    $response->assertRedirect(route('client.clubs.show', $teacherClub, absolute: false));
    $response->assertSessionHas('provisioned_credentials');
    $response->assertSessionHas('status');

    expect(session('provisioned_credentials'))->toHaveCount(1)
        ->and($pending->fresh()->isLinked())->toBeTrue()
        ->and($linked->fresh()->user_id)->toBe($linkedUser->id)
        ->and(session('status'))->toContain('1 already had an account');
});

test('student can sign in with admission number and pin', function () {
    Notification::fake();

    ['school' => $school, 'competition' => $competition] = createCompetitionFixture();
    $teacherClub = createTeacherClubForCompetition($competition, $school);
    $rosterStudent = createRosterStudentForPin($teacherClub);

    $credential = app(StudentAccountProvisionService::class)->provision($rosterStudent);

    $response = $this->post('/login', [
        'login_method' => 'student_id',
        'login_id' => $credential->loginId,
        'password' => $credential->pin,
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
    $this->assertAuthenticatedAs($rosterStudent->fresh()->user);
});

test('email password login still works', function () {
    $user = User::factory()->create([
        'role' => 'student',
        'gender' => 'female',
        'date_of_birth' => '2011-05-20',
        'student_onboarding_completed_at' => now(),
    ]);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('wrong pin does not authenticate', function () {
    Notification::fake();

    ['school' => $school, 'competition' => $competition] = createCompetitionFixture();
    $teacherClub = createTeacherClubForCompetition($competition, $school);
    $rosterStudent = createRosterStudentForPin($teacherClub);
    $credential = app(StudentAccountProvisionService::class)->provision($rosterStudent);

    $this->post('/login', [
        'login_method' => 'student_id',
        'login_id' => $credential->loginId,
        'password' => '000000',
    ]);

    $this->assertGuest();
});

test('login page includes student id mode without requiring email', function () {
    $this->get('/login')
        ->assertOk()
        ->assertSee('Student ID')
        ->assertSee('Admission number')
        ->assertSee('name="login_id"', false);

    $this->get('/login?method=student_id')
        ->assertOk()
        ->assertSee('name="login_method"', false)
        ->assertSee('value="student_id"', false);
});

test('teacher my students shows provisioned student as registered', function () {
    Notification::fake();

    ['school' => $school, 'competition' => $competition] = createCompetitionFixture();
    $teacherClub = createTeacherClubForCompetition($competition, $school);
    $teacher = $teacherClub->user;
    $rosterStudent = createRosterStudentForPin($teacherClub);

    app(StudentAccountProvisionService::class)->provision($rosterStudent);

    $this->actingAs($teacher)
        ->get(route('client.my-students.index'))
        ->assertOk()
        ->assertSee('Amina Hassan')
        ->assertSee('Registered');
});

test('pin user is not asked for onboarding or phone after login', function () {
    Notification::fake();

    ['school' => $school, 'competition' => $competition] = createCompetitionFixture();
    $teacherClub = createTeacherClubForCompetition($competition, $school);
    $rosterStudent = createRosterStudentForPin($teacherClub);
    $credential = app(StudentAccountProvisionService::class)->provision($rosterStudent);

    $this->post('/login', [
        'login_method' => 'student_id',
        'login_id' => $credential->loginId,
        'password' => $credential->pin,
    ])->assertRedirect(route('dashboard', absolute: false));

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertDontSee('Your phone number is required');
});

test('reset pin invalidates the old pin', function () {
    Notification::fake();

    ['school' => $school, 'competition' => $competition] = createCompetitionFixture();
    $teacherClub = createTeacherClubForCompetition($competition, $school);
    $teacher = $teacherClub->user;
    $rosterStudent = createRosterStudentForPin($teacherClub);
    $original = app(StudentAccountProvisionService::class)->provision($rosterStudent);

    $response = $this->actingAs($teacher)
        ->post(route('client.students.credentials.reset', $rosterStudent));

    $response->assertSessionHas('provisioned_credentials');
    $newPin = session('provisioned_credentials')[0]['pin'];

    expect($newPin)->not->toBe($original->pin);

    $this->post('/logout');

    $this->post('/login', [
        'login_method' => 'student_id',
        'login_id' => $original->loginId,
        'password' => $original->pin,
    ]);
    $this->assertGuest();

    $this->post('/login', [
        'login_method' => 'student_id',
        'login_id' => $original->loginId,
        'password' => $newPin,
    ]);
    $this->assertAuthenticated();
});

test('inactive pin user cannot sign in', function () {
    Notification::fake();

    ['school' => $school, 'competition' => $competition] = createCompetitionFixture();
    $teacherClub = createTeacherClubForCompetition($competition, $school);
    $rosterStudent = createRosterStudentForPin($teacherClub);
    $credential = app(StudentAccountProvisionService::class)->provision($rosterStudent);

    $rosterStudent->fresh()->user->update(['is_active' => false]);

    $this->post('/login', [
        'login_method' => 'student_id',
        'login_id' => $credential->loginId,
        'password' => $credential->pin,
    ]);

    $this->assertGuest();
});
