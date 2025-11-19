<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;

/**
 * @internal
 * @coversNothing
 */
class ExampleTest extends TestCase
{
    public function testTheApplicationReturnsSuccessfulResponse()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertJson([
            'method' => 'GET',
            'message' => 'Hello Hypervel.',
        ]);
    }

    public function testTheApplicationReturnsSuccessfulResponseWithUserParameter()
    {
        $response = $this->get('/?user=TestUser');

        $response->assertStatus(200);
        $response->assertJson([
            'method' => 'GET',
            'message' => 'Hello TestUser.',
        ]);
    }

    public function testDifferentHttpMethods()
    {
        $methods = ['POST', 'PUT', 'PATCH', 'DELETE'];
        
        foreach ($methods as $method) {
            $response = $this->{$method}('/');
            
            $response->assertStatus(200);
            $response->assertJson([
                'method' => $method,
                'message' => 'Hello Hypervel.',
            ]);
        }
    }

    public function testApplicationReturnsCorrectMethodInResponse()
    {
        $response = $this->post('/');

        $response->assertStatus(200);
        $response->assertJson([
            'method' => 'POST',
            'message' => 'Hello Hypervel.',
        ]);
    }
}
