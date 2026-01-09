<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\AuthService;
use App\Services\TokenBlacklistService;

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

    public function test_user_registration_with_database_persistence()
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

    public function test_duplicate_email_registration_fails()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'duplicate@example.com',
            'password' => 'password123',
        ];

        $this->authService->register($userData);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('User with this email already exists');

        $this->authService->register($userData);
    }

    public function test_successful_login_with_correct_credentials()
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

    public function test_failed_login_with_wrong_credentials()
    {
        $userData = [
            'name' => 'Wrong Password User',
            'email' => 'wrongpassword@example.com',
            'password' => 'correctpassword',
        ];

        $this->authService->register($userData);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid credentials');

        $this->authService->login('wrongpassword@example.com', 'wrongpassword');
    }

    public function test_login_with_nonexistent_user_fails()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid credentials');

        $this->authService->login('nonexistent@example.com', 'anypassword');
    }

    public function test_get_user_from_token()
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

    public function test_get_user_from_blacklisted_token_returns_null()
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

    public function test_token_refresh()
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

    public function test_refresh_blacklisted_token_fails()
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

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Token is blacklisted');

        $this->authService->refreshToken($token);
    }

    public function test_password_reset_request_for_existing_user()
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
        $this->assertArrayHasKey('reset_token', $result);
        $this->assertArrayHasKey('expires_at', $result);
        $this->assertIsString($result['reset_token']);
        $this->assertIsInt($result['expires_at']);
    }

    public function test_password_reset_request_for_nonexistent_user()
    {
        $result = $this->authService->requestPasswordReset('nonexistent@example.com');

        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('message', $result);
        $this->assertArrayNotHasKey('reset_token', $result);
        $this->assertArrayNotHasKey('expires_at', $result);
    }

    public function test_reset_password_with_valid_token()
    {
        $result = $this->authService->resetPassword(
            str_repeat('a', 64),
            'newpassword123'
        );

        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('message', $result);
    }

    public function test_reset_password_with_invalid_token_format()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid reset token');

        $this->authService->resetPassword('invalidtoken', 'newpassword123');
    }

    public function test_reset_password_with_weak_password()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Password must be at least 8 characters');

        $this->authService->resetPassword(
            str_repeat('a', 64),
            'weak'
        );
    }

    public function test_reset_password_without_uppercase()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Password must contain at least one uppercase letter');

        $this->authService->resetPassword(
            str_repeat('a', 64),
            'password1!'
        );
    }

    public function test_reset_password_without_lowercase()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Password must contain at least one lowercase letter');

        $this->authService->resetPassword(
            str_repeat('a', 64),
            'PASSWORD1!'
        );
    }

    public function test_reset_password_without_number()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Password must contain at least one number');

        $this->authService->resetPassword(
            str_repeat('a', 64),
            'Password!'
        );
    }

    public function test_reset_password_without_special_char()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Password must contain at least one special character');

        $this->authService->resetPassword(
            str_repeat('a', 64),
            'Password1'
        );
    }

    public function test_reset_password_with_common_password()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Password is too common');

        $this->authService->resetPassword(
            str_repeat('a', 64),
            'Password123!'
        );
    }

    public function test_reset_password_with_strong_password()
    {
        $result = $this->authService->resetPassword(
            str_repeat('a', 64),
            'SecureP@ssw0rd'
        );

        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('message', $result);
    }

    public function test_change_password()
    {
        $result = $this->authService->changePassword('user-id', 'currentpass', 'newpassword123');

        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('message', $result);
    }

    public function test_change_password_with_weak_password()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Password must be at least 8 characters');

        $this->authService->changePassword('user-id', 'currentpass', 'weak');
    }

    public function test_change_password_without_uppercase()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Password must contain at least one uppercase letter');

        $this->authService->changePassword('user-id', 'currentpass', 'password1!');
    }

    public function test_change_password_without_lowercase()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Password must contain at least one lowercase letter');

        $this->authService->changePassword('user-id', 'currentpass', 'PASSWORD1!');
    }

    public function test_change_password_without_number()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Password must contain at least one number');

        $this->authService->changePassword('user-id', 'currentpass', 'Password!');
    }

    public function test_change_password_without_special_char()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Password must contain at least one special character');

        $this->authService->changePassword('user-id', 'currentpass', 'Password1');
    }

    public function test_change_password_with_common_password()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Password is too common');

        $this->authService->changePassword('user-id', 'currentpass', 'Password123!');
    }

    public function test_change_password_with_strong_password()
    {
        $result = $this->authService->changePassword('user-id', 'currentpass', 'SecureP@ssw0rd');

        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('message', $result);
    }
}
