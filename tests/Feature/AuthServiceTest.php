<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Services\AuthService;
use Exception;
use Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
class AuthServiceTest extends TestCase
{
    private AuthService $authService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authService = new AuthService();
    }

    public function testUserRegistrationWithDatabasePersistence()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'securePassword123',
        ];

        $result = $this->authService->register($userData);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('user', $result);
        $this->assertArrayHasKey('id', $result['user']);
        $this->assertArrayHasKey('email', $result['user']);
        $this->assertArrayHasKey('name', $result['user']);
        $this->assertEquals('Test User', $result['user']['name']);
        $this->assertEquals('test@example.com', $result['user']['email']);
        $this->assertArrayHasKey('password', $result['user']);
        $this->assertTrue(password_verify('securePassword123', $result['user']['password']));

        $savedUser = User::where('email', 'test@example.com')->first();
        $this->assertNotNull($savedUser);
        $this->assertEquals('Test User', $savedUser->name);
    }

    public function testDuplicateEmailRegistrationFails()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'duplicate@example.com',
            'password' => 'securePassword123',
        ];

        $this->authService->register($userData);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('User with this email already exists');

        $this->authService->register($userData);
    }

    public function testSuccessfulLoginWithCorrectCredentials()
    {
        $userData = [
            'name' => 'Login Test User',
            'email' => 'login@example.com',
            'password' => 'correctPassword123',
        ];

        $this->authService->register($userData);

        $result = $this->authService->login('login@example.com', 'correctPassword123');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('user', $result);
        $this->assertArrayHasKey('token', $result);
        $this->assertArrayHasKey('access_token', $result['token']);
        $this->assertArrayHasKey('token_type', $result['token']);
        $this->assertEquals('bearer', $result['token']['token_type']);
        $this->assertArrayHasKey('expires_in', $result['token']);
    }

    public function testFailedLoginWithWrongCredentials()
    {
        $userData = [
            'name' => 'Wrong Password User',
            'email' => 'wrong@example.com',
            'password' => 'correctPassword123',
        ];

        $this->authService->register($userData);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid credentials');

        $this->authService->login('wrong@example.com', 'wrongPassword123');
    }

    public function testLoginWithNonexistentUserFails()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid credentials');

        $this->authService->login('nonexistent@example.com', 'anyPassword123');
    }

    public function testGetUserFromToken()
    {
        $userData = [
            'name' => 'Token Test User',
            'email' => 'token@example.com',
            'password' => 'tokenPassword123',
        ];

        $registerResult = $this->authService->register($userData);
        $userId = $registerResult['user']['id'];

        $loginResult = $this->authService->login('token@example.com', 'tokenPassword123');
        $token = $loginResult['token']['access_token'];

        $user = $this->authService->getUserFromToken($token);

        $this->assertIsArray($user);
        $this->assertEquals($userId, $user['id']);
        $this->assertEquals('token@example.com', $user['email']);
        $this->assertEquals('Token Test User', $user['name']);
    }

    public function testGetUserFromBlacklistedTokenReturnsNull()
    {
        $userData = [
            'name' => 'Blacklist Test User',
            'email' => 'blacklist@example.com',
            'password' => 'blacklistPassword123',
        ];

        $this->authService->register($userData);

        $loginResult = $this->authService->login('blacklist@example.com', 'blacklistPassword123');
        $token = $loginResult['token']['access_token'];

        $this->authService->logout($token);

        $user = $this->authService->getUserFromToken($token);

        $this->assertNull($user);
    }

    public function testTokenRefresh()
    {
        $userData = [
            'name' => 'Refresh Test User',
            'email' => 'refresh@example.com',
            'password' => 'refreshPassword123',
        ];

        $this->authService->register($userData);

        $loginResult = $this->authService->login('refresh@example.com', 'refreshPassword123');
        $oldToken = $loginResult['token']['access_token'];

        $refreshResult = $this->authService->refreshToken($oldToken);

        $this->assertIsArray($refreshResult);
        $this->assertArrayHasKey('token', $refreshResult);
        $this->assertArrayHasKey('access_token', $refreshResult['token']);
        $this->assertNotEquals($oldToken, $refreshResult['token']['access_token']);
    }

    public function testRefreshBlacklistedTokenFails()
    {
        $userData = [
            'name' => 'Blacklist Refresh User',
            'email' => 'blacklistrefresh@example.com',
            'password' => 'refreshBlacklistPassword123',
        ];

        $this->authService->register($userData);

        $loginResult = $this->authService->login('blacklistrefresh@example.com', 'refreshBlacklistPassword123');
        $token = $loginResult['token']['access_token'];

        $this->authService->logout($token);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Token is blacklisted');

        $this->authService->refreshToken($token);
    }

    public function testPasswordResetRequestForExistingUser()
    {
        $userData = [
            'name' => 'Password Reset User',
            'email' => 'reset@example.com',
            'password' => 'originalPassword123',
        ];

        $this->authService->register($userData);

        $result = $this->authService->requestPasswordReset('reset@example.com');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('message', $result);
        $this->assertArrayHasKey('reset_token', $result);
    }

    public function testPasswordResetRequestForNonexistentUser()
    {
        $result = $this->authService->requestPasswordReset('nonexistent@example.com');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('message', $result);
        $this->assertEquals('If email exists, a reset link has been sent', $result['message']);
    }
}
