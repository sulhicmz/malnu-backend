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

    'headers' => [
        /*
        |--------------------------------------------------------------------------
        | Content Security Policy (CSP)
        |--------------------------------------------------------------------------
        |
        | Content Security Policy helps prevent XSS attacks by controlling
        | which resources can be loaded and executed.
        |
        */
        'csp' => [
            'enabled' => env('CSP_ENABLED', true),
            'policy' => [
                'production' => "default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self' data:; connect-src 'self'; object-src 'none'; frame-ancestors 'none'; base-uri 'self'; form-action 'self';",
                'development' => "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self' data:; connect-src 'self' http://localhost:*; object-src 'none'; frame-ancestors 'none'; base-uri 'self'; form-action 'self';",
                'local' => "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self' data:; connect-src 'self' http://localhost:*; object-src 'none'; frame-ancestors 'none'; base-uri 'self'; form-action 'self';",
            ],
            'report_only' => env('CSP_REPORT_ONLY', false),
        ],

        /*
        |--------------------------------------------------------------------------
        | HTTP Strict Transport Security (HSTS)
        |--------------------------------------------------------------------------
        |
        | Enforces HTTPS connections and prevents protocol downgrade attacks.
        |
        */
        'hsts' => [
            'enabled' => env('HSTS_ENABLED', true),
            'max_age' => env('HSTS_MAX_AGE', 31536000), // 1 year
            'include_sub_domains' => env('HSTS_INCLUDE_SUBDOMAINS', true),
            'preload' => env('HSTS_PRELOAD', true),
        ],

        /*
        |--------------------------------------------------------------------------
        | X-Frame-Options
        |--------------------------------------------------------------------------
        |
        | Prevents clickjacking attacks by controlling iframe embedding.
        |
        */
        'x_frame_options' => [
            'enabled' => env('X_FRAME_OPTIONS_ENABLED', true),
            'value' => env('X_FRAME_OPTIONS', 'DENY'),
        ],

        /*
        |--------------------------------------------------------------------------
        | X-Content-Type-Options
        |--------------------------------------------------------------------------
        |
        | Prevents MIME-type sniffing attacks.
        |
        */
        'x_content_type_options' => [
            'enabled' => env('X_CONTENT_TYPE_OPTIONS_ENABLED', true),
            'value' => env('X_CONTENT_TYPE_OPTIONS', 'nosniff'),
        ],

        /*
        |--------------------------------------------------------------------------
        | Referrer-Policy
        |--------------------------------------------------------------------------
        |
        | Controls referrer information leakage.
        |
        */
        'referrer_policy' => [
            'enabled' => env('REFERRER_POLICY_ENABLED', true),
            'value' => env('REFERRER_POLICY', 'strict-origin-when-cross-origin'),
        ],

        /*
        |--------------------------------------------------------------------------
        | Permissions-Policy
        |--------------------------------------------------------------------------
        |
        | Controls browser feature access.
        |
        */
        'permissions_policy' => [
            'enabled' => env('PERMISSIONS_POLICY_ENABLED', true),
            'value' => env('PERMISSIONS_POLICY', 'geolocation=(), microphone=(), camera=()'),
        ],

        /*
        |--------------------------------------------------------------------------
        | X-XSS-Protection
        |--------------------------------------------------------------------------
        |
        | Enables browser XSS protection (legacy but still useful).
        |
        */
        'x_xss_protection' => [
            'enabled' => env('X_XSS_PROTECTION_ENABLED', true),
            'value' => env('X_XSS_PROTECTION', '1; mode=block'),
        ],
    ],
];