<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Hypervel\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    public function testApiRootEndpointExists()
    {
        $response = $this->get('/api/');

        $response->assertStatus(200);
    }

    public function testApiHealthCheck()
    {
        $response = $this->get('/api/');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'method',
                     'message'
                 ]);
    }

    public function testApiAcceptsJsonRequests()
    {
        $response = $this->withHeaders([
            'Accept' => 'application/json',
        ])->get('/api/');
        
        $response->assertStatus(200)
                 ->assertHeader('Content-Type', 'application/json');
    }

    public function testApiReturnsCorrectHeaders()
    {
        $response = $this->get('/api/');

        $response->assertStatus(200);
        $this->assertNotNull($response->headers->get('content-type'));
    }

    public function testApiHealthCheck()
    {
        $response = $this->get('/');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'method',
                     'message'
                 ]);
    }

    public function testApiRequiresAuthenticationForProtectedEndpoints()
    {
        // Test that certain endpoints require authentication
        $response = $this->get('/api/user');
        
        // Should return 401 Unauthorized if authentication is required
        $response->assertStatus(401);
    }

    public function testApiAcceptsJsonRequests()
    {
        $response = $this->withHeaders([
            'Accept' => 'application/json',
        ])->get('/');

        $response->assertStatus(200)
                 ->assertHeader('Content-Type', 'application/json');
    }

    public function testApiReturnsCorrectHeaders()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $this->assertNotNull($response->headers->get('content-type'));
    }
}