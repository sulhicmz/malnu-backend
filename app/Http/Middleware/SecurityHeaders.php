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

        // Apply security headers based on configuration
        $this->applyCspHeader($response);
        $this->applyHstsHeader($response);
        $this->applyXFrameOptionsHeader($response);
        $this->applyXContentTypeOptionsHeader($response);
        $this->applyReferrerPolicyHeader($response);
        $this->applyPermissionsPolicyHeader($response);
        $this->applyXXssProtectionHeader($response);

        return $response;
    }

    private function applyCspHeader(Response $response): void
    {
        if (config('security.enabled.csp', true)) {
            $environment = config('app.env', 'production');
            $cspPolicies = config('security.csp.policies');
            $reportOnly = config('security.csp.report_only', false);
            
            $policy = $this->buildCspPolicy($cspPolicies[$environment] ?? $cspPolicies['production']);
            $headerName = $reportOnly ? 'Content-Security-Policy-Report-Only' : 'Content-Security-Policy';
            
            $response->headers->set($headerName, $policy);
        }
    }

    private function applyHstsHeader(Response $response): void
    {
        if (config('security.enabled.hsts', true)) {
            $maxAge = config('security.hsts.max_age', 31536000);
            $includeSubDomains = config('security.hsts.include_sub_domains', true);
            $preload = config('security.hsts.preload', true);
            
            $hstsValue = 'max-age=' . $maxAge;
            if ($includeSubDomains) {
                $hstsValue .= '; includeSubDomains';
            }
            if ($preload) {
                $hstsValue .= '; preload';
            }
            
            $response->headers->set('Strict-Transport-Security', $hstsValue);
        }
    }

    private function applyXFrameOptionsHeader(Response $response): void
    {
        if (config('security.enabled.x_frame_options', true)) {
            $policy = config('security.x_frame_options.policy', 'DENY');
            $response->headers->set('X-Frame-Options', $policy);
        }
    }

    private function applyXContentTypeOptionsHeader(Response $response): void
    {
        if (config('security.enabled.x_content_type_options', true)) {
            $value = config('security.x_content_type_options.value', 'nosniff');
            $response->headers->set('X-Content-Type-Options', $value);
        }
    }

    private function applyReferrerPolicyHeader(Response $response): void
    {
        if (config('security.enabled.referrer_policy', true)) {
            $policy = config('security.referrer_policy.policy', 'strict-origin-when-cross-origin');
            $response->headers->set('Referrer-Policy', $policy);
        }
    }

    private function applyPermissionsPolicyHeader(Response $response): void
    {
        if (config('security.enabled.permissions_policy', true)) {
            $policy = config('security.permissions_policy.policy', 'geolocation=(), microphone=(), camera=()');
            $response->headers->set('Permissions-Policy', $policy);
        }
    }

    private function applyXXssProtectionHeader(Response $response): void
    {
        if (config('security.enabled.x_xss_protection', true)) {
            $value = config('security.x_xss_protection.value', '1; mode=block');
            $response->headers->set('X-XSS-Protection', $value);
        }
    }

    private function buildCspPolicy(array $directives): string
    {
        $policyParts = [];
        foreach ($directives as $directive => $value) {
            if (!empty($value)) {
                $policyParts[] = $directive . ' ' . $value;
            }
        }
        return implode('; ', $policyParts) . ';';
    }
}