<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\PasswordResetToken;
use App\Models\User;
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
            'password' => 'Password123!',
        ];

        $result = $this->authService->register($userData);

        $this->assertArrayHasKey('user', $result);
        $this->assertArrayHasKey('id', $result['user']);
        $this->assertArrayHasKey('name', $result['user']);
        $this->assertArrayHasKey('email', $result['user']);
        $this->assertEquals('Test User', $result['user']['name']);
        $this->assertEquals('test@example.com', $result['user']['email']);
        $this->assertTrue(password_verify('Password123!', $result['user']['password']));
    }

    public function testDuplicateEmailRegistrationFails()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'duplicate@example.com',
            'password' => 'Password123!',
        ];

        $this->authService->register($userData);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('User with this email already exists');

        $this->authService->register($userData);
    }

    public function testRegistrationWithTooShortPassword()
    {
        $userData = [
            'name' => 'Short Password User',
            'email' => 'short@example.com',
            'password' => 'Short1!',
        ];

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Password must contain at least 8 characters');

        $this->authService->register($userData);
    }

    public function testRegistrationWithoutUppercase()
    {
        $userData = [
            'name' => 'No Uppercase User',
            'email' => 'noupper2@example.com',
            'password' => 'lowercase1!',
        ];

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Password must contain at least 1 uppercase letter');

        $this->authService->register($userData);
    }

    public function testRegistrationWithoutLowercase()
    {
        $userData = [
            'name' => 'No Lowercase User',
            'email' => 'nolower2@example.com',
            'password' => 'UPPERCASE1!',
        ];

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Password must contain at least 1 lowercase letter');

        $this->authService->register($userData);
    }

    public function testRegistrationWithoutNumber()
    {
        $userData = [
            'name' => 'No Number User',
            'email' => 'nonumber2@example.com',
            'password' => 'NoNumber!',
        ];

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Password must contain at least 1 number');

        $this->authService->register($userData);
    }

    public function testRegistrationWithoutSpecialCharacter()
    {
        $userData = [
            'name' => 'No Special User',
            'email' => 'nospecial2@example.com',
            'password' => 'NoSpecial1',
        ];

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Password must contain at least 1 special character');

        $this->authService->register($userData);
    }

    public function testRegistrationWithCommonPassword()
    {
        $userData = [
            'name' => 'Common Password User',
            'email' => 'common2@example.com',
            'password' => 'Password123',
        ];

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('not use a common password');

        $this->authService->register($userData);
    }

    public function testSuccessfulLoginWithCorrectCredentials()
    {
        $userData = [
            'name' => 'Login Test User',
            'email' => 'login@example.com',
            'password' => 'Password123!',
        ];

        $this->authService->register($userData);

        $result = $this->authService->login('login@example.com', 'Password123!');

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
            'password' => 'Password123!',
        ];

        $this->authService->register($userData);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid credentials');

        $this->authService->login('wrongpassword@example.com', 'wrongpassword!');
    }

    public function testLoginWithNonexistentUserFails()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid credentials');

        $this->authService->login('nonexistent@example.com', 'WrongPassword123!');
    }

    public function testGetUserFromToken()
    {
        $userData = [
            'name' => 'Token Test User',
            'email' => 'token@example.com',
            'password' => 'Password123!',
        ];

        $registerResult = $this->authService->register($userData);
        $loginResult = $this->authService->login('token@example.com', 'Password123!');

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
            'password' => 'Password123!',
        ];

        $this->authService->register($userData);
        $loginResult = $this->authService->login('blacklist@example.com', 'Password123!');

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
            'password' => 'Password123!',
        ];

        $this->authService->register($userData);
        $loginResult = $this->authService->login('refresh@example.com', 'Password123!');

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
            'password' => 'Password123!',
        ];

        $this->authService->register($userData);
        $loginResult = $this->authService->login('blacklistrefresh@example.com', 'Password123!');

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
            'password' => 'Password123!',
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
            'password' => 'Password123!',
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

        $result = $this->authService->resetPassword($resetToken, 'NewP@ss123!');

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

        $this->authService->resetPassword('invalidtoken', 'NewP@ss123!');
    }

    public function testResetPasswordWithTooShortPassword()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Password must contain at least 8 characters');

        $this->authService->resetPassword(
            str_repeat('a', 64),
            'Short1!'
        );
    }

    public function testResetPasswordWithoutUppercase()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Password must contain at least 1 uppercase letter');

        $this->authService->resetPassword(
            str_repeat('a', 64),
            'lowercase1!'
        );
    }

    public function testResetPasswordWithoutLowercase()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Password must contain at least 1 lowercase letter');

        $this->authService->resetPassword(
            str_repeat('a', 64),
            'UPPERCASE1!'
        );
    }

    public function testResetPasswordWithoutNumber()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Password must contain at least 1 number');

        $this->authService->resetPassword(
            str_repeat('a', 64),
            'NoNumber!'
        );
    }

    public function testResetPasswordWithoutSpecialCharacter()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Password must contain at least 1 special character');

        $this->authService->resetPassword(
            str_repeat('a', 64),
            'NoSpecial1'
        );
    }

    public function testResetPasswordWithCommonPassword()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('not use a common password');

        $this->authService->resetPassword(
            str_repeat('a', 64),
            'Password123'
        );
    }

    public function testResetPasswordWithStrongPassword()
    {
        $userData = [
            'name' => 'Strong Password User',
            'email' => 'strongpass@example.com',
            'password' => 'originalpassword123',
        ];

        $this->authService->register($userData);

        $user = User::where('email', 'strongpass@example.com')->first();

        $resetToken = bin2hex(random_bytes(32));
        PasswordResetToken::create([
            'user_id' => $user->id,
            'token' => password_hash($resetToken, PASSWORD_DEFAULT),
            'expires_at' => now()->addHour(),
        ]);

        $result = $this->authService->resetPassword($resetToken, 'NewStr0ng!');

        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success']);

        $user->refresh();
        $this->assertTrue(password_verify('NewStr0ng!', $user->password));
    }

    public function testChangePassword()
    {
        $userData = [
            'name' => 'Change Password User',
            'email' => 'changepass@example.com',
            'password' => 'Password123!',
        ];

        $registerResult = $this->authService->register($userData);
        $userId = $registerResult['user']['id'];

        $result = $this->authService->changePassword($userId, 'Password123!', 'NewP@ss123!');

        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('message', $result);

        $user = User::find($userId);
        $this->assertTrue(password_verify('newpassword123', $user->password));
    }

    public function testChangePasswordWithTooShortPassword()
    {
        $userData = [
            'name' => 'Weak Password User',
            'email' => 'weakpass@example.com',
            'password' => 'OriginalPassword123!',
        ];

        $registerResult = $this->authService->register($userData);
        $userId = $registerResult['user']['id'];

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Password must contain at least 8 characters');

        $this->authService->changePassword($userId, 'OriginalPassword123!', 'Short1!');
    }

    public function testChangePasswordWithoutUppercase()
    {
        $userData = [
            'name' => 'No Uppercase User',
            'email' => 'noupper@example.com',
            'password' => 'OriginalPassword123!',
        ];

        $registerResult = $this->authService->register($userData);
        $userId = $registerResult['user']['id'];

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Password must contain at least 1 uppercase letter');

        $this->authService->changePassword($userId, 'OriginalPassword123!', 'lowercase1!');
    }

    public function testChangePasswordWithoutLowercase()
    {
        $userData = [
            'name' => 'No Lowercase User',
            'email' => 'nolower@example.com',
            'password' => 'OriginalPassword123!',
        ];

        $registerResult = $this->authService->register($userData);
        $userId = $registerResult['user']['id'];

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Password must contain at least 1 lowercase letter');

        $this->authService->changePassword($userId, 'OriginalPassword123!', 'UPPERCASE1!');
    }

    public function testChangePasswordWithoutNumber()
    {
        $userData = [
            'name' => 'No Number User',
            'email' => 'nonumber@example.com',
            'password' => 'OriginalPassword123!',
        ];

        $registerResult = $this->authService->register($userData);
        $userId = $registerResult['user']['id'];

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Password must contain at least 1 number');

        $this->authService->changePassword($userId, 'OriginalPassword123!', 'NoNumber!');
    }

    public function testChangePasswordWithoutSpecialCharacter()
    {
        $userData = [
            'name' => 'No Special User',
            'email' => 'nospecial@example.com',
            'password' => 'OriginalPassword123!',
        ];

        $registerResult = $this->authService->register($userData);
        $userId = $registerResult['user']['id'];

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Password must contain at least 1 special character');

        $this->authService->changePassword($userId, 'OriginalPassword123!', 'NoSpecial1');
    }

    public function testChangePasswordWithCommonPassword()
    {
        $userData = [
            'name' => 'Common Password User',
            'email' => 'commonpass@example.com',
            'password' => 'OriginalPassword123!',
        ];

        $registerResult = $this->authService->register($userData);
        $userId = $registerResult['user']['id'];

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('not use a common password');

        $this->authService->changePassword($userId, 'OriginalPassword123!', 'Password123');
    }

    public function testChangePasswordWithStrongPassword()
    {
        $userData = [
            'name' => 'Strong Change User',
            'email' => 'strongchange@example.com',
            'password' => 'OriginalPassword123!',
        ];

        $registerResult = $this->authService->register($userData);
        $userId = $registerResult['user']['id'];

        $result = $this->authService->changePassword($userId, 'OriginalPassword123!', 'NewStr0ng!');

        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('message', $result);

        $user = User::find($userId);
        $this->assertTrue(password_verify('NewStr0ng!', $user->password));
    }

    public function testChangePasswordWithIncorrectCurrentPassword()
    {
        $userData = [
            'name' => 'Incorrect Password User',
            'email' => 'incorrectpass@example.com',
            'password' => 'Password123!',
        ];

        $registerResult = $this->authService->register($userData);
        $userId = $registerResult['user']['id'];

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Current password is incorrect');

        $this->authService->changePassword($userId, 'WrongP@ss123!', 'NewP@ss123!');
    }

    public function testResetPasswordWithExpiredToken()
    {
        $userData = [
            'name' => 'Expired Token User',
            'email' => 'expired@example.com',
            'password' => 'Password123!',
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

        $this->authService->resetPassword($resetToken, 'NewP@ss123!');
    }

    public function testResetPasswordWithInvalidTokenHash()
    {
        $userData = [
            'name' => 'Invalid Token User',
            'email' => 'invalid@example.com',
            'password' => 'Password123!',
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

        $this->authService->resetPassword('wrongtoken' . str_repeat('a', 64), 'NewP@ss123!');
    }
}
