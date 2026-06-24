<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\StudentNote;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class StudentApiNotesTest extends TestCase
{
    use DatabaseTransactions;

    public function test_can_save_note_with_resource(): void
    {
        $student = User::create([
            'name' => 'Notes Student',
            'email' => 'notes_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'student',
            'learning_style' => 'read_write',
        ]);

        Sanctum::actingAs($student, ['*']);

        $response = $this->postJson('/api/student/notes/save', [
            'topic' => 'Al-Quran',
            'title' => 'My Al-Quran Notes',
            'content' => 'Acronym: abcde',
            'resource_type' => 'content',
            'resource_id' => 123,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['note']);

        $this->assertDatabaseHas('student_notes', [
            'user_id' => $student->id,
            'topic' => 'Al-Quran',
            'resource_type' => 'content',
            'resource_id' => 123,
            'title' => 'My Al-Quran Notes',
            'content' => 'Acronym: abcde',
        ]);
    }

    public function test_can_save_note_with_empty_or_no_resource(): void
    {
        $student = User::create([
            'name' => 'Notes Student',
            'email' => 'notes_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'student',
            'learning_style' => 'read_write',
        ]);

        Sanctum::actingAs($student, ['*']);

        $response = $this->postJson('/api/student/notes/save', [
            'topic' => 'Hadis',
            'title' => 'My Hadis Notes',
            'content' => 'Some content',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('student_notes', [
            'user_id' => $student->id,
            'topic' => 'Hadis',
            'title' => 'My Hadis Notes',
            'content' => 'Some content',
            'resource_type' => null,
            'resource_id' => null,
        ]);
    }
}
