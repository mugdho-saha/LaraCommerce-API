<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Roles (using firstOrCreate to avoid duplicate errors)
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $userRole  = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);

        // 2. Create a default Admin User
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'), // Change this in production!
            ]
        );

        // Assign Admin Role
        if (!$admin->hasRole('admin')) {
            $admin->assignRole($adminRole);
        }

        // 3. Create a default Regular User
        $testUser = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password'),
            ]
        );

        // Assign User Role
        if (!$testUser->hasRole('user')) {
            $testUser->assignRole($userRole);
        }

        // 4. Optional: Create additional random users using Factory
        // User::factory(10)->create()->each(function ($user) use ($userRole) {
        //     $user->assignRole($userRole);
        // });
    }
}