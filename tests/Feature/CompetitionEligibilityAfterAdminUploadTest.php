<?php

use App\Models\CompetitionRegistration;
use App\Models\SchoolClass;
use App\Models\User;
use App\Services\CompetitionEligibilityPoolService;
use App\Services\CompetitionParticipantRegistrationService;
use App\Services\StudentCsvBulkImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

require_once __DIR__.'/../Helpers/CompetitionTestHelper.php';

function makeStudentCsvFile(array $rows): UploadedFile
{
    $lines = ['name,gender,class_name,age'];
    foreach ($rows as $row) {
        $lines[] = implode(',', $row);
    }

    $path = sys_get_temp_dir().'/students_'.uniqid('', true).'.csv';
    file_put_contents($path, implode("\n", $lines)."\n");

    return new UploadedFile($path, 'students.csv', 'text/csv', null, true);
}

test('admin csv import refreshes draft competition eligibility snapshots', function () {
    ['school' => $school, 'competition' => $competition] = createCompetitionFixture([
        'min_members' => 3,
    ]);
    $teacherClub = createTeacherClubForCompetition($competition, $school);

    app(CompetitionParticipantRegistrationService::class)
        ->ensureDraftRegistration($teacherClub, $competition);

    $className = 'Form 1 '.Str::random(6);
    SchoolClass::query()->create([
        'name' => $className,
        'is_active' => true,
    ]);

    $csv = makeStudentCsvFile([
        ['Amina', 'female', $className, '14'],
        ['Neema', 'female', $className, '15'],
        ['Juma', 'male', $className, '14'],
    ]);

    $result = app(StudentCsvBulkImportService::class)->import($teacherClub, $csv);

    expect($result['errors'])->toBe([])
        ->and($result['success_count'])->toBe(3);

    $registration = CompetitionRegistration::query()
        ->where('competition_id', $competition->id)
        ->where('teacher_club_id', $teacherClub->id)
        ->first();

    expect($registration)->not->toBeNull()
        ->and($registration->student_count_snapshot)->toBe(3)
        ->and($registration->girls_count_snapshot)->toBe(2)
        ->and((float) $registration->girls_ratio_snapshot)->toBe(66.67)
        ->and($registration->eligibility_met)->toBeFalse(); // < 70% girls
});

test('admin csv import marks eligibility met when pool rules pass', function () {
    ['school' => $school, 'competition' => $competition] = createCompetitionFixture([
        'min_members' => 3,
    ]);
    $teacherClub = createTeacherClubForCompetition($competition, $school);

    app(CompetitionParticipantRegistrationService::class)
        ->ensureDraftRegistration($teacherClub, $competition);

    $className = 'Form 2 '.Str::random(6);
    SchoolClass::query()->create([
        'name' => $className,
        'is_active' => true,
    ]);

    $csv = makeStudentCsvFile([
        ['Amina', 'female', $className, '14'],
        ['Neema', 'female', $className, '15'],
        ['Fatuma', 'female', $className, '14'],
    ]);

    $result = app(StudentCsvBulkImportService::class)->import($teacherClub, $csv);

    expect($result['success_count'])->toBe(3);

    $registration = CompetitionRegistration::query()
        ->where('competition_id', $competition->id)
        ->where('teacher_club_id', $teacherClub->id)
        ->first();

    expect($registration->student_count_snapshot)->toBe(3)
        ->and($registration->girls_count_snapshot)->toBe(3)
        ->and((float) $registration->girls_ratio_snapshot)->toBe(100.0)
        ->and($registration->eligibility_met)->toBeTrue();
});

test('eligibility pool ignores students outside competition class requirements', function () {
    ['school' => $school, 'competition' => $competition] = createCompetitionFixture([
        'min_members' => 2,
    ]);
    $teacherClub = createTeacherClubForCompetition($competition, $school);

    $allowed = SchoolClass::query()->create(['name' => 'Allowed '.Str::random(4), 'is_active' => true]);
    $other = SchoolClass::query()->create(['name' => 'Other '.Str::random(4), 'is_active' => true]);

    $competition->classRequirements()->create(['school_class_id' => $allowed->id]);

    app(CompetitionParticipantRegistrationService::class)
        ->ensureDraftRegistration($teacherClub, $competition);

    $teacherClub->students()->create([
        'name' => 'In Pool',
        'gender' => 'female',
        'age' => 14,
        'school_class_id' => $allowed->id,
    ]);
    $teacherClub->students()->create([
        'name' => 'Out Of Pool',
        'gender' => 'female',
        'age' => 14,
        'school_class_id' => $other->id,
    ]);

    $pool = app(CompetitionEligibilityPoolService::class)->computePool($competition, $teacherClub);

    expect($pool['count'])->toBe(1)
        ->and($pool['girls_count'])->toBe(1)
        ->and($pool['eligible'])->toBeFalse(); // needs min 2
});

test('admin registration show heals stale draft eligibility snapshots', function () {
    ['school' => $school, 'competition' => $competition] = createCompetitionFixture([
        'min_members' => 2,
    ]);
    $teacherClub = createTeacherClubForCompetition($competition, $school);
    $class = SchoolClass::query()->create(['name' => 'Form 3 '.Str::random(4), 'is_active' => true]);

    $registration = app(CompetitionParticipantRegistrationService::class)
        ->ensureDraftRegistration($teacherClub, $competition);

    $teacherClub->students()->create([
        'name' => 'Girl One',
        'gender' => 'female',
        'age' => 14,
        'school_class_id' => $class->id,
    ]);
    $teacherClub->students()->create([
        'name' => 'Girl Two',
        'gender' => 'female',
        'age' => 15,
        'school_class_id' => $class->id,
    ]);

    // Simulate pre-fix state: students exist but snapshots still at draft zeros.
    $registration->forceFill([
        'student_count_snapshot' => 0,
        'girls_count_snapshot' => 0,
        'girls_ratio_snapshot' => 0,
        'eligibility_met' => false,
    ])->save();

    $admin = User::factory()->create(['role' => 'admin']);
    $host = adminRequestHost();

    $response = $this->actingAs($admin)
        ->withServerVariables(['HTTP_HOST' => $host])
        ->get('http://'.$host.'/competitions/'.$competition->ulid.'/registrations/'.$registration->ulid);

    $response->assertOk();
    $response->assertSee('Eligibility (live pool)');
    $response->assertSee('2</strong> students', false);
    $response->assertSee('Yes');

    $registration->refresh();
    expect($registration->student_count_snapshot)->toBe(2)
        ->and($registration->girls_count_snapshot)->toBe(2)
        ->and($registration->eligibility_met)->toBeTrue();
});
