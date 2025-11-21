<?php

declare(strict_types=1);

namespace Tests\Feature\API;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_registration_requires_valid_data(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => '', // Required
            'email' => 'invalid-email', // Invalid email format
            'password' => '123', // Too short
            'password_confirmation' => 'different', // Doesn't match
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_user_login_requires_valid_credentials(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
                 ->assertJson([
                     'message' => 'Invalid credentials'
                 ]);
    }

    public function test_student_creation_requires_valid_data(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/students', [
            'user_id' => 'invalid', // Should be integer
            'class_id' => 'invalid', // Should be integer
            'nis' => '', // Required
            'nisn' => 'short', // Should be longer
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['user_id', 'class_id', 'nis', 'nisn']);
    }
}