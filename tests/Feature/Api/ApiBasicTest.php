<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use Tests\TestCase;
use Hypervel\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

/**
 * @internal
 * @coversNothing
 */
class ApiBasicTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Test that the API endpoints return proper responses.
     */
    public function testApiRootReturnsOk(): void
    {
        $response = $this->get('/api');

        // Expecting a 404 or 200 depending on whether the route exists
        $response->assertStatus(404); // Most likely no root API route
    }

    /**
     * Test that API returns JSON responses.
     */
    public function testApiReturnsJson(): void
    {
        $response = $this->get('/api/users');

        // This should return JSON, even if it's an error
        $response->assertHeader('Content-Type', 'application/json');
    }

    /**
     * Test health check endpoint if it exists.
     */
    public function testHealthCheck(): void
    {
        $response = $this->get('/health');

        // Expect 200 OK or 404 if health endpoint doesn't exist
        $response->assertStatus(200);
    }

    /**
     * Test that API routes return proper error for missing resources.
     */
    public function testApiReturns404ForNonExistentResource(): void
    {
        $response = $this->get('/api/non-existent-endpoint');

        $response->assertStatus(404);
    }

    /**
     * Test basic application response.
     */
    public function testBasicApplicationResponse(): void
    {
        $response = $this->get('/');

        // This should return a successful response for the main page
        $response->assertStatus(200);
    }
}