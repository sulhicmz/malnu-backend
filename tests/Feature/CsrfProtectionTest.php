<?php

declare(strict_types=1);

namespace Tests\Feature;

use HyperfTest\Http\TestCase;

class CsrfProtectionTest extends TestCase
{
    public function test_csrf_token_endpoint_returns_token()
    {
        $response = $this->get('/api/auth/csrf-token');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'token',
                'expires_in'
            ]
        ]);
    }

    public function test_csrf_token_format_is_valid()
    {
        $response = $this->get('/api/auth/csrf-token');

        $data = json_decode($response->getBody()->getContents(), true);
        
        $this->assertArrayHasKey('token', $data['data']);
        $this->assertIsString($data['data']['token']);
        $this->assertEquals(64, strlen($data['data']['token']));
        $this->assertArrayHasKey('expires_in', $data['data']);
        $this->assertEquals(3600, $data['data']['expires_in']);
    }

    public function test_csrf_token_endpoint_is_public()
    {
        $response = $this->get('/api/auth/csrf-token');

        $response->assertStatus(200);
    }

    public function test_csrf_tokens_are_unique()
    {
        $response1 = $this->get('/api/auth/csrf-token');
        $response2 = $this->get('/api/auth/csrf-token');

        $data1 = json_decode($response1->getBody()->getContents(), true);
        $data2 = json_decode($response2->getBody()->getContents(), true);

        $this->assertNotEquals($data1['data']['token'], $data2['data']['token']);
    }

    public function test_csrf_endpoint_uses_rate_limit()
    {
        for ($i = 0; $i < 100; $i++) {
            $this->get('/api/auth/csrf-token');
        }

        $response = $this->get('/api/auth/csrf-token');
        $this->assertStatus(429);
    }
}
