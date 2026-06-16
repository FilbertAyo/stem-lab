<?php

use App\Models\School;
use App\Models\TeacherClub;
use App\Models\User;
use App\Services\ShortCodeGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

require_once __DIR__.'/../Helpers/CompetitionTestHelper.php';

test('first teacher club at a school and club gets a club code', function () {
    ['school' => $school, 'competition' => $competition] = createCompetitionFixture();

    $teacherClub = createTeacherClubForCompetition($competition, $school);

    expect($teacherClub->club_code)->not->toBeEmpty()
        ->and(app(ShortCodeGenerator::class)->isValidFormat($teacherClub->club_code))->toBeTrue();
});

test('second teacher club at same school and club reuses the club code', function () {
    ['school' => $school, 'competition' => $competition] = createCompetitionFixture();

    $firstTeacherClub = createTeacherClubForCompetition($competition, $school);

    $secondTeacher = User::factory()->create(['role' => 'teacher']);

    $secondTeacherClub = TeacherClub::query()->create([
        'user_id' => $secondTeacher->id,
        'club_id' => $competition->club_id,
        'school_id' => $school->id,
        'status' => 'approved',
        'is_leader' => false,
    ]);

    expect($secondTeacherClub->club_code)->toBe($firstTeacherClub->club_code);
});

test('different school club groups receive different club codes', function () {
    ['school' => $schoolA, 'competition' => $competition] = createCompetitionFixture();

    $schoolB = School::query()->create([
        'name' => 'Other School '.Str::random(8),
        'is_active' => true,
    ]);

    $clubA = createTeacherClubForCompetition($competition, $schoolA);
    $clubB = createTeacherClubForCompetition($competition, $schoolB);

    expect($clubA->club_code)->not->toBe($clubB->club_code);
});
