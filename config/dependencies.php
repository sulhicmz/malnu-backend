<?php

declare(strict_types=1);

use App\Contracts\AuthServiceInterface;
use App\Contracts\EmailServiceInterface;
use App\Contracts\JWTServiceInterface;
use App\Contracts\TokenBlacklistServiceInterface;
use App\Services\AuthService;
use App\Services\EmailService;
use App\Services\JWTService;
use App\Services\TokenBlacklistService;
use Psr\Container\ContainerInterface;

return [
    ContainerInterface::class => [
        AuthServiceInterface::class => AuthService::class,
        JWTServiceInterface::class => JWTService::class,
        TokenBlacklistServiceInterface::class => TokenBlacklistService::class,
        EmailServiceInterface::class => EmailService::class,
    ],
];
