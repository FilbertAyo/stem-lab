<?php

use App\Models\SchoolClass;
use App\Models\Student;
use App\Services\ShortCodeGenerator;
use App\Services\StudentCsvBulkImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

require_once __DIR__.'/../Helpers/CompetitionTestHelper.php';

test('creating a student assigns a unique admission number', function () {
    ['school' => $school, 'competition' => $competition] = createCompetitionFixture();
    $teacherClub = createTeacherClubForCompetition($competition, $school);

    $schoolClass = SchoolClass::query()->create([
        'name' => 'Form 1 '.Str::random(6),
        'is_active' => true,
    ]);

    $student = $teacherClub->students()->create([
        'name' => 'Jane Doe',
        'gender' => 'female',
        'age' => 14,
        'date_of_birth' => '2011-07-14',
        'school_class_id' => $schoolClass->id,
    ]);

    expect($student->admission_number)->not->toBeEmpty()
        ->and(app(ShortCodeGenerator::class)->isValidFormat($student->admission_number))->toBeTrue();

    $otherStudent = $teacherClub->students()->create([
        'name' => 'John Doe',
        'gender' => 'male',
        'age' => 15,
        'date_of_birth' => '2010-03-01',
        'school_class_id' => $schoolClass->id,
    ]);

    expect($otherStudent->admission_number)->not->toBe($student->admission_number);
});

test('bulk csv import assigns admission numbers to imported students', function () {
    ['school' => $school, 'competition' => $competition] = createCompetitionFixture();
    $teacherClub = createTeacherClubForCompetition($competition, $school);

    $className = 'Form 2 '.Str::random(6);
    SchoolClass::query()->create([
        'name' => $className,
        'is_active' => true,
    ]);

    $csv = "name,date_of_birth,gender,class_name\n";
    $csv .= "Alice Example,14-07-2011,female,{$className}\n";
    $csv .= "Bob Example,01-03-2010,male,{$className}\n";

    $file = UploadedFile::fake()->createWithContent('students.csv', $csv);

    $result = app(StudentCsvBulkImportService::class)->import($teacherClub, $file);

    expect($result['errors'])->toBe([])
        ->and($result['success_count'])->toBe(2);

    $students = Student::query()->where('teacher_club_id', $teacherClub->id)->get();

    expect($students)->toHaveCount(2)
        ->and($students->pluck('admission_number')->filter()->unique())->toHaveCount(2);
});

test('migration backfill assigns admission numbers to existing students', function () {
    ['school' => $school, 'competition' => $competition] = createCompetitionFixture();
    $teacherClub = createTeacherClubForCompetition($competition, $school);

    $schoolClass = SchoolClass::query()->create([
        'name' => 'Form 3 '.Str::random(6),
        'is_active' => true,
    ]);

    $studentId = $teacherClub->students()->create([
        'name' => 'Legacy Student',
        'gender' => 'female',
        'age' => 13,
        'school_class_id' => $schoolClass->id,
    ])->id;

    expect(Student::query()->find($studentId)->admission_number)->not->toBeEmpty();
});
