<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Hypervel\Http\Request;
use Hypervel\Http\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Hypervel\Http\Request  $request
     * @param  \Closure  $next
     * @return \Hypervel\Http\Response
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Check if security headers are enabled
        if (!config('security.enabled', true)) {
            return $response;
        }

        // Content Security Policy - Prevent XSS attacks
        if (config('security.headers.csp.enabled', true)) {
            $cspPolicy = $this->getContentSecurityPolicy();
            $headerName = config('security.headers.csp.report_only', false) 
                ? 'Content-Security-Policy-Report-Only' 
                : 'Content-Security-Policy';
            $response->headers->set($headerName, $cspPolicy);
        }

        // HTTP Strict Transport Security - Enforce HTTPS
        if (config('security.headers.hsts.enabled', true)) {
            $maxAge = config('security.headers.hsts.max_age', 31536000);
            $includeSubDomains = config('security.headers.hsts.include_sub_domains', true) ? '; includeSubDomains' : '';
            $preload = config('security.headers.hsts.preload', true) ? '; preload' : '';
            $hstsValue = "max-age={$maxAge}{$includeSubDomains}{$preload}";
            $response->headers->set('Strict-Transport-Security', $hstsValue);
        }

        // X-Frame-Options - Prevent clickjacking
        if (config('security.headers.x_frame_options.enabled', true)) {
            $xFrameOptions = config('security.headers.x_frame_options.value', 'DENY');
            $response->headers->set('X-Frame-Options', $xFrameOptions);
        }

        // X-Content-Type-Options - Prevent MIME-type sniffing
        if (config('security.headers.x_content_type_options.enabled', true)) {
            $xContentTypeOptions = config('security.headers.x_content_type_options.value', 'nosniff');
            $response->headers->set('X-Content-Type-Options', $xContentTypeOptions);
        }

        // Referrer-Policy - Control referrer information
        if (config('security.headers.referrer_policy.enabled', true)) {
            $referrerPolicy = config('security.headers.referrer_policy.value', 'strict-origin-when-cross-origin');
            $response->headers->set('Referrer-Policy', $referrerPolicy);
        }

        // Permissions-Policy - Control browser feature access
        if (config('security.headers.permissions_policy.enabled', true)) {
            $permissionsPolicy = config('security.headers.permissions_policy.value', 'geolocation=(), microphone=(), camera=()');
            $response->headers->set('Permissions-Policy', $permissionsPolicy);
        }

        // X-XSS-Protection - Enable browser XSS protection (legacy but still useful)
        if (config('security.headers.x_xss_protection.enabled', true)) {
            $xXssProtection = config('security.headers.x_xss_protection.value', '1; mode=block');
            $response->headers->set('X-XSS-Protection', $xXssProtection);
        }

        return $response;
    }

    /**
     * Get Content Security Policy based on environment
     *
     * @return string
     */
    private function getContentSecurityPolicy(): string
    {
        $environment = app()->environment();
        $policies = config('security.headers.csp.policy', []);

        if (isset($policies[$environment])) {
            return $policies[$environment];
        }

        // Default to production policy if environment-specific policy not found
        return $policies['production'] ?? "default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self' data:; connect-src 'self'; object-src 'none'; frame-ancestors 'none'; base-uri 'self'; form-action 'self';";
    }
}