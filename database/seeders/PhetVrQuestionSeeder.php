<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PhetVrQuestionSeeder extends Seeder
{
    /**
     * Intentionally disabled — template only.
     *
     * VR question banks were seeded on production and activated by admins.
     * Re-running a seeder here could reset is_active flags or overwrite question content.
     *
     * Reference data remains in database/seeders/data/questions/ if needed later.
     */
    public function run(): void
    {
        $this->command?->warn('PhetVrQuestionSeeder is disabled. No records were created or updated.');
    }
}
