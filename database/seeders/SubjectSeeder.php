<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $subjects = [
            [
                'subject_name' => 'Physics',
                'description' => 'Explore the fundamental laws of nature, from mechanics and thermodynamics to electricity and magnetism. Understand how the universe works through interactive experiments and simulations.',
                'icon_url' => 'images/physics.svg',
                'image_url' => 'images/physics.svg',
                'display_order' => 1,
                'is_active' => true,
            ],
            [
                'subject_name' => 'Chemistry',
                'description' => 'Dive into the world of atoms, molecules, and chemical reactions. Learn about the composition of matter and how substances interact through virtual lab experiments.',
                'icon_url' => 'images/chemistry.svg',
                'image_url' => 'images/chemistry.svg',
                'display_order' => 2,
                'is_active' => true,
            ],
            [
                'subject_name' => 'Biology',
                'description' => 'Discover the science of life, from cells and genetics to ecosystems and evolution. Explore 3D models of organs, organisms, and biological processes.',
                'icon_url' => 'images/biology.svg',
                'image_url' => 'images/biology.svg',
                'display_order' => 3,
                'is_active' => true,
            ],
            [
                'subject_name' => 'Mathematics',
                'description' => 'Master mathematical concepts from algebra and geometry to calculus and statistics. Visualize abstract concepts through interactive demonstrations and problem-solving exercises.',
                'icon_url' => 'images/mathematics.svg',
                'image_url' => 'images/mathematics.svg',
                'display_order' => 4,
                'is_active' => true,
            ],
            [
                'subject_name' => 'Engineering',
                'description' => 'Apply scientific principles to design and build solutions. Learn about mechanical, electrical, and civil engineering through hands-on projects and simulations.',
                'icon_url' => 'images/icon-01.svg',
                'image_url' => 'images/icon-01.svg',
                'display_order' => 5,
                'is_active' => true,
            ],
            [
                'subject_name' => 'Coding',
                'description' => 'Learn programming languages and computational thinking. Develop problem-solving skills through coding challenges, algorithms, and software development projects.',
                'icon_url' => 'images/codeing.svg',
                'image_url' => 'images/codeing.svg',
                'display_order' => 6,
                'is_active' => true,
            ],
            [
                'subject_name' => 'Robotics',
                'description' => 'Combine engineering, coding, and electronics to build and program robots. Learn about sensors, actuators, and artificial intelligence through practical robotics projects.',
                'icon_url' => 'images/robotics.svg',
                'image_url' => 'images/robotics.svg',
                'display_order' => 7,
                'is_active' => true,
            ],
        ];

        foreach ($subjects as $subject) {
            Subject::updateOrCreate(
                ['subject_name' => $subject['subject_name']],
                $subject
            );
        }
    }
}
