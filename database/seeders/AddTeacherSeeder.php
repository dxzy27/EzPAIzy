<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AddTeacherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Usage: php artisan db:seed --class=AddTeacherSeeder
     */
    public function run(): void
    {
        // Add a new teacher
        User::create([
            'name' => 'Sarah Math Teacher',
            'email' => 'sarah@example.com',
            'password' => Hash::make('password123'),
            'phone' => '555-2222',
            'address' => '321 Math Way',
            'role' => 'teacher',
            'email_verified_at' => now(),
        ]);

        echo "✅ New teacher created!\n";
        echo "Email: sarah@example.com\n";
        echo "Password: password123\n";
    }
}
