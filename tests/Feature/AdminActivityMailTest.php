<?php

use App\Mail\AdminActivityMail;
use App\Mail\ContactSupportMail;
use App\Models\Club;
use App\Models\CompetitionRegistration;
use App\Models\User;
use App\Services\AdminActivityNotifier;
use App\Services\TeacherClubJoinService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

require_once __DIR__.'/../Helpers/CompetitionTestHelper.php';

test('new school registration emails every active admin and the support inbox', function () {
    Mail::fake();

    $firstAdmin = User::factory()->create([
        'role' => 'admin',
        'email' => 'admin.one@example.com',
        'is_active' => true,
    ]);
    $secondAdmin = User::factory()->create([
        'role' => 'admin',
        'email' => 'admin.two@example.com',
        'is_active' => true,
    ]);
    User::factory()->create([
        'role' => 'admin',
        'email' => 'away.admin@example.com',
        'is_active' => false,
    ]);

    $club = Club::query()->create([
        'name' => 'Club '.Str::random(6),
        'is_active' => true,
    ]);
    ['region' => $region, 'district' => $district] = createRegionAndDistrict('Mara', 'Alert District');
    $teacher = User::factory()->create([
        'role' => 'teacher',
        'name' => 'Asha Teacher',
        'email' => 'asha.teacher@example.com',
    ]);

    $schoolName = 'Alert School '.Str::random(6);
    $result = app(TeacherClubJoinService::class)->join(
        $teacher,
        $club,
        Request::create('/', 'POST', [
            'is_new_school' => true,
            'school_name' => $schoolName,
            'school_region_id' => $region->id,
            'school_district_id' => $district->id,
        ]),
    );

    expect($result['ok'])->toBeTrue();

    Mail::assertSent(AdminActivityMail::class, function (AdminActivityMail $mail) use ($firstAdmin, $secondAdmin, $schoolName) {
        return $mail->hasTo($firstAdmin->email)
            && $mail->hasTo($secondAdmin->email)
            && $mail->hasTo('support@adilishastemlabs.com')
            && ! $mail->hasTo('away.admin@example.com')
            && str_contains($mail->subjectLine, 'review new school')
            && str_contains($mail->summary, $schoolName)
            && str_contains((string) $mail->actionUrl, '/schools/');
    });
});

test('a newly registered user emails every active admin', function () {
    Mail::fake();

    $admin = User::factory()->create([
        'role' => 'admin',
        'email' => 'admin.user@example.com',
        'is_active' => true,
    ]);
    $teacher = User::factory()->create([
        'role' => 'teacher',
        'name' => 'Baraka Teacher',
        'email' => 'baraka.teacher@example.com',
    ]);

    event(new Registered($teacher));

    Mail::assertSent(AdminActivityMail::class, function (AdminActivityMail $mail) use ($admin) {
        return $mail->hasTo($admin->email)
            && $mail->hasTo('support@adilishastemlabs.com')
            && $mail->headline === 'New user registered'
            && str_contains($mail->summary, 'Baraka Teacher');
    });
});

test('competition submission emails every active admin with a review link', function () {
    Mail::fake();

    $admin = User::factory()->create([
        'role' => 'admin',
        'email' => 'admin.comp@example.com',
        'is_active' => true,
    ]);
    ['school' => $school, 'competition' => $competition] = createCompetitionFixture();
    $teacherClub = createTeacherClubForCompetition($competition, $school);

    $registration = CompetitionRegistration::query()->create([
        'competition_id' => $competition->id,
        'teacher_club_id' => $teacherClub->id,
        'teacher_user_id' => $teacherClub->user_id,
        'school_id' => $school->id,
        'status' => 'pending',
        'teacher_name' => 'Idea Teacher',
        'teacher_email' => 'idea.teacher@example.com',
        'school_name' => $school->name,
        'submitted_at' => now(),
    ]);

    app(AdminActivityNotifier::class)->competitionSubmitted(
        'idea',
        $competition,
        $registration,
        'Clean water pump',
        'https://admin.example.test/competitions/idea',
    );

    Mail::assertSent(AdminActivityMail::class, function (AdminActivityMail $mail) use ($admin, $school) {
        return $mail->hasTo($admin->email)
            && $mail->hasTo('support@adilishastemlabs.com')
            && $mail->headline === 'Competition idea submitted'
            && str_contains($mail->summary, $school->name)
            && $mail->actionUrl === 'https://admin.example.test/competitions/idea'
            && str_contains((string) $mail->nextStep, 'shortlist');
    });
});

test('contact form is sent to every active admin and support', function () {
    Mail::fake();

    $admin = User::factory()->create([
        'role' => 'admin',
        'email' => 'admin.contact@example.com',
        'is_active' => true,
    ]);

    $response = $this->post('/support', [
        'name' => 'Parent User',
        'email' => 'parent@example.com',
        'message' => 'Please help with the club login.',
    ]);

    $response->assertRedirect(route('support'));
    $response->assertSessionHas('success');

    Mail::assertSent(ContactSupportMail::class, function (ContactSupportMail $mail) use ($admin) {
        return $mail->hasTo($admin->email)
            && $mail->hasTo('support@adilishastemlabs.com')
            && $mail->senderEmail === 'parent@example.com';
    });
});
