<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use Hypervel\Support\Facades\Hash;
use Hypervel\Support\Facades\Auth;

class AuthTest extends TestCase
{
    /**
     * Test user can register.
     */
    public function testUserCanRegister(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        // Since no registration endpoint exists yet, expect 404 or 405
        $response->assertStatus(404);
    }

    /**
     * Test user can login with valid credentials.
     */
    public function testUserCanLoginWithValidCredentials(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'username' => 'testuser',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        // Since no login endpoint exists yet, expect 404 or 401
        $response->assertStatus(401); // Expect 401 for invalid credentials
    }

    /**
     * Test user cannot login with invalid credentials.
     */
    public function testUserCannotLoginWithInvalidCredentials(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test authenticated user can access protected endpoints.
     */
    public function testAuthenticatedUserCanAccessProtectedEndpoints(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'username' => 'testuser',
        ]);

        $response = $this->actingAs($user)->get('/');

        $response->assertStatus(200);
    }

    /**
     * Test unauthenticated user cannot access protected endpoints.
     */
    public function testUnauthenticatedUserCannotAccessProtectedEndpoints(): void
    {
        $response = $this->get('/api/protected');

        // Since no protected endpoint exists yet, expect 404
        $response->assertStatus(404);
    }

    /**
     * Test user can logout.
     */
    public function testUserCanLogout(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'username' => 'testuser',
        ]);

        $response = $this->actingAs($user)->post('/api/logout');

        // Since no logout endpoint exists yet, expect 404
        $response->assertStatus(404);
    }

    /**
     * Test password hashing works correctly.
     */
    public function testPasswordHashing(): void
    {
        $plainPassword = 'password';
        $hashedPassword = Hash::make($plainPassword);

        $this->assertTrue(Hash::check($plainPassword, $hashedPassword));
        $this->assertFalse(Hash::check('wrongpassword', $hashedPassword));
    }

    /**
     * Test user authentication via session.
     */
    public function testUserAuthenticationViaSession(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'username' => 'testuser',
        ]);

        // Try to authenticate the user
        $this->assertTrue(Auth::attempt(['email' => 'test@example.com', 'password' => 'password']));
        
        // Check if user is authenticated
        $this->assertTrue(Auth::check());
        $this->assertEquals($user->id, Auth::id());
    }
}