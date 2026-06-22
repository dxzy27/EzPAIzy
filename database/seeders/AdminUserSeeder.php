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
            ['email' => 'admin@ezpaizy.test'],
            [
                'name' => 'System Admin',
                'password' => Hash::make('password'),
                'phone' => '123-456-7890',
                'address' => 'HQ Administration',
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );
    }
}
