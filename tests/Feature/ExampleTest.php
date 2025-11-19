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
    public function testTheApplicationReturnsSuccessfulResponse()
    {
        $response = $this->get('/');
        
        $response->assertStatus(200);
    }
    
    /**
     * Test API routes return proper responses.
     */
    public function test_api_routes()
    {
        $response = $this->get('/api/health');
        
        // Expect either 200 or 404 depending on if the route exists
        $response->assertStatus(200);
    }
    
    /**
     * Test basic JSON response structure.
     */
    public function test_json_response_structure()
    {
        $response = $this->getJson('/api/health');
        
        $response->assertHeader('Content-Type', 'application/json');
    }
}
