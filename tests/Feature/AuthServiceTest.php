<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Services\AuthService;
use App\Services\TokenBlacklistService;

class AuthServiceTest extends TestCase
{
    private AuthService $authService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authService = new AuthService();
    }

    public function test_user_registration_creates_user_in_database()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123'
        ];

        $result = $this->authService->register($userData);

        $this->assertArrayHasKey('user', $result);
        $this->assertArrayHasKey('id', $result['user']);
        $this->assertEquals($userData['name'], $result['user']['name']);
        $this->assertEquals($userData['email'], $result['user']['email']);
        $this->assertArrayHasKey('password', $result['user']);

        $this->assertDatabaseHas('users', [
            'email' => $userData['email']
        ]);
    }

    public function test_duplicate_email_registration_throws_exception()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123'
        ];

        $this->authService->register($userData);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('User with this email already exists');

        $this->authService->register($userData);
    }

    public function test_login_with_valid_credentials_returns_token()
    {
        $password = 'password123';
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => $password
        ];

        $this->authService->register($userData);

        $result = $this->authService->login($userData['email'], $password);

        $this->assertArrayHasKey('user', $result);
        $this->assertArrayHasKey('token', $result);
        $this->assertArrayHasKey('access_token', $result['token']);
        $this->assertArrayHasKey('token_type', $result['token']);
        $this->assertEquals('bearer', $result['token']['token_type']);
        $this->assertArrayHasKey('expires_in', $result['token']);
    }

    public function test_login_with_invalid_credentials_throws_exception()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123'
        ];

        $this->authService->register($userData);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid credentials');

        $this->authService->login('test@example.com', 'wrongpassword');
    }

    public function test_login_with_non_existent_user_throws_exception()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid credentials');

        $this->authService->login('nonexistent@example.com', 'password123');
    }

    public function test_user_retrieval_from_valid_token_works()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123'
        ];

        $registerResult = $this->authService->register($userData);
        $token = $registerResult['user']['id'];

        $user = $this->authService->getUserFromToken($token);

        $this->assertNotNull($user);
        $this->assertEquals($userData['email'], $user['email']);
        $this->assertEquals($userData['name'], $user['name']);
    }

    public function test_user_retrieval_from_blacklisted_token_returns_null()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123'
        ];

        $registerResult = $this->authService->register($userData);
        $token = $registerResult['user']['id'];

        $blacklistService = new TokenBlacklistService();
        $blacklistService->blacklistToken($token);

        $user = $this->authService->getUserFromToken($token);

        $this->assertNull($user);
    }

    public function test_token_refresh_generates_new_token()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123'
        ];

        $this->authService->register($userData);

        $loginResult = $this->authService->login($userData['email'], $userData['password']);
        $oldToken = $loginResult['token']['access_token'];

        $refreshResult = $this->authService->refreshToken($oldToken);

        $this->assertArrayHasKey('token', $refreshResult);
        $this->assertArrayHasKey('access_token', $refreshResult['token']);
        $this->assertNotEquals($oldToken, $refreshResult['token']['access_token']);
    }

    public function test_token_refresh_with_blacklisted_token_throws_exception()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123'
        ];

        $this->authService->register($userData);

        $loginResult = $this->authService->login($userData['email'], $userData['password']);
        $token = $loginResult['token']['access_token'];

        $this->authService->logout($token);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Token is blacklisted');

        $this->authService->refreshToken($token);
    }

    public function test_logout_adds_token_to_blacklist()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123'
        ];

        $this->authService->register($userData);

        $loginResult = $this->authService->login($userData['email'], $userData['password']);
        $token = $loginResult['token']['access_token'];

        $this->authService->logout($token);

        $blacklistService = new TokenBlacklistService();
        $this->assertTrue($blacklistService->isTokenBlacklisted($token));
    }
}
