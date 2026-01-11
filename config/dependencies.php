<?php

declare(strict_types=1);

use Hyperf\Config\Annotation\Value;
use Psr\Container\ContainerInterface;
use App\Contracts\AuthServiceInterface;
use App\Contracts\JWTServiceInterface;
use App\Contracts\TokenBlacklistServiceInterface;
use App\Contracts\EmailServiceInterface;
use App\Services\AuthService;
use App\Services\JWTService;
use App\Services\TokenBlacklistService;
use App\Services\EmailService;

return [
    ContainerInterface::class => [
        AuthServiceInterface::class => AuthService::class,
        JWTServiceInterface::class => JWTService::class,
        TokenBlacklistServiceInterface::class => TokenBlacklistService::class,
        EmailServiceInterface::class => EmailService::class,
    ],
];
