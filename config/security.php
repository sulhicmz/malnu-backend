<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Security Headers Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration defines security headers for the application.
    | These headers help protect against common web vulnerabilities.
    |
    */

    'enabled' => env('SECURITY_HEADERS_ENABLED', true),

    'hsts' => [
        'enabled' => env('HSTS_ENABLED', true),
        'max_age' => env('HSTS_MAX_AGE', 31536000), // 1 year
        'include_sub_domains' => env('HSTS_INCLUDE_SUBDOMAINS', true),
        'preload' => env('HSTS_PRELOAD', true),
    ],

    'csp' => [
        'enabled' => env('CSP_ENABLED', true),
        'report_only' => env('CSP_REPORT_ONLY', false),
        'policies' => [
            'default' => [
                'development' => "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self' data:; connect-src 'self' http: https:; frame-ancestors 'self';",
                'production' => "default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self' data:; connect-src 'self'; frame-ancestors 'self';",
            ],
        ],
    ],

    'x_frame_options' => [
        'enabled' => env('X_FRAME_OPTIONS_ENABLED', true),
        'value' => env('X_FRAME_OPTIONS', 'DENY'),
    ],

    'x_content_type_options' => [
        'enabled' => env('X_CONTENT_TYPE_OPTIONS_ENABLED', true),
        'value' => env('X_CONTENT_TYPE_OPTIONS', 'nosniff'),
    ],

    'referrer_policy' => [
        'enabled' => env('REFERRER_POLICY_ENABLED', true),
        'value' => env('REFERRER_POLICY', 'strict-origin-when-cross-origin'),
    ],

    'permissions_policy' => [
        'enabled' => env('PERMISSIONS_POLICY_ENABLED', true),
        'value' => env('PERMISSIONS_POLICY', 'geolocation=(), microphone=(), camera=()'),
    ],

    'x_xss_protection' => [
        'enabled' => env('X_XSS_PROTECTION_ENABLED', true),
        'value' => env('X_XSS_PROTECTION', '1; mode=block'),
    ],
];