<?php

use App\Models\CompetitionIdea;
use App\Models\CompetitionPhase;
use App\Models\CompetitionPhaseEvidenceForm;
use App\Models\CompetitionPrototypeSubmission;
use App\Models\CompetitionShortlist;
use App\Services\CompetitionParticipantRegistrationService;
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
    CompetitionPhaseEvidenceForm::query()->create([
        'competition_phase_id' => $withFeedback['phase']->id,
        'google_form_url' => 'https://docs.google.com/forms/d/e/example/viewform',
    ]);

    $visible = $this->actingAs($withFeedback['teacherClub']->user)
        ->get(route('client.competitions.show', $withFeedback['competition']).'?phase=prototype_development');

    $visible->assertOk();
    $visible->assertSee('Feedback on your idea', false);
    $visible->assertSee('Please show how the bag holds water.', false);
    $visible->assertSee('View representatives', false);
    $visible->assertSee('https://docs.google.com/forms/d/e/example/viewform', false);
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
