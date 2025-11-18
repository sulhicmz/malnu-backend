<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Security Headers Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration defines security headers to protect against common
    | web vulnerabilities. These headers are applied globally to all responses.
    |
    */

    'enabled' => [
        'csp' => env('SECURITY_CSP_ENABLED', true),
        'hsts' => env('SECURITY_HSTS_ENABLED', true),
        'x_frame_options' => env('SECURITY_X_FRAME_OPTIONS_ENABLED', true),
        'x_content_type_options' => env('SECURITY_X_CONTENT_TYPE_OPTIONS_ENABLED', true),
        'referrer_policy' => env('SECURITY_REFERRER_POLICY_ENABLED', true),
        'permissions_policy' => env('SECURITY_PERMISSIONS_POLICY_ENABLED', true),
        'x_xss_protection' => env('SECURITY_X_XSS_PROTECTION_ENABLED', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Content Security Policy (CSP)
    |--------------------------------------------------------------------------
    |
    | Content Security Policy helps prevent XSS attacks by controlling resource
    | loading. Configure directives based on your application's needs.
    |
    */
    'csp' => [
        'report_only' => env('CSP_REPORT_ONLY', false),
        'policies' => [
            'production' => [
                'default-src' => "'self'",
                'script-src' => "'self'",
                'style-src' => "'self' 'unsafe-inline'",
                'img-src' => "'self' data: https:",
                'font-src' => "'self' data:",
                'connect-src' => "'self' https:",
                'frame-src' => "'self'",
                'object-src' => "'none'",
                'base-uri' => "'self'",
                'form-action' => "'self'",
            ],
            'development' => [
                'default-src' => "'self'",
                'script-src' => "'self' 'unsafe-inline' 'unsafe-eval'",
                'style-src' => "'self' 'unsafe-inline'",
                'img-src' => "'self' data: https:",
                'font-src' => "'self' data:",
                'connect-src' => "'self' http: https:",
                'frame-src' => "'self'",
                'object-src' => "'none'",
                'base-uri' => "'self'",
                'form-action' => "'self'",
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | HTTP Strict Transport Security (HSTS)
    |--------------------------------------------------------------------------
    |
    | HSTS enforces HTTPS connections and prevents protocol downgrade attacks.
    |
    */
    'hsts' => [
        'max_age' => env('HSTS_MAX_AGE', 31536000), // 1 year
        'include_sub_domains' => env('HSTS_INCLUDE_SUBDOMAINS', true),
        'preload' => env('HSTS_PRELOAD', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | X-Frame-Options
    |--------------------------------------------------------------------------
    |
    | Controls whether the application can be embedded in iframes to prevent
    | clickjacking attacks.
    |
    */
    'x_frame_options' => [
        'policy' => env('X_FRAME_OPTIONS', 'DENY'),
    ],

    /*
    |--------------------------------------------------------------------------
    | X-Content-Type-Options
    |--------------------------------------------------------------------------
    |
    | Prevents browsers from MIME-type sniffing to prevent security vulnerabilities.
    |
    */
    'x_content_type_options' => [
        'value' => env('X_CONTENT_TYPE_OPTIONS', 'nosniff'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Referrer-Policy
    |--------------------------------------------------------------------------
    |
    | Controls how much referrer information is included with requests.
    |
    */
    'referrer_policy' => [
        'policy' => env('REFERRER_POLICY', 'strict-origin-when-cross-origin'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Permissions-Policy
    |--------------------------------------------------------------------------
    |
    | Controls which browser features and APIs are allowed to be used.
    |
    */
    'permissions_policy' => [
        'policy' => env('PERMISSIONS_POLICY', 'geolocation=(), microphone=(), camera=()'),
    ],

    /*
    |--------------------------------------------------------------------------
    | X-XSS-Protection
    |--------------------------------------------------------------------------
    |
    | Enables browser XSS filtering (legacy header, but still useful).
    |
    */
    'x_xss_protection' => [
        'value' => env('X_XSS_PROTECTION', '1; mode=block'),
    ],
];