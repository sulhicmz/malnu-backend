<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Rate Limiting Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration defines the rate limiting settings for API endpoints
    | to prevent abuse and ensure fair usage of the application resources.
    |
    */

    'enabled' => env('RATE_LIMIT_ENABLED', true),

    'max_attempts' => env('RATE_LIMIT_MAX_ATTEMPTS', 60),

    'decay_minutes' => env('RATE_LIMIT_DECAY_MINUTES', 1),

    'api_groups' => [
        'auth' => [
            'max_attempts' => env('RATE_LIMIT_AUTH_ATTEMPTS', 5),
            'decay_minutes' => env('RATE_LIMIT_AUTH_DECAY', 1),
        ],
        'public' => [
            'max_attempts' => env('RATE_LIMIT_PUBLIC_ATTEMPTS', 60),
            'decay_minutes' => env('RATE_LIMIT_PUBLIC_DECAY', 1),
        ],
        'private' => [
            'max_attempts' => env('RATE_LIMIT_PRIVATE_ATTEMPTS', 120),
            'decay_minutes' => env('RATE_LIMIT_PRIVATE_DECAY', 1),
        ],
    ],
];