<?php

declare(strict_types=1);

namespace Tests\Feature\API;

use Tests\TestCase;
use Hypervel\Foundation\Testing\RefreshDatabase;

class IndexControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_returns_hello_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertJson([
            'method' => 'GET',
            'message' => 'Hello Hypervel.',
        ]);
    }

    public function test_api_returns_custom_user_response(): void
    {
        $response = $this->get('/?user=TestUser');

        $response->assertStatus(200);
        $response->assertJson([
            'method' => 'GET',
            'message' => 'Hello TestUser.',
        ]);
    }

    public function test_post_request_returns_correct_method(): void
    {
        $response = $this->post('/');

        $response->assertStatus(200);
        $response->assertJson([
            'method' => 'POST',
            'message' => 'Hello Hypervel.',
        ]);
    }

    public function test_put_request_returns_correct_method(): void
    {
        $response = $this->put('/');

        $response->assertStatus(200);
        $response->assertJson([
            'method' => 'PUT',
            'message' => 'Hello Hypervel.',
        ]);
    }

    public function test_delete_request_returns_correct_method(): void
    {
        $response = $this->delete('/');

        $response->assertStatus(200);
        $response->assertJson([
            'method' => 'DELETE',
            'message' => 'Hello Hypervel.',
        ]);
    }
}