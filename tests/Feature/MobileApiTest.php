<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;
use Hypervel\Support\Facades\Hash;
use Hypervel\Foundation\Testing\RefreshDatabase;

class MobileApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_routes_exist(): void
    {
        // Test basic API route
        $response = $this->get('/api/');
        $response->assertStatus(200);
        
        // Test auth routes exist (should return 401 since no credentials provided)
        $response = $this->postJson('/api/v1/auth/login');
        $response->assertStatus(422); // Validation error expected without credentials
        
        $response = $this->postJson('/api/v1/auth/me');
        $response->assertStatus(401); // Unauthorized without token
    }

    public function test_login_route(): void
    {
        // Create a test user
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        // Test login with valid credentials
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'message',
                     'data' => [
                         'access_token',
                         'refresh_token',
                         'token_type',
                         'expires_in',
                         'user' => [
                             'id',
                             'name',
                             'email',
                             'role'
                         ]
                     ]
                 ]);
    }

    public function test_protected_route_requires_auth(): void
    {
        // Test that protected route requires authentication
        $response = $this->getJson('/api/v1/student/profile');
        $response->assertStatus(401);
    }
}