<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use Hypervel\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;

/**
 * @internal
 * @coversNothing
 */
class UserApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Test user registration endpoint.
     */
    public function testUserCanRegister(): void
    {
        $userData = [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post('/api/register', $userData);

        $response->assertStatus(201);
        $this->assertDatabaseHas('users', [
            'email' => $userData['email'],
        ]);
    }

    /**
     * Test user login endpoint.
     */
    public function testUserCanLogin(): void
    {
        $password = 'password123';
        $user = User::factory()->create([
            'password' => Hash::make($password),
        ]);

        $loginData = [
            'email' => $user->email,
            'password' => $password,
        ];

        $response = $this->post('/api/login', $loginData);

        $response->assertStatus(200);
    }

    /**
     * Test user profile endpoint requires authentication.
     */
    public function testUserProfileRequiresAuthentication(): void
    {
        $response = $this->get('/api/user');

        $response->assertStatus(401);
    }

    /**
     * Test authenticated user can access profile.
     */
    public function testAuthenticatedUserCanAccessProfile(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/api/user');

        $response->assertStatus(200);
        $response->assertJson([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ]);
    }

    /**
     * Test user update endpoint.
     */
    public function testUserCanUpdateProfile(): void
    {
        $user = User::factory()->create();
        $newName = $this->faker->name();

        $updateData = [
            'name' => $newName,
            'email' => $user->email,
        ];

        $response = $this->actingAs($user)
            ->put("/api/user/{$user->id}", $updateData);

        $response->assertStatus(200);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => $newName,
        ]);
    }

    /**
     * Test user deletion endpoint.
     */
    public function testUserCanDeleteAccount(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->delete("/api/user/{$user->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
    }
}
