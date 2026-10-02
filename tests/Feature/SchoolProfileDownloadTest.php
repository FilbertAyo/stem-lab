<?php

use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

require_once __DIR__.'/../Helpers/CompetitionTestHelper.php';

test('admin can download a school profile with teachers students and map', function () {
    $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==');
    Http::fake([
        'staticmap.openstreetmap.de/*' => Http::response($png, 200, ['Content-Type' => 'image/png']),
    ]);

    $admin = User::factory()->create(['role' => 'admin']);
    ['school' => $school, 'competition' => $competition] = createCompetitionFixture();
    $school->update([
        'email' => 'office@example.com',
        'phone' => '0765890921',
        'ward' => 'Makumbusho',
        'latitude' => '-6.7923540',
        'longitude' => '39.2083284',
    ]);

    $teacherClub = createTeacherClubForCompetition($competition, $school, [
        'name' => 'Neema Teacher',
        'email' => 'neema@example.com',
        'phone' => '0765890922',
    ]);

    $class = SchoolClass::query()->create([
        'name' => 'Form Two '.Str::random(4),
        'is_active' => true,
    ]);

    $student = Student::query()->create([
        'teacher_club_id' => $teacherClub->id,
        'name' => 'Amina Student',
        'age' => 14,
        'gender' => 'female',
        'school_class_id' => $class->id,
    ]);

    $html = view('admin.downloads.school-profile', [
        'school' => $school->fresh(['regionModel', 'districtModel']),
        'clubGroups' => collect([[
            'name' => $competition->club->name,
            'day' => 'Monday',
            'leader' => 'Neema Teacher',
            'memberships' => collect([$teacherClub->load('user')]),
            'students' => collect([$student->load('schoolClass')]),
        ]]),
        'registrations' => collect(),
        'teacherCount' => 1,
        'studentCount' => 1,
        'mapImage' => 'data:image/png;base64,abc',
        'generatedAt' => now(),
    ])->render();

    expect($html)->toContain('Neema Teacher')
        ->and($html)->toContain('neema@example.com')
        ->and($html)->toContain('Amina Student')
        ->and($html)->toContain($student->admission_number)
        ->and($html)->toContain($class->name)
        ->and($html)->toContain('office@example.com')
        ->and($html)->toContain('Makumbusho')
        ->and($html)->toContain('data:image/png;base64,abc')
        ->and($html)->not->toContain('Created by');

    $host = adminRequestHost();
    $show = $this->actingAs($admin)
        ->withServerVariables(['HTTP_HOST' => $host])
        ->get('http://'.$host.'/schools/'.$school->public_id);

    $show->assertOk();
    $show->assertSee('Download profile');
    $show->assertSee('Neema Teacher');
    $show->assertSee('Amina Student');

    $response = $this->actingAs($admin)
        ->withServerVariables(['HTTP_HOST' => $host])
        ->get('http://'.$host.'/schools/'.$school->public_id.'/profile');

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('application/pdf');
    expect($response->headers->get('content-disposition'))->toContain('school_profile_');

    Http::assertSent(fn ($request) => str_contains($request->url(), 'staticmap.openstreetmap.de'));
});
