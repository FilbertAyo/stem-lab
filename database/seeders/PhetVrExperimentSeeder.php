<?php

namespace Database\Seeders;

use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\VrExperiment;
use Illuminate\Database\Seeder;
use RuntimeException;

class PhetVrExperimentSeeder extends Seeder
{
    /**
     * Seed PhET VR experiments from JSON. All records are created inactive
     * so admins can review them before publishing to students.
     *
     * @var array<int, string>
     */
    private array $dataFiles = [
        'phet_physics_experiments.json',
        'phet_chemistry_experiments.json',
        'phet_biology_experiments.json',
        'phet_mathematics_experiments.json',
        'phet_batch2_experiments.json',
    ];

    public function run(): void
    {
        foreach ($this->dataFiles as $filename) {
            $this->seedFromFile(database_path('seeders/data/'.$filename));
        }
    }

    private function seedFromFile(string $path): void
    {
        if (! is_file($path)) {
            throw new RuntimeException("PhET experiment data file not found: {$path}");
        }

        $experiments = json_decode(file_get_contents($path), true);

        if (! is_array($experiments)) {
            throw new RuntimeException("PhET experiment data file contains invalid JSON: {$path}");
        }

        foreach ($experiments as $experiment) {
            $subject = Subject::where('subject_name', $experiment['subject_name'])->first();

            if ($subject === null) {
                throw new RuntimeException("Subject not found: {$experiment['subject_name']}");
            }

            $classIds = [];

            foreach ($experiment['class_names'] as $className) {
                $class = SchoolClass::where('name', $className)->first();

                if ($class === null) {
                    throw new RuntimeException("School class not found: {$className}");
                }

                $classIds[] = $class->id;
            }

            $vrExperiment = VrExperiment::firstOrNew([
                'name' => $experiment['name'],
                'subject_id' => $subject->id,
                'external_url' => $experiment['external_url'],
            ]);

            $vrExperiment->description = $experiment['description'] ?? null;

            if (! $vrExperiment->exists) {
                $vrExperiment->is_active = false;
            }

            $vrExperiment->save();
            $vrExperiment->schoolClasses()->sync(array_values(array_unique($classIds)));
        }
    }
}
