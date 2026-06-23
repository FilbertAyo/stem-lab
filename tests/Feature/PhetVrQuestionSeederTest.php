<?php

use App\Models\Subject;
use App\Models\VrExperiment;
use App\Models\VrQuestion;
use App\Models\VrQuestionBank;
use Database\Seeders\PhetVrQuestionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

test('phet vr question seeder attaches questions only to existing experiments', function () {
    $subject = Subject::query()->create([
        'subject_name' => 'Physics '.Str::random(4),
        'is_active' => true,
        'display_order' => 1,
    ]);

    VrExperiment::query()->create([
        'name' => 'Forces and Motion: Basics',
        'description' => 'Explore forces',
        'external_url' => 'https://phet.colorado.edu/sims/html/forces-and-motion-basics/latest/forces-and-motion-basics_en.html',
        'subject_id' => $subject->id,
        'is_active' => true,
    ]);

    $this->seed(PhetVrQuestionSeeder::class);

    $bank = VrQuestionBank::query()
        ->where('title', 'Forces and Motion: Basics — Physics Assessment')
        ->first();

    expect($bank)->not->toBeNull()
        ->and($bank->is_active)->toBeFalse()
        ->and($bank->questions()->count())->toBe(10)
        ->and(VrQuestion::query()->count())->toBeGreaterThanOrEqual(10);
});

test('phet vr question seeder skips experiments that are not in the database', function () {
    $beforeBanks = VrQuestionBank::query()->count();
    $beforeQuestions = VrQuestion::query()->count();

    $this->seed(PhetVrQuestionSeeder::class);

    expect(VrQuestionBank::query()->count())->toBe($beforeBanks)
        ->and(VrQuestion::query()->count())->toBe($beforeQuestions);
});

test('phet vr question seeder attaches chemistry questions for build an atom', function () {
    $subject = Subject::query()->create([
        'subject_name' => 'Chemistry '.Str::random(4),
        'is_active' => true,
        'display_order' => 1,
    ]);

    VrExperiment::query()->create([
        'name' => 'Build an Atom',
        'description' => 'Explore atomic structure',
        'external_url' => 'https://phet.colorado.edu/sims/html/build-an-atom/latest/build-an-atom_en.html',
        'subject_id' => $subject->id,
        'is_active' => true,
    ]);

    $this->seed(PhetVrQuestionSeeder::class);

    $bank = VrQuestionBank::query()
        ->where('title', 'Build an Atom — Chemistry Assessment')
        ->first();

    expect($bank)->not->toBeNull()
        ->and($bank->is_active)->toBeFalse()
        ->and($bank->time_limit)->toBe(15)
        ->and($bank->passing_score)->toBe(60)
        ->and($bank->questions()->count())->toBe(10);
});

test('phet vr question seeder is idempotent for the same experiment', function () {
    $subject = Subject::query()->create([
        'subject_name' => 'Physics '.Str::random(4),
        'is_active' => true,
        'display_order' => 1,
    ]);

    VrExperiment::query()->create([
        'name' => 'Ohm\'s Law',
        'description' => 'Explore circuits',
        'external_url' => 'https://phet.colorado.edu/sims/html/ohms-law/latest/ohms-law_en.html',
        'subject_id' => $subject->id,
        'is_active' => true,
    ]);

    $this->seed(PhetVrQuestionSeeder::class);
    $this->seed(PhetVrQuestionSeeder::class);

    $bankCount = VrQuestionBank::query()
        ->where('title', 'Ohm\'s Law — Physics Assessment')
        ->count();

    $questionCount = VrQuestion::query()
        ->whereHas('questionBank', fn ($query) => $query->where('title', 'Ohm\'s Law — Physics Assessment'))
        ->count();

    expect($bankCount)->toBe(1)
        ->and($questionCount)->toBe(10);
});
