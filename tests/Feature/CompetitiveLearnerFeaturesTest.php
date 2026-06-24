<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\Progress;
use App\Models\Topic;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CompetitiveLearnerFeaturesTest extends TestCase
{
    use DatabaseTransactions;

    public function test_classmate_leaderboard_for_competitive_learners(): void
    {
        // 1. Create students
        $student1 = User::create([
            'name' => 'Comp Student A',
            'email' => 'compA_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'student',
            'class_name' => 'Class X',
            'learning_style' => 'competitive',
        ]);

        $student2 = User::create([
            'name' => 'Visual Student A',
            'email' => 'visA_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'student',
            'class_name' => 'Class X',
            'learning_style' => 'visual',
        ]);

        $student3 = User::create([
            'name' => 'Comp Student B',
            'email' => 'compB_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'student',
            'class_name' => 'Class Y',
            'learning_style' => 'competitive',
        ]);

        $teacher = User::create([
            'name' => 'Test Teacher',
            'email' => 'teacher_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'teacher',
            'class_name' => 'Class X',
        ]);

        // 2. Create quizzes
        $easyQuiz = Quiz::create([
            'title' => 'Easy Quiz',
            'difficulty' => 'easy',
            'topic' => 'Topic 1',
            'teacher_id' => $teacher->id,
        ]);

        // 3. Create progress
        Progress::create([
            'student_id' => $student1->id,
            'quiz_id' => $easyQuiz->id,
            'score' => 90, // 90 * 1 = 90 pts
            'status' => 'completed',
        ]);

        Progress::create([
            'student_id' => $student2->id,
            'quiz_id' => $easyQuiz->id,
            'score' => 100, // 100 * 1 = 100 pts
            'status' => 'completed',
        ]);

        Progress::create([
            'student_id' => $student3->id,
            'quiz_id' => $easyQuiz->id,
            'score' => 100,
            'status' => 'completed',
        ]);

        // 4. Act: View dashboard as student 1 (competitive)
        $response = $this->actingAs($student1)->get(route('student.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Class Leaderboard');
        $response->assertSee('Comp Student A');
        $response->assertSee('Visual Student A');
        $response->assertSee('90 pts');
        $response->assertSee('100 pts');
        // Should not see classmate from another class
        $response->assertDontSee('Comp Student B');

        // 5. Act: View dashboard as student 2 (visual) - should not see the leaderboard
        $responseVisual = $this->actingAs($student2)->get(route('student.dashboard'));
        $responseVisual->assertDontSee('Class Leaderboard');
    }

    public function test_difficulty_unlocking_for_competitive_learners(): void
    {
        $student = User::create([
            'name' => 'Comp Student',
            'email' => 'comp_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'student',
            'class_name' => 'Class Z',
            'learning_style' => 'competitive',
        ]);

        $nonCompStudent = User::create([
            'name' => 'Visual Student',
            'email' => 'vis_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'student',
            'class_name' => 'Class Z',
            'learning_style' => 'visual',
        ]);

        $teacher = User::create([
            'name' => 'Test Teacher',
            'email' => 'teacher_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'teacher',
            'class_name' => 'Class Z',
        ]);

        $topicName = 'Topic Z';
        Topic::create([
            'name' => $topicName,
            'type' => 'quiz',
            'user_id' => $teacher->id,
        ]);

        $easyQuiz = Quiz::create([
            'title' => 'Easy Quiz Z',
            'difficulty' => 'easy',
            'topic' => $topicName,
            'teacher_id' => $teacher->id,
        ]);
        Question::create(['quiz_id' => $easyQuiz->id, 'question_text' => 'Q1', 'type' => 'mcq', 'correct_answer' => 'a', 'points' => 10]);

        $mediumQuiz = Quiz::create([
            'title' => 'Medium Quiz Z',
            'difficulty' => 'medium',
            'topic' => $topicName,
            'teacher_id' => $teacher->id,
        ]);
        Question::create(['quiz_id' => $mediumQuiz->id, 'question_text' => 'Q2', 'type' => 'short_answer', 'correct_answer' => 'ans', 'points' => 10]);

        // Case 1: No progress yet - Medium should be Locked for competitive student
        $response = $this->actingAs($student)->get(route('student.quizzes.folder', ['topic' => $topicName]));
        $response->assertSee('Locked: Score 80%+ on Easy quizzes first');

        // Medium should be Unlocked for visual student
        $responseVisual = $this->actingAs($nonCompStudent)->get(route('student.quizzes.folder', ['topic' => $topicName]));
        $responseVisual->assertDontSee('Locked:');

        // Case 2: Scored 60% on easy quiz (below 80% threshold) - Medium should still be Locked
        $progress = Progress::create([
            'student_id' => $student->id,
            'quiz_id' => $easyQuiz->id,
            'score' => 60,
            'status' => 'completed',
        ]);

        $response = $this->actingAs($student)->get(route('student.quizzes.folder', ['topic' => $topicName]));
        $response->assertSee('Locked: Score 80%+ on Easy quizzes first');

        // Case 3: Scored 90% on easy quiz (above 80% threshold) - Medium should unlock
        $progress->update(['score' => 90]);

        $response = $this->actingAs($student)->get(route('student.quizzes.folder', ['topic' => $topicName]));
        $response->assertDontSee('Locked: Score 80%+ on Easy quizzes first');
    }
}
