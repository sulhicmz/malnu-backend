<?php

declare(strict_types=1);

return [
    'default' => [
        'failure_threshold' => env('CIRCUIT_BREAKER_FAILURE_THRESHOLD', 5),
        'recovery_timeout' => env('CIRCUIT_BREAKER_RECOVERY_TIMEOUT', 60),
        'half_open_attempts' => env('CIRCUIT_BREAKER_HALF_OPEN_ATTEMPTS', 1),
    ],

    'services' => [
        'email' => [
            'failure_threshold' => env('CIRCUIT_BREAKER_EMAIL_FAILURE_THRESHOLD', 3),
            'recovery_timeout' => env('CIRCUIT_BREAKER_EMAIL_RECOVERY_TIMEOUT', 120),
            'half_open_attempts' => 1,
        ],

        'http' => [
            'failure_threshold' => env('CIRCUIT_BREAKER_HTTP_FAILURE_THRESHOLD', 5),
            'recovery_timeout' => env('CIRCUIT_BREAKER_HTTP_RECOVERY_TIMEOUT', 60),
            'half_open_attempts' => 1,
        ],

        'database' => [
            'failure_threshold' => env('CIRCUIT_BREAKER_DB_FAILURE_THRESHOLD', 5),
            'recovery_timeout' => env('CIRCUIT_BREAKER_DB_RECOVERY_TIMEOUT', 30),
            'half_open_attempts' => 1,
        ],

        'redis' => [
            'failure_threshold' => env('CIRCUIT_BREAKER_REDIS_FAILURE_THRESHOLD', 3),
            'recovery_timeout' => env('CIRCUIT_BREAKER_REDIS_RECOVERY_TIMEOUT', 30),
            'half_open_attempts' => 1,
        ],
    ],
];
