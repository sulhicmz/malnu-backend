<?php

namespace Tests\Feature;

use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    public function test_security_headers_are_present_on_web_routes()
    {
        $response = $this->get('/');

        // Test Content Security Policy header
        $response->assertHeader('Content-Security-Policy');
        
        // Test X-Frame-Options header
        $response->assertHeader('X-Frame-Options');
        
        // Test X-Content-Type-Options header
        $response->assertHeader('X-Content-Type-Options');
        
        // Test Referrer-Policy header
        $response->assertHeader('Referrer-Policy');
        
        // Test Permissions-Policy header
        $response->assertHeader('Permissions-Policy');
        
        // Test X-XSS-Protection header
        $response->assertHeader('X-XSS-Protection');
    }

    public function test_security_headers_are_present_on_api_routes()
    {
        $response = $this->getJson('/api');

        // Test that security headers are also present on API routes
        $response->assertHeader('Content-Security-Policy');
        $response->assertHeader('X-Frame-Options');
        $response->assertHeader('X-Content-Type-Options');
        $response->assertHeader('Referrer-Policy');
        $response->assertHeader('Permissions-Policy');
        $response->assertHeader('X-XSS-Protection');
    }

    public function test_hsts_header_is_present()
    {
        $response = $this->get('/');

        $response->assertHeader('Strict-Transport-Security');
    }
}