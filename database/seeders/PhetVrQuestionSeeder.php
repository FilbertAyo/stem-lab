<?php

namespace Database\Seeders;

use App\Models\VrExperiment;
use App\Models\VrQuestion;
use App\Models\VrQuestionBank;
use Illuminate\Database\Seeder;
use RuntimeException;

class PhetVrQuestionSeeder extends Seeder
{
    /**
     * Seed curriculum-aligned question banks from JSON.
     *
     * Only attaches questions to experiments that already exist in the database.
     * Experiments removed from production are skipped (not recreated).
     *
     * @var array<int, string>
     */
    private array $dataFiles = [
        'questions/phet_physics_pilot.json',
    ];

    public function run(): void
    {
        $seeded = 0;
        $skipped = 0;

        foreach ($this->dataFiles as $filename) {
            [$fileSeeded, $fileSkipped] = $this->seedFromFile(database_path('seeders/data/'.$filename));
            $seeded += $fileSeeded;
            $skipped += $fileSkipped;
        }

        $this->command?->info("PhET question seeding complete: {$seeded} experiment(s) seeded, {$skipped} skipped (not in database).");
    }

    /**
     * @return array{0: int, 1: int} [seeded count, skipped count]
     */
    private function seedFromFile(string $path): array
    {
        if (! is_file($path)) {
            throw new RuntimeException("PhET question data file not found: {$path}");
        }

        $entries = json_decode(file_get_contents($path), true);

        if (! is_array($entries)) {
            throw new RuntimeException("PhET question data file contains invalid JSON: {$path}");
        }

        $seeded = 0;
        $skipped = 0;

        foreach ($entries as $entry) {
            if ($this->seedEntry($entry)) {
                $seeded++;
            } else {
                $skipped++;
            }
        }

        return [$seeded, $skipped];
    }

    /**
     * @param  array<string, mixed>  $entry
     */
    private function seedEntry(array $entry): bool
    {
        $externalUrl = $entry['external_url'] ?? null;
        $slug = $entry['phet_slug'] ?? 'unknown';

        if (! is_string($externalUrl) || $externalUrl === '') {
            throw new RuntimeException("Question entry missing external_url for slug: {$slug}");
        }

        $experiment = VrExperiment::query()
            ->where('external_url', $externalUrl)
            ->first();

        if ($experiment === null) {
            $this->command?->warn("Skipped questions for [{$slug}]: experiment not in database ({$externalUrl}).");

            return false;
        }

        $bankData = $entry['question_bank'] ?? null;

        if (! is_array($bankData) || ! isset($bankData['title'])) {
            throw new RuntimeException("Question entry missing question_bank.title for slug: {$slug}");
        }

        $questions = $entry['questions'] ?? [];

        if (! is_array($questions) || $questions === []) {
            throw new RuntimeException("Question entry has no questions for slug: {$slug}");
        }

        $questionBank = VrQuestionBank::firstOrNew([
            'vr_experiment_id' => $experiment->id,
            'title' => $bankData['title'],
        ]);

        $questionBank->fill([
            'description' => $bankData['description'] ?? null,
            'time_limit' => $bankData['time_limit'] ?? null,
            'passing_score' => $bankData['passing_score'] ?? 60,
            'is_active' => (bool) ($bankData['is_active'] ?? false),
        ]);

        if (! $questionBank->exists) {
            $maxOrder = VrQuestionBank::query()
                ->where('vr_experiment_id', $experiment->id)
                ->max('display_order') ?? 0;
            $questionBank->display_order = $maxOrder + 1;
        }

        $questionBank->save();

        foreach ($questions as $question) {
            $this->seedQuestion($questionBank, $question, $slug);
        }

        $this->command?->info("Seeded question bank for [{$slug}] on experiment: {$experiment->name}");

        return true;
    }

    /**
     * @param  array<string, mixed>  $question
     */
    private function seedQuestion(VrQuestionBank $questionBank, array $question, string $slug): void
    {
        $order = $question['order'] ?? null;
        $questionType = $question['question_type'] ?? null;
        $questionText = $question['question_text'] ?? null;
        $correctAnswer = $question['correct_answer'] ?? null;

        if (! is_int($order) || ! is_string($questionType) || ! is_string($questionText) || ! is_string($correctAnswer)) {
            throw new RuntimeException("Invalid question data for slug: {$slug}, order: ".($order ?? 'missing'));
        }

        $options = $question['options'] ?? null;

        if ($questionType === 'mcq') {
            if (! is_array($options) || count($options) < 2) {
                throw new RuntimeException("MCQ requires at least 2 options for slug: {$slug}, order: {$order}");
            }
        } elseif ($questionType === 'true_false') {
            $options = ['True', 'False'];
        } else {
            $options = null;
        }

        VrQuestion::updateOrCreate(
            [
                'question_bank_id' => $questionBank->id,
                'order' => $order,
            ],
            [
                'question_type' => $questionType,
                'question_text' => $questionText,
                'options' => $options,
                'correct_answer' => $correctAnswer,
                'explanation' => $question['explanation'] ?? null,
                'points' => (int) ($question['points'] ?? 1),
            ]
        );
    }
}
