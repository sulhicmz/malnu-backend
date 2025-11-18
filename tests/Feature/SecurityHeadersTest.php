<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    public function test_security_headers_are_present_on_web_routes(): void
    {
        $response = $this->get('/');

        // Test Content Security Policy header
        $response->assertHeader('Content-Security-Policy');
        
        // Test X-Frame-Options header
        $response->assertHeader('X-Frame-Options');
        $this->assertEquals('DENY', $response->headers->get('X-Frame-Options'));
        
        // Test X-Content-Type-Options header
        $response->assertHeader('X-Content-Type-Options');
        $this->assertEquals('nosniff', $response->headers->get('X-Content-Type-Options'));
        
        // Test Referrer-Policy header
        $response->assertHeader('Referrer-Policy');
        
        // Test Strict-Transport-Security header
        $response->assertHeader('Strict-Transport-Security');
        
        // Test Permissions-Policy header
        $response->assertHeader('Permissions-Policy');
        
        // Test X-XSS-Protection header
        $response->assertHeader('X-XSS-Protection');
        $this->assertEquals('1; mode=block', $response->headers->get('X-XSS-Protection'));
    }

    public function test_security_headers_are_present_on_api_routes(): void
    {
        $response = $this->getJson('/api/health-check');

        // Test that security headers are also applied to API routes
        $response->assertHeader('Content-Security-Policy');
        $response->assertHeader('X-Frame-Options');
        $response->assertHeader('X-Content-Type-Options');
        $response->assertHeader('Referrer-Policy');
        $response->assertHeader('Strict-Transport-Security');
        $response->assertHeader('Permissions-Policy');
        $response->assertHeader('X-XSS-Protection');
    }

    public function test_csp_header_format(): void
    {
        $response = $this->get('/');

        $csp = $response->headers->get('Content-Security-Policy');
        $this->assertStringContainsString('default-src', $csp);
        $this->assertStringContainsString('script-src', $csp);
        $this->assertStringContainsString('style-src', $csp);
        $this->assertStringEndsWith(';', $csp);
    }

    public function test_hsts_header_format(): void
    {
        $response = $this->get('/');

        $hsts = $response->headers->get('Strict-Transport-Security');
        $this->assertStringContainsString('max-age=', $hsts);
        $this->assertStringContainsString('includeSubDomains', $hsts);
        $this->assertStringContainsString('preload', $hsts);
    }
}