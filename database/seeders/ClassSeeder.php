<?php

namespace Database\Seeders;

use App\Models\SchoolClass;
use Illuminate\Database\Seeder;

class ClassSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $classes = [
            [
                'name' => 'Form 1',
                'description' => 'Introductory secondary school class.',
            ],
            [
                'name' => 'Form 2',
                'description' => 'Lower secondary learners building fundamentals.',
            ],
            [
                'name' => 'Form 3',
                'description' => 'Upper secondary students focusing on STEM mastery.',
            ],
            [
                'name' => 'Form 4',
                'description' => 'Exam preparation and advanced STEM practice.',
            ],
        ];

        foreach ($classes as $class) {
            SchoolClass::updateOrCreate(
                ['name' => $class['name']],
                [
                    'description' => $class['description'],
                    'is_active' => true,
                ]
            );
        }
    }
}
