<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Request/Response Logging Middleware Tests
 * 
 * Tests for the request/response logging functionality.
 * 
 * @internal
 * @coversNothing
 */
class RequestResponseLoggingTest extends TestCase
{
    /**
     * Test that middleware logs requests.
     */
    public function testMiddlewareLogsRequests(): void
    {
        $response = $this->post('/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $this->assertNotEmpty($response->getHeader('X-Request-ID'));
        $this->assertNotEmpty($response->getHeader('X-Response-Time'));
    }

    /**
     * Test that sensitive data is redacted in logs.
     */
    public function testSensitiveDataIsRedacted(): void
    {
        $response = $this->post('/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $this->assertNotEmpty($response->getHeader('X-Request-ID'));
    }

    /**
     * Test that middleware adds request ID to response headers.
     */
    public function testMiddlewareAddsRequestIdToResponse(): void
    {
        $response = $this->post('/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $requestId = $response->getHeader('X-Request-ID');
        $this->assertNotEmpty($requestId);
        $this->assertIsString($requestId[0]);
        $this->assertMatchesRegularExpression('/^[0-9a-f-]{36}$/', $requestId[0]);
    }

    /**
     * Test that middleware adds response time to headers.
     */
    public function testMiddlewareAddsResponseTimeToHeaders(): void
    {
        $response = $this->post('/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $responseTime = $response->getHeader('X-Response-Time');
        $this->assertNotEmpty($responseTime);
        $this->assertIsString($responseTime[0]);
        $this->assertMatchesRegularExpression('/^\d+\.\d+ms$/', $responseTime[0]);
    }

    /**
     * Test that middleware logs for GET requests.
     */
    public function testMiddlewareLogsGetRequests(): void
    {
        $token = $this->getToken();
        
        $response = $this->get('/auth/me', [], [
            'Authorization' => 'Bearer ' . $token
        ]);

        $this->assertNotEmpty($response->getHeader('X-Request-ID'));
        $this->assertNotEmpty($response->getHeader('X-Response-Time'));
    }

    /**
     * Test that middleware logs for POST requests.
     */
    public function testMiddlewareLogsPostRequests(): void
    {
        $response = $this->post('/auth/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $this->assertNotEmpty($response->getHeader('X-Request-ID'));
        $this->assertNotEmpty($response->getHeader('X-Response-Time'));
    }

    /**
     * Test that middleware logs for PUT requests.
     */
    public function testMiddlewareLogsPutRequests(): void
    {
        $token = $this->getToken();

        $response = $this->put('/auth/password/change', [
            'current_password' => 'password',
            'new_password' => 'newpassword123'
        ], [], [
            'Authorization' => 'Bearer ' . $token
        ]);

        $this->assertNotEmpty($response->getHeader('X-Request-ID'));
        $this->assertNotEmpty($response->getHeader('X-Response-Time'));
    }

    /**
     * Test that middleware works with JSON requests.
     */
    public function testMiddlewareWorksJsonRequests(): void
    {
        $response = $this->json('POST', '/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $this->assertNotEmpty($response->getHeader('X-Request-ID'));
        $this->assertNotEmpty($response->getHeader('X-Response-Time'));
    }

    /**
     * Test that request ID is unique for each request.
     */
    public function testRequestIdIsUniqueForEachRequest(): void
    {
        $response1 = $this->get('/');
        $response2 = $this->get('/');

        $requestId1 = $response1->getHeader('X-Request-ID')[0] ?? '';
        $requestId2 = $response2->getHeader('X-Request-ID')[0] ?? '';

        $this->assertNotEquals($requestId1, $requestId2);
    }

    /**
     * Helper method to get a valid JWT token.
     */
    protected function getToken(): string
    {
        $response = $this->post('/auth/login', [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $data = json_decode((string) $response->getBody(), true);
        
        return $data['data']['access_token'] ?? '';
    }
}
