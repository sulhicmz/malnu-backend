<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\PasswordResetToken;
use App\Models\User;
use App\Services\AuthService;
use App\Services\EmailService;
use App\Services\JWTService;
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

        $jwtService = new JWTService();
        $tokenBlacklistService = new TokenBlacklistService();
        $emailService = new EmailService();

        $this->authService = new AuthService($jwtService, $tokenBlacklistService, $emailService);
        $this->tokenBlacklistService = $tokenBlacklistService;
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

    public function testLoginWithInactiveAccountFails()
    {
        $userData = [
            'name' => 'Inactive User',
            'email' => 'inactive@example.com',
            'password' => 'password123',
        ];

        $result = $this->authService->register($userData);
        $userId = $result['user']['id'];

        $user = User::find($userId);
        $user->update(['is_active' => false]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Account is inactive');

        $this->authService->login('inactive@example.com', 'password123');
    }

    public function testGetUserFromTokenWithInactiveUserReturnsNull()
    {
        $userData = [
            'name' => 'Inactive Token User',
            'email' => 'inactivetoken@example.com',
            'password' => 'password123',
        ];

        $result = $this->authService->register($userData);
        $userId = $result['user']['id'];

        $user = User::find($userId);
        $user->update(['is_active' => false]);

        $loginResult = $this->authService->login('inactivetoken@example.com', 'password123');
        $token = $loginResult['token']['access_token'];

        $userFromToken = $this->authService->getUserFromToken($token);

        $this->assertNull($userFromToken);
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
        $this->assertArrayNotHasKey('expires_at', $result);

        $user = User::where('email', 'reset@example.com')->first();
        $tokenRecord = PasswordResetToken::where('user_id', $user->id)->first();

        $this->assertNotNull($tokenRecord);
        $this->assertIsString($tokenRecord->token);
        $this->assertNotNull($tokenRecord->expires_at);
    }

    public function testPasswordResetRequestForNonexistentUser()
    {
        $result = $this->authService->requestPasswordReset('nonexistent@example.com');

        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('message', $result);
        $this->assertArrayNotHasKey('reset_token', $result);
        $this->assertArrayNotHasKey('expires_at', $result);
    }

    public function testResetPasswordWithValidToken()
    {
        $userData = [
            'name' => 'Reset Password User',
            'email' => 'resetpass@example.com',
            'password' => 'originalpassword',
        ];

        $this->authService->register($userData);

        $user = User::where('email', 'resetpass@example.com')->first();
        $originalHash = $user->password;

        $resetToken = bin2hex(random_bytes(32));
        PasswordResetToken::create([
            'user_id' => $user->id,
            'token' => password_hash($resetToken, PASSWORD_DEFAULT),
            'expires_at' => now()->addHour(),
        ]);

        $result = $this->authService->resetPassword($resetToken, 'newpassword123');

        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('message', $result);

        $user->refresh();
        $this->assertNotEquals($originalHash, $user->password);
        $this->assertTrue(password_verify('newpassword123', $user->password));
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

    public function testChangePassword()
    {
        $userData = [
            'name' => 'Change Password User',
            'email' => 'changepass@example.com',
            'password' => 'originalpassword',
        ];

        $registerResult = $this->authService->register($userData);
        $userId = $registerResult['user']['id'];

        $result = $this->authService->changePassword($userId, 'originalpassword', 'newpassword123');

        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('message', $result);

        $user = User::find($userId);
        $this->assertTrue(password_verify('newpassword123', $user->password));
    }

    public function testChangePasswordWithWeakPassword()
    {
        $userData = [
            'name' => 'Weak Password User',
            'email' => 'weakpass@example.com',
            'password' => 'originalpassword',
        ];

        $registerResult = $this->authService->register($userData);
        $userId = $registerResult['user']['id'];

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('New password must be at least 8 characters');

        $this->authService->changePassword($userId, 'originalpassword', 'weak');
    }

    public function testChangePasswordWithIncorrectCurrentPassword()
    {
        $userData = [
            'name' => 'Incorrect Password User',
            'email' => 'incorrectpass@example.com',
            'password' => 'originalpassword',
        ];

        $registerResult = $this->authService->register($userData);
        $userId = $registerResult['user']['id'];

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Current password is incorrect');

        $this->authService->changePassword($userId, 'wrongpassword', 'newpassword123');
    }

    public function testResetPasswordWithExpiredToken()
    {
        $userData = [
            'name' => 'Expired Token User',
            'email' => 'expired@example.com',
            'password' => 'originalpassword',
        ];

        $this->authService->register($userData);

        $user = User::where('email', 'expired@example.com')->first();

        $resetToken = bin2hex(random_bytes(32));
        PasswordResetToken::create([
            'user_id' => $user->id,
            'token' => password_hash($resetToken, PASSWORD_DEFAULT),
            'expires_at' => now()->subHour(),
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Reset token has expired');

        $this->authService->resetPassword($resetToken, 'newpassword123');
    }

    public function testResetPasswordWithInvalidTokenHash()
    {
        $userData = [
            'name' => 'Invalid Token User',
            'email' => 'invalid@example.com',
            'password' => 'originalpassword',
        ];

        $this->authService->register($userData);

        $user = User::where('email', 'invalid@example.com')->first();

        $resetToken = bin2hex(random_bytes(32));
        PasswordResetToken::create([
            'user_id' => $user->id,
            'token' => password_hash($resetToken, PASSWORD_DEFAULT),
            'expires_at' => now()->addHour(),
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid reset token');

        $this->authService->resetPassword('wrongtoken' . str_repeat('a', 64), 'newpassword123');
    }
}
