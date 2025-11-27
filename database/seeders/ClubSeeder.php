<?php

namespace Database\Seeders;

use App\Models\Club;
use Illuminate\Database\Seeder;

class ClubSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clubs = [
            [
                'name' => 'CHOMOZA STEM CLUB',
                'description' => "Chomoza STEM Club is focused on getting more adolescent girls into STEM fields by attracting and retaining them in science classes.\n\n**Mission:**\nTo empower girls and young women with 21st-century skills through hands-on STEM activities, preparing them for the future of work in Science, Technology, Engineering, and Mathematics fields.\n\n**Activities:**\n• Hands-on robotics and coding workshops\n• Science experiments and lab sessions\n• Engineering design challenges\n• Mathematics problem-solving competitions\n• Mentorship programs with women in STEM careers\n• Field trips to tech companies and research institutions\n• Annual STEM exhibitions and showcases\n\n**Who Can Join:**\nAll students passionate about Science, Technology, Engineering, and Mathematics, with special focus on encouraging girls' participation.\n\n**Meeting Schedule:**\nEvery Saturday, 9:00 AM - 12:00 PM",
                'image_path' => null,
                'is_active' => true,
            ],
        ];

        foreach ($clubs as $club) {
            Club::updateOrCreate(
                ['name' => $club['name']],
                $club
            );
        }
    }
}

