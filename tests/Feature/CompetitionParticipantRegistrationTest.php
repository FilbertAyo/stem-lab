<?php

use App\Models\CompetitionRegistration;
use App\Models\SchoolClass;
use App\Models\TeacherClub;
use App\Models\User;
use App\Services\CompetitionParticipantRegistrationService;
use App\Services\TeacherClubJoinService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

require_once __DIR__.'/../Helpers/CompetitionTestHelper.php';

test('teacher can open register page before a draft registration exists', function () {
    ['school' => $school, 'competition' => $competition] = createCompetitionFixture();
    $teacherClub = createTeacherClubForCompetition($competition, $school);
    $teacher = $teacherClub->user;

    expect(
        CompetitionRegistration::query()
            ->where('competition_id', $competition->id)
            ->where('school_id', $school->id)
            ->exists()
    )->toBeFalse();

    $response = $this->actingAs($teacher)->get(route('client.competitions.register', $competition));

    $response->assertOk();
    $response->assertSee('School &amp; teacher contact', false);
});

test('explore teacher signup creates draft competition registration', function () {
    ['club' => $club, 'school' => $school, 'competition' => $competition] = createCompetitionFixture();
    $email = 'jane.'.uniqid().'@school.test';

    $response = $this->post(route('competitions.public.join-as-teacher', $competition), [
        'name' => 'Jane Teacher',
        'email' => $email,
        'phone' => '+255765890921',
        'password' => 'Password1',
        'password_confirmation' => 'Password1',
        'school_id' => $school->id,
    ]);

    $response->assertRedirect(route('client.competitions.index'));

    $user = User::query()->where('email', $email)->first();
    expect($user)->not->toBeNull();

    $teacherClub = TeacherClub::query()->where('user_id', $user->id)->where('club_id', $club->id)->first();
    expect($teacherClub)->not->toBeNull();

    $registration = CompetitionRegistration::query()
        ->where('competition_id', $competition->id)
        ->where('teacher_club_id', $teacherClub->id)
        ->first();

    expect($registration)->not->toBeNull()
        ->and($registration->status)->toBe('draft')
        ->and($registration->student_count_snapshot)->toBe(0)
        ->and($registration->eligibility_met)->toBeFalse();
});

test('club join from dashboard creates draft registration for club competition', function () {
    ['school' => $school, 'competition' => $competition] = createCompetitionFixture();

    $teacher = User::factory()->create(['role' => 'teacher']);

    $joinService = app(TeacherClubJoinService::class);
    $request = Request::create('/', 'POST', ['school_id' => $school->id]);
    $competition->load('club');
    $result = $joinService->join($teacher, $competition->club, $request);

    expect($result['ok'])->toBeTrue();

    $registration = CompetitionRegistration::query()
        ->where('competition_id', $competition->id)
        ->where('teacher_club_id', $result['teacher_club']->id)
        ->first();

    expect($registration)->not->toBeNull()
        ->and($registration->status)->toBe('draft');
});

test('admin registrations index includes draft participants', function () {
    ['school' => $school, 'competition' => $competition] = createCompetitionFixture();
    $teacherClub = createTeacherClubForCompetition($competition, $school);

    app(CompetitionParticipantRegistrationService::class)
        ->ensureDraftRegistration($teacherClub, $competition);

    $admin = User::factory()->create(['role' => 'admin']);
    $host = adminRequestHost();

    $response = $this->actingAs($admin)
        ->withServerVariables(['HTTP_HOST' => $host])
        ->get('http://'.$host.'/competitions/'.$competition->ulid.'/registrations');

    $response->assertOk();
    $response->assertSee('Incomplete');
    $response->assertSee($school->name);
    $response->assertSee('Enroll School');
    $response->assertSee('All regions');
    $response->assertSee('All districts');
});

test('admin can filter competition registrations by region and district', function () {
    ['region' => $regionA, 'district' => $districtA] = createRegionAndDistrict('Mara', 'Musoma');
    ['region' => $regionB, 'district' => $districtB] = createRegionAndDistrict('Mwanza', 'Ilemela');

    ['school' => $schoolA, 'competition' => $competition] = createCompetitionFixture();
    $schoolA->update([
        'region_id' => $regionA->id,
        'district_id' => $districtA->id,
    ]);

    $schoolB = \App\Models\School::query()->create([
        'name' => 'Other School '.Str::random(6),
        'is_active' => true,
        'region_id' => $regionB->id,
        'district_id' => $districtB->id,
    ]);

    $teacherClubA = createTeacherClubForCompetition($competition, $schoolA);
    $teacherClubB = createTeacherClubForCompetition($competition, $schoolB);

    $service = app(CompetitionParticipantRegistrationService::class);
    $registrationA = $service->ensureDraftRegistration($teacherClubA, $competition);
    $registrationB = $service->ensureDraftRegistration($teacherClubB, $competition);

    $registrationA->update([
        'region_id' => $regionA->id,
        'district_id' => $districtA->id,
    ]);
    $registrationB->update([
        'region_id' => $regionB->id,
        'district_id' => $districtB->id,
    ]);

    $admin = User::factory()->create(['role' => 'admin']);
    $host = adminRequestHost();

    $response = $this->actingAs($admin)
        ->withServerVariables(['HTTP_HOST' => $host])
        ->get('http://'.$host.'/competitions/'.$competition->ulid.'/registrations?region_id='.$regionA->id.'&district_id='.$districtA->id);

    $response->assertOk();
    $response->assertSee($schoolA->name);
    $response->assertDontSee($schoolB->name);
});

test('admin can enroll a school into a competition registration', function () {
    ['school' => $school, 'competition' => $competition] = createCompetitionFixture();
    createTeacherClubForCompetition($competition, $school);

    expect(
        CompetitionRegistration::query()
            ->where('competition_id', $competition->id)
            ->where('school_id', $school->id)
            ->exists()
    )->toBeFalse();

    $admin = User::factory()->create(['role' => 'admin']);
    $host = adminRequestHost();

    $response = $this->actingAs($admin)
        ->withServerVariables(['HTTP_HOST' => $host])
        ->post('http://'.$host.'/competitions/'.$competition->ulid.'/registrations/enroll', [
            'school_id' => $school->id,
        ]);

    $response->assertRedirect('http://'.$host.'/competitions/'.$competition->ulid.'/registrations');
    $response->assertSessionHas('status');

    $registration = CompetitionRegistration::query()
        ->where('competition_id', $competition->id)
        ->where('school_id', $school->id)
        ->first();

    expect($registration)->not->toBeNull()
        ->and($registration->status)->toBe('draft')
        ->and($registration->school_name)->toBe($school->name);
});

test('admin cannot enroll a school that is already registered', function () {
    ['school' => $school, 'competition' => $competition] = createCompetitionFixture();
    $teacherClub = createTeacherClubForCompetition($competition, $school);

    app(CompetitionParticipantRegistrationService::class)
        ->ensureDraftRegistration($teacherClub, $competition);

    $admin = User::factory()->create(['role' => 'admin']);
    $host = adminRequestHost();

    $response = $this->actingAs($admin)
        ->withServerVariables(['HTTP_HOST' => $host])
        ->from('http://'.$host.'/competitions/'.$competition->ulid.'/registrations')
        ->post('http://'.$host.'/competitions/'.$competition->ulid.'/registrations/enroll', [
            'school_id' => $school->id,
        ]);

    $response->assertRedirect('http://'.$host.'/competitions/'.$competition->ulid.'/registrations');
    $response->assertSessionHas('error');

    expect(
        CompetitionRegistration::query()
            ->where('competition_id', $competition->id)
            ->where('school_id', $school->id)
            ->count()
    )->toBe(1);
});

test('admin can override failed eligibility and pass a participant registration', function () {
    ['school' => $school, 'competition' => $competition] = createCompetitionFixture();
    $teacherClub = createTeacherClubForCompetition($competition, $school);

    $registration = app(CompetitionParticipantRegistrationService::class)
        ->ensureDraftRegistration($teacherClub, $competition);

    $admin = User::factory()->create(['role' => 'admin']);
    $host = adminRequestHost();

    $response = $this->actingAs($admin)
        ->withServerVariables(['HTTP_HOST' => $host])
        ->post(
            'http://'.$host.'/competitions/'.$competition->ulid.'/registrations/'.$registration->ulid.'/override-eligibility',
            ['override_reason' => 'Approved after manual review of the participant circumstances.'],
        );

    $response->assertRedirect();
    $response->assertSessionHas('status');

    $registration->refresh();

    expect($registration->status)->toBe('approved')
        ->and($registration->eligibility_met)->toBeFalse()
        ->and($registration->approved_by)->toBe($admin->id)
        ->and($registration->approved_at)->not->toBeNull()
        ->and($registration->submitted_at)->not->toBeNull();

    $event = \App\Models\CompetitionStageEvent::query()
        ->where('competition_id', $competition->id)
        ->where('event_type', 'registration_eligibility_overridden')
        ->first();

    expect($event)->not->toBeNull()
        ->and($event->actor_user_id)->toBe($admin->id)
        ->and($event->payload['reason'])->toBe('Approved after manual review of the participant circumstances.');
});

test('store registration promotes draft to pending when eligibility passes', function () {
    ['school' => $school, 'competition' => $competition] = createCompetitionFixture();
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
            'school_name' => $school->name,
            'school_district_id' => $school->district_id,
            'school_ward' => null,
        ],
    );

    $response->assertRedirect(route('client.competitions.show', $competition));

    $registration = CompetitionRegistration::query()
        ->where('teacher_club_id', $teacherClub->id)
        ->where('competition_id', $competition->id)
        ->first();

    expect($registration->status)->toBe('pending')
        ->and($registration->eligibility_met)->toBeTrue()
        ->and($registration->student_count_snapshot)->toBe(75);
});

test('backfill command only creates registrations for teacher clubs in date range', function () {
    ['school' => $school, 'competition' => $competition] = createCompetitionFixture();
    $otherSchool = \App\Models\School::query()->create([
        'name' => 'Other School '.Str::random(6),
        'is_active' => true,
    ]);

    $inRange = createTeacherClubForCompetition($competition, $school);
    $inRange->forceFill(['created_at' => '2026-05-12 10:00:00', 'updated_at' => '2026-05-12 10:00:00'])->save();

    $outOfRange = createTeacherClubForCompetition($competition, $otherSchool, [
        'email' => 'old.'.uniqid().'@school.test',
    ]);
    $outOfRange->forceFill(['created_at' => '2026-05-01 10:00:00', 'updated_at' => '2026-05-01 10:00:00'])->save();

    Artisan::call('competitions:backfill-participant-registrations', [
        'competition_ulid' => $competition->ulid,
        '--from' => '2026-05-11',
        '--to' => '2026-05-16',
    ]);

    expect(
        CompetitionRegistration::query()
            ->where('competition_id', $competition->id)
            ->where('school_id', $school->id)
            ->exists()
    )->toBeTrue();

    expect(
        CompetitionRegistration::query()
            ->where('competition_id', $competition->id)
            ->where('school_id', $otherSchool->id)
            ->exists()
    )->toBeFalse();
});
