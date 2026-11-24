<?php

use App\Models\CompetitionIdea;
use App\Models\CompetitionPhase;
use App\Models\CompetitionPrototypeImage;
use App\Models\CompetitionPrototypeModification;
use App\Models\CompetitionPrototypeShortlist;
use App\Models\CompetitionPrototypeSubmission;
use App\Models\CompetitionShortlist;
use App\Services\Competition\PrototypeMediaService;
use App\Services\CompetitionParticipantRegistrationService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

require_once __DIR__.'/../Helpers/CompetitionTestHelper.php';

function prototypeFixture(array $ideaOverrides = []): array
{
    ['school' => $school, 'competition' => $competition] = createCompetitionFixture();
    $teacherEmail = 'teacher-prototype-'.Str::lower(Str::random(8)).'@example.com';
    $teacherClub = createTeacherClubForCompetition($competition, $school, [
        'email' => $teacherEmail,
    ]);

    $registration = app(CompetitionParticipantRegistrationService::class)
        ->ensureDraftRegistration($teacherClub, $competition);

    $registration->update([
        'status' => 'approved',
        'approved_at' => now(),
        'teacher_email' => $teacherEmail,
        'school_name' => $school->name,
        'eligibility_met' => true,
    ]);

    $phase = CompetitionPhase::query()->create([
        'competition_id' => $competition->id,
        'phase_key' => 'prototype_development',
        'name' => 'Prototype Development',
        'sequence' => 4,
        'status' => 'open',
        'opens_at' => now()->subDay(),
        'closes_at' => now()->addMonth(),
        'is_required_action' => false,
        'action_type' => 'prototype_development',
    ]);

    $idea = CompetitionIdea::query()->create(array_merge([
        'competition_id' => $competition->id,
        'competition_registration_id' => $registration->id,
        'status' => 'shortlisted',
        'problem_title' => 'Root-waste bags',
        'problem_statement' => 'Plastic waste.',
        'proposed_solution' => 'Use tuber starch.',
        'stem_category' => 'Environment',
        'submitted_at' => now()->subDay(),
        'admin_feedback' => 'Please show how the bag holds water.',
    ], $ideaOverrides));

    CompetitionShortlist::query()->create([
        'competition_id' => $competition->id,
        'competition_idea_id' => $idea->id,
        'status' => 'shortlisted',
        'decided_at' => now(),
    ]);

    return compact('competition', 'registration', 'idea', 'phase', 'teacherClub');
}

function prototypeVideo(string $name = 'demo.mp4', int $kilobytes = 512): UploadedFile
{
    return UploadedFile::fake()->create($name, $kilobytes, 'video/mp4');
}

function prototypePhoto(string $name = 'photo.jpg', int $kilobytes = 40): UploadedFile
{
    return UploadedFile::fake()->image($name, 40, 40)->size($kilobytes);
}

function prototypePayload(array $overrides = []): array
{
    return array_merge([
        'prototype_title' => 'Starch bag press',
        'prototype_concept' => 'A hand press that forms bags from tuber waste.',
        'prototype_objectives' => 'Replace thin plastic bags at the market.',
        'prototype_design_description' => 'Dry the mash, then press it in a wooden frame.',
        'prototype_materials' => 'Cassava peel, wooden frame, cloth.',
        'prototype_build_process' => 'Dry, grind, wet, press, and dry again.',
        'prototype_challenges' => 'Bags tore when still damp.',
        'prototype_test_results' => 'Held 1kg of beans.',
        'prototype_next_steps' => 'Thicken the walls.',
    ], $overrides);
}

test('prototype draft saves partial answers and submit requires the rest', function () {
    $fixture = prototypeFixture();
    $teacher = $fixture['teacherClub']->user;

    $draft = $this->actingAs($teacher)->post(route('client.competitions.prototype.store', $fixture['competition']), [
        'prototype_title' => 'Starch bag press',
        'prototype_concept' => 'A first sketch.',
        'submit_final' => '0',
        'submit_intent' => 'draft',
    ]);

    $draft->assertRedirect();
    $draft->assertSessionHas('status', 'Prototype draft saved. It is not submitted yet.');

    $saved = CompetitionPrototypeSubmission::query()->where('competition_idea_id', $fixture['idea']->id)->first();
    expect($saved)->not->toBeNull()
        ->and($saved->status)->toBe('draft')
        ->and($saved->prototype_title)->toBe('Starch bag press')
        ->and($saved->prototype_concept)->toBe('A first sketch.')
        ->and($saved->submitted_at)->toBeNull();

    $partial = $this->actingAs($teacher)->post(route('client.competitions.prototype.store', $fixture['competition']), [
        'prototype_objectives' => 'Carry market goods.',
        'submit_final' => '0',
        'submit_intent' => 'draft',
    ]);
    $partial->assertSessionHas('status', 'Prototype draft saved. It is not submitted yet.');

    $saved->refresh();
    expect($saved->prototype_title)->toBe('Starch bag press')
        ->and($saved->prototype_concept)->toBe('A first sketch.')
        ->and($saved->prototype_objectives)->toBe('Carry market goods.')
        ->and($saved->status)->toBe('draft');

    $rejected = $this->actingAs($teacher)->post(route('client.competitions.prototype.store', $fixture['competition']), [
        'prototype_title' => 'Starch bag press',
        'submit_final' => '1',
        'submit_intent' => 'submit',
    ]);

    $rejected->assertSessionHasErrors([
        'prototype_concept',
        'prototype_objectives',
        'prototype_design_description',
        'prototype_materials',
        'prototype_build_process',
        'prototype_video',
    ]);
    expect($saved->fresh()->status)->toBe('draft')
        ->and($saved->fresh()->prototype_title)->toBe('Starch bag press');
});

test('prototype submit persists and a later draft does not unsubmit', function () {
    $fixture = prototypeFixture();
    $teacher = $fixture['teacherClub']->user;

    $submitted = $this->actingAs($teacher)->post(
        route('client.competitions.prototype.store', $fixture['competition']),
        prototypePayload([
            'submit_final' => '1',
            'submit_intent' => 'submit',
            'prototype_video' => prototypeVideo(),
        ])
    );

    $submitted->assertRedirect();
    $submitted->assertSessionHas('status', 'Prototype submitted successfully. Awaiting prototype shortlisting.');

    $saved = CompetitionPrototypeSubmission::query()->where('competition_idea_id', $fixture['idea']->id)->first();
    $submittedAt = $saved->submitted_at;
    expect($saved->status)->toBe('submitted')
        ->and($submittedAt)->not->toBeNull()
        ->and(CompetitionPrototypeSubmission::query()->where('competition_idea_id', $fixture['idea']->id)->count())->toBe(1);

    $draftAgain = $this->actingAs($teacher)->post(route('client.competitions.prototype.store', $fixture['competition']), [
        'prototype_challenges' => 'The first batch cracked.',
        'submit_final' => '0',
        'submit_intent' => 'draft',
    ]);

    $draftAgain->assertSessionHas('status', 'Prototype answers saved. It remains submitted.');
    $saved->refresh();
    expect($saved->status)->toBe('submitted')
        ->and($saved->prototype_title)->toBe('Starch bag press')
        ->and($saved->prototype_challenges)->toBe('The first batch cracked.')
        ->and($saved->submitted_at)->not->toBeNull();
});

test('prototype page shows admin feedback and hides an empty feedback block', function () {
    $withFeedback = prototypeFixture();

    $visible = $this->actingAs($withFeedback['teacherClub']->user)
        ->get(route('client.competitions.show', $withFeedback['competition']).'?phase=prototype_development');

    $visible->assertOk();
    $visible->assertSee('Feedback on your idea', false);
    $visible->assertSee('Please show how the bag holds water.', false);
    $visible->assertSee('View representatives', false);
    $visible->assertSee('Demo video', false);
    $visible->assertSee('Brief note about the media', false);
    $visible->assertDontSee('Google Form', false);
    $visible->assertSee('Save draft', false);
    $visible->assertSee('Submit prototype', false);

    $without = prototypeFixture(['admin_feedback' => null]);
    $quiet = $this->actingAs($without['teacherClub']->user)
        ->get(route('client.competitions.show', $without['competition']).'?phase=prototype_development');

    $quiet->assertOk();
    $quiet->assertDontSee('Feedback on your idea', false);
});

test('competition short link redirects and the ulid url stays in place', function () {
    ['competition' => $competition] = createCompetitionFixture();

    expect($competition->sms_code)->not->toBeEmpty();

    $canonical = route('client.competitions.show', $competition);
    expect($canonical)->toContain('/competitions/'.$competition->ulid)
        ->and($canonical)->not->toContain('/c/'.$competition->sms_code);

    $this->get('/c/'.$competition->sms_code)->assertRedirect($canonical);

    $legacy = $this->get('/competitions/'.$competition->ulid);
    $legacy->assertRedirect();
    expect($legacy->headers->get('Location'))->not->toContain('/c/'.$competition->sms_code);
});

test('prototype media stores one video, photos, and an optional note', function () {
    Storage::fake('public');
    $fixture = prototypeFixture();
    $teacher = $fixture['teacherClub']->user;

    $response = $this->actingAs($teacher)->post(route('client.competitions.prototype.store', $fixture['competition']), prototypePayload([
        'submit_final' => '1',
        'submit_intent' => 'submit',
        'prototype_video' => prototypeVideo(),
        'prototype_images' => [
            prototypePhoto('front.jpg'),
            prototypePhoto('side.jpg'),
        ],
        'prototype_media_notes' => 'Filmed in the workshop.',
    ]));

    $response->assertRedirect();
    $response->assertSessionHasNoErrors();

    $saved = CompetitionPrototypeSubmission::query()->where('competition_idea_id', $fixture['idea']->id)->first();
    expect($saved)->not->toBeNull()
        ->and($saved->status)->toBe('submitted')
        ->and($saved->video_path)->not->toBeEmpty()
        ->and($saved->prototype_media_notes)->toBe('Filmed in the workshop.')
        ->and($saved->images)->toHaveCount(2);

    Storage::disk('public')->assertExists($saved->video_path);
    Storage::disk('public')->assertExists($saved->images[0]->file_path);
    Storage::disk('public')->assertExists($saved->images[1]->file_path);
});

test('prototype media rejects an oversized video, an oversized photo, and a sixth photo', function () {
    Storage::fake('public');
    $fixture = prototypeFixture();
    $teacher = $fixture['teacherClub']->user;
    $route = route('client.competitions.prototype.store', $fixture['competition']);

    $oversizeVideo = $this->actingAs($teacher)->post($route, prototypePayload([
        'submit_final' => '0',
        'prototype_video' => prototypeVideo('big.mp4', 10241),
    ]));
    $oversizeVideo->assertSessionHasErrors('prototype_video');

    $oversizePhoto = $this->actingAs($teacher)->post($route, prototypePayload([
        'submit_final' => '0',
        'prototype_images' => [prototypePhoto('big.jpg', 2049)],
    ]));
    $oversizePhoto->assertSessionHasErrors('prototype_images.0');

    $photos = [];
    for ($i = 1; $i <= 5; $i++) {
        $photos[] = prototypePhoto("kept-{$i}.jpg");
    }

    $this->actingAs($teacher)->post($route, [
        'prototype_title' => 'Starch bag press',
        'submit_final' => '0',
        'prototype_images' => $photos,
    ])->assertSessionHasNoErrors();

    $saved = CompetitionPrototypeSubmission::query()->where('competition_idea_id', $fixture['idea']->id)->first();
    expect($saved->images)->toHaveCount(5);

    $sixth = $this->actingAs($teacher)->post($route, [
        'submit_final' => '0',
        'prototype_images' => [prototypePhoto('extra.jpg')],
    ]);
    $sixth->assertSessionHasErrors('prototype_images');
    expect($saved->fresh()->images)->toHaveCount(5);
    expect($saved->fresh()->prototype_title)->toBe('Starch bag press');
});

test('replacing a prototype video removes the previous file only after the new one is saved', function () {
    Storage::fake('public');
    $fixture = prototypeFixture();
    $teacher = $fixture['teacherClub']->user;
    $route = route('client.competitions.prototype.store', $fixture['competition']);

    $this->actingAs($teacher)->post($route, prototypePayload([
        'submit_final' => '1',
        'prototype_video' => prototypeVideo('first.mp4'),
        'prototype_media_notes' => 'First cut.',
    ]))->assertSessionHasNoErrors();

    $saved = CompetitionPrototypeSubmission::query()->where('competition_idea_id', $fixture['idea']->id)->first();
    $oldPath = $saved->video_path;
    Storage::disk('public')->assertExists($oldPath);

    $this->actingAs($teacher)->post($route, prototypePayload([
        'submit_final' => '1',
        'prototype_video' => prototypeVideo('second.mp4'),
        'prototype_media_notes' => 'Second cut.',
    ]))->assertSessionHasNoErrors();

    $saved->refresh();
    expect($saved->video_path)->not->toBe($oldPath)
        ->and($saved->prototype_media_notes)->toBe('Second cut.');
    Storage::disk('public')->assertMissing($oldPath);
    Storage::disk('public')->assertExists($saved->video_path);
});

test('a failed prototype media save keeps the previous video and drops the new files', function () {
    Storage::fake('public');
    $fixture = prototypeFixture();
    $oldPath = prototypeVideo('old.mp4', 80)->store('competition-prototypes/seed/video', 'public');

    $submission = CompetitionPrototypeSubmission::query()->create([
        'competition_id' => $fixture['competition']->id,
        'competition_registration_id' => $fixture['registration']->id,
        'competition_idea_id' => $fixture['idea']->id,
        'status' => 'draft',
        'prototype_title' => 'Starch bag press',
        'video_path' => $oldPath,
        'prototype_media_notes' => 'Keep this note.',
    ]);

    CompetitionPrototypeImage::creating(function (): void {
        throw new RuntimeException('fail after store');
    });

    try {
        app(PrototypeMediaService::class)->sync(
            $submission,
            'competition-prototypes',
            prototypeVideo('new.mp4', 90),
            [prototypePhoto('new.jpg')],
            [],
            'Replacement note.',
            false,
        );
        expect(false)->toBeTrue();
    } catch (RuntimeException $exception) {
        expect($exception->getMessage())->toBe('fail after store');
    } finally {
        CompetitionPrototypeImage::flushEventListeners();
    }

    $submission->refresh();
    expect($submission->video_path)->toBe($oldPath)
        ->and($submission->prototype_media_notes)->toBe('Keep this note.')
        ->and($submission->images)->toHaveCount(0);
    Storage::disk('public')->assertExists($oldPath);
    expect(Storage::disk('public')->allFiles())->toBe([$oldPath]);
});

test('a teacher cannot remove another team prototype photo', function () {
    Storage::fake('public');
    $first = prototypeFixture();
    $second = prototypeFixture();
    $firstRoute = route('client.competitions.prototype.store', $first['competition']);
    $secondRoute = route('client.competitions.prototype.store', $second['competition']);

    $this->actingAs($first['teacherClub']->user)->post($firstRoute, [
        'prototype_title' => 'First press',
        'submit_final' => '0',
        'prototype_images' => [prototypePhoto('first.jpg')],
    ])->assertSessionHasNoErrors();

    $this->actingAs($second['teacherClub']->user)->post($secondRoute, [
        'prototype_title' => 'Second press',
        'submit_final' => '0',
        'prototype_images' => [prototypePhoto('second.jpg')],
    ])->assertSessionHasNoErrors();

    $firstImage = CompetitionPrototypeSubmission::query()
        ->where('competition_idea_id', $first['idea']->id)
        ->first()
        ->images()
        ->first();
    $secondImage = CompetitionPrototypeSubmission::query()
        ->where('competition_idea_id', $second['idea']->id)
        ->first()
        ->images()
        ->first();

    $stolen = $this->actingAs($first['teacherClub']->user)->post($firstRoute, [
        'prototype_title' => 'First press changed',
        'submit_final' => '0',
        'remove_image_ids' => [$secondImage->id],
    ]);

    $stolen->assertSessionHasErrors('remove_image_ids');
    expect(CompetitionPrototypeImage::query()->whereKey($secondImage->id)->exists())->toBeTrue()
        ->and(CompetitionPrototypeImage::query()->whereKey($firstImage->id)->exists())->toBeTrue()
        ->and(CompetitionPrototypeSubmission::query()->where('competition_idea_id', $first['idea']->id)->first()->prototype_title)
        ->toBe('First press');
    Storage::disk('public')->assertExists($secondImage->file_path);
});

test('prototype modification stores its own video and requires one on submit', function () {
    Storage::fake('public');
    $fixture = prototypeFixture();
    $submission = CompetitionPrototypeSubmission::query()->create([
        'competition_id' => $fixture['competition']->id,
        'competition_registration_id' => $fixture['registration']->id,
        'competition_idea_id' => $fixture['idea']->id,
        'status' => 'submitted',
        'submitted_at' => now(),
        'prototype_title' => 'Starch bag press',
    ]);
    CompetitionPrototypeShortlist::query()->create([
        'competition_id' => $fixture['competition']->id,
        'competition_prototype_submission_id' => $submission->id,
        'status' => 'shortlisted',
        'decided_at' => now(),
    ]);
    CompetitionPhase::query()->create([
        'competition_id' => $fixture['competition']->id,
        'phase_key' => 'prototype_modification',
        'name' => 'Prototype Modification',
        'sequence' => 6,
        'status' => 'open',
        'opens_at' => now()->subDay(),
        'closes_at' => now()->addMonth(),
        'is_required_action' => false,
        'action_type' => 'prototype_modification',
    ]);

    $teacher = $fixture['teacherClub']->user;
    $route = route('client.competitions.prototype-modification.store', $fixture['competition']);

    $missingVideo = $this->actingAs($teacher)->post($route, prototypePayload([
        'submit_final' => '1',
        'submit_intent' => 'submit',
    ]));
    $missingVideo->assertSessionHasErrors('prototype_video');
    expect(CompetitionPrototypeModification::query()->where('competition_idea_id', $fixture['idea']->id)->exists())->toBeFalse();

    $stored = $this->actingAs($teacher)->post($route, prototypePayload([
        'submit_final' => '1',
        'submit_intent' => 'submit',
        'prototype_video' => prototypeVideo('modified.mp4'),
        'prototype_images' => [prototypePhoto('modified.jpg')],
        'prototype_media_notes' => 'Added a stronger frame.',
    ]));
    $stored->assertSessionHasNoErrors();

    $modification = CompetitionPrototypeModification::query()->where('competition_idea_id', $fixture['idea']->id)->first();
    expect($modification)->not->toBeNull()
        ->and($modification->status)->toBe('submitted')
        ->and($modification->video_path)->not->toBeEmpty()
        ->and($modification->prototype_media_notes)->toBe('Added a stronger frame.')
        ->and($modification->images)->toHaveCount(1);
    Storage::disk('public')->assertExists($modification->video_path);
});
