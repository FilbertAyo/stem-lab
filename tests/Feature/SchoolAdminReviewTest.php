<?php

use App\Models\Club;
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

    $teacher = User::factory()->create(['role' => 'teacher']);
    $joinService = app(TeacherClubJoinService::class);

    $request = Request::create('/', 'POST', [
        'is_new_school' => true,
        'school_name' => 'New School '.Str::random(8),
        'school_district' => 'Test District',
        'school_latitude' => '-6.7923540',
        'school_longitude' => '39.2083284',
    ]);

    $result = $joinService->join($teacher, $club, $request);

    expect($result['ok'])->toBeTrue();

    $school = School::query()->find($result['teacher_club']->school_id);

    expect($school)->not->toBeNull()
        ->and($school->is_active)->toBeTrue()
        ->and($school->admin_reviewed_at)->toBeNull()
        ->and($school->latitude)->toBe('-6.7923540')
        ->and($school->longitude)->toBe('39.2083284');
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
        ->post(adminSchoolsUrl('/'.$school->ulid.'/mark-reviewed'));

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
        ->post(adminSchoolsUrl('/'.$school->ulid.'/teachers'), [
            'user_id' => $teacher->id,
            'club_id' => $club->id,
            'club_day' => 'monday',
        ]);

    $response->assertRedirect(adminSchoolsUrl('/'.$school->ulid));
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
        ->post(adminSchoolsUrl('/'.$targetSchool->ulid.'/teachers'), [
            'user_id' => $teacher->id,
            'club_id' => $club->id,
        ]);

    $response->assertRedirect(adminSchoolsUrl('/'.$targetSchool->ulid));
    $response->assertSessionHasErrors('user_id');

    expect($teacher->fresh()->teacherClubs()->count())->toBe(1);
});

test('mark all reviewed bulk approves pending schools matching filters', function () {
    $pendingOne = School::query()->create([
        'name' => 'Bulk A '.Str::random(6),
        'is_active' => true,
        'district' => 'Mara',
        'admin_reviewed_at' => null,
    ]);

    $pendingTwo = School::query()->create([
        'name' => 'Bulk B '.Str::random(6),
        'is_active' => true,
        'district' => 'Mara',
        'admin_reviewed_at' => null,
    ]);

    School::query()->create([
        'name' => 'Other District '.Str::random(6),
        'is_active' => true,
        'district' => 'Mwanza',
        'admin_reviewed_at' => null,
    ]);

    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)
        ->withServerVariables(['HTTP_HOST' => adminRequestHost()])
        ->post(adminSchoolsUrl('/mark-all-reviewed'), [
            'review' => 'pending',
            'district' => 'Mara',
        ]);

    $response->assertRedirect();

    $pendingOne->refresh();
    $pendingTwo->refresh();

    expect($pendingOne->admin_reviewed_at)->not->toBeNull()
        ->and($pendingTwo->admin_reviewed_at)->not->toBeNull();

    expect(
        School::query()->needsReview()->where('district', 'Mwanza')->exists()
    )->toBeTrue();
});
