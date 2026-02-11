<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Rate Limiting Storage Driver
    |--------------------------------------------------------------------------
    |
    | This option defines the storage driver to use for rate limiting.
    | Supported drivers: "redis", "array"
    |
    */

    'default' => env('RATE_LIMIT_DRIVER', 'redis'),

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting Rules
    |--------------------------------------------------------------------------
    |
    | Define rate limiting rules for different route groups.
    | Each rule specifies:
    | - max_attempts: Maximum number of attempts
    | - decay_minutes: Time window in minutes for the attempts
    | - key_type: "ip" for IP-based limiting, "user" for user-based limiting, "both" for combined
    |
    */

    'limits' => [
        'auth.login' => [
            'max_attempts' => 5,
            'decay_minutes' => 1,
            'key_type' => 'ip',
        ],
        'auth.register' => [
            'max_attempts' => 3,
            'decay_minutes' => 1,
            'key_type' => 'ip',
        ],
        'auth.password.reset' => [
            'max_attempts' => 3,
            'decay_minutes' => 1,
            'key_type' => 'ip',
        ],
        'auth.password.forgot' => [
            'max_attempts' => 3,
            'decay_minutes' => 15,
            'key_type' => 'ip',
        ],
        'public_api' => [
            'max_attempts' => 60,
            'decay_minutes' => 1,
            'key_type' => 'ip',
        ],
        'protected_api' => [
            'max_attempts' => 300,
            'decay_minutes' => 1,
            'key_type' => 'user',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting Headers
    |--------------------------------------------------------------------------
    |
    | Configure which headers to include in rate-limited responses.
    |
    */

    'headers' => [
        'limit' => 'X-RateLimit-Limit',
        'remaining' => 'X-RateLimit-Remaining',
        'reset' => 'X-RateLimit-Reset',
        'retry_after' => 'Retry-After',
    ],

    /*
    |--------------------------------------------------------------------------
    | Redis Configuration
    |--------------------------------------------------------------------------
    |
    | Redis connection settings for rate limiting.
    |
    */

    'redis' => [
        'connection' => env('RATE_LIMIT_REDIS_CONNECTION', 'default'),
        'prefix' => env('RATE_LIMIT_PREFIX', 'ratelimit:'),
    ],
];
