<?php

namespace Tests\Feature\API;

use Tests\TestCase;
use App\Models\User;
use Hypervel\Foundation\Testing\RefreshDatabase;

class UserApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_api_endpoint_returns_json(): void
    {
        $response = $this->getJson('/api/users');
        
        // Expect either 200 or 404 depending on if the route exists
        $response->assertStatus(200)
                 ->assertHeader('Content-Type', 'application/json');
    }

    public function test_user_can_be_retrieved_via_api(): void
    {
        $user = User::factory()->create();

        $response = $this->getJson("/api/users/{$user->id}");

        $response->assertStatus(200);
    }

    public function test_user_can_be_created_via_api(): void
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/users', $userData);

        $response->assertStatus(201);
        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }

    public function test_user_can_be_updated_via_api(): void
    {
        $user = User::factory()->create();
        $updatedData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ];

        $response = $this->putJson("/api/users/{$user->id}", $updatedData);

        $response->assertStatus(200);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);
    }

    public function test_user_can_be_deleted_via_api(): void
    {
        $user = User::factory()->create();

        $response = $this->deleteJson("/api/users/{$user->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }
}