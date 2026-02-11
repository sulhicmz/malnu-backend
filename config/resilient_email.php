<?php

declare(strict_types=1);

return [
    'timeout' => env('MAIL_TIMEOUT', 30),
    'retry_attempts' => env('MAIL_RETRY_ATTEMPTS', 3),
    'fallback_to_queue' => env('MAIL_FALLBACK_TO_QUEUE', true),
];
