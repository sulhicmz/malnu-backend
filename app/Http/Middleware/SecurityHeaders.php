<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        
        // Only add security headers if they're enabled in config
        if (config('security.enabled', true) && $response instanceof Response) {
            $this->addSecurityHeaders($response);
        }
        
        return $response;
    }
    
    private function addSecurityHeaders($response)
    {
        $environment = app()->environment();
        
        // Content Security Policy
        if (config('security.csp.enabled', true)) {
            $csp = $this->buildCspHeader();
            
            $headerName = config('security.csp.report_only') ? 'Content-Security-Policy-Report-Only' : 'Content-Security-Policy';
            $response->headers->set($headerName, $csp);
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
            
            $response->headers->set('Strict-Transport-Security', implode('; ', $hstsDirectives));
        }
        
        // X-Frame-Options to prevent clickjacking
        $response->headers->set('X-Frame-Options', config('security.x_frame_options', 'DENY'));
        
        // X-Content-Type-Options to prevent MIME type sniffing
        $response->headers->set('X-Content-Type-Options', config('security.x_content_type_options', 'nosniff'));
        
        // Referrer-Policy to control referrer information
        $response->headers->set('Referrer-Policy', config('security.referrer_policy', 'strict-origin-when-cross-origin'));
        
        // Permissions-Policy to control browser features
        $response->headers->set('Permissions-Policy', config('security.permissions_policy', 'geolocation=(), microphone=(), camera=()'));
        
        // X-XSS-Protection (for older browsers that don't support CSP)
        $response->headers->set('X-XSS-Protection', config('security.x_xss_protection', '1; mode=block'));
    }
    
    private function buildCspHeader()
    {
        $policies = config('security.csp.policies', []);
        $csp = '';
        
        foreach ($policies as $directive => $value) {
            if (!empty($value)) {
                $csp .= ($csp ? '; ' : '') . $directive . '-src ' . $value;
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