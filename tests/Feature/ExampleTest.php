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
        $this->get('/')
            ->assertSuccessful();
    }

    public function testSecurityHeadersArePresent()
    {
        $response = $this->get('/');

        // Check that security headers are present
        $response->assertHeader('Content-Security-Policy');
        $response->assertHeader('X-Frame-Options');
        $response->assertHeader('X-Content-Type-Options');
        $response->assertHeader('Referrer-Policy');
        $response->assertHeader('Permissions-Policy');
        $response->assertHeader('Strict-Transport-Security');
        $response->assertHeader('X-XSS-Protection');

        // Verify specific header values
        $cspHeader = $response->headers->get('Content-Security-Policy');
        $this->assertStringContainsString('default-src \'self\'', $cspHeader);
        $this->assertStringContainsString('script-src \'self\' \'unsafe-inline\' \'unsafe-eval\'', $cspHeader);

        $xFrameOptions = $response->headers->get('X-Frame-Options');
        $this->assertEquals('DENY', $xFrameOptions);

        $xContentTypeOptions = $response->headers->get('X-Content-Type-Options');
        $this->assertEquals('nosniff', $xContentTypeOptions);

        $hstsHeader = $response->headers->get('Strict-Transport-Security');
        $this->assertEquals('max-age=31536000; includeSubDomains; preload', $hstsHeader);
    }

    public function testCspReportEndpoint()
    {
        // Test that the CSP report endpoint exists and returns 204
        $response = $this->post('/csp-report', [
            'csp-report' => [
                'document-uri' => 'https://example.com/page',
                'violated-directive' => 'script-src',
                'blocked-uri' => 'inline',
                'line-number' => 10,
                'source-file' => 'script.js',
            ],
        ], [
            'Content-Type' => 'application/csp-report',
        ]);

        $response->assertStatus(204);
    }
}
