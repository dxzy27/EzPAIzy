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
                'password' => Hash::make('admin123'),
                'phone' => '01533782689',
                'address' => 'HQ Administration',
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );
    }
}
