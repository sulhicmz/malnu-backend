<?php

declare(strict_types=1);

return [
    'default' => [
        'max_attempts' => env('RETRY_MAX_ATTEMPTS', 3),
        'initial_delay' => env('RETRY_INITIAL_DELAY', 1000),
        'max_delay' => env('RETRY_MAX_DELAY', 10000),
        'multiplier' => env('RETRY_MULTIPLIER', 2),
        'jitter' => env('RETRY_JITTER', true),
    ],

    'operations' => [
        'email' => [
            'max_attempts' => env('RETRY_EMAIL_MAX_ATTEMPTS', 3),
            'initial_delay' => env('RETRY_EMAIL_INITIAL_DELAY', 2000),
            'max_delay' => env('RETRY_EMAIL_MAX_DELAY', 30000),
            'multiplier' => 2,
            'jitter' => true,
        ],

        'http' => [
            'max_attempts' => env('RETRY_HTTP_MAX_ATTEMPTS', 3),
            'initial_delay' => env('RETRY_HTTP_INITIAL_DELAY', 500),
            'max_delay' => env('RETRY_HTTP_MAX_DELAY', 10000),
            'multiplier' => 2,
            'jitter' => true,
        ],

        'database' => [
            'max_attempts' => env('RETRY_DB_MAX_ATTEMPTS', 2),
            'initial_delay' => env('RETRY_DB_INITIAL_DELAY', 100),
            'max_delay' => env('RETRY_DB_MAX_DELAY', 1000),
            'multiplier' => 2,
            'jitter' => false,
        ],

        'redis' => [
            'max_attempts' => env('RETRY_REDIS_MAX_ATTEMPTS', 2),
            'initial_delay' => env('RETRY_REDIS_INITIAL_DELAY', 50),
            'max_delay' => env('RETRY_REDIS_MAX_DELAY', 500),
            'multiplier' => 2,
            'jitter' => false,
        ],
    ],
];
