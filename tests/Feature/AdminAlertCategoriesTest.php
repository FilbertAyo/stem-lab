<?php

use App\Mail\AdminActivityMail;
use App\Mail\AdminAttentionDigestMail;
use App\Mail\ContactSupportMail;
use App\Models\Club;
use App\Models\CompetitionRegistration;
use App\Models\School;
use App\Models\User;
use App\Services\AdminActivityNotifier;
use App\Services\TeacherClubJoinService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

require_once __DIR__.'/../Helpers/CompetitionTestHelper.php';

test('admin with only competition category does not receive club-only alerts', function () {
    Mail::fake();

    $competitionAdmin = User::factory()->create([
        'role' => 'admin',
        'email' => 'comp.only@example.com',
        'is_active' => true,
        'admin_alert_categories' => ['competition'],
    ]);
    $clubsAdmin = User::factory()->create([
        'role' => 'admin',
        'email' => 'clubs.only@example.com',
        'is_active' => true,
        'admin_alert_categories' => ['clubs'],
    ]);

    $club = Club::query()->create([
        'name' => 'Club '.Str::random(6),
        'is_active' => true,
    ]);
    $school = School::query()->create([
        'name' => 'Existing School '.Str::random(6),
        'is_active' => true,
        'admin_reviewed_at' => now(),
    ]);
    $teacher = User::factory()->create([
        'role' => 'teacher',
        'name' => 'Club Only Teacher',
        'email' => 'club.only.teacher@example.com',
    ]);

    $result = app(TeacherClubJoinService::class)->join(
        $teacher,
        $club,
        Request::create('/', 'POST', [
            'school_id' => $school->id,
        ]),
    );

    expect($result['ok'])->toBeTrue();

    Mail::assertSent(AdminActivityMail::class, function (AdminActivityMail $mail) use ($clubsAdmin, $competitionAdmin) {
        return $mail->headline === 'Teacher joined a club'
            && $mail->hasTo($clubsAdmin->email)
            && $mail->hasTo('support@adilishastemlabs.com')
            && ! $mail->hasTo($competitionAdmin->email);
    });
});

test('new school alerts merge needs_attention and clubs recipients and always include support', function () {
    Mail::fake();

    $needsAdmin = User::factory()->create([
        'role' => 'admin',
        'email' => 'needs@example.com',
        'is_active' => true,
        'admin_alert_categories' => ['needs_attention'],
    ]);
    $clubsAdmin = User::factory()->create([
        'role' => 'admin',
        'email' => 'clubs@example.com',
        'is_active' => true,
        'admin_alert_categories' => ['clubs'],
    ]);
    $contentAdmin = User::factory()->create([
        'role' => 'admin',
        'email' => 'content@example.com',
        'is_active' => true,
        'admin_alert_categories' => ['content'],
    ]);

    $club = Club::query()->create([
        'name' => 'Club '.Str::random(6),
        'is_active' => true,
    ]);
    ['region' => $region, 'district' => $district] = createRegionAndDistrict('Mara', 'Category District');
    $teacher = User::factory()->create(['role' => 'teacher']);

    $schoolName = 'Category School '.Str::random(6);
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

    Mail::assertSent(AdminActivityMail::class, function (AdminActivityMail $mail) use ($needsAdmin, $clubsAdmin, $contentAdmin, $schoolName) {
        return str_contains($mail->subjectLine, 'review new school')
            && str_contains($mail->summary, $schoolName)
            && $mail->hasTo($needsAdmin->email)
            && $mail->hasTo($clubsAdmin->email)
            && $mail->hasTo('support@adilishastemlabs.com')
            && ! $mail->hasTo($contentAdmin->email);
    });
});

test('admins with null categories still receive all alerts', function () {
    Mail::fake();

    $legacyAdmin = User::factory()->create([
        'role' => 'admin',
        'email' => 'legacy.admin@example.com',
        'is_active' => true,
        'admin_alert_categories' => null,
    ]);

    $teacher = User::factory()->create([
        'role' => 'teacher',
        'name' => 'Legacy Path Teacher',
        'email' => 'legacy.path@example.com',
    ]);

    event(new Registered($teacher));

    Mail::assertSent(AdminActivityMail::class, function (AdminActivityMail $mail) use ($legacyAdmin) {
        return $mail->headline === 'New user registered'
            && $mail->hasTo($legacyAdmin->email)
            && $mail->hasTo('support@adilishastemlabs.com');
    });
});

test('contact form only goes to technical category admins and support', function () {
    Mail::fake();

    $technicalAdmin = User::factory()->create([
        'role' => 'admin',
        'email' => 'tech@example.com',
        'is_active' => true,
        'admin_alert_categories' => ['technical'],
    ]);
    $competitionAdmin = User::factory()->create([
        'role' => 'admin',
        'email' => 'comp@example.com',
        'is_active' => true,
        'admin_alert_categories' => ['competition'],
    ]);

    $response = $this->post('/support', [
        'name' => 'Parent User',
        'email' => 'parent@example.com',
        'message' => 'Please help with the club login.',
    ]);

    $response->assertRedirect(route('support'));

    Mail::assertSent(ContactSupportMail::class, function (ContactSupportMail $mail) use ($technicalAdmin, $competitionAdmin) {
        return $mail->hasTo($technicalAdmin->email)
            && $mail->hasTo('support@adilishastemlabs.com')
            && ! $mail->hasTo($competitionAdmin->email);
    });
});

test('admin create and update persist alert categories and reject empty assignment', function () {
    $actor = User::factory()->create([
        'role' => 'admin',
        'email' => 'actor@example.com',
        'is_active' => true,
        'admin_alert_categories' => ['technical'],
    ]);
    $host = adminRequestHost();

    $create = $this->actingAs($actor)
        ->withServerVariables(['HTTP_HOST' => $host])
        ->post('http://'.$host.'/users', [
            'name' => 'Queue Admin',
            'email' => 'queue.admin@example.com',
            'password' => 'Password1!',
            'admin_alert_categories' => ['competition', 'clubs'],
        ]);

    $create->assertRedirect();
    $created = User::query()->where('email', 'queue.admin@example.com')->first();
    expect($created)->not->toBeNull()
        ->and($created->admin_alert_categories)->toEqualCanonicalizing(['competition', 'clubs']);

    $empty = $this->actingAs($actor)
        ->withServerVariables(['HTTP_HOST' => $host])
        ->put('http://'.$host.'/users/'.$created->public_id, [
            'name' => 'Queue Admin',
            'email' => 'queue.admin@example.com',
            'admin_alert_categories' => [],
        ]);

    $empty->assertSessionHasErrors('admin_alert_categories');

    $update = $this->actingAs($actor)
        ->withServerVariables(['HTTP_HOST' => $host])
        ->put('http://'.$host.'/users/'.$created->public_id, [
            'name' => 'Queue Admin',
            'email' => 'queue.admin@example.com',
            'admin_alert_categories' => ['content', 'needs_attention'],
        ]);

    $update->assertRedirect();
    expect($created->fresh()->admin_alert_categories)->toEqualCanonicalizing(['content', 'needs_attention']);
});

test('admin users index shows alert category badges', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'email' => 'viewer@example.com',
        'is_active' => true,
        'admin_alert_categories' => ['competition', 'technical'],
    ]);
    $host = adminRequestHost();

    $response = $this->actingAs($admin)
        ->withServerVariables(['HTTP_HOST' => $host])
        ->get('http://'.$host.'/users');

    $response->assertOk();
    $response->assertSee('Alert categories');
    $response->assertSee('Competitions');
    $response->assertSee('Technical / support');
});

test('digest command sends category digests only when backlog exists and routes by category', function () {
    Mail::fake();

    $needsAdmin = User::factory()->create([
        'role' => 'admin',
        'email' => 'digest.needs@example.com',
        'is_active' => true,
        'admin_alert_categories' => ['needs_attention'],
    ]);
    $competitionAdmin = User::factory()->create([
        'role' => 'admin',
        'email' => 'digest.comp@example.com',
        'is_active' => true,
        'admin_alert_categories' => ['competition'],
    ]);

    School::query()->create([
        'name' => 'Unreviewed Digest School '.Str::random(5),
        'is_active' => true,
        'admin_reviewed_at' => null,
    ]);

    ['school' => $school, 'competition' => $competition] = createCompetitionFixture();
    $teacherClub = createTeacherClubForCompetition($competition, $school);
    CompetitionRegistration::query()->create([
        'competition_id' => $competition->id,
        'teacher_club_id' => $teacherClub->id,
        'teacher_user_id' => $teacherClub->user_id,
        'school_id' => $school->id,
        'status' => 'pending',
        'teacher_name' => 'Digest Teacher',
        'teacher_email' => 'digest.teacher@example.com',
        'school_name' => $school->name,
        'submitted_at' => now()->subDays(2),
    ]);

    Artisan::call('admin:send-attention-digest');

    Mail::assertSent(AdminAttentionDigestMail::class, function (AdminAttentionDigestMail $mail) use ($needsAdmin, $competitionAdmin) {
        return $mail->categoryKey === 'needs_attention'
            && $mail->hasTo($needsAdmin->email)
            && $mail->hasTo('support@adilishastemlabs.com')
            && ! $mail->hasTo($competitionAdmin->email);
    });

    Mail::assertSent(AdminAttentionDigestMail::class, function (AdminAttentionDigestMail $mail) use ($competitionAdmin, $needsAdmin) {
        return $mail->categoryKey === 'competition'
            && $mail->hasTo($competitionAdmin->email)
            && $mail->hasTo('support@adilishastemlabs.com')
            && ! $mail->hasTo($needsAdmin->email);
    });
});

test('digest command skips categories with empty backlog', function () {
    Mail::fake();

    User::factory()->create([
        'role' => 'admin',
        'email' => 'empty.digest@example.com',
        'is_active' => true,
        'admin_alert_categories' => ['content'],
    ]);

    Artisan::call('admin:send-attention-digest');

    Mail::assertNotSent(AdminAttentionDigestMail::class);
});

test('competition submission still reaches competition category admins', function () {
    Mail::fake();

    $competitionAdmin = User::factory()->create([
        'role' => 'admin',
        'email' => 'comp.queue@example.com',
        'is_active' => true,
        'admin_alert_categories' => ['competition'],
    ]);
    $clubsAdmin = User::factory()->create([
        'role' => 'admin',
        'email' => 'clubs.queue@example.com',
        'is_active' => true,
        'admin_alert_categories' => ['clubs'],
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

    Mail::assertSent(AdminActivityMail::class, function (AdminActivityMail $mail) use ($competitionAdmin, $clubsAdmin) {
        return $mail->headline === 'Competition idea submitted'
            && $mail->hasTo($competitionAdmin->email)
            && $mail->hasTo('support@adilishastemlabs.com')
            && ! $mail->hasTo($clubsAdmin->email);
    });
});
