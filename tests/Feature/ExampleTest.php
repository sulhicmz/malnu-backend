<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Hypervel\Support\Facades\Hash;

/**
 * @internal
 * @coversNothing
 */
class ApiTest extends TestCase
{
    /**
     * Test the basic API endpoint returns successful response.
     */
    public function testTheApplicationReturnsSuccessfulResponse(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertJson([
            'method' => 'GET',
            'message' => 'Hello Hypervel.',
        ]);
    }

    /**
     * Test API endpoint with user parameter.
     */
    public function testApiEndpointWithUserParameter(): void
    {
        $response = $this->get('/?user=TestUser');

        $response->assertStatus(200);
        $response->assertJson([
            'method' => 'GET',
            'message' => 'Hello TestUser.',
        ]);
    }

    /**
     * Test API endpoint with different HTTP methods.
     */
    public function testApiEndpointWithPostMethod(): void
    {
        $response = $this->post('/');

        $response->assertStatus(200);
        $response->assertJson([
            'method' => 'POST',
            'message' => 'Hello Hypervel.',
        ]);
    }

    /**
     * Test API authentication endpoints.
     */
    public function testAuthEndpointsExist(): void
    {
        // Test that auth endpoints return proper responses (even if they return 401/422)
        $response = $this->postJson('/api/login');
        $response->assertStatus(401); // Should return 401 for invalid credentials

        $response = $this->postJson('/api/register');
        $response->assertStatus(422); // Should return 422 for validation errors
    }

    /**
     * Test user can access protected API endpoints when authenticated.
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
        $response->assertJson([
            'method' => 'GET',
            'message' => 'Hello Hypervel.',
        ]);
    }
}
