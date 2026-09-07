<?php

use App\Models\Club;
use App\Models\Region;
use App\Models\School;
use App\Models\User;
use App\Services\TeacherClubJoinService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

require_once __DIR__.'/../Helpers/CompetitionTestHelper.php';

function adminSchoolsUrl(string $path = '/'): string
{
    $host = parse_url(config('app.admin_url'), PHP_URL_HOST) ?: 'admin.test';

    return 'http://'.$host.'/schools'.$path;
}

test('teacher new school join creates active school pending admin review', function () {
    $club = Club::query()->create([
        'name' => 'Club '.Str::random(6),
        'is_active' => true,
    ]);

    ['region' => $region, 'district' => $district] = createRegionAndDistrict('Mara', 'Test District');

    $teacher = User::factory()->create(['role' => 'teacher']);
    $joinService = app(TeacherClubJoinService::class);

    $request = Request::create('/', 'POST', [
        'is_new_school' => true,
        'school_name' => 'New School '.Str::random(8),
        'school_region_id' => $region->id,
        'school_district_id' => $district->id,
        'school_latitude' => '-6.7923540',
        'school_longitude' => '39.2083284',
    ]);

    $result = $joinService->join($teacher, $club, $request);

    expect($result['ok'])->toBeTrue();

    $school = School::query()->find($result['teacher_club']->school_id);

    expect($school)->not->toBeNull()
        ->and($school->is_active)->toBeTrue()
        ->and($school->admin_reviewed_at)->toBeNull()
        ->and($school->district_id)->toBe($district->id)
        ->and($school->region_id)->toBe($region->id);
});

test('admin created school is auto reviewed', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)
        ->withServerVariables(['HTTP_HOST' => adminRequestHost()])
        ->post(adminSchoolsUrl(), [
            'name' => 'Admin School '.Str::random(8),
            'latitude' => '-6.8160837',
            'longitude' => '39.2803583',
            'is_active' => '1',
        ]);

    $response->assertRedirect();

    $school = School::query()->where('name', 'like', 'Admin School %')->latest('id')->first();

    expect($school)->not->toBeNull()
        ->and($school->admin_reviewed_at)->not->toBeNull()
        ->and($school->latitude)->toBe('-6.8160837')
        ->and($school->longitude)->toBe('39.2803583');
});

test('admin school coordinates must be valid and supplied together', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)
        ->withServerVariables(['HTTP_HOST' => adminRequestHost()])
        ->post(adminSchoolsUrl(), [
            'name' => 'Invalid Coordinates '.Str::random(8),
            'latitude' => '91',
            'is_active' => '1',
        ]);

    $response->assertSessionHasErrors(['latitude', 'longitude']);
    expect(School::query()->where('name', 'like', 'Invalid Coordinates %')->exists())->toBeFalse();
});

test('admin schools index shows pending review filter', function () {
    $pending = School::query()->create([
        'name' => 'Pending School '.Str::random(6),
        'is_active' => true,
        'admin_reviewed_at' => null,
    ]);

    School::query()->create([
        'name' => 'Reviewed School '.Str::random(6),
        'is_active' => true,
        'admin_reviewed_at' => now(),
    ]);

    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)
        ->withServerVariables(['HTTP_HOST' => adminRequestHost()])
        ->get(adminSchoolsUrl().'?review=pending');

    $response->assertOk();
    $response->assertSee($pending->name);
    $response->assertSee('New');
});

test('mark reviewed keeps school active and removes from pending queue', function () {
    $school = School::query()->create([
        'name' => 'Review Me '.Str::random(6),
        'is_active' => true,
        'admin_reviewed_at' => null,
    ]);

    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)
        ->withServerVariables(['HTTP_HOST' => adminRequestHost()])
        ->post(adminSchoolsUrl('/'.$school->getRouteKey().'/mark-reviewed'));

    $response->assertRedirect();

    $school->refresh();

    expect($school->is_active)->toBeTrue()
        ->and($school->admin_reviewed_at)->not->toBeNull();

    expect(School::query()->needsReview()->where('id', $school->id)->exists())->toBeFalse();
});

test('admin can assign unassigned teacher to school from school show page', function () {
    $school = School::query()->create([
        'name' => 'Add Teacher School '.Str::random(6),
        'is_active' => true,
        'admin_reviewed_at' => now(),
    ]);

    $club = Club::query()->create([
        'name' => 'Club '.Str::random(6),
        'is_active' => true,
    ]);

    $teacher = User::factory()->create(['role' => 'teacher']);
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)
        ->withServerVariables(['HTTP_HOST' => adminRequestHost()])
        ->post(adminSchoolsUrl('/'.$school->getRouteKey().'/teachers'), [
            'user_id' => $teacher->id,
            'club_id' => $club->id,
            'club_day' => 'monday',
        ]);

    $response->assertRedirect(adminSchoolsUrl('/'.$school->getRouteKey().'?tab=teachers'));
    $response->assertSessionHas('success');

    $teacherClub = $teacher->fresh()->teacherClubs()->first();

    expect($teacherClub)->not->toBeNull()
        ->and($teacherClub->school_id)->toBe($school->id)
        ->and($teacherClub->club_id)->toBe($club->id)
        ->and($teacherClub->status)->toBe('approved')
        ->and($teacherClub->is_leader)->toBeTrue()
        ->and($teacherClub->club_day)->toBe('monday');
});

test('admin cannot assign teacher who already belongs to a school', function () {
    $existingSchool = School::query()->create([
        'name' => 'Existing School '.Str::random(6),
        'is_active' => true,
    ]);

    $targetSchool = School::query()->create([
        'name' => 'Target School '.Str::random(6),
        'is_active' => true,
    ]);

    $club = Club::query()->create([
        'name' => 'Club '.Str::random(6),
        'is_active' => true,
    ]);

    $teacher = User::factory()->create(['role' => 'teacher']);

    $teacher->teacherClubs()->create([
        'club_id' => $club->id,
        'school_id' => $existingSchool->id,
        'status' => 'approved',
        'is_leader' => true,
    ]);

    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)
        ->withServerVariables(['HTTP_HOST' => adminRequestHost()])
        ->post(adminSchoolsUrl('/'.$targetSchool->getRouteKey().'/teachers'), [
            'user_id' => $teacher->id,
            'club_id' => $club->id,
        ]);

    $response->assertRedirect(adminSchoolsUrl('/'.$targetSchool->getRouteKey().'?tab=teachers'));
    $response->assertSessionHasErrors('user_id');

    expect($teacher->fresh()->teacherClubs()->count())->toBe(1);
});

test('updating club day syncs to all teachers in the same school club', function () {
    $school = School::query()->create([
        'name' => 'Shared Day School '.Str::random(6),
        'is_active' => true,
        'admin_reviewed_at' => now(),
    ]);

    $club = Club::query()->create([
        'name' => 'Club '.Str::random(6),
        'is_active' => true,
    ]);

    $leader = User::factory()->create(['role' => 'teacher']);
    $coTeacher = User::factory()->create(['role' => 'teacher']);

    $leaderMembership = $leader->teacherClubs()->create([
        'club_id' => $club->id,
        'school_id' => $school->id,
        'status' => 'approved',
        'is_leader' => true,
        'club_day' => 'monday',
    ]);

    $coMembership = $coTeacher->teacherClubs()->create([
        'club_id' => $club->id,
        'school_id' => $school->id,
        'status' => 'approved',
        'is_leader' => false,
        'club_day' => 'monday',
    ]);

    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)
        ->withServerVariables(['HTTP_HOST' => adminRequestHost()])
        ->put(adminSchoolsUrl('/'.$school->getRouteKey().'/teacher-clubs/'.$coMembership->ulid.'/club-day'), [
            'club_day' => 'wednesday',
        ]);

    $response->assertRedirect(adminSchoolsUrl('/'.$school->getRouteKey().'?tab=teachers'));
    $response->assertSessionHas('status');

    expect($leaderMembership->fresh()->club_day)->toBe('wednesday')
        ->and($coMembership->fresh()->club_day)->toBe('wednesday');
});

test('adding co-teacher inherits existing shared club day', function () {
    $school = School::query()->create([
        'name' => 'Inherit Day School '.Str::random(6),
        'is_active' => true,
        'admin_reviewed_at' => now(),
    ]);

    $club = Club::query()->create([
        'name' => 'Club '.Str::random(6),
        'is_active' => true,
    ]);

    $leader = User::factory()->create(['role' => 'teacher']);
    $leader->teacherClubs()->create([
        'club_id' => $club->id,
        'school_id' => $school->id,
        'status' => 'approved',
        'is_leader' => true,
        'club_day' => 'friday',
    ]);

    $newTeacher = User::factory()->create(['role' => 'teacher']);
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)
        ->withServerVariables(['HTTP_HOST' => adminRequestHost()])
        ->post(adminSchoolsUrl('/'.$school->getRouteKey().'/teachers'), [
            'user_id' => $newTeacher->id,
            'club_id' => $club->id,
        ]);

    $response->assertRedirect(adminSchoolsUrl('/'.$school->getRouteKey().'?tab=teachers'));

    $newMembership = $newTeacher->fresh()->teacherClubs()->first();

    expect($newMembership)->not->toBeNull()
        ->and($newMembership->is_leader)->toBeFalse()
        ->and($newMembership->club_day)->toBe('friday');
});

test('admin bulk upload attaches students to club leader for shared roster', function () {
    $school = School::query()->create([
        'name' => 'Roster School '.Str::random(6),
        'is_active' => true,
        'admin_reviewed_at' => now(),
    ]);

    $club = Club::query()->create([
        'name' => 'Club '.Str::random(6),
        'is_active' => true,
    ]);

    $schoolClass = \App\Models\SchoolClass::query()->create([
        'name' => 'Form 1 '.Str::random(4),
        'is_active' => true,
    ]);

    $leader = User::factory()->create(['role' => 'teacher']);
    $coTeacher = User::factory()->create(['role' => 'teacher']);

    $leaderMembership = $leader->teacherClubs()->create([
        'club_id' => $club->id,
        'school_id' => $school->id,
        'status' => 'approved',
        'is_leader' => true,
        'club_day' => 'monday',
    ]);

    $coMembership = $coTeacher->teacherClubs()->create([
        'club_id' => $club->id,
        'school_id' => $school->id,
        'status' => 'approved',
        'is_leader' => false,
        'club_day' => 'monday',
    ]);

    $sharedContext = app(\App\Services\TeacherClubSharedContextService::class);
    $rosterOwner = $sharedContext->resolveRosterOwner($coMembership);

    expect($rosterOwner->id)->toBe($leaderMembership->id);

    $csv = "name,date_of_birth,gender,class_name\nShared Student,14-07-2011,female,{$schoolClass->name}\n";
    $path = sys_get_temp_dir().'/students_'.uniqid().'.csv';
    file_put_contents($path, $csv);
    $file = new \Illuminate\Http\UploadedFile($path, 'students.csv', 'text/csv', null, true);

    $result = app(\App\Services\StudentCsvBulkImportService::class)->import($rosterOwner, $file);

    @unlink($path);

    expect($result['errors'])->toBe([])
        ->and($result['success_count'])->toBe(1);

    $student = \App\Models\Student::query()->where('name', 'Shared Student')->first();

    expect($student)->not->toBeNull()
        ->and($student->teacher_club_id)->toBe($leaderMembership->id)
        ->and($coTeacher->approvedCoMemberTeacherClubIds()->contains($student->teacher_club_id))->toBeTrue();
});

test('mark all reviewed bulk approves pending schools matching filters', function () {
    ['region' => $region, 'district' => $maraDistrict] = createRegionAndDistrict('Mara', 'Musoma');
    ['district' => $mwanzaDistrict] = createRegionAndDistrict('Mwanza', 'Ilemela');

    $pendingOne = School::query()->create([
        'name' => 'Bulk A '.Str::random(6),
        'is_active' => true,
        'region_id' => $region->id,
        'district_id' => $maraDistrict->id,
        'district' => $maraDistrict->name,
        'admin_reviewed_at' => null,
    ]);

    $pendingTwo = School::query()->create([
        'name' => 'Bulk B '.Str::random(6),
        'is_active' => true,
        'region_id' => $region->id,
        'district_id' => $maraDistrict->id,
        'district' => $maraDistrict->name,
        'admin_reviewed_at' => null,
    ]);

    School::query()->create([
        'name' => 'Other District '.Str::random(6),
        'is_active' => true,
        'region_id' => $mwanzaDistrict->region_id,
        'district_id' => $mwanzaDistrict->id,
        'district' => $mwanzaDistrict->name,
        'admin_reviewed_at' => null,
    ]);

    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)
        ->withServerVariables(['HTTP_HOST' => adminRequestHost()])
        ->post(adminSchoolsUrl('/mark-all-reviewed'), [
            'review' => 'pending',
            'district_id' => $maraDistrict->id,
        ]);

    $response->assertRedirect();

    $pendingOne->refresh();
    $pendingTwo->refresh();

    expect($pendingOne->admin_reviewed_at)->not->toBeNull()
        ->and($pendingTwo->admin_reviewed_at)->not->toBeNull();

    expect(
        School::query()->needsReview()->where('district_id', $mwanzaDistrict->id)->exists()
    )->toBeTrue();
});

test('admin school show uses full-width tabs for teachers and students', function () {
    $school = School::query()->create([
        'name' => 'Tab School '.Str::random(6),
        'is_active' => true,
        'admin_reviewed_at' => now(),
    ]);

    $club = Club::query()->create([
        'name' => 'Club '.Str::random(6),
        'is_active' => true,
    ]);

    $teacher = User::factory()->create(['role' => 'teacher']);
    $teacherClub = $teacher->teacherClubs()->create([
        'club_id' => $club->id,
        'school_id' => $school->id,
        'status' => 'approved',
        'is_leader' => true,
    ]);

    $schoolClass = \App\Models\SchoolClass::query()->create([
        'name' => 'Form 1 '.Str::random(4),
        'is_active' => true,
    ]);

    $teacherClub->students()->create([
        'name' => 'Tab Student',
        'gender' => 'female',
        'age' => 14,
        'school_class_id' => $schoolClass->id,
    ]);

    $admin = User::factory()->create(['role' => 'admin']);

    $defaultView = $this->actingAs($admin)
        ->withServerVariables(['HTTP_HOST' => adminRequestHost()])
        ->get(adminSchoolsUrl('/'.$school->getRouteKey()));

    $defaultView->assertOk()
        ->assertViewHas('activeTab', 'students')
        ->assertSee('id="school-tabs"', false)
        ->assertSee('id="pills-teachers"', false)
        ->assertSee('id="pills-students"', false)
        ->assertSee($teacher->name)
        ->assertSee('Tab Student');

    $teachersView = $this->actingAs($admin)
        ->withServerVariables(['HTTP_HOST' => adminRequestHost()])
        ->get(adminSchoolsUrl('/'.$school->getRouteKey().'?tab=teachers'));

    $teachersView->assertOk()
        ->assertViewHas('activeTab', 'teachers');
});
