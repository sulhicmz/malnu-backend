<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Hypervel\Http\Request;
use Hypervel\Http\Response;
use Hypervel\Support\Facades\Config;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        
        $environment = Config::get('app.env', 'production');
        $securityConfig = Config::get('security', []);
        
        // Content Security Policy - Prevent XSS attacks
        if ($securityConfig['enabled']['csp'] ?? true) {
            $cspHeader = $securityConfig['csp'][$environment === 'production' ? 'production' : 'development'] ?? $securityConfig['csp']['production'];
            $headerName = (bool)($securityConfig['csp']['report_only'] ?? false) ? 'Content-Security-Policy-Report-Only' : 'Content-Security-Policy';
            $response->headers->set($headerName, $cspHeader);
        }
        
        // HTTP Strict Transport Security - Enforce HTTPS
        if ($securityConfig['enabled']['hsts'] ?? true) {
            $hstsDirectives = [
                'max-age=' . ($securityConfig['hsts']['max_age'] ?? 31536000),
            ];
            
            if ($securityConfig['hsts']['include_sub_domains'] ?? true) {
                $hstsDirectives[] = 'includeSubDomains';
            }
            
            if ($securityConfig['hsts']['preload'] ?? false) {
                $hstsDirectives[] = 'preload';
            }
            
            $response->headers->set('Strict-Transport-Security', implode('; ', $hstsDirectives));
        }
        
        // X-Frame-Options - Prevent clickjacking
        if ($securityConfig['enabled']['xframe'] ?? true) {
            $xframeOption = $securityConfig['xframe']['option'] ?? 'DENY';
            $response->headers->set('X-Frame-Options', $xframeOption);
            
            // If X-Frame-Options is set to ALLOW-FROM, we also need to specify the URI
            if ($xframeOption === 'ALLOW-FROM' && !empty($securityConfig['xframe']['allow_from'])) {
                // Note: ALLOW-FROM is deprecated in modern browsers, but we support it for compatibility
                $response->headers->set('X-Frame-Options', $xframeOption . ' ' . $securityConfig['xframe']['allow_from']);
            }
        }
        
        // X-Content-Type-Options - Prevent MIME-type sniffing
        if ($securityConfig['enabled']['xcto'] ?? true) {
            $response->headers->set('X-Content-Type-Options', 'nosniff');
        }
        
        // Referrer-Policy - Control referrer information
        if ($securityConfig['enabled']['referrer'] ?? true) {
            $response->headers->set('Referrer-Policy', $securityConfig['referrer']['policy'] ?? 'strict-origin-when-cross-origin');
        }
        
        // Permissions-Policy - Control browser feature access
        if ($securityConfig['enabled']['permissions'] ?? true) {
            $response->headers->set('Permissions-Policy', $securityConfig['permissions']['policy'] ?? 'geolocation=(), microphone=(), camera=()');
        }
        
        // X-XSS-Protection - Additional XSS protection (legacy but still useful)
        if ($securityConfig['enabled']['xxss'] ?? true) {
            $xxssEnabled = $securityConfig['xxss']['enabled'] ?? true;
            $xxssMode = $securityConfig['xxss']['mode'] ?? 'block';
            $xxssValue = $xxssEnabled ? "1; mode={$xxssMode}" : "0";
            $response->headers->set('X-XSS-Protection', $xxssValue);
        }
        
        return $response;
    }
}