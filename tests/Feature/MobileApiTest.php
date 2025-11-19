<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Hypervel\Support\Facades\JWT;
use Hypervel\Foundation\Testing\RefreshDatabase;

class MobileApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_mobile_api_login_endpoint()
    {
        // Create a test user
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password')
        ]);

        $response = $this->post('/api/v1/login', [
            'email' => 'test@example.com',
            'password' => 'password'
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'message',
                     'data' => [
                         'access_token',
                         'token_type',
                         'expires_in',
                         'user' => [
                             'id',
                             'name',
                             'email',
                             'role',
                             'profile_type'
                         ]
                     ]
                 ]);
    }

    public function test_mobile_api_requires_authentication()
    {
        $response = $this->get('/api/v1/me');

        $response->assertStatus(401)
                 ->assertJson([
                     'success' => false,
                     'message' => 'User not authenticated'
                 ]);
    }

    public function test_mobile_api_with_valid_token()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password')
        ]);

        // Generate a valid JWT token
        $token = JWT::fromUser($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->get('/api/v1/me');

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'User retrieved successfully'
                 ]);
    }

    public function test_student_profile_endpoint()
    {
        $user = User::factory()->create([
            'email' => 'student@example.com',
            'password' => bcrypt('password')
        ]);

        $token = JWT::fromUser($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->get('/api/v1/student/profile');

        // This will likely return a 404 since the user doesn't have a student profile
        $response->assertStatus(404);
    }

    public function test_parent_profile_endpoint()
    {
        $user = User::factory()->create([
            'email' => 'parent@example.com',
            'password' => bcrypt('password')
        ]);

        $token = JWT::fromUser($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->get('/api/v1/parent/profile');

        // This will likely return a 404 since the user doesn't have a parent profile
        $response->assertStatus(404);
    }

    public function test_teacher_profile_endpoint()
    {
        $user = User::factory()->create([
            'email' => 'teacher@example.com',
            'password' => bcrypt('password')
        ]);

        $token = JWT::fromUser($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->get('/api/v1/teacher/profile');

        // This will likely return a 404 since the user doesn't have a teacher profile
        $response->assertStatus(404);
    }
}