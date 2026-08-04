<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'System Admin',
                'password' => Hash::make('Admin@123'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'adminezpaizy@gmail.com'],
            [
                'name' => 'System Admin',
                'password' => '$2y$12$3e7RHOHF0AI7SjHDDjEqIuYASa5nQEZsrvvjI4QjgNZEfc3jiYhtm',
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );
    }
}
