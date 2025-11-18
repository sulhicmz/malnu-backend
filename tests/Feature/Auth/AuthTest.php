<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use Tests\TestCase;
use Hypervel\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;

/**
 * @internal
 * @coversNothing
 */
class AuthTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Test that a user can register.
     */
    public function testUserCanRegister(): void
    {
        $userData = [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post('/register', $userData);

        $response->assertStatus(302); // Redirect after successful registration
        $this->assertDatabaseHas('users', [
            'email' => $userData['email'],
        ]);
    }

    /**
     * Test that a user can login with valid credentials.
     */
    public function testUserCanLoginWithValidCredentials(): void
    {
        $password = 'password123';
        $user = User::factory()->create([
            'password' => Hash::make($password),
        ]);

        $loginData = [
            'email' => $user->email,
            'password' => $password,
        ];

        $response = $this->post('/login', $loginData);

        $response->assertStatus(302); // Redirect after successful login
    }

    /**
     * Test that a user cannot login with invalid credentials.
     */
    public function testUserCannotLoginWithInvalidCredentials(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('validpassword'),
        ]);

        $loginData = [
            'email' => $user->email,
            'password' => 'invalidpassword',
        ];

        $response = $this->post('/login', $loginData);

        $response->assertStatus(302); // Should redirect back with error
        $response->assertSessionHasErrors();
    }

    /**
     * Test that a user can logout.
     */
    public function testUserCanLogout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $response->assertStatus(302); // Redirect after logout
    }

    /**
     * Test that authentication is required for protected routes.
     */
    public function testAuthenticationIsRequiredForProtectedRoutes(): void
    {
        $response = $this->get('/dashboard');

        // Should redirect to login page
        $response->assertRedirect('/login');
    }

    /**
     * Test that authenticated user can access protected routes.
     */
    public function testAuthenticatedUserCanAccessProtectedRoutes(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
    }

    /**
     * Test registration validation.
     */
    public function testRegistrationValidation(): void
    {
        $userData = [
            'name' => '', // Empty name should fail validation
            'email' => 'invalid-email', // Invalid email should fail
            'password' => '123', // Too short password should fail
            'password_confirmation' => 'different', // Different confirmation should fail
        ];

        $response = $this->post('/register', $userData);

        $response->assertStatus(302); // Redirect with validation errors
        $response->assertSessionHasErrors(['name', 'email', 'password']);
    }
}