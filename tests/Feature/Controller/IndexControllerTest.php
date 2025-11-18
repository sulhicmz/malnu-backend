<?php

declare(strict_types=1);

namespace Tests\Feature\Controller;

use Tests\TestCase;

class IndexControllerTest extends TestCase
{
    public function testIndexReturnsCorrectResponse()
    {
        $response = $this->get('/');

        $response->assertStatus(200)
                 ->assertJson([
                     'method' => 'GET',
                     'message' => 'Hello Hypervel.',
                 ]);
    }

    public function testIndexWithCustomUserParameter()
    {
        $response = $this->get('/?user=TestUser');

        $response->assertStatus(200)
                 ->assertJson([
                     'method' => 'GET',
                     'message' => 'Hello TestUser.',
                 ]);
    }

    public function testIndexWithPostMethod()
    {
        $response = $this->post('/');

        $response->assertStatus(200)
                 ->assertJson([
                     'method' => 'POST',
                     'message' => 'Hello Hypervel.',
                 ]);
    }

    public function testIndexWithDifferentMethods()
    {
        $methods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'];
        
        foreach ($methods as $method) {
            $response = $this->$method('/');
            
            $response->assertStatus(200)
                     ->assertJson([
                         'method' => $method,
                         'message' => 'Hello Hypervel.',
                     ]);
        }
    }
}