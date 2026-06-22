<?php

namespace Database\Seeders;

use App\Models\Topic;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TopicSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Disable foreign key checks to allow truncation if referenced? 
        // But topics referenced by contents/quizzes...
        // I won't truncate if there are contents. 
        // But user asked to SET these topics.
        // I will use updateOrCreate based on name, or delete others?
        
        // Let's just create them if not exist.
        $topics = [
            'Al-Quran',
            'Hadis',
            'Akidah',
            'Fiqh',
            'Sirah and Tamadun Islam',
            'Akhlak Islamiah'
        ];

        foreach ($topics as $name) {
            Topic::firstOrCreate(
                ['name' => $name],
                ['is_system' => true, 'user_id' => null]
            );
        }
        
        // Optional: Mark them as system if they exist but aren't system?
        Topic::whereIn('name', $topics)->update(['is_system' => true]);
    }
}
