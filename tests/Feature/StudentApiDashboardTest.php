<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Quiz;
use App\Models\Progress;
use App\Models\Content;
use App\Models\FlashcardSet;
use App\Models\LearningProfile;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class StudentApiDashboardTest extends TestCase
{
    use DatabaseTransactions;

    public function test_api_dashboard_for_competitive_learner(): void
    {
        // Clean database state for isolation
        User::query()->delete();
        Quiz::query()->delete();
        Progress::query()->delete();

        $student1 = User::create([
            'name' => 'Comp Student A',
            'email' => 'compA_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'student',
            'class_name' => 'Class X',
            'learning_style' => 'competitive',
        ]);

        $student2 = User::create([
            'name' => 'Comp Student B',
            'email' => 'compB_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'student',
            'class_name' => 'Class X',
            'learning_style' => 'competitive',
        ]);

        $teacher = User::create([
            'name' => 'Test Teacher',
            'email' => 'teacher_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'teacher',
            'class_name' => 'Class X',
        ]);

        $quiz = Quiz::create([
            'title' => 'Test Quiz',
            'difficulty' => 'easy',
            'topic' => 'Topic X',
            'teacher_id' => $teacher->id,
        ]);

        Progress::create([
            'student_id' => $student1->id,
            'quiz_id' => $quiz->id,
            'score' => 90,
            'status' => 'completed',
        ]);

        Progress::create([
            'student_id' => $student2->id,
            'quiz_id' => $quiz->id,
            'score' => 100,
            'status' => 'completed',
        ]);

        Sanctum::actingAs($student1, ['*']);

        $response = $this->getJson('/api/student/dashboard');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'user',
                'quiz_count',
                'materials_count',
                'completed_count',
                'best_score',
                'recent_results',
                'new_materials',
                'leaderboard',
            ])
            ->assertJsonPath('best_score', 90)
            ->assertJsonCount(2, 'leaderboard')
            ->assertJsonPath('leaderboard.0.name', 'Comp Student B')
            ->assertJsonPath('leaderboard.0.points', 100)
            ->assertJsonPath('leaderboard.1.name', 'Comp Student A')
            ->assertJsonPath('leaderboard.1.points', 90);
    }

    public function test_api_dashboard_for_read_write_learner(): void
    {
        // Clean database state for isolation
        User::query()->delete();
        Content::query()->delete();
        FlashcardSet::query()->delete();

        $student = User::create([
            'name' => 'RW Student',
            'email' => 'rw_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'student',
            'class_name' => 'Class X',
            'learning_style' => 'read_write',
        ]);

        $teacher = User::create([
            'name' => 'Test Teacher',
            'email' => 'teacher_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'teacher',
            'class_name' => 'Class X',
        ]);

        $content = Content::create([
            'title' => 'Important Notes',
            'content' => 'Lalala',
            'teacher_id' => $teacher->id,
            'topic' => 'Fiqah',
        ]);

        $flashcard = FlashcardSet::create([
            'title' => 'Fiqah Cards',
            'description' => 'Fiqah term cards',
            'user_id' => $teacher->id,
            'topic' => 'Fiqah',
        ]);

        Sanctum::actingAs($student, ['*']);

        $response = $this->getJson('/api/student/dashboard');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'new_materials',
            ])
            ->assertJsonCount(2, 'new_materials');

        $materials = $response->json('new_materials');
        // For read_write, flashcard should be returned first if it was latest or concatenated first
        $this->assertEquals('Flashcard', $materials[0]['type']);
        $this->assertEquals('Content', $materials[1]['type']);
    }
}
