<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\Progress;
use App\Models\Topic;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class QuizProgressIndicatorsTest extends TestCase
{
    use DatabaseTransactions;

    public function test_quiz_progress_indicators_in_folder_view(): void
    {
        // 1. Create a student user
        $student = User::create([
            'name' => 'Test Student',
            'email' => 'student_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'student',
            'class_name' => 'Class A',
        ]);

        // 2. Create a teacher user in the same class
        $teacher = User::create([
            'name' => 'Test Teacher',
            'email' => 'teacher_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'teacher',
            'class_name' => 'Class A',
        ]);

        // 3. Create a topic for the quiz
        $topicName = 'Al-Quran';
        Topic::create([
            'name' => $topicName,
            'type' => 'quiz',
            'user_id' => $teacher->id,
        ]);

        // 4. Create an easy quiz with questions
        $easyQuiz = Quiz::create([
            'title' => 'Easy Test Quiz',
            'topic' => $topicName,
            'difficulty' => 'easy',
            'teacher_id' => $teacher->id,
        ]);

        Question::create([
            'quiz_id' => $easyQuiz->id,
            'question_text' => 'What is Q1?',
            'type' => 'mcq',
            'correct_answer' => 'a',
            'points' => 10,
        ]);

        // 5. Create a hard quiz with questions
        $hardQuiz = Quiz::create([
            'title' => 'Hard Test Quiz',
            'topic' => $topicName,
            'difficulty' => 'hard',
            'teacher_id' => $teacher->id,
        ]);

        Question::create([
            'quiz_id' => $hardQuiz->id,
            'question_text' => 'What is Q2?',
            'type' => 'short_answer',
            'correct_answer' => 'answer text',
            'points' => 10,
        ]);

        // 6. Act: Access the quiz folder route as student (Unattempted state)
        $response = $this->actingAs($student)
            ->get(route('student.quizzes.folder', ['topic' => $topicName]));

        $response->assertStatus(200);
        $response->assertSee('Easy Test Quiz');
        $response->assertSee('Hard Test Quiz');
        $response->assertSee('Not Attempted');
        $response->assertSee('Take Quiz');

        // 7. Act: Simulate completed progress for the easy quiz
        Progress::create([
            'student_id' => $student->id,
            'quiz_id' => $easyQuiz->id,
            'score' => 85,
            'status' => 'completed',
        ]);

        // Simulate pending progress for the hard quiz
        Progress::create([
            'student_id' => $student->id,
            'quiz_id' => $hardQuiz->id,
            'score' => 0,
            'status' => 'pending',
        ]);

        // Access the folder again
        $responseAfterAttempt = $this->actingAs($student)
            ->get(route('student.quizzes.folder', ['topic' => $topicName]));

        // 8. Assert: Check progress indicators and changed button text
        $responseAfterAttempt->assertStatus(200);
        $responseAfterAttempt->assertSee('85%');
        $responseAfterAttempt->assertSee('Passed');
        $responseAfterAttempt->assertSee('Retake Quiz');
        
        $responseAfterAttempt->assertSee('Pending Review');
        $responseAfterAttempt->assertSee('Awaiting grading');
    }
}
