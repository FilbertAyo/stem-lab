<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RolePermissionSeeder extends Seeder
{
    /**
     * Seed the application's roles and default users.
     */
    public function run(): void
    {
        $admin = User::factory()->create([
            'name' => 'Filbert Ayo',
            'email' => 'filbertayo@adilisha.or.tz',
            'password' => Hash::make(env('ADMIN_PASSWORD', 'password')),
            'role' => 'admin',
        ]);
    }
}

    