<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\AuthService;
use App\Services\TokenBlacklistService;
use App\Services\PasswordValidator;
use App\Services\JWTService;
use App\Services\EmailService;
use App\Models\User;
use App\Models\PasswordResetToken;
use App\Exceptions\AuthenticationException;
use App\Exceptions\BusinessLogicException;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;

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
        $passwordValidator = new PasswordValidator();

        $this->authService = new AuthService(
            $jwtService,
            $tokenBlacklistService,
            $emailService,
            $passwordValidator
        );
        $this->tokenBlacklistService = $tokenBlacklistService;
    }

    public function test_user_registration_with_database_persistence()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'SecurePass123!',
        ];

        $result = $this->authService->register($userData);

        $this->assertArrayHasKey('user', $result);
        $this->assertArrayHasKey('id', $result['user']);
        $this->assertArrayHasKey('name', $result['user']);
        $this->assertArrayHasKey('email', $result['user']);
        $this->assertEquals('Test User', $result['user']['name']);
        $this->assertEquals('test@example.com', $result['user']['email']);
        $this->assertTrue(password_verify('SecurePass123!', $result['user']['password']));
    }

    public function test_duplicate_email_registration_fails()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'duplicate@example.com',
            'password' => 'SecurePass123!',
        ];

        $this->authService->register($userData);

        $this->expectException(BusinessLogicException::class);
        $this->expectExceptionMessage('User with this email already exists');

        $this->authService->register($userData);
    }

    public function test_successful_login_with_correct_credentials()
    {
        $userData = [
            'name' => 'Login Test User',
            'email' => 'login@example.com',
            'password' => 'CorrectPass123!',
        ];

        $this->authService->register($userData);

        $result = $this->authService->login('login@example.com', 'CorrectPass123!');

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
            'password' => 'CorrectPass123!',
        ];

        $this->authService->register($userData);

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Invalid credentials');

        $this->authService->login('wrongpassword@example.com', 'wrongpassword');
    }

    public function test_login_with_nonexistent_user_fails()
    {
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Invalid credentials');

        $this->authService->login('nonexistent@example.com', 'anypassword');
    }

    public function test_get_user_from_token()
    {
        $userData = [
            'name' => 'Token Test User',
            'email' => 'token@example.com',
            'password' => 'TokenPass123!',
        ];

        $registerResult = $this->authService->register($userData);
        $loginResult = $this->authService->login('token@example.com', 'TokenPass123!');

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
            'password' => 'BlacklistPass123!',
        ];

        $this->authService->register($userData);
        $loginResult = $this->authService->login('blacklist@example.com', 'BlacklistPass123!');

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
            'password' => 'RefreshPass123!',
        ];

        $this->authService->register($userData);
        $loginResult = $this->authService->login('refresh@example.com', 'RefreshPass123!');

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
            'password' => 'BlacklistRefreshPass123!',
        ];

        $this->authService->register($userData);
        $loginResult = $this->authService->login('blacklistrefresh@example.com', 'BlacklistRefreshPass123!');

        $token = $loginResult['token']['access_token'];
        $this->authService->logout($token);

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Token is blacklisted');

        $this->authService->refreshToken($token);
    }

    public function test_password_reset_request_for_existing_user()
    {
        $userData = [
            'name' => 'Password Reset User',
            'email' => 'reset@example.com',
            'password' => 'OriginalPass123!',
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
        $userData = [
            'name' => 'Reset Password User',
            'email' => 'resetpass@example.com',
            'password' => 'OriginalPass123!',
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

        $result = $this->authService->resetPassword($resetToken, 'NewSecurePass123!');

        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('message', $result);

        $user->refresh();
        $this->assertEquals('NewSecurePass123!', $user->getOriginal('password'));
        $this->assertTrue(password_verify('NewSecurePass123!', $user->password));
    }

    public function test_password_validation_requires_minimum_length()
    {
        $userData = [
            'name' => 'Validation Test User',
            'email' => 'validation@example.com',
            'password' => 'Short1!',
        ];

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('must be at least 8 characters');

        $this->authService->resetPassword(
            str_repeat('a', 64),
            'Weak1!'
        );
    }

    public function test_change_password()
    {
        $userData = [
            'name' => 'Change Password User',
            'email' => 'changepass@example.com',
            'password' => 'OriginalPass123!',
        ];

        $registerResult = $this->authService->register($userData);
        $userId = $registerResult['user']['id'];

        $result = $this->authService->changePassword($userId, 'OriginalPass123!', 'NewSecurePass123!');

        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('message', $result);

        $user = User::find($userId);
        $this->assertTrue(password_verify('NewSecurePass123!', $user->password));
    }

    public function test_change_password_with_weak_password()
    {
        $userData = [
            'name' => 'Weak Password User',
            'email' => 'weakpass@example.com',
            'password' => 'OriginalPass123!',
        ];

        $registerResult = $this->authService->register($userData);
        $userId = $registerResult['user']['id'];

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('must be at least 8 characters');

        $this->authService->changePassword($userId, 'OriginalPass123!', 'Weak1!');
    }

    public function test_change_password_with_incorrect_current_password()
    {
        $userData = [
            'name' => 'Incorrect Password User',
            'email' => 'incorrectpass@example.com',
            'password' => 'OriginalPass123!',
        ];

        $registerResult = $this->authService->register($userData);
        $userId = $registerResult['user']['id'];

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Current password is incorrect');

        $this->authService->changePassword($userId, 'WrongPass123!', 'NewSecurePass123!');
    }

    public function test_reset_password_with_expired_token()
    {
        $userData = [
            'name' => 'Expired Token User',
            'email' => 'expired@example.com',
            'password' => 'OriginalPass123!',
        ];

        $this->authService->register($userData);

        $user = User::where('email', 'expired@example.com')->first();

        $resetToken = bin2hex(random_bytes(32));
        PasswordResetToken::create([
            'user_id' => $user->id,
            'token' => password_hash($resetToken, PASSWORD_DEFAULT),
            'expires_at' => now()->subHour(),
        ]);

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Reset token has expired');

        $this->authService->resetPassword($resetToken, 'NewSecurePass123!');
    }

    public function test_reset_password_with_invalid_token_hash()
    {
        $userData = [
            'name' => 'Invalid Token User',
            'email' => 'invalid@example.com',
            'password' => 'OriginalPass123!',
        ];

        $this->authService->register($userData);

        $user = User::where('email', 'invalid@example.com')->first();

        $resetToken = bin2hex(random_bytes(32));
        PasswordResetToken::create([
            'user_id' => $user->id,
            'token' => password_hash($resetToken, PASSWORD_DEFAULT),
            'expires_at' => now()->addHour(),
        ]);

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Invalid reset token');

        $this->authService->resetPassword('wrongtoken' . str_repeat('a', 64), 'NewSecurePass123!');
    }

    public function test_password_complexity_requires_minimum_length()
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('must be at least 8 characters');

        $this->authService->resetPassword(str_repeat('a', 64), 'Short1!');
    }

    public function test_password_complexity_requires_uppercase()
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('must contain at least one uppercase letter');

        $this->authService->resetPassword(str_repeat('a', 64), 'lowercase123!');
    }

    public function test_password_complexity_requires_lowercase()
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('must contain at least one lowercase letter');

        $this->authService->resetPassword(str_repeat('a', 64), 'UPPERCASE123!');
    }

    public function test_password_complexity_requires_number()
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('must contain at least one number');

        $this->authService->resetPassword(str_repeat('a', 64), 'NoNumbers!');
    }

    public function test_password_complexity_requires_special_character()
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('must contain at least one special character');

        $this->authService->resetPassword(str_repeat('a', 64), 'NoSpecialChar123');
    }

    public function test_password_complexity_rejects_common_password()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'commonpass@example.com',
            'password' => 'Password123!',
        ];

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Password is too common');

        $this->authService->register($userData);
    }

    public function test_registration_fails_with_password_missing_uppercase()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'nouppercase@example.com',
            'password' => 'nouppercase123!',
        ];

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('must contain at least one uppercase letter');

        $this->authService->register($userData);
    }

    public function test_registration_fails_with_password_missing_lowercase()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'nolowercase@example.com',
            'password' => 'NOLOWERCASE123!',
        ];

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('must contain at least one lowercase letter');

        $this->authService->register($userData);
    }

    public function test_registration_fails_with_password_missing_number()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'nonumber@example.com',
            'password' => 'NoNumberPass!',
        ];

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('must contain at least one number');

        $this->authService->register($userData);
    }

    public function test_registration_fails_with_password_missing_special_character()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'nospecial@example.com',
            'password' => 'NoSpecialChar123',
        ];

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('must contain at least one special character');

        $this->authService->register($userData);
    }

    public function test_reset_password_fails_with_password_missing_uppercase()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'resetnouppercase@example.com',
            'password' => 'OriginalPass123!',
        ];

        $this->authService->register($userData);
        $user = User::where('email', 'resetnouppercase@example.com')->first();

        $resetToken = bin2hex(random_bytes(32));
        PasswordResetToken::create([
            'user_id' => $user->id,
            'token' => password_hash($resetToken, PASSWORD_DEFAULT),
            'expires_at' => now()->addHour(),
        ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('must contain at least one uppercase letter');

        $this->authService->resetPassword($resetToken, 'nouppercase123!');
    }

    public function test_change_password_fails_with_password_missing_lowercase()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'changenolowercase@example.com',
            'password' => 'OriginalPass123!',
        ];

        $registerResult = $this->authService->register($userData);
        $userId = $registerResult['user']['id'];

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('must contain at least one lowercase letter');

        $this->authService->changePassword($userId, 'OriginalPass123!', 'NOLOWERCASE123!');
    }
}
