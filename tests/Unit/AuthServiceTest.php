<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\AuthService;
use App\Services\JWTService;
use App\Services\TokenBlacklistService;
use App\Models\User;

class AuthServiceTest extends TestCase
{
    private AuthService $authService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authService = new AuthService();
    }

    public function test_register_creates_user_in_database()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
        ];

        $result = $this->authService->register($userData);

        $this->assertArrayHasKey('user', $result);
        $this->assertArrayHasKey('id', $result['user']);
        $this->assertEquals('Test User', $result['user']['name']);
        $this->assertEquals('test@example.com', $result['user']['email']);

        // Verify user is in database
        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('Test User', $user->name);
    }

    public function test_register_throws_exception_for_duplicate_email()
    {
        // Create a user first
        User::create([
            'name' => 'Existing User',
            'email' => 'existing@example.com',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
            'username' => 'existing',
            'full_name' => 'Existing User',
            'is_active' => true,
        ]);

        $userData = [
            'name' => 'New User',
            'email' => 'existing@example.com',
            'password' => 'password123',
        ];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('User with this email already exists');

        $this->authService->register($userData);
    }

    public function test_login_with_valid_credentials_succeeds()
    {
        // Create a user
        $password = password_hash('correct_password', PASSWORD_DEFAULT);
        User::create([
            'name' => 'Test User',
            'email' => 'login@example.com',
            'password' => $password,
            'username' => 'login_user',
            'full_name' => 'Test User',
            'is_active' => true,
        ]);

        $result = $this->authService->login('login@example.com', 'correct_password');

        $this->assertArrayHasKey('user', $result);
        $this->assertArrayHasKey('token', $result);
        $this->assertArrayHasKey('access_token', $result['token']);
        $this->assertEquals('login@example.com', $result['user']['email']);
    }

    public function test_login_with_invalid_credentials_fails()
    {
        User::create([
            'name' => 'Test User',
            'email' => 'invalid@example.com',
            'password' => password_hash('correct_password', PASSWORD_DEFAULT),
            'username' => 'invalid_user',
            'full_name' => 'Test User',
            'is_active' => true,
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid credentials');

        $this->authService->login('invalid@example.com', 'wrong_password');
    }

    public function test_get_user_from_token_returns_user()
    {
        $user = User::create([
            'name' => 'Token Test User',
            'email' => 'token@example.com',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
            'username' => 'token_user',
            'full_name' => 'Token Test User',
            'is_active' => true,
        ]);

        $jwtService = new JWTService();
        $token = $jwtService->generateToken([
            'id' => $user->id,
            'email' => $user->email
        ]);

        $retrievedUser = $this->authService->getUserFromToken($token);

        $this->assertNotNull($retrievedUser);
        $this->assertEquals($user->id, $retrievedUser['id']);
        $this->assertEquals('token@example.com', $retrievedUser['email']);
    }

    public function test_get_user_from_blacklisted_token_returns_null()
    {
        $user = User::create([
            'name' => 'Blacklist User',
            'email' => 'blacklist@example.com',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
            'username' => 'blacklist_user',
            'full_name' => 'Blacklist User',
            'is_active' => true,
        ]);

        $jwtService = new JWTService();
        $token = $jwtService->generateToken([
            'id' => $user->id,
            'email' => $user->email
        ]);

        // Blacklist the token
        $blacklistService = new TokenBlacklistService();
        $blacklistService->blacklistToken($token);

        $retrievedUser = $this->authService->getUserFromToken($token);

        $this->assertNull($retrievedUser);
    }

    public function test_change_password_with_valid_current_password()
    {
        $user = User::create([
            'name' => 'Password User',
            'email' => 'password@example.com',
            'password' => password_hash('current_password', PASSWORD_DEFAULT),
            'username' => 'password_user',
            'full_name' => 'Password User',
            'is_active' => true,
        ]);

        $result = $this->authService->changePassword(
            $user->id,
            'current_password',
            'new_password123'
        );

        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success']);

        // Verify password was changed
        $updatedUser = User::find($user->id);
        $this->assertTrue(password_verify('new_password123', $updatedUser->password));
        $this->assertFalse(password_verify('current_password', $updatedUser->password));
    }

    public function test_change_password_with_invalid_current_password_fails()
    {
        $user = User::create([
            'name' => 'Password Fail User',
            'email' => 'password_fail@example.com',
            'password' => password_hash('current_password', PASSWORD_DEFAULT),
            'username' => 'password_fail_user',
            'full_name' => 'Password Fail User',
            'is_active' => true,
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Current password is incorrect');

        $this->authService->changePassword(
            $user->id,
            'wrong_password',
            'new_password123'
        );
    }

    public function test_change_password_with_weak_password_fails()
    {
        $user = User::create([
            'name' => 'Weak Password User',
            'email' => 'weak@example.com',
            'password' => password_hash('current_password', PASSWORD_DEFAULT),
            'username' => 'weak_user',
            'full_name' => 'Weak Password User',
            'is_active' => true,
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('New password must be at least 8 characters');

        $this->authService->changePassword(
            $user->id,
            'current_password',
            'weak'
        );
    }
}
