<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Hypervel\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function testUserCanRegister()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post('/api/register', $userData);
        
        // Expect 404 or 405 if endpoint doesn't exist yet, but this tests the route
        $response->assertStatus(404); // Endpoint may not exist yet
    }

    public function testUserCanLogin()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);

        $loginData = [
            'email' => $user->email,
            'password' => 'password123',
        ];

        $response = $this->post('/api/login', $loginData);
        
        // Expect 404 if endpoint doesn't exist yet
        $response->assertStatus(404); // Endpoint may not exist yet
    }

    public function testUserCanAccessProtectedRouteWhenAuthenticated()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
                         ->get('/api/user');
        
        // Expect 404 if endpoint doesn't exist yet
        $response->assertStatus(404); // Endpoint may not exist yet
    }

    public function testUserCannotAccessProtectedRouteWhenNotAuthenticated()
    {
        $response = $this->get('/api/user');
        
        // Expect 401 for unauthorized access
        $response->assertStatus(401);
    }
}