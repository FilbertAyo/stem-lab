<?php

use App\Models\CompetitionPhase;
use App\Support\CompetitionParticipantPhaseNavigator;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

require_once __DIR__.'/../Helpers/CompetitionTestHelper.php';

test('approved participant defaults to latest accessible open phase not registration', function () {
    ['competition' => $competition] = createCompetitionFixture();

    CompetitionPhase::query()->create([
        'competition_id' => $competition->id,
        'phase_key' => 'idea_submission',
        'name' => 'Idea submission',
        'sequence' => 2,
        'status' => 'open',
        'opens_at' => now()->subDay(),
        'closes_at' => now()->addMonth(),
        'is_required_action' => true,
        'action_type' => 'idea',
    ]);

    $competition->load('phases');

    $view = CompetitionParticipantPhaseNavigator::resolve(
        $competition,
        registrationApproved: true,
        ideaSubmitted: false,
        ideaShortlisted: false,
        prototypeSubmitted: false,
        prototypeShortlisted: false,
        modificationSubmitted: false,
    );

    expect($view['accessiblePhaseKeys'])->toBe(['registration', 'idea_submission'])
        ->and($view['defaultPhaseKey'])->toBe('idea_submission')
        ->and($view['selectedPhaseKey'])->toBe('idea_submission');
});

test('pending participant stays on registration when idea submission is open', function () {
    ['competition' => $competition] = createCompetitionFixture();

    CompetitionPhase::query()->create([
        'competition_id' => $competition->id,
        'phase_key' => 'idea_submission',
        'name' => 'Idea submission',
        'sequence' => 2,
        'status' => 'open',
        'opens_at' => now()->subDay(),
        'closes_at' => now()->addMonth(),
        'is_required_action' => true,
        'action_type' => 'idea',
    ]);

    $competition->load('phases');

    $view = CompetitionParticipantPhaseNavigator::resolve(
        $competition,
        registrationApproved: false,
        ideaSubmitted: false,
        ideaShortlisted: false,
        prototypeSubmitted: false,
        prototypeShortlisted: false,
        modificationSubmitted: false,
    );

    expect($view['accessiblePhaseKeys'])->toBe(['registration'])
        ->and($view['selectedPhaseKey'])->toBe('registration');
});

test('participant can request an earlier accessible phase explicitly', function () {
    ['competition' => $competition] = createCompetitionFixture();

    CompetitionPhase::query()->create([
        'competition_id' => $competition->id,
        'phase_key' => 'idea_submission',
        'name' => 'Idea submission',
        'sequence' => 2,
        'status' => 'open',
        'opens_at' => now()->subDay(),
        'closes_at' => now()->addMonth(),
        'is_required_action' => true,
        'action_type' => 'idea',
    ]);

    $competition->load('phases');

    $view = CompetitionParticipantPhaseNavigator::resolve(
        $competition,
        registrationApproved: true,
        ideaSubmitted: false,
        ideaShortlisted: false,
        prototypeSubmitted: false,
        prototypeShortlisted: false,
        modificationSubmitted: false,
        requestedPhaseKey: 'registration',
    );

    expect($view['selectedPhaseKey'])->toBe('registration');
});
