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
            'name' => 'Platform Administrator',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        $admin = User::factory()->create([
            'name' => 'Administrator1',
            'email' => 'admin1@example.com',
            'password' => Hash::make('123'),
            'role' => 'admin',
        ]);

        $admin = User::factory()->create([
            'name' => 'Administrator2',
            'email' => 'admin2@example.com',
            'password' => Hash::make('123'),
            'role' => 'admin',
        ]);

        $admin = User::factory()->create([
            'name' => 'Administrator3',
            'email' => 'admin3@example.com',
            'password' => Hash::make('123'),
            'role' => 'admin',
        ]);

        $admin = User::factory()->create([
            'name' => 'Administrator4',
            'email' => 'admin4@example.com',
            'password' => Hash::make('123'),
            'role' => 'admin',
        ]);

        $admin = User::factory()->create([
            'name' => 'Administrator5',
            'email' => 'admin5@example.com',
            'password' => Hash::make('123'),
            'role' => 'admin',
        ]);

        $admin = User::factory()->create([
            'name' => 'Administrator6',
            'email' => 'admin6@example.com',
            'password' => Hash::make('123'),
            'role' => 'admin',
        ]);

        $admin = User::factory()->create([
            'name' => 'Administrator7',
            'email' => 'admin7@example.com',
            'password' => Hash::make('123'),
            'role' => 'admin',
        ]);

        $admin = User::factory()->create([
            'name' => 'Administrator8',
            'email' => 'admin8@example.com',
            'password' => Hash::make('123'),
            'role' => 'admin',
        ]);

        $admin = User::factory()->create([
            'name' => 'Administrator9',
            'email' => 'admin9@example.com',
            'password' => Hash::make('123'),
            'role' => 'admin',
        ]);

        $admin = User::factory()->create([
            'name' => 'Administrator10',
            'email' => 'admin10@example.com',
            'password' => Hash::make('123'),
            'role' => 'admin',
        ]);

        $teacher = User::factory()->create([
            'name' => 'Sample Teacher',
            'email' => 'teacher@example.com',
            'password' => Hash::make('password'),
            'role' => 'teacher',
        ]);

        $student = User::factory()->create([
            'name' => 'Sample Student',
            'email' => 'student@example.com',
            'password' => Hash::make('password'),
            'role' => 'student',
        ]);
    }
}

    