<?php

namespace App\Http\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class SecurityHeaders implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        
        // Only add security headers if they're enabled
        $this->addSecurityHeaders($response);
        
        return $response;
    }
    
    private function addSecurityHeaders($response)
    {
        // Content Security Policy
        if (config('security.csp.enabled', true)) {
            $csp = $this->buildCspHeader();
            
            $headerName = config('security.csp.report_only') ? 'Content-Security-Policy-Report-Only' : 'Content-Security-Policy';
            $response = $response->withHeader($headerName, $csp);
        }
        
        // HTTP Strict Transport Security
        if (config('security.hsts.enabled', true)) {
            $hstsDirectives = [
                'max-age=' . config('security.hsts.max_age', 31536000)
            ];
            
            if (config('security.hsts.include_sub_domains', true)) {
                $hstsDirectives[] = 'includeSubDomains';
            }
            
            if (config('security.hsts.preload', true)) {
                $hstsDirectives[] = 'preload';
            }
            
            $response = $response->withHeader('Strict-Transport-Security', implode('; ', $hstsDirectives));
        }
        
        // X-Frame-Options to prevent clickjacking
        $response = $response->withHeader('X-Frame-Options', config('security.x_frame_options', 'DENY'));
        
        // X-Content-Type-Options to prevent MIME type sniffing
        $response = $response->withHeader('X-Content-Type-Options', config('security.x_content_type_options', 'nosniff'));
        
        // Referrer-Policy to control referrer information
        $response = $response->withHeader('Referrer-Policy', config('security.referrer_policy', 'strict-origin-when-cross-origin'));
        
        // Permissions-Policy to control browser features
        $response = $response->withHeader('Permissions-Policy', config('security.permissions_policy', 'geolocation=(), microphone=(), camera=()'));
        
        // X-XSS-Protection (for older browsers that don't support CSP)
        $response = $response->withHeader('X-XSS-Protection', config('security.x_xss_protection', '1; mode=block'));
        
        return $response;
    }
    
    private function buildCspHeader()
    {
        $policies = config('security.csp.policies', []);
        $csp = '';
        
        foreach ($policies as $directive => $value) {
            if (!empty($value)) {
                $csp .= ($csp ? '; ' : '') . $directive . ' ' . $value;
            }
        }
        
        // Add report URI if configured
        $reportUri = config('security.csp.report_uri');
        if (!empty($reportUri)) {
            $csp .= ($csp ? '; ' : '') . 'report-uri ' . $reportUri;
        }
        
        return $csp;
    }
}