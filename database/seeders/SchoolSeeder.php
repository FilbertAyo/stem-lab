<?php

namespace Database\Seeders;

use App\Models\School;
use Illuminate\Database\Seeder;

class SchoolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $schools = [
            [
                'name' => 'Mwanza Secondary School',
                'address' => 'Pamba Road, Nyamagana District, Mwanza',
                'phone' => '+255 28 250 0001',
                'email' => 'info@mwanzasecondary.ac.tz',
                'district' => 'Nyamagana',
                'region' => 'Mwanza',
                'is_active' => true,
            ],
            [
                'name' => 'Lake Secondary School',
                'address' => 'Capri Point, Ilemela District, Mwanza',
                'phone' => '+255 28 250 0002',
                'email' => 'info@lakesecondary.ac.tz',
                'district' => 'Ilemela',
                'region' => 'Mwanza',
                'is_active' => true,
            ],
            [
                'name' => 'Mkolani Secondary School',
                'address' => 'Mkolani Area, Nyamagana District, Mwanza',
                'phone' => '+255 28 250 0003',
                'email' => 'info@mkolanisecondary.ac.tz',
                'district' => 'Nyamagana',
                'region' => 'Mwanza',
                'is_active' => true,
            ],
        ];

        foreach ($schools as $school) {
            School::updateOrCreate(
                ['name' => $school['name']],
                $school
            );
        }
    }
}

