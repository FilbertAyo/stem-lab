<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PhetVrExperimentSeeder extends Seeder
{
    /**
     * Intentionally disabled — template only.
     *
     * PhET VR experiments were seeded on production. Re-running a seeder here could
     * recreate deleted experiments or overwrite admin changes (active flags, classes, etc.).
     *
     * Reference data remains in database/seeders/data/phet_*.json if needed later.
     */
    public function run(): void
    {
        $this->command?->warn('PhetVrExperimentSeeder is disabled. No records were created or updated.');
    }
}
