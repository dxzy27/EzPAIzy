<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Quiz;
use App\Models\Content;
use App\Models\StudentNote;
use App\Models\LearningProfile;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ReadWriteLearnerFeaturesTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Test diagnosis calculation successfully categorizes a student as read_write.
     */
    public function test_diagnosis_calculation_categorizes_as_read_write(): void
    {
        $student = User::create([
            'name' => 'ReadWrite Candidate',
            'email' => 'rw_cand_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'student',
            'class_name' => 'Class X',
            'learning_style' => null,
        ]);

        // Submit diagnostic options that heavily favor read_write:
        // q1 => A (read_write: 3)
        // q2 => A (read_write: 2)
        // q3 => A (read_write: 2)
        // q4 => A (read_write: 3)
        // q5 => C (read_write: 2)
        // q6 => A (read_write: 2)
        // q7 => A (read_write: 3)
        // q8 => C (read_write: 3)
        // q9 => B (read_write: 3)
        // q10 => A (read_write: 2)
        $response = $this->actingAs($student)->post(route('student.diagnosis.store'), [
            'q1' => 'A',
            'q2' => 'A',
            'q3' => 'A',
            'q4' => 'A',
            'q5' => 'C',
            'q6' => 'A',
            'q7' => 'A',
            'q8' => 'C',
            'q9' => 'B',
            'q10' => 'A',
        ]);

        $response->assertRedirect(route('student.diagnosis.show'));

        $student->refresh();
        $this->assertEquals('read_write', $student->learning_style);

        $profile = LearningProfile::where('user_id', $student->id)->first();
        $this->assertNotNull($profile);
        $this->assertEquals('read_write', $profile->learning_style);
        $this->assertGreaterThan(0, $profile->score_read_write);
    }

    /**
     * Test saving a note via the AJAX route saves correct attributes.
     */
    public function test_saving_note_via_ajax(): void
    {
        $student = User::create([
            'name' => 'ReadWrite Student',
            'email' => 'rw_stud_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'student',
            'class_name' => 'Class X',
            'learning_style' => 'read_write',
        ]);

        $response = $this->actingAs($student)->postJson(route('student.notes.save'), [
            'topic' => 'Al-Quran',
            'difficulty' => 'medium',
            'title' => 'My Quran Study Notes',
            'content' => 'Acronym: T.A.S.M.I.',
            'resource_type' => 'content',
            'resource_id' => 45,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);

        $this->assertDatabaseHas('student_notes', [
            'user_id' => $student->id,
            'topic' => 'Al-Quran',
            'difficulty' => 'medium',
            'title' => 'My Quran Study Notes',
            'content' => 'Acronym: T.A.S.M.I.',
            'resource_type' => 'content',
            'resource_id' => 45,
        ]);
    }

    /**
     * Test "My Folders" sidebar element lists the correct topics for a Read/Write student.
     */
    public function test_my_folders_sidebar_element_lists_topics_only_for_read_write(): void
    {
        $rwStudent = User::create([
            'name' => 'ReadWrite Stud Sidebar',
            'email' => 'rw_side_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'student',
            'class_name' => 'Class X',
            'learning_style' => 'read_write',
        ]);

        // Add a note
        StudentNote::create([
            'user_id' => $rwStudent->id,
            'topic' => 'Fiqh',
            'difficulty' => 'easy',
            'title' => 'Fiqh note',
            'content' => 'Content here',
        ]);

        $audStudent = User::create([
            'name' => 'Auditory Stud Sidebar',
            'email' => 'aud_side_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'student',
            'class_name' => 'Class X',
            'learning_style' => 'auditory',
        ]);

        // Act as Read/Write student: should see folder
        $responseRw = $this->actingAs($rwStudent)->get(route('student.dashboard'));
        $responseRw->assertStatus(200);
        $responseRw->assertSee('My Folders');
        $responseRw->assertSee('Fiqh');

        // Act as Auditory student: should not see folders menu
        $responseAud = $this->actingAs($audStudent)->get(route('student.dashboard'));
        $responseAud->assertStatus(200);
        $responseAud->assertDontSee('My Folders');
        $responseAud->assertDontSee('Fiqh');
    }

    /**
     * Test the notepad widget does not display for Auditory or Competitive students,
     * but does display for Read/Write students.
     */
    public function test_notepad_widget_visibility(): void
    {
        $teacher = User::create([
            'name' => 'Teacher X',
            'email' => 'teach_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'teacher',
        ]);

        $content = Content::create([
            'title' => 'Important Content',
            'content' => 'This is content text.',
            'teacher_id' => $teacher->id,
            'topic' => 'Tauhid',
            'is_flagged' => false,
        ]);

        $rwStudent = User::create([
            'name' => 'ReadWrite Notepad',
            'email' => 'rw_note_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'student',
            'learning_style' => 'read_write',
        ]);

        $compStudent = User::create([
            'name' => 'Comp Notepad',
            'email' => 'comp_note_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'student',
            'learning_style' => 'competitive',
        ]);

        // Act as Read/Write student: should see notepad
        $responseRw = $this->actingAs($rwStudent)->get(route('student.contents.show', $content));
        $responseRw->assertStatus(200);
        $responseRw->assertSee('Study Notepad');
        $responseRw->assertSee('Acronyms');

        // Act as Competitive student: should not see notepad
        $responseComp = $this->actingAs($compStudent)->get(route('student.contents.show', $content));
        $responseComp->assertStatus(200);
        $responseComp->assertDontSee('Study Notepad');
    }

    /**
     * Test the notepad widget displays on flashcard study page for Read/Write students,
     * but not for Competitive students.
     */
    public function test_flashcard_notepad_widget_visibility(): void
    {
        $teacher = User::create([
            'name' => 'Teacher Y',
            'email' => 'teach_y_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'teacher',
        ]);

        $set = \App\Models\FlashcardSet::create([
            'title' => 'Important Flashcards',
            'description' => 'A flashcard set',
            'user_id' => $teacher->id,
            'topic' => 'Fiqh',
            'is_public' => true,
        ]);

        $rwStudent = User::create([
            'name' => 'ReadWrite Student',
            'email' => 'rw_stud_fc_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'student',
            'learning_style' => 'read_write',
        ]);

        $compStudent = User::create([
            'name' => 'Competitive Student',
            'email' => 'comp_stud_fc_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'student',
            'learning_style' => 'competitive',
        ]);

        // Act as Read/Write student: should see notepad
        $responseRw = $this->actingAs($rwStudent)->get(route('student.flashcards.show', $set));
        $responseRw->assertStatus(200);
        $responseRw->assertSee('Study Notepad');

        // Act as Competitive student: should not see notepad
        $responseComp = $this->actingAs($compStudent)->get(route('student.flashcards.show', $set));
        $responseComp->assertStatus(200);
        $responseComp->assertDontSee('Study Notepad');
    }
}
