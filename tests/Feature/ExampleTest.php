<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use Hypervel\Foundation\Testing\RefreshDatabase;

/**
 * @internal
 * @coversNothing
 */
class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function testTheApplicationReturnsSuccessfulResponse()
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertJson([
            'method' => 'GET',
            'message' => 'Hello Hypervel.',
        ]);
    }
    
    public function testTheApplicationReturnsCustomUserResponse()
    {
        $response = $this->get('/?user=TestUser');
        $response->assertStatus(200);
        $response->assertJson([
            'method' => 'GET',
            'message' => 'Hello TestUser.',
        ]);
    }
}
