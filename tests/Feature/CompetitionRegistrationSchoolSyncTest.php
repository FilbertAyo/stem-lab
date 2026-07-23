<?php

use App\Models\CompetitionRegistration;
use App\Models\SchoolClass;
use App\Models\User;
use App\Services\CompetitionParticipantRegistrationService;
use App\Services\CompetitionRegistrationSchoolSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

require_once __DIR__.'/../Helpers/CompetitionTestHelper.php';

test('updating a school syncs competition registration school snapshots', function () {
    ['region' => $region, 'district' => $district] = createRegionAndDistrict();
    ['school' => $school, 'competition' => $competition] = createCompetitionFixture();

    $school->update([
        'name' => 'lowercase school name',
        'region_id' => $region->id,
        'district_id' => $district->id,
        'ward' => 'Old Ward',
    ]);

    $teacherClub = createTeacherClubForCompetition($competition, $school);
    $registration = app(CompetitionParticipantRegistrationService::class)
        ->ensureDraftRegistration($teacherClub, $competition);

    $registration->update([
        'school_name' => 'lowercase school name',
        'school_ward' => 'Old Ward',
        'status' => 'approved',
    ]);

    $admin = User::factory()->create(['role' => 'admin']);
    $host = adminRequestHost();

    $response = $this->actingAs($admin)
        ->withServerVariables(['HTTP_HOST' => $host])
        ->put(route('admin.schools.update', $school), [
            'name' => 'LOWERCASE SCHOOL NAME',
            'region_id' => $region->id,
            'district_id' => $district->id,
            'ward' => 'New Ward',
            'is_active' => true,
        ]);

    $response->assertRedirect(route('admin.schools.index'));

    $registration->refresh();

    expect($registration->school_name)->toBe('LOWERCASE SCHOOL NAME')
        ->and($registration->school_ward)->toBe('New Ward')
        ->and($registration->district_id)->toBe($district->id)
        ->and($registration->region_id)->toBe($region->id)
        ->and($registration->resolvedSchoolName())->toBe('LOWERCASE SCHOOL NAME');

    $index = $this->actingAs($admin)
        ->withServerVariables(['HTTP_HOST' => $host])
        ->get(route('admin.competitions.registrations.index', $competition));

    $index->assertOk();
    $index->assertSee('LOWERCASE SCHOOL NAME');
});

test('teacher registration submit stores linked school name not typed override', function () {
    ['school' => $school, 'competition' => $competition] = createCompetitionFixture();
    $school->update(['name' => 'Official School Name', 'ward' => 'Official Ward']);

    $teacherClub = createTeacherClubForCompetition($competition, $school);
    $teacher = $teacherClub->user;

    app(CompetitionParticipantRegistrationService::class)
        ->ensureDraftRegistration($teacherClub, $competition);

    $schoolClass = SchoolClass::query()->create([
        'name' => 'Form 1 '.Str::random(6),
        'is_active' => true,
    ]);

    for ($i = 0; $i < 75; $i++) {
        $teacherClub->students()->create([
            'name' => "Student {$i}",
            'gender' => $i < 53 ? 'female' : 'male',
            'age' => 15,
            'school_class_id' => $schoolClass->id,
        ]);
    }

    $response = $this->actingAs($teacher)->post(
        route('client.competitions.register.store', $competition),
        [
            'school_name' => 'Typed Different Name',
            'school_ward' => 'Typed Ward',
        ],
    );

    $response->assertRedirect(route('client.competitions.show', $competition));

    $registration = CompetitionRegistration::query()
        ->where('teacher_club_id', $teacherClub->id)
        ->where('competition_id', $competition->id)
        ->first();

    expect($registration->status)->toBe('pending')
        ->and($registration->school_name)->toBe('Official School Name')
        ->and($registration->school_ward)->toBe('Official Ward')
        ->and($registration->resolvedSchoolName())->toBe('Official School Name');
});

test('backfill command syncs registration school snapshots from schools', function () {
    ['school' => $school, 'competition' => $competition] = createCompetitionFixture();
    $teacherClub = createTeacherClubForCompetition($competition, $school);

    $registration = app(CompetitionParticipantRegistrationService::class)
        ->ensureDraftRegistration($teacherClub, $competition);

    $registration->update([
        'school_name' => 'stale snapshot name',
        'school_ward' => 'stale ward',
    ]);

    $school->update([
        'name' => 'Fresh School Name',
        'ward' => 'Fresh Ward',
    ]);

    Artisan::call('competitions:sync-registration-school-snapshots');

    $registration->refresh();

    expect($registration->school_name)->toBe('Fresh School Name')
        ->and($registration->school_ward)->toBe('Fresh Ward');

    expect(app(CompetitionRegistrationSchoolSyncService::class)->syncFromSchool($school->fresh()))
        ->toBeGreaterThanOrEqual(1);
});
