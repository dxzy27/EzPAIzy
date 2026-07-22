<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SchoolClassSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $classes = ['5A1', '5A2', '5A3', '5B1', '5B2', '5B3'];

        foreach ($classes as $className) {
            \App\Models\SchoolClass::firstOrCreate(['name' => $className]);
        }
    }
}
