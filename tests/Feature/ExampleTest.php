<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
class ExampleTest extends TestCase
{
    public function testApiRootReturnsSuccessfulResponse()
    {
        $response = $this->get('/api/');
        
        $response->assertStatus(200)
                 ->assertJson([
                     'method' => 'GET',
                     'message' => 'Hello Hypervel.',
                 ]);
    }

    public function testApiRootWithUserParameter()
    {
        $response = $this->get('/api/?user=TestUser');
        
        $response->assertStatus(200)
                 ->assertJson([
                     'method' => 'GET',
                     'message' => 'Hello TestUser.',
                 ]);
    }
}
