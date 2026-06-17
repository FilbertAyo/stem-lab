<?php

use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\TeacherClub;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

require_once __DIR__.'/../Helpers/CompetitionTestHelper.php';

test('approved teacher can view their club show page', function () {
    ['school' => $school, 'competition' => $competition] = createCompetitionFixture();
    $teacherClub = createTeacherClubForCompetition($competition, $school);
    $teacher = $teacherClub->user;

    $response = $this->actingAs($teacher)->get(route('client.clubs.show', $teacherClub));

    $response->assertOk();
    $response->assertSee($competition->club->name);
    $response->assertSee($school->name);
    $response->assertSee('Registered Students');
});

test('non member cannot view club show page', function () {
    ['school' => $school, 'competition' => $competition] = createCompetitionFixture();
    $teacherClub = createTeacherClubForCompetition($competition, $school);
    $otherTeacher = User::factory()->create(['role' => 'teacher']);

    $response = $this->actingAs($otherTeacher)->get(route('client.clubs.show', $teacherClub));

    $response->assertForbidden();
});

test('join redirects to club show when auto approved', function () {
    ['club' => $club, 'school' => $school] = createCompetitionFixture();

    $teacher = User::factory()->create(['role' => 'teacher']);

    $response = $this->actingAs($teacher)->post(route('client.clubs.join', $club), [
        'school_id' => $school->id,
    ]);

    $membership = TeacherClub::query()->where('user_id', $teacher->id)->where('club_id', $club->id)->first();
    expect($membership)->not->toBeNull();

    $response->assertRedirect(route('client.clubs.show', $membership));
});

test('get students redirects to clubs index', function () {
    ['school' => $school, 'competition' => $competition] = createCompetitionFixture();
    $teacherClub = createTeacherClubForCompetition($competition, $school);

    $response = $this->actingAs($teacherClub->user)->get('/students');

    $response->assertRedirect('/clubs');
});

test('student store from club show redirects back to show page', function () {
    ['school' => $school, 'competition' => $competition] = createCompetitionFixture();
    $teacherClub = createTeacherClubForCompetition($competition, $school);
    $teacher = $teacherClub->user;

    $schoolClass = SchoolClass::query()->create([
        'name' => 'Form 1 '.Str::random(6),
        'is_active' => true,
    ]);

    $response = $this->actingAs($teacher)->post(route('client.students.store'), [
        'teacher_club_id' => $teacherClub->id,
        'name' => 'Test Student',
        'date_of_birth' => '2011-07-14',
        'gender' => 'female',
        'school_class_id' => $schoolClass->id,
        'redirect_to' => route('client.clubs.show', $teacherClub, absolute: false),
    ]);

    $response->assertRedirect(route('client.clubs.show', $teacherClub));
    $response->assertSessionHas('status');
    expect(Student::query()->where('teacher_club_id', $teacherClub->id)->where('name', 'Test Student')->exists())->toBeTrue();
});

test('student store validation failure redirects back to club show not students index', function () {
    ['school' => $school, 'competition' => $competition] = createCompetitionFixture();
    $teacherClub = createTeacherClubForCompetition($competition, $school);
    $teacher = $teacherClub->user;

    $response = $this->actingAs($teacher)->post(route('client.students.store'), [
        'teacher_club_id' => $teacherClub->id,
        'redirect_to' => route('client.clubs.show', $teacherClub, absolute: false),
        'gender' => 'female',
    ]);

    $response->assertRedirect(route('client.clubs.show', $teacherClub));
    $response->assertSessionHasErrors(['name', 'date_of_birth', 'school_class_id']);
});

test('co member teacher can register student from shared club page', function () {
    ['school' => $school, 'competition' => $competition] = createCompetitionFixture();
    $leaderClub = createTeacherClubForCompetition($competition, $school);

    $coTeacher = User::factory()->create(['role' => 'teacher']);
    $coTeacherClub = TeacherClub::query()->create([
        'user_id' => $coTeacher->id,
        'club_id' => $competition->club_id,
        'school_id' => $school->id,
        'status' => 'approved',
        'is_leader' => false,
    ]);

    $schoolClass = SchoolClass::query()->create([
        'name' => 'Form 2 '.Str::random(6),
        'is_active' => true,
    ]);

    $response = $this->actingAs($coTeacher)->post(route('client.students.store'), [
        'teacher_club_id' => $leaderClub->id,
        'name' => 'Co-member Student',
        'date_of_birth' => '2010-05-20',
        'gender' => 'male',
        'school_class_id' => $schoolClass->id,
        'redirect_to' => route('client.clubs.show', $leaderClub, absolute: false),
    ]);

    $response->assertRedirect(route('client.clubs.show', $leaderClub));
    expect(Student::query()->where('teacher_club_id', $coTeacherClub->id)->where('name', 'Co-member Student')->exists())->toBeTrue();
});

test('student store accepts day first date of birth format', function () {
    ['school' => $school, 'competition' => $competition] = createCompetitionFixture();
    $teacherClub = createTeacherClubForCompetition($competition, $school);
    $teacher = $teacherClub->user;

    $schoolClass = SchoolClass::query()->create([
        'name' => 'Form 3 '.Str::random(6),
        'is_active' => true,
    ]);

    $response = $this->actingAs($teacher)->post(route('client.students.store'), [
        'teacher_club_id' => $teacherClub->id,
        'name' => 'Day First Student',
        'date_of_birth' => '14-07-2011',
        'gender' => 'female',
        'school_class_id' => $schoolClass->id,
        'redirect_to' => route('client.clubs.show', $teacherClub, absolute: false),
    ]);

    $response->assertRedirect(route('client.clubs.show', $teacherClub));
    $response->assertSessionHas('status');
    expect(Student::query()->where('teacher_club_id', $teacherClub->id)->where('name', 'Day First Student')->exists())->toBeTrue();
});

test('clubs index shows view club link for joined membership', function () {
    ['school' => $school, 'competition' => $competition] = createCompetitionFixture();
    $teacherClub = createTeacherClubForCompetition($competition, $school);

    $response = $this->actingAs($teacherClub->user)->get(route('client.clubs.index'));

    $response->assertOk();
    $response->assertSee('View club');
    $response->assertSee($competition->club->name);
});
