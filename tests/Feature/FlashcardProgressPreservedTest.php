<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\FlashcardSet;
use App\Models\Flashcard;
use App\Models\FlashcardProgress;
use App\Models\LearningProfile;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class FlashcardProgressPreservedTest extends TestCase
{
    use DatabaseTransactions;

    public function test_flashcard_progress_is_preserved_when_resetting_to_basic_ui(): void
    {
        // 1. Create a student user
        $student = User::create([
            'name' => 'Test Student',
            'email' => 'student_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'student',
            'learning_style' => 'visual',
        ]);

        // 2. Create a teacher user (since sets usually belong to teachers)
        $teacher = User::create([
            'name' => 'Test Teacher',
            'email' => 'teacher_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'teacher',
        ]);

        // 3. Create a LearningProfile
        $profile = LearningProfile::create([
            'user_id' => $student->id,
            'learning_style' => 'visual',
            'answers' => ['q1' => 'A', 'q2' => 'A'],
            'score_visual' => 10,
            'score_auditory' => 2,
            'score_competitive' => 1,
            'confidence' => 80.0,
            'persona' => 'Visual Learner',
            'recommendations' => ['Use flashcards.'],
        ]);

        // 4. Create a FlashcardSet and Flashcard
        $set = FlashcardSet::create([
            'title' => 'Test Set',
            'description' => 'Test description',
            'user_id' => $teacher->id,
            'topic' => 'General',
            'is_public' => true,
        ]);

        $card = Flashcard::create([
            'flashcard_set_id' => $set->id,
            'term' => 'Test Term',
            'definition' => 'Test Definition',
            'position' => 1,
        ]);

        // 5. Record flashcard progress
        $progress = FlashcardProgress::create([
            'user_id' => $student->id,
            'flashcard_id' => $card->id,
            'status' => 'learning',
            'ease_factor' => 2.5,
            'interval' => 1,
            'repetitions' => 1,
            'next_review_date' => now()->addDay(),
        ]);

        // 6. Act: Call the reset learning style endpoint
        $response = $this->actingAs($student)
            ->post(route('student.diagnosis.reset'));

        // 7. Assert: Redirect to dashboard
        $response->assertRedirect(route('student.dashboard'));

        // 8. Assert: User learning style is set to null
        $student->refresh();
        $this->assertNull($student->learning_style);

        // 9. Assert: LearningProfile record is deleted
        $this->assertDatabaseMissing('learning_profiles', [
            'id' => $profile->id,
        ]);

        // 10. Assert: FlashcardProgress record still exists and is untouched
        $this->assertDatabaseHas('flashcard_progress', [
            'id' => $progress->id,
            'user_id' => $student->id,
            'flashcard_id' => $card->id,
            'status' => 'learning',
            'interval' => 1,
            'repetitions' => 1,
        ]);
    }
}
