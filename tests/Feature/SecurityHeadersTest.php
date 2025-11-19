<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
class SecurityHeadersTest extends TestCase
{
    public function testSecurityHeadersAreAppliedToResponses()
    {
        // Use a simple route that should exist in the application
        $response = $this->get('/');

        // Test Content Security Policy header
        $response->assertHeader('Content-Security-Policy');
        
        // Test Strict Transport Security header
        $response->assertHeader('Strict-Transport-Security');
        
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

    public function testSecurityHeadersHaveCorrectValues()
    {
        // Use a simple route that should exist in the application
        $response = $this->get('/');

        // Get the actual header values
        $csp = $response->headers->get('Content-Security-Policy');
        $hsts = $response->headers->get('Strict-Transport-Security');
        $xfo = $response->headers->get('X-Frame-Options');
        $xcto = $response->headers->get('X-Content-Type-Options');
        $referrerPolicy = $response->headers->get('Referrer-Policy');
        $permissionsPolicy = $response->headers->get('Permissions-Policy');
        $xxss = $response->headers->get('X-XSS-Protection');

        // Assert header values are not empty
        $this->assertNotEmpty($csp, 'Content-Security-Policy header should not be empty');
        $this->assertStringContainsString('max-age=', $hsts, 'HSTS header should contain max-age directive');
        $this->assertNotEmpty($xfo, 'X-Frame-Options header should not be empty');
        $this->assertEquals('nosniff', $xcto, 'X-Content-Type-Options should be nosniff');
        $this->assertNotEmpty($referrerPolicy, 'Referrer-Policy header should not be empty');
        $this->assertNotEmpty($permissionsPolicy, 'Permissions-Policy header should not be empty');
        $this->assertNotEmpty($xxss, 'X-XSS-Protection header should not be empty');
    }
    
    public function testSecurityHeadersAreAppliedToApiRoutes()
    {
        // Test that security headers are also applied to API routes
        $response = $this->getJson('/api');
        
        // All security headers should be present
        $response->assertHeader('Content-Security-Policy');
        $response->assertHeader('Strict-Transport-Security');
        $response->assertHeader('X-Frame-Options');
        $response->assertHeader('X-Content-Type-Options');
        $response->assertHeader('Referrer-Policy');
        $response->assertHeader('Permissions-Policy');
        $response->assertHeader('X-XSS-Protection');
    }
}