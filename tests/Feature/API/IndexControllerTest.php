<?php

declare(strict_types=1);

namespace Tests\Feature\API;

use Tests\TestCase;

class IndexControllerTest extends TestCase
{
    public function test_get_request_returns_correct_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200)
                 ->assertJson([
                     'method' => 'GET',
                     'message' => 'Hello Hypervel.',
                 ]);
    }

    public function test_post_request_returns_correct_response(): void
    {
        $response = $this->post('/');

        $response->assertStatus(200)
                 ->assertJson([
                     'method' => 'POST',
                     'message' => 'Hello Hypervel.',
                 ]);
    }

    public function test_put_request_returns_correct_response(): void
    {
        $response = $this->put('/');

        $response->assertStatus(200)
                 ->assertJson([
                     'method' => 'PUT',
                     'message' => 'Hello Hypervel.',
                 ]);
    }

    public function test_patch_request_returns_correct_response(): void
    {
        $response = $this->patch('/');

        $response->assertStatus(200)
                 ->assertJson([
                     'method' => 'PATCH',
                     'message' => 'Hello Hypervel.',
                 ]);
    }

    public function test_delete_request_returns_correct_response(): void
    {
        $response = $this->delete('/');

        $response->assertStatus(200)
                 ->assertJson([
                     'method' => 'DELETE',
                     'message' => 'Hello Hypervel.',
                 ]);
    }

    public function test_user_parameter_affects_response_message(): void
    {
        $response = $this->get('/?user=John');

        $response->assertStatus(200)
                 ->assertJson([
                     'method' => 'GET',
                     'message' => 'Hello John.',
                 ]);
    }

    public function test_multiple_parameters(): void
    {
        $response = $this->get('/?user=Jane&extra=param');

        $response->assertStatus(200)
                 ->assertJson([
                     'method' => 'GET',
                     'message' => 'Hello Jane.',
                 ]);
    }

    public function test_response_structure(): void
    {
        $response = $this->get('/');

        $response->assertJsonStructure([
            'method',
            'message'
        ]);
    }

    public function test_response_method_matches_request_method(): void
    {
        $methods = [
            ['method' => 'GET', 'url' => '/'],
            ['method' => 'POST', 'url' => '/'],
            ['method' => 'PUT', 'url' => '/'],
            ['method' => 'PATCH', 'url' => '/'],
            ['method' => 'DELETE', 'url' => '/'],
        ];

        foreach ($methods as $test) {
            $response = $this->{$test['method']}($test['url']);
            $response->assertJson([
                'method' => $test['method'],
                'message' => 'Hello Hypervel.',
            ]);
        }
    }
}