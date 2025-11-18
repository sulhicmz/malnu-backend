<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use Hypervel\Support\Facades\Hash;

class IndexControllerTest extends TestCase
{
    /**
     * Test the index endpoint returns correct response for GET request.
     */
    public function testIndexEndpointReturnsCorrectResponseForGet(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertJson([
            'method' => 'GET',
            'message' => 'Hello Hypervel.',
        ]);
    }

    /**
     * Test the index endpoint returns correct response for POST request.
     */
    public function testIndexEndpointReturnsCorrectResponseForPost(): void
    {
        $response = $this->post('/');

        $response->assertStatus(200);
        $response->assertJson([
            'method' => 'POST',
            'message' => 'Hello Hypervel.',
        ]);
    }

    /**
     * Test the index endpoint with user parameter.
     */
    public function testIndexEndpointWithUserParameter(): void
    {
        $response = $this->get('/?user=JohnDoe');

        $response->assertStatus(200);
        $response->assertJson([
            'method' => 'GET',
            'message' => 'Hello JohnDoe.',
        ]);
    }

    /**
     * Test the index endpoint with different HTTP methods.
     */
    public function testIndexEndpointWithDifferentMethods(): void
    {
        $methods = ['PUT', 'PATCH', 'DELETE'];

        foreach ($methods as $method) {
            $response = $this->$method('/');
            $response->assertStatus(200);
            $response->assertJson([
                'method' => $method,
                'message' => 'Hello Hypervel.',
            ]);
        }
    }

    /**
     * Test the index endpoint with authenticated user.
     */
    public function testIndexEndpointWithAuthenticatedUser(): void
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

    /**
     * Test the index endpoint with multiple query parameters.
     */
    public function testIndexEndpointWithMultipleParameters(): void
    {
        $response = $this->get('/?user=Jane&other=param');

        $response->assertStatus(200);
        $response->assertJson([
            'method' => 'GET',
            'message' => 'Hello Jane.',
        ]);
    }
}