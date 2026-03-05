<?php

namespace Database\Seeders;

use App\Models\ProjectCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProjectCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Robotics', 'description' => 'Robots, motion control, motors, and mechatronics projects.'],
            ['name' => 'Coding', 'description' => 'Programming projects: apps, games, algorithms, and software.'],
            ['name' => 'Aviation', 'description' => 'Drones, flight simulations, and aerospace-related projects.'],
            ['name' => 'Mathematics', 'description' => 'Math experiments, visualizations, and problem-solving projects.'],
            ['name' => 'Environment Monitoring', 'description' => 'Weather, air quality, water level, and environmental sensors.'],
            ['name' => 'Home Automation', 'description' => 'Smart home projects: lights, switches, sensors, and control systems.'],
            ['name' => 'Wearables', 'description' => 'Wearable tech projects: sensors, LEDs, and smart accessories.'],
            ['name' => 'IoT & Connectivity', 'description' => 'Internet-connected projects: Wi‑Fi/Bluetooth, dashboards, and APIs.'],
        ];

        foreach ($categories as $i => $cat) {
            $slug = Str::slug($cat['name']);

            ProjectCategory::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $cat['name'],
                    'description' => $cat['description'],
                    'sort_order' => $i + 1,
                    'is_active' => true,
                ]
            );
        }
    }
}

