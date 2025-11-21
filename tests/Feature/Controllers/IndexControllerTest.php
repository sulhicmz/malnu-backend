<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers;

use Tests\TestCase;

class IndexControllerTest extends TestCase
{
    public function test_index_controller_returns_correct_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200)
                 ->assertJson([
                     'method' => 'GET',
                     'message' => 'Hello Hypervel.',
                 ]);
    }

    public function test_index_controller_handles_different_methods(): void
    {
        $methods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS', 'HEAD'];

        foreach ($methods as $method) {
            $response = $this->$method('/');
            $response->assertStatus(200);
            
            $expectedResponse = [
                'method' => $method,
                'message' => 'Hello Hypervel.',
            ];
            
            if ($method === 'HEAD') {
                // HEAD requests don't have response body
                continue;
            }
            
            $response->assertJson($expectedResponse);
        }
    }

    public function test_index_controller_accepts_user_parameter(): void
    {
        $response = $this->get('/?user=TestUser');

        $response->assertStatus(200)
                 ->assertJson([
                     'method' => 'GET',
                     'message' => 'Hello TestUser.',
                 ]);
    }

    public function test_index_controller_handles_multiple_parameters(): void
    {
        $response = $this->get('/?user=Jane&param1=value1&param2=value2');

        $response->assertStatus(200)
                 ->assertJson([
                     'method' => 'GET',
                     'message' => 'Hello Jane.',
                 ]);
    }

    public function test_index_controller_response_structure(): void
    {
        $response = $this->get('/');

        $response->assertJsonStructure([
            'method',
            'message'
        ]);
    }

    public function test_index_controller_works_with_refresh_database(): void
    {
        // This test ensures that the controller works in the context of database refreshes
        $this->assertDatabaseCount('migrations', 0); // Since we're using RefreshDatabase trait
        
        $response = $this->get('/');

        $response->assertStatus(200)
                 ->assertJson([
                     'method' => 'GET',
                     'message' => 'Hello Hypervel.',
                 ]);
    }
}