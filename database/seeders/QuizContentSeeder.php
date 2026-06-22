<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Quiz;
use App\Models\Content;

class QuizContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the teacher
        $teacher = User::where('email', 'teacher@example.com')->first();

        if ($teacher) {
            // Create sample quizzes
            Quiz::create([
                'title' => 'Mathematics Basics',
                'teacher_id' => $teacher->id,
            ]);

            Quiz::create([
                'title' => 'English Literature',
                'teacher_id' => $teacher->id,
            ]);

            Quiz::create([
                'title' => 'Science Fundamentals',
                'teacher_id' => $teacher->id,
            ]);

            // Create sample contents
            Content::create([
                'title' => 'Introduction to Algebra',
                'content' => "Algebra is a branch of mathematics dealing with symbols and the rules for manipulating those symbols. 

Topics covered:
- Variables and Constants
- Equations and Expressions
- Solving Linear Equations
- Graphing Functions
- Polynomial Operations",
                'teacher_id' => $teacher->id,
            ]);

            Content::create([
                'title' => 'World History Overview',
                'content' => "This lesson covers major events in world history from ancient civilizations to modern times.

Key Periods:
- Ancient Civilizations (Egypt, Greece, Rome)
- Medieval Period
- Renaissance and Enlightenment
- Industrial Revolution
- Modern Era",
                'teacher_id' => $teacher->id,
            ]);

            Content::create([
                'title' => 'Basic Biology',
                'content' => "Understanding the fundamentals of living organisms.

Topics:
- Cell Structure and Function
- Photosynthesis and Respiration
- DNA and Genetics
- Evolution
- Ecosystems and Biodiversity",
                'teacher_id' => $teacher->id,
            ]);
        }
    }
}
