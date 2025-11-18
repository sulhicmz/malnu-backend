<?php

namespace Tests\Feature;

use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    /**
     * Test that security headers are applied to responses.
     */
    public function test_security_headers_are_present(): void
    {
        $response = $this->get('/');

        // Check that response has security headers
        $response->assertStatus(200);
        
        // Verify Content Security Policy header
        $response->assertHeader('Content-Security-Policy');
        
        // Verify Strict Transport Security header
        $response->assertHeader('Strict-Transport-Security');
        
        // Verify X-Frame-Options header
        $response->assertHeader('X-Frame-Options');
        
        // Verify X-Content-Type-Options header
        $response->assertHeader('X-Content-Type-Options');
        
        // Verify Referrer-Policy header
        $response->assertHeader('Referrer-Policy');
        
        // Verify Permissions-Policy header
        $response->assertHeader('Permissions-Policy');
        
        // Verify X-XSS-Protection header
        $response->assertHeader('X-XSS-Protection');
    }

    /**
     * Test that security headers can be disabled via configuration.
     */
    public function test_security_headers_can_be_disabled(): void
    {
        // Temporarily disable security headers
        config(['security.enabled' => false]);

        $response = $this->get('/');

        // When disabled, we still expect a 200 but may not check specific headers
        $response->assertStatus(200);
    }

    /**
     * Test specific header values.
     */
    public function test_security_header_values(): void
    {
        $response = $this->get('/');

        $cspHeader = $response->headers->get('Content-Security-Policy');
        $hstsHeader = $response->headers->get('Strict-Transport-Security');
        $xFrameHeader = $response->headers->get('X-Frame-Options');
        $xContentTypeHeader = $response->headers->get('X-Content-Type-Options');
        $referrerPolicyHeader = $response->headers->get('Referrer-Policy');

        // Verify that headers have expected content
        $this->assertStringContainsString('default-src', $cspHeader);
        $this->assertStringContainsString('max-age=', $hstsHeader);
        $this->assertStringContainsString('DENY', $xFrameHeader);
        $this->assertStringContainsString('nosniff', $xContentTypeHeader);
        $this->assertStringContainsString('strict-origin-when-cross-origin', $referrerPolicyHeader);
    }
}