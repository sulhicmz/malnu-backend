<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Security Headers Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration defines security headers that will be applied to all
    | responses in the application. These headers help protect against common
    | web vulnerabilities like XSS, clickjacking, and content-type sniffing.
    |
    */

    'enabled' => env('SECURITY_HEADERS_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Content Security Policy (CSP)
    |--------------------------------------------------------------------------
    |
    | Content Security Policy helps prevent XSS attacks by controlling which
    | resources can be loaded and executed on the page.
    |
    */
    'csp' => [
        'enabled' => env('CSP_ENABLED', true),
        'report_only' => env('CSP_REPORT_ONLY', false),
        'policies' => [
            'default' => env('CSP_DEFAULT_SRC', "'self'"),
            'script' => env('CSP_SCRIPT_SRC', "'self' 'unsafe-inline' 'unsafe-eval'"),
            'style' => env('CSP_STYLE_SRC', "'self' 'unsafe-inline'"),
            'img' => env('CSP_IMG_SRC', "'self' data: https:"),
            'font' => env('CSP_FONT_SRC', "'self' data:"),
            'connect' => env('CSP_CONNECT_SRC', "'self'"),
            'frame' => env('CSP_FRAME_SRC', "'self'"),
            'object' => env('CSP_OBJECT_SRC', "'none'"),
            'media' => env('CSP_MEDIA_SRC', "'self'"),
            'child' => env('CSP_CHILD_SRC', "'self'"),
        ],
        'report_uri' => env('CSP_REPORT_URI', '/csp-report'),
    ],

    /*
    |--------------------------------------------------------------------------
    | HTTP Strict Transport Security (HSTS)
    |--------------------------------------------------------------------------
    |
    | HSTS forces browsers to use HTTPS instead of HTTP, preventing protocol
    | downgrade attacks and cookie hijacking.
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
    | Additional Security Headers
    |--------------------------------------------------------------------------
    |
    | Other important security headers that provide additional protection.
    |
    */
    'x_frame_options' => env('X_FRAME_OPTIONS', 'DENY'),
    'x_content_type_options' => env('X_CONTENT_TYPE_OPTIONS', 'nosniff'),
    'x_xss_protection' => env('X_XSS_PROTECTION', '1; mode=block'),
    'referrer_policy' => env('REFERRER_POLICY', 'strict-origin-when-cross-origin'),
    'permissions_policy' => env('PERMISSIONS_POLICY', 'geolocation=(), microphone=(), camera=()'),
];
