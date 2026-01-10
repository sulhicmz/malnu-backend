<?php

declare(strict_types=1);

return [
    \App\Contracts\AuthServiceInterface::class => \App\Services\AuthService::class,
    \App\Contracts\JWTServiceInterface::class => \App\Services\JWTService::class,
    \App\Contracts\TokenBlacklistServiceInterface::class => \App\Services\TokenBlacklistService::class,
];
