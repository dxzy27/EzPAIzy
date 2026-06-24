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

    public function test_teacher_can_create_hard_difficulty_quiz(): void
    {
        $teacher = User::create([
            'name' => 'Test Teacher',
            'email' => 'teacher_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'teacher',
            'class_name' => 'Class A',
        ]);

        $response = $this->actingAs($teacher)
            ->post(route('teacher.quizzes.store'), [
                'title' => 'Hard Quiz Title',
                'topic' => 'Al-Quran',
                'difficulty' => 'hard',
                'questions' => [
                    1 => [
                        'text' => 'Bincangkan ciri-ciri mukmin berjaya.',
                        'type' => 'short_answer',
                        'correct' => '1. Solat khusyuk, 2. Jauhkan diri dari perkara sia-sia.',
                    ]
                ]
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('quizzes', [
            'title' => 'Hard Quiz Title',
            'difficulty' => 'hard',
        ]);
        $this->assertDatabaseHas('questions', [
            'question_text' => 'Bincangkan ciri-ciri mukmin berjaya.',
            'type' => 'short_answer',
            'correct_answer' => '1. Solat khusyuk, 2. Jauhkan diri dari perkara sia-sia.',
        ]);
    }

    public function test_teacher_can_edit_hard_difficulty_quiz(): void
    {
        $teacher = User::create([
            'name' => 'Test Teacher',
            'email' => 'teacher_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'teacher',
            'class_name' => 'Class A',
        ]);

        $quiz = Quiz::create([
            'title' => 'Initial Hard Quiz',
            'topic' => 'Al-Quran',
            'difficulty' => 'hard',
            'teacher_id' => $teacher->id,
        ]);

        $question = Question::create([
            'quiz_id' => $quiz->id,
            'question_text' => 'Bincangkan ciri-ciri mukmin.',
            'type' => 'short_answer',
            'correct_answer' => 'Solat khusyuk.',
            'points' => 10,
        ]);

        $response = $this->actingAs($teacher)
            ->put(route('teacher.quizzes.update', $quiz), [
                'title' => 'Updated Hard Quiz',
                'topic' => 'Al-Quran',
                'questions' => [
                    1 => [
                        'text' => 'Bincangkan ciri-ciri mukmin berjaya (Updated).',
                        'type' => 'short_answer',
                        'correct' => '1. Solat khusyuk, 2. Jauhkan diri.',
                    ]
                ]
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('quizzes', [
            'id' => $quiz->id,
            'title' => 'Updated Hard Quiz',
            'difficulty' => 'hard',
        ]);
        $this->assertDatabaseHas('questions', [
            'quiz_id' => $quiz->id,
            'question_text' => 'Bincangkan ciri-ciri mukmin berjaya (Updated).',
            'type' => 'short_answer',
            'correct_answer' => '1. Solat khusyuk, 2. Jauhkan diri.',
        ]);
    }

    public function test_quizzes_are_sorted_by_difficulty_easy_medium_hard(): void
    {
        $teacher = User::create([
            'name' => 'Test Teacher',
            'email' => 'teacher_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'teacher',
            'class_name' => 'Class A',
        ]);

        $student = User::create([
            'name' => 'Test Student',
            'email' => 'student_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'student',
            'class_name' => 'Class A',
        ]);

        $topicName = 'Al-Quran';
        Topic::create([
            'name' => $topicName,
            'type' => 'quiz',
            'user_id' => $teacher->id,
        ]);

        // Create in reverse order of difficulty (hard, then medium, then easy)
        $hardQuiz = Quiz::create(['title' => 'Hard Quiz', 'topic' => $topicName, 'difficulty' => 'hard', 'teacher_id' => $teacher->id]);
        $mediumQuiz = Quiz::create(['title' => 'Medium Quiz', 'topic' => $topicName, 'difficulty' => 'medium', 'teacher_id' => $teacher->id]);
        $easyQuiz = Quiz::create(['title' => 'Easy Quiz', 'topic' => $topicName, 'difficulty' => 'easy', 'teacher_id' => $teacher->id]);

        // Student View Check
        $responseStudent = $this->actingAs($student)->get(route('student.quizzes.folder', ['topic' => $topicName]));
        $responseStudent->assertStatus(200);
        
        $quizzesStudent = $responseStudent->viewData('quizzes');
        $this->assertEquals('easy', $quizzesStudent[0]->difficulty);
        $this->assertEquals('medium', $quizzesStudent[1]->difficulty);
        $this->assertEquals('hard', $quizzesStudent[2]->difficulty);

        // Teacher View Check
        $responseTeacher = $this->actingAs($teacher)->get(route('teacher.quizzes.folder', ['topic' => $topicName]));
        $responseTeacher->assertStatus(200);
        
        $quizzesTeacher = $responseTeacher->viewData('quizzes');
        $this->assertEquals('easy', $quizzesTeacher[0]->difficulty);
        $this->assertEquals('medium', $quizzesTeacher[1]->difficulty);
        $this->assertEquals('hard', $quizzesTeacher[2]->difficulty);
    }

    public function test_student_progress_page_renders_statistics_successfully(): void
    {
        $student = User::create([
            'name' => 'Test Student',
            'email' => 'student_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'student',
            'class_name' => 'Class A',
        ]);

        $teacher = User::create([
            'name' => 'Test Teacher',
            'email' => 'teacher_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'teacher',
            'class_name' => 'Class A',
        ]);

        $topicName = 'Al-Quran';
        Topic::create([
            'name' => $topicName,
            'type' => 'quiz',
            'user_id' => $teacher->id,
        ]);

        $easyQuiz = Quiz::create([
            'title' => 'Easy Test Quiz',
            'topic' => $topicName,
            'difficulty' => 'easy',
            'teacher_id' => $teacher->id,
        ]);

        Progress::create([
            'student_id' => $student->id,
            'quiz_id' => $easyQuiz->id,
            'score' => 85,
            'status' => 'completed',
        ]);

        $response = $this->actingAs($student)->get(route('student.progress'));
        $response->assertStatus(200);
        $response->assertViewHas('totalQuizzes', 1);
        $response->assertViewHas('averageScore', 85);
        $response->assertViewHas('highestScore', 85);
    }
}
