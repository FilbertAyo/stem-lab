<?php

use App\Models\Club;
use App\Models\Competition;
use App\Models\CompetitionPhase;
use App\Models\District;
use App\Models\Region;
use App\Models\School;
use App\Models\TeacherClub;
use App\Models\User;
use Illuminate\Support\Str;

function createRegionAndDistrict(string $regionName = 'Mara', string $districtName = 'Musoma'): array
{
    $region = Region::query()->firstOrCreate(['name' => $regionName]);
    $district = District::query()->firstOrCreate(
        ['region_id' => $region->id, 'name' => $districtName],
        ['region_id' => $region->id, 'name' => $districtName],
    );

    return compact('region', 'district');
}

function createCompetitionFixture(array $competitionOverrides = []): array
{
    $club = Club::query()->create([
        'name' => 'Test Club '.Str::random(6),
        'is_active' => true,
    ]);

    $school = School::query()->create([
        'name' => 'Test School '.Str::random(8),
        'is_active' => true,
    ]);

    $competition = Competition::query()->create(array_merge([
        'name' => 'Test Competition',
        'slug' => 'test-comp-'.Str::random(8),
        'club_id' => $club->id,
        'status' => 'ongoing',
        'is_active' => true,
        'min_members' => 75,
        'representative_count' => 10,
    ], $competitionOverrides));

    CompetitionPhase::query()->create([
        'competition_id' => $competition->id,
        'phase_key' => 'registration',
        'name' => 'Registration',
        'sequence' => 1,
        'status' => 'open',
        'opens_at' => now()->subDay(),
        'closes_at' => now()->addMonth(),
        'is_required_action' => true,
        'action_type' => 'registration',
    ]);

    return compact('club', 'school', 'competition');
}

function createTeacherClubForCompetition(Competition $competition, School $school, array $userOverrides = []): TeacherClub
{
    $teacher = User::factory()->create(array_merge([
        'role' => 'teacher',
    ], $userOverrides));

    return TeacherClub::query()->create([
        'user_id' => $teacher->id,
        'club_id' => $competition->club_id,
        'school_id' => $school->id,
        'status' => 'approved',
        'is_leader' => true,
    ]);
}

function adminRequestHost(): string
{
    return parse_url(config('app.admin_url'), PHP_URL_HOST) ?: 'admin.test';
}
