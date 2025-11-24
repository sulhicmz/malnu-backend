<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Hyperf\Support\Facades\Hash;
use Hyperf\Support\Facades\Auth;

class JwtAuthTest extends TestCase
{
    public function test_user_can_register()
    {
        $response = $this->post('/api/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password'
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'message',
                'access_token',
                'token_type',
                'expires_in',
                'user' => [
                    'id',
                    'name',
                    'email'
                ]
            ],
            'message',
            'timestamp'
        ]);
    }

    public function test_user_can_login()
    {
        // Create a user first
        $user = User::create([
            'name' => 'Test User',
            'email' => 'login@example.com',
            'password' => Hash::make('password')
        ]);

        $response = $this->post('/api/auth/login', [
            'email' => 'login@example.com',
            'password' => 'password'
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'access_token',
                'token_type',
                'expires_in'
            ],
            'message',
            'timestamp'
        ]);
    }

    public function test_user_can_get_profile()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'profile@example.com',
            'password' => Hash::make('password')
        ]);

        $token = Auth::guard('jwt')->login($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->get('/api/auth/me');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'id',
                'name',
                'email'
            ],
            'message',
            'timestamp'
        ]);
    }

    public function test_user_can_logout()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'logout@example.com',
            'password' => Hash::make('password')
        ]);

        $token = Auth::guard('jwt')->login($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->post('/api/auth/logout');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => null,
            'message' => 'Successfully logged out',
            'timestamp' => $this->anything()
        ]);
    }

    public function test_rate_limiting_on_login_attempts()
    {
        // Try to login with invalid credentials multiple times
        for ($i = 0; $i < 6; $i++) {
            $response = $this->post('/api/auth/login', [
                'email' => 'nonexistent@example.com',
                'password' => 'wrongpassword'
            ]);
        }

        // The 6th attempt should be rate limited
        $response = $this->post('/api/auth/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'wrongpassword'
        ]);

        $response->assertStatus(429);
        $response->assertJsonStructure([
            'message',
            'error_code'
        ]);
    }
}