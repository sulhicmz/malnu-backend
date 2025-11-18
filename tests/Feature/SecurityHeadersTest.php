<?php

namespace Tests\Feature;

use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    public function test_security_headers_are_present_on_web_routes(): void
    {
        $response = $this->get('/');

        // Test that security headers are present
        $response->assertHeader('Content-Security-Policy');
        $response->assertHeader('Strict-Transport-Security');
        $response->assertHeader('X-Frame-Options');
        $response->assertHeader('X-Content-Type-Options');
        $response->assertHeader('Referrer-Policy');
        $response->assertHeader('Permissions-Policy');
        $response->assertHeader('X-XSS-Protection');
    }

    public function test_security_headers_are_present_on_api_routes(): void
    {
        $response = $this->getJson('/'); // Using the API root endpoint

        // Test that security headers are present on API responses too
        $response->assertHeader('Content-Security-Policy');
        $response->assertHeader('Strict-Transport-Security');
        $response->assertHeader('X-Frame-Options');
        $response->assertHeader('X-Content-Type-Options');
        $response->assertHeader('Referrer-Policy');
        $response->assertHeader('Permissions-Policy');
        $response->assertHeader('X-XSS-Protection');
    }

    public function test_csp_header_content(): void
    {
        $response = $this->get('/');

        $csp = $response->headers->get('Content-Security-Policy');
        $this->assertStringContainsString('default-src', $csp);
        $this->assertStringContainsString("'self'", $csp);
    }

    public function test_hsts_header_content(): void
    {
        $response = $this->get('/');

        $hsts = $response->headers->get('Strict-Transport-Security');
        $this->assertStringContainsString('max-age=', $hsts);
        $this->assertStringContainsString('includeSubDomains', $hsts);
    }

    public function test_x_frame_options_header_content(): void
    {
        $response = $this->get('/');

        $xFrameOptions = $response->headers->get('X-Frame-Options');
        $this->assertEquals('DENY', $xFrameOptions);
    }

    public function test_x_content_type_options_header_content(): void
    {
        $response = $this->get('/');

        $xContentTypeOptions = $response->headers->get('X-Content-Type-Options');
        $this->assertEquals('nosniff', $xContentTypeOptions);
    }
}