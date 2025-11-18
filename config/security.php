<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Security Headers Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration defines security headers to protect against common
    | web vulnerabilities. These headers are applied by the SecurityHeaders
    | middleware.
    |
    */

    'enabled' => [
        'csp' => true,
        'hsts' => true,
        'x_frame_options' => true,
        'x_content_type_options' => true,
        'referrer_policy' => true,
        'permissions_policy' => true,
        'x_xss_protection' => true,
    ],

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
        'report_only' => env('CSP_REPORT_ONLY', false),
        'policies' => [
            'production' => [
                'default-src' => "'self'",
                'script-src' => "'self' 'unsafe-inline' 'unsafe-eval'",
                'style-src' => "'self' 'unsafe-inline'",
                'img-src' => "'self' data: https:",
                'font-src' => "'self' data:",
                'connect-src' => "'self' https://api.example.com",
                'frame-ancestors' => "'none'",
                'base-uri' => "'self'",
                'form-action' => "'self'",
            ],
            'development' => [
                'default-src' => "'self'",
                'script-src' => "'self' 'unsafe-inline' 'unsafe-eval' http://localhost:* https://localhost:*",
                'style-src' => "'self' 'unsafe-inline' http://localhost:* https://localhost:*",
                'img-src' => "'self' data: https: http://localhost:* https://localhost:*",
                'font-src' => "'self' data: http://localhost:* https://localhost:*",
                'connect-src' => "'self' https://* http://localhost:* https://localhost:*",
                'frame-ancestors' => "'none'",
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
    | Enforces HTTPS connections and prevents protocol downgrade attacks.
    |
    */

    'hsts' => [
        'max_age' => 31536000, // 1 year
        'include_sub_domains' => true,
        'preload' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | X-Frame-Options
    |--------------------------------------------------------------------------
    |
    | Prevents clickjacking attacks by controlling iframe embedding.
    |
    */

    'x_frame_options' => 'DENY', // or 'SAMEORIGIN'

    /*
    |--------------------------------------------------------------------------
    | Referrer Policy
    |--------------------------------------------------------------------------
    |
    | Controls how much referrer information is included with requests.
    |
    */

    'referrer_policy' => 'strict-origin-when-cross-origin',

    /*
    |--------------------------------------------------------------------------
    | Permissions Policy
    |--------------------------------------------------------------------------
    |
    | Controls which browser features and APIs are allowed.
    |
    */

    'permissions_policy' => [
        'geolocation' => '()',
        'microphone' => '()',
        'camera' => '()',
        'usb' => '()',
        'fullscreen' => 'self',
    ],
];