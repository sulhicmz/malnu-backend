<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\User;
use Tests\TestCase;

class AuthenticationApiTest extends TestCase
{
    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => password_hash('password123', PASSWORD_BCRYPT),
        ]);

        $response = $this->apiPost('/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'token',
                'user' => [
                    'id',
                    'email',
                    'first_name',
                    'last_name',
                    'role',
                ],
            ],
        ]);
        $this->assertTrue($response->json('success'));
    }

    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => password_hash('password123', PASSWORD_BCRYPT),
        ]);

        $response = $this->apiPost('/auth/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401);
        $this->assertFalse($response->json('success'));
    }

    public function test_user_cannot_login_with_nonexistent_email(): void
    {
        $response = $this->apiPost('/auth/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(401);
        $this->assertFalse($response->json('success'));
    }

    public function test_login_requires_email(): void
    {
        $response = $this->apiPost('/auth/login', [
            'password' => 'password123',
        ]);

        $response->assertStatus(422);
        $this->assertValidationError($response->json(), 'email');
    }

    public function test_login_requires_password(): void
    {
        $response = $this->apiPost('/auth/login', [
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(422);
        $this->assertValidationError($response->json(), 'password');
    }

    public function test_authenticated_user_can_access_protected_route(): void
    {
        $user = User::factory()->create();
        $token = $this->generateJwtToken($user);

        $response = $this->apiGet('/auth/me', [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);
        $this->assertEquals($user->id, $response->json('data.id'));
    }

    public function test_unauthenticated_user_cannot_access_protected_route(): void
    {
        $response = $this->apiGet('/auth/me');

        $response->assertStatus(401);
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();
        $token = $this->generateJwtToken($user);

        $response = $this->apiPost('/auth/logout', [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);
        $this->assertTrue($response->json('success'));
    }

    public function test_inactive_user_cannot_login(): void
    {
        $user = User::factory()->inactive()->create([
            'email' => 'inactive@example.com',
            'password' => password_hash('password123', PASSWORD_BCRYPT),
        ]);

        $response = $this->apiPost('/auth/login', [
            'email' => 'inactive@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(403);
        $this->assertFalse($response->json('success'));
    }
}
