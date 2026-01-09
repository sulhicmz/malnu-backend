<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\AuthService;
use App\Services\TokenBlacklistService;
use Exception;
use Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
class AuthServiceTest extends TestCase
{
    private AuthService $authService;

    private TokenBlacklistService $tokenBlacklistService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->authService = new AuthService();
        $this->tokenBlacklistService = new TokenBlacklistService();
    }

    public function testUserRegistrationWithDatabasePersistence()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
        ];

        $result = $this->authService->register($userData);

        $this->assertArrayHasKey('user', $result);
        $this->assertArrayHasKey('id', $result['user']);
        $this->assertArrayHasKey('name', $result['user']);
        $this->assertArrayHasKey('email', $result['user']);
        $this->assertEquals('Test User', $result['user']['name']);
        $this->assertEquals('test@example.com', $result['user']['email']);
        $this->assertTrue(password_verify('password123', $result['user']['password']));
    }

    public function testDuplicateEmailRegistrationFails()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'duplicate@example.com',
            'password' => 'password123',
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
            'password' => 'correctpassword',
        ];

        $this->authService->register($userData);

        $result = $this->authService->login('login@example.com', 'correctpassword');

        $this->assertArrayHasKey('user', $result);
        $this->assertArrayHasKey('token', $result);
        $this->assertArrayHasKey('access_token', $result['token']);
        $this->assertArrayHasKey('token_type', $result['token']);
        $this->assertEquals('bearer', $result['token']['token_type']);
        $this->assertEquals('Login Test User', $result['user']['name']);
        $this->assertEquals('login@example.com', $result['user']['email']);
    }

    public function testFailedLoginWithWrongCredentials()
    {
        $userData = [
            'name' => 'Wrong Password User',
            'email' => 'wrongpassword@example.com',
            'password' => 'correctpassword',
        ];

        $this->authService->register($userData);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid credentials');

        $this->authService->login('wrongpassword@example.com', 'wrongpassword');
    }

    public function testLoginWithNonexistentUserFails()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid credentials');

        $this->authService->login('nonexistent@example.com', 'anypassword');
    }

    public function testGetUserFromToken()
    {
        $userData = [
            'name' => 'Token Test User',
            'email' => 'token@example.com',
            'password' => 'tokenpassword',
        ];

        $registerResult = $this->authService->register($userData);
        $loginResult = $this->authService->login('token@example.com', 'tokenpassword');

        $token = $loginResult['token']['access_token'];
        $user = $this->authService->getUserFromToken($token);

        $this->assertNotNull($user);
        $this->assertEquals('Token Test User', $user['name']);
        $this->assertEquals('token@example.com', $user['email']);
        $this->assertEquals($registerResult['user']['id'], $user['id']);
    }

    public function testGetUserFromBlacklistedTokenReturnsNull()
    {
        $userData = [
            'name' => 'Blacklist Test User',
            'email' => 'blacklist@example.com',
            'password' => 'blacklistpassword',
        ];

        $this->authService->register($userData);
        $loginResult = $this->authService->login('blacklist@example.com', 'blacklistpassword');

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
            'password' => 'refreshpassword',
        ];

        $this->authService->register($userData);
        $loginResult = $this->authService->login('refresh@example.com', 'refreshpassword');

        $oldToken = $loginResult['token']['access_token'];
        $refreshResult = $this->authService->refreshToken($oldToken);

        $this->assertArrayHasKey('token', $refreshResult);
        $this->assertArrayHasKey('access_token', $refreshResult['token']);
        $this->assertNotEquals($oldToken, $refreshResult['token']['access_token']);

        $newUser = $this->authService->getUserFromToken($refreshResult['token']['access_token']);
        $this->assertNotNull($newUser);
        $this->assertEquals('Refresh Test User', $newUser['name']);
    }

    public function testRefreshBlacklistedTokenFails()
    {
        $userData = [
            'name' => 'Blacklist Refresh User',
            'email' => 'blacklistrefresh@example.com',
            'password' => 'blacklistrefreshpassword',
        ];

        $this->authService->register($userData);
        $loginResult = $this->authService->login('blacklistrefresh@example.com', 'blacklistrefreshpassword');

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
            'password' => 'originalpassword',
        ];

        $this->authService->register($userData);

        $result = $this->authService->requestPasswordReset('reset@example.com');

        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('message', $result);
        $this->assertArrayNotHasKey('reset_token', $result);
    }

    public function testPasswordResetRequestForNonexistentUser()
    {
        $result = $this->authService->requestPasswordReset('nonexistent@example.com');

        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('message', $result);
        $this->assertArrayNotHasKey('reset_token', $result);
    }

    public function testPasswordResetTokenStoredInDatabase()
    {
        $userData = [
            'name' => 'Token Storage User',
            'email' => 'token@example.com',
            'password' => 'originalpassword',
        ];

        $registerResult = $this->authService->register($userData);

        $this->authService->requestPasswordReset('token@example.com');

        $tokens = \App\Models\PasswordResetToken::where('user_id', $registerResult['user']['id'])->get();
        $this->assertGreaterThan(0, $tokens->count());
    }

    public function testResetPasswordWithValidTokenUpdatesPassword()
    {
        $userData = [
            'name' => 'Reset Password User',
            'email' => 'resetpass@example.com',
            'password' => 'originalpassword',
        ];

        $registerResult = $this->authService->register($userData);
        $userId = $registerResult['user']['id'];

        $this->authService->requestPasswordReset('resetpass@example.com');

        $tokenRecord = \App\Models\PasswordResetToken::where('user_id', $userId)->first();

        $newPassword = 'newpassword123';
        $result = $this->authService->resetPassword($tokenRecord->token, $newPassword);

        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success']);

        $loginResult = $this->authService->login('resetpass@example.com', $newPassword);
        $this->assertArrayHasKey('user', $loginResult);
        $this->assertEquals('Reset Password User', $loginResult['user']['name']);
    }

    public function testResetPasswordWithInvalidTokenFormat()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid or expired reset token');

        $this->authService->resetPassword('invalidtoken', 'newpassword123');
    }

    public function testResetPasswordWithWeakPassword()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Password must be at least 8 characters');

        $this->authService->resetPassword(
            str_repeat('a', 64),
            'weak'
        );
    }

    public function testChangePasswordWithCorrectCurrentPassword()
    {
        $userData = [
            'name' => 'Change Password User',
            'email' => 'changepass@example.com',
            'password' => 'currentpassword',
        ];

        $registerResult = $this->authService->register($userData);
        $userId = $registerResult['user']['id'];

        $newPassword = 'newpassword123';
        $result = $this->authService->changePassword($userId, 'currentpassword', $newPassword);

        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success']);

        $loginResult = $this->authService->login('changepass@example.com', $newPassword);
        $this->assertArrayHasKey('user', $loginResult);
    }

    public function testChangePasswordWithIncorrectCurrentPassword()
    {
        $userData = [
            'name' => 'Wrong Current Password User',
            'email' => 'wrongpass@example.com',
            'password' => 'currentpassword',
        ];

        $registerResult = $this->authService->register($userData);
        $userId = $registerResult['user']['id'];

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Current password is incorrect');

        $this->authService->changePassword($userId, 'wrongpassword', 'newpassword123');
    }

    public function testChangePasswordWithWeakNewPassword()
    {
        $userData = [
            'name' => 'Weak New Password User',
            'email' => 'weakpass@example.com',
            'password' => 'currentpassword',
        ];

        $registerResult = $this->authService->register($userData);
        $userId = $registerResult['user']['id'];

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('New password must be at least 8 characters');

        $this->authService->changePassword($userId, 'currentpassword', 'weak');
    }
}
