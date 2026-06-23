<?php

use App\Models\VrQuestion;
use App\Models\VrQuestionBank;
use Database\Seeders\PhetVrQuestionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('phet vr question seeder is disabled and does not modify data', function () {
    $this->seed(PhetVrQuestionSeeder::class);

    expect(VrQuestionBank::query()->count())->toBe(0)
        ->and(VrQuestion::query()->count())->toBe(0);
});
