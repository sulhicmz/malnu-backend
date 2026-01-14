<?php

declare(strict_types=1);

return [
    'default' => [
        'timeout' => env('HTTP_CLIENT_TIMEOUT', 30),
        'connect_timeout' => env('HTTP_CLIENT_CONNECT_TIMEOUT', 10),
    ],

    'services' => [
        'default' => [
            'timeout' => env('HTTP_CLIENT_TIMEOUT', 30),
            'connect_timeout' => env('HTTP_CLIENT_CONNECT_TIMEOUT', 10),
        ],

        'email' => [
            'timeout' => env('HTTP_CLIENT_EMAIL_TIMEOUT', 30),
            'connect_timeout' => env('HTTP_CLIENT_EMAIL_CONNECT_TIMEOUT', 10),
        ],

        'fast' => [
            'timeout' => env('HTTP_CLIENT_FAST_TIMEOUT', 10),
            'connect_timeout' => env('HTTP_CLIENT_FAST_CONNECT_TIMEOUT', 5),
        ],

        'slow' => [
            'timeout' => env('HTTP_CLIENT_SLOW_TIMEOUT', 60),
            'connect_timeout' => env('HTTP_CLIENT_SLOW_CONNECT_TIMEOUT', 15),
        ],
    ],
];
