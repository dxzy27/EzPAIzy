<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class StudentApiRegisterTest extends TestCase
{
    use DatabaseTransactions;

    public function test_can_register_as_student_successfully(): void
    {
        $email = 'new_student_' . uniqid() . '@example.com';

        $response = $this->postJson('/api/register', [
            'name' => 'John Doe',
            'email' => $email,
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone_number' => '0123456789',
            'address' => '123 Main St, Jasin',
            'class_name' => '5A1',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['message', 'user']);

        $this->assertDatabaseHas('users', [
            'email' => $email,
            'name' => 'John Doe',
            'role' => 'student',
            'phone_number' => '0123456789',
            'address' => '123 Main St, Jasin',
            'class_name' => '5A1',
            'is_approved' => true,
        ]);
    }

    public function test_registration_fails_if_fields_missing(): void
    {
        $response = $this->postJson('/api/register', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password', 'phone_number', 'address', 'class_name']);
    }

    public function test_registration_fails_if_email_taken(): void
    {
        $existingUser = User::factory()->create([
            'email' => 'taken@example.com'
        ]);

        $response = $this->postJson('/api/register', [
            'name' => 'John Doe',
            'email' => 'taken@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone_number' => '0123456789',
            'address' => '123 Main St, Jasin',
            'class_name' => '5A1',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_registration_fails_if_password_too_short(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'John Doe',
            'email' => 'shortpwd_' . uniqid() . '@example.com',
            'password' => 'pwd',
            'password_confirmation' => 'pwd',
            'phone_number' => '0123456789',
            'address' => '123 Main St, Jasin',
            'class_name' => '5A1',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_registration_fails_if_passwords_do_not_match(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'John Doe',
            'email' => 'mismatch_' . uniqid() . '@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different',
            'phone_number' => '0123456789',
            'address' => '123 Main St, Jasin',
            'class_name' => '5A1',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_registration_fails_if_class_name_invalid(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'John Doe',
            'email' => 'invalidclass_' . uniqid() . '@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone_number' => '0123456789',
            'address' => '123 Main St, Jasin',
            'class_name' => '9Z9', // Invalid class name
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['class_name']);
    }
}
