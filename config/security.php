<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Security Headers Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration defines various security headers to protect against
    | common web vulnerabilities. These headers are applied globally via
    | the SecurityHeaders middleware.
    |
    */

    'enabled' => [
        'csp' => env('SECURITY_CSP_ENABLED', true),
        'hsts' => env('SECURITY_HSTS_ENABLED', true),
        'xframe' => env('SECURITY_XFRAME_ENABLED', true),
        'xcto' => env('SECURITY_XCTO_ENABLED', true),
        'referrer' => env('SECURITY_REFERRER_ENABLED', true),
        'permissions' => env('SECURITY_PERMISSIONS_ENABLED', true),
        'xxss' => env('SECURITY_XXSS_ENABLED', true),
    ],

    'csp' => [
        'production' => "default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self' data:; connect-src 'self' https:; frame-ancestors 'self';",
        'development' => "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self' data:; connect-src 'self' http: https:; frame-ancestors 'self';",
        'report_only' => env('SECURITY_CSP_REPORT_ONLY', false),
    ],

    'hsts' => [
        'max_age' => env('SECURITY_HSTS_MAX_AGE', 31536000), // 1 year
        'include_sub_domains' => env('SECURITY_HSTS_INCLUDE_SUBDOMAINS', true),
        'preload' => env('SECURITY_HSTS_PRELOAD', false),
    ],

    'xframe' => [
        'option' => env('SECURITY_XFRAME_OPTION', 'DENY'), // DENY, SAMEORIGIN, ALLOW-FROM uri
        'allow_from' => env('SECURITY_XFRAME_ALLOW_FROM', null),
    ],

    'referrer' => [
        'policy' => env('SECURITY_REFERRER_POLICY', 'strict-origin-when-cross-origin'),
    ],

    'permissions' => [
        'policy' => env('SECURITY_PERMISSIONS_POLICY', 'geolocation=(), microphone=(), camera=()'),
    ],

    'xxss' => [
        'enabled' => env('SECURITY_XXSS_PROTECTION', true),
        'mode' => env('SECURITY_XXSS_BLOCK_MODE', 'block'),
    ],
];