<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\AuthService;
use App\Models\User;
use Hyperf\DbConnection\Db;

class AuthServiceTest extends TestCase
{
    protected AuthService $authService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authService = new AuthService();
        Db::table('users')->truncate();
    }

    protected function tearDown(): void
    {
        Db::table('users')->truncate();
        parent::tearDown();
    }

    public function test_register_creates_user_in_database()
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
        ];

        $result = $this->authService->register($userData);

        $this->assertArrayHasKey('user', $result);
        $this->assertEquals('John Doe', $result['user']['name']);
        $this->assertEquals('john@example.com', $result['user']['email']);

        $userInDb = User::where('email', 'john@example.com')->first();
        $this->assertNotNull($userInDb);
        $this->assertEquals('John Doe', $userInDb->name);
    }

    public function test_register_throws_exception_for_duplicate_email()
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
        ];

        $this->authService->register($userData);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('User with this email already exists');

        $this->authService->register($userData);
    }

    public function test_login_with_valid_credentials()
    {
        $userData = [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'password123',
        ];

        $this->authService->register($userData);

        $result = $this->authService->login('jane@example.com', 'password123');

        $this->assertArrayHasKey('user', $result);
        $this->assertArrayHasKey('token', $result);
        $this->assertEquals('Jane Doe', $result['user']['name']);
        $this->assertArrayHasKey('access_token', $result['token']);
    }

    public function test_login_throws_exception_for_invalid_credentials()
    {
        $userData = [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'password123',
        ];

        $this->authService->register($userData);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid credentials');

        $this->authService->login('jane@example.com', 'wrongpassword');
    }

    public function test_login_throws_exception_for_nonexistent_user()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid credentials');

        $this->authService->login('nonexistent@example.com', 'password123');
    }

    public function test_getUserFromToken_returns_user_for_valid_token()
    {
        $userData = [
            'name' => 'Alice',
            'email' => 'alice@example.com',
            'password' => 'password123',
        ];

        $registerResult = $this->authService->register($userData);
        $userId = $registerResult['user']['id'];

        $loginResult = $this->authService->login('alice@example.com', 'password123');
        $token = $loginResult['token']['access_token'];

        $user = $this->authService->getUserFromToken($token);

        $this->assertNotNull($user);
        $this->assertEquals($userId, $user['id']);
        $this->assertEquals('Alice', $user['name']);
    }

    public function test_getUserFromToken_returns_null_for_blacklisted_token()
    {
        $userData = [
            'name' => 'Bob',
            'email' => 'bob@example.com',
            'password' => 'password123',
        ];

        $this->authService->register($userData);

        $loginResult = $this->authService->login('bob@example.com', 'password123');
        $token = $loginResult['token']['access_token'];

        $this->authService->logout($token);

        $user = $this->authService->getUserFromToken($token);

        $this->assertNull($user);
    }

    public function test_refresh_token_generates_new_token()
    {
        $userData = [
            'name' => 'Charlie',
            'email' => 'charlie@example.com',
            'password' => 'password123',
        ];

        $this->authService->register($userData);

        $loginResult = $this->authService->login('charlie@example.com', 'password123');
        $originalToken = $loginResult['token']['access_token'];

        $refreshResult = $this->authService->refreshToken($originalToken);

        $this->assertArrayHasKey('token', $refreshResult);
        $this->assertArrayHasKey('access_token', $refreshResult['token']);
        $this->assertNotEquals($originalToken, $refreshResult['token']['access_token']);
    }
}
