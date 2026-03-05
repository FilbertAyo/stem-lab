<?php

namespace Database\Seeders;

use App\Models\Platform;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PlatformSeeder extends Seeder
{
    public function run(): void
    {
        $platforms = [
            ['name' => 'Arduino', 'icon' => null],
            ['name' => 'Raspberry Pi', 'icon' => null],
            ['name' => 'Python', 'icon' => null],
            ['name' => 'Scratch', 'icon' => null],
            ['name' => 'Web', 'icon' => null],
            ['name' => 'Tinkercad', 'icon' => null],
            ['name' => 'MIT App Inventor', 'icon' => null],
        ];

        foreach ($platforms as $i => $plat) {
            $slug = Str::slug($plat['name']);

            Platform::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $plat['name'],
                    'icon' => $plat['icon'] ?? null,
                    'is_active' => true,
                    'sort_order' => $i + 1,
                ]
            );
        }
    }
}
