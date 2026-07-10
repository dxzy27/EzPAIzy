<?php

namespace Tests\Feature;

use App\Models\User;
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
        if ($response->status() !== 200) {
            $e = $response->exception;
            dd($e->getMessage(), $e->getFile(), $e->getLine());
        }
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
