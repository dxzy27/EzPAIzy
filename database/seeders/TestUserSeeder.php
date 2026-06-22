<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class TestUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a test teacher
        User::create([
            'name' => 'John Teacher',
            'email' => 'teacher@example.com',
            'password' => Hash::make('password123'),
            'phone' => '555-1234',
            'address' => '123 Teacher St',
            'role' => 'teacher',
            'email_verified_at' => now(),
        ]);

        // Create a test student
        User::create([
            'name' => 'Jane Student',
            'email' => 'student@example.com',
            'password' => Hash::make('password123'),
            'phone' => '555-5678',
            'address' => '456 Student Ave',
            'role' => 'student',
            'email_verified_at' => now(),
        ]);

        // Create another student
        User::create([
            'name' => 'Bob Student',
            'email' => 'student2@example.com',
            'password' => Hash::make('password123'),
            'phone' => '555-9999',
            'address' => '789 Learning Ln',
            'role' => 'student',
            'email_verified_at' => now(),
        ]);
    }
}
