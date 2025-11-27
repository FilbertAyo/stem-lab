<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            ClassSeeder::class,
            SubjectSeeder::class,
            ClubSeeder::class,
            SchoolSeeder::class,
            Model3dSeeder::class,
            VrExperimentSeeder::class,
            VrAssessmentSeeder::class,
        ]);
    }
}
