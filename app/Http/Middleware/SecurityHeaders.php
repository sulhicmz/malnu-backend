<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        
        $securityConfig = config('security');
        
        if ($securityConfig['enabled']['csp'] ?? true) {
            $cspPolicy = $this->getContentSecurityPolicy();
            $headerName = (config('security.csp.report_only') === true) ? 'Content-Security-Policy-Report-Only' : 'Content-Security-Policy';
            $response->headers->set($headerName, $cspPolicy);
        }
        
        if ($securityConfig['enabled']['hsts'] ?? true) {
            $hstsValue = $this->getHstsHeaderValue();
            $response->headers->set('Strict-Transport-Security', $hstsValue);
        }
        
        if ($securityConfig['enabled']['x_frame_options'] ?? true) {
            $xFrameOptions = config('security.x_frame_options', 'DENY');
            $response->headers->set('X-Frame-Options', $xFrameOptions);
        }
        
        if ($securityConfig['enabled']['x_content_type_options'] ?? true) {
            $response->headers->set('X-Content-Type-Options', 'nosniff');
        }
        
        if ($securityConfig['enabled']['referrer_policy'] ?? true) {
            $referrerPolicy = config('security.referrer_policy', 'strict-origin-when-cross-origin');
            $response->headers->set('Referrer-Policy', $referrerPolicy);
        }
        
        if ($securityConfig['enabled']['permissions_policy'] ?? true) {
            $permissionsPolicy = $this->getPermissionsPolicyValue();
            $response->headers->set('Permissions-Policy', $permissionsPolicy);
        }
        
        if ($securityConfig['enabled']['x_xss_protection'] ?? true) {
            $response->headers->set('X-XSS-Protection', '1; mode=block');
        }
        
        return $response;
    }
    
    private function getContentSecurityPolicy(): string
    {
        $environment = app()->environment();
        $policies = config('security.csp.policies.' . $environment, config('security.csp.policies.development'));
        
        $policyParts = [];
        foreach ($policies as $directive => $value) {
            if (!empty($value)) {
                $policyParts[] = $directive . ' ' . $value;
            }
        }
        
        return implode('; ', $policyParts) . ';';
    }
    
    private function getHstsHeaderValue(): string
    {
        $config = config('security.hsts', []);
        $value = 'max-age=' . ($config['max_age'] ?? 31536000);
        
        if ($config['include_sub_domains'] ?? false) {
            $value .= '; includeSubDomains';
        }
        
        if ($config['preload'] ?? false) {
            $value .= '; preload';
        }
        
        return $value;
    }
    
    private function getPermissionsPolicyValue(): string
    {
        $policies = config('security.permissions_policy', []);
        
        $policyParts = [];
        foreach ($policies as $feature => $value) {
            $policyParts[] = $feature . '=' . $value;
        }
        
        return implode(', ', $policyParts);
    }
}