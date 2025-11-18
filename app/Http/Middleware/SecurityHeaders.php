<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Hypervel\Http\Request;
use Hypervel\Http\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $securityConfig = config('security');

        if (!$securityConfig['enabled']) {
            return $response;
        }

        // Content Security Policy - Prevent XSS attacks
        if ($securityConfig['csp']['enabled']) {
            $cspPolicy = $this->getCspPolicy();
            $headerName = $securityConfig['csp']['report_only'] ? 'Content-Security-Policy-Report-Only' : 'Content-Security-Policy';
            $response->headers->set($headerName, $cspPolicy);
        }

        // HTTP Strict Transport Security - Enforce HTTPS
        if ($securityConfig['hsts']['enabled']) {
            $hstsValue = 'max-age=' . $securityConfig['hsts']['max_age'];
            if ($securityConfig['hsts']['include_sub_domains']) {
                $hstsValue .= '; includeSubDomains';
            }
            if ($securityConfig['hsts']['preload']) {
                $hstsValue .= '; preload';
            }
            $response->headers->set('Strict-Transport-Security', $hstsValue);
        }

        // X-Frame-Options - Prevent clickjacking
        if ($securityConfig['x_frame_options']['enabled']) {
            $response->headers->set('X-Frame-Options', $securityConfig['x_frame_options']['value']);
        }

        // X-Content-Type-Options - Prevent MIME-type sniffing
        if ($securityConfig['x_content_type_options']['enabled']) {
            $response->headers->set('X-Content-Type-Options', $securityConfig['x_content_type_options']['value']);
        }

        // Referrer-Policy - Control referrer information
        if ($securityConfig['referrer_policy']['enabled']) {
            $response->headers->set('Referrer-Policy', $securityConfig['referrer_policy']['value']);
        }

        // Permissions-Policy - Control browser feature access
        if ($securityConfig['permissions_policy']['enabled']) {
            $response->headers->set('Permissions-Policy', $securityConfig['permissions_policy']['value']);
        }

        // X-XSS-Protection - Enable browser XSS protection (legacy but still useful)
        if ($securityConfig['x_xss_protection']['enabled']) {
            $response->headers->set('X-XSS-Protection', $securityConfig['x_xss_protection']['value']);
        }

        return $response;
    }

    private function getCspPolicy(): string
    {
        $cspConfig = config('security.csp.policies.default');
        
        // Check environment to determine CSP policy
        $environment = app()->environment();
        if (in_array($environment, ['local', 'development', 'testing'])) {
            return $cspConfig['development'] ?? "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self' data:; connect-src 'self' http: https:; frame-ancestors 'self';";
        }

        // Use production policy for staging, production, etc.
        return $cspConfig['production'] ?? "default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self' data:; connect-src 'self'; frame-ancestors 'self';";
    }
}

