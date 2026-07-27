<?php

use App\Models\Club;
use App\Models\School;
use App\Models\TeacherClub;
use App\Models\User;
use Illuminate\Support\Str;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

require_once __DIR__.'/../Helpers/CompetitionTestHelper.php';

test('admin can search club leaders members and pending requests by name email school or club code', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $host = adminRequestHost();

    $club = Club::query()->create([
        'name' => 'Search Club '.Str::random(6),
        'is_active' => true,
    ]);

    $alphaSchool = School::query()->create([
        'name' => 'Alpha Secondary '.Str::random(4),
        'is_active' => true,
    ]);
    $betaSchool = School::query()->create([
        'name' => 'Beta Primary '.Str::random(4),
        'is_active' => true,
    ]);

    $alphaLeader = User::factory()->create([
        'role' => 'teacher',
        'name' => 'Amina Leader',
        'email' => 'amina.leader@example.com',
    ]);
    $betaMember = User::factory()->create([
        'role' => 'teacher',
        'name' => 'Baraka Member',
        'email' => 'baraka.member@example.com',
    ]);
    $pendingTeacher = User::factory()->create([
        'role' => 'teacher',
        'name' => 'Clara Pending',
        'email' => 'clara.pending@example.com',
    ]);

    TeacherClub::query()->create([
        'user_id' => $alphaLeader->id,
        'club_id' => $club->id,
        'school_id' => $alphaSchool->id,
        'status' => 'approved',
        'is_leader' => true,
        'club_code' => 'ALP-LEAD-1',
    ]);

    TeacherClub::query()->create([
        'user_id' => $betaMember->id,
        'club_id' => $club->id,
        'school_id' => $betaSchool->id,
        'status' => 'approved',
        'is_leader' => false,
        'club_code' => 'BET-MEM-9',
    ]);

    TeacherClub::query()->create([
        'user_id' => $pendingTeacher->id,
        'club_id' => $club->id,
        'school_id' => $alphaSchool->id,
        'status' => 'pending',
        'is_leader' => false,
        'club_code' => 'ALP-PEND-3',
    ]);

    $leadersResponse = $this->actingAs($admin)
        ->withServerVariables(['HTTP_HOST' => $host])
        ->get(route('admin.clubs.show', ['club' => $club, 'tab' => 'leaders', 'leaders_search' => 'Amina']));

    $leadersResponse->assertOk()
        ->assertViewHas('activeTab', 'leaders')
        ->assertViewHas('leaders', function ($leaders) use ($alphaLeader, $betaMember, $pendingTeacher) {
            $names = $leaders->pluck('user.name')->all();

            return in_array('Amina Leader', $names, true)
                && ! in_array('Baraka Member', $names, true)
                && ! in_array('Clara Pending', $names, true)
                && $leaders->every(fn ($row) => (int) $row->user_id === (int) $alphaLeader->id);
        });

    $membersResponse = $this->actingAs($admin)
        ->withServerVariables(['HTTP_HOST' => $host])
        ->get(route('admin.clubs.show', ['club' => $club, 'tab' => 'members', 'members_search' => 'Beta Primary']));

    $membersResponse->assertOk()
        ->assertViewHas('activeTab', 'members')
        ->assertViewHas('approvedMembers', function ($members) use ($betaMember) {
            $names = $members->pluck('user.name')->all();

            return $members->count() === 1
                && in_array('Baraka Member', $names, true)
                && (int) $members->first()->user_id === (int) $betaMember->id;
        });

    $requestsResponse = $this->actingAs($admin)
        ->withServerVariables(['HTTP_HOST' => $host])
        ->get(route('admin.clubs.show', ['club' => $club, 'tab' => 'requests', 'requests_search' => 'ALP-PEND']));

    $requestsResponse->assertOk()
        ->assertViewHas('activeTab', 'requests')
        ->assertViewHas('pendingRequests', function ($requests) use ($pendingTeacher) {
            return $requests->count() === 1
                && (int) $requests->first()->user_id === (int) $pendingTeacher->id;
        });

    $this->actingAs($admin)
        ->withServerVariables(['HTTP_HOST' => $host])
        ->get(route('admin.clubs.show', ['club' => $club, 'tab' => 'leaders', 'leaders_search' => 'no-such-person']))
        ->assertOk()
        ->assertSee('No leaders match your search.');
});
