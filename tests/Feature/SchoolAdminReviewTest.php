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
    ]);

    $result = $joinService->join($teacher, $club, $request);

    expect($result['ok'])->toBeTrue();

    $school = School::query()->find($result['teacher_club']->school_id);

    expect($school)->not->toBeNull()
        ->and($school->is_active)->toBeTrue()
        ->and($school->admin_reviewed_at)->toBeNull();
});

test('admin created school is auto reviewed', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)
        ->withServerVariables(['HTTP_HOST' => adminRequestHost()])
        ->post(adminSchoolsUrl(), [
            'name' => 'Admin School '.Str::random(8),
            'is_active' => '1',
        ]);

    $response->assertRedirect();

    $school = School::query()->where('name', 'like', 'Admin School %')->latest('id')->first();

    expect($school)->not->toBeNull()
        ->and($school->admin_reviewed_at)->not->toBeNull();
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

test('admin can add teacher to school from school show page', function () {
    $school = School::query()->create([
        'name' => 'Add Teacher School '.Str::random(6),
        'is_active' => true,
        'admin_reviewed_at' => now(),
    ]);

    $club = Club::query()->create([
        'name' => 'Club '.Str::random(6),
        'is_active' => true,
    ]);

    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)
        ->withServerVariables(['HTTP_HOST' => adminRequestHost()])
        ->post(adminSchoolsUrl('/'.$school->ulid.'/teachers'), [
            'name' => 'Jane Teacher',
            'email' => 'jane.teacher.'.strtolower(Str::random(8)).'@example.com',
            'phone' => '0765890921',
            'password' => 'Password1',
            'club_id' => $club->id,
            'club_day' => 'monday',
        ]);

    $response->assertRedirect(adminSchoolsUrl('/'.$school->ulid));
    $response->assertSessionHas('success');

    $teacher = User::query()->where('email', 'like', 'jane.teacher.%@example.com')->first();

    expect($teacher)->not->toBeNull()
        ->and($teacher->role)->toBe('teacher')
        ->and($teacher->phone)->toBe('+255765890921');

    $teacherClub = $teacher->teacherClubs()->first();

    expect($teacherClub)->not->toBeNull()
        ->and($teacherClub->school_id)->toBe($school->id)
        ->and($teacherClub->club_id)->toBe($club->id)
        ->and($teacherClub->status)->toBe('approved')
        ->and($teacherClub->is_leader)->toBeTrue()
        ->and($teacherClub->club_day)->toBe('monday');
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
