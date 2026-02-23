<?php

declare(strict_types=1);

use App\Contracts\AuthServiceInterface;
use App\Contracts\CacheServiceInterface;
use App\Contracts\CalendarServiceInterface;
use App\Contracts\EmailServiceInterface;
use App\Contracts\JWTServiceInterface;
use App\Contracts\LoggingServiceInterface;
use App\Contracts\TokenBlacklistServiceInterface;
use App\Contracts\UserRepositoryInterface;
use App\Contracts\AttendanceRepositoryInterface;
use App\Contracts\CalendarRepositoryInterface;
use App\Services\AuthService;
use App\Services\CacheService;
use App\Services\CalendarService;
use App\Services\EmailService;
use App\Services\JWTService;
use App\Services\LoggingService;
use App\Services\TokenBlacklistService;
use App\Repositories\EloquentUserRepository;
use App\Repositories\EloquentAttendanceRepository;
use App\Repositories\EloquentCalendarRepository;
use Psr\Container\ContainerInterface;






return [
    ContainerInterface::class => [
        AuthServiceInterface::class => AuthService::class,
        CacheServiceInterface::class => CacheService::class,
        CalendarServiceInterface::class => CalendarService::class,
        EmailServiceInterface::class => EmailService::class,
        JWTServiceInterface::class => JWTService::class,
        LoggingServiceInterface::class => LoggingService::class,
        TokenBlacklistServiceInterface::class => TokenBlacklistService::class,
        UserRepositoryInterface::class => EloquentUserRepository::class,
        AttendanceRepositoryInterface::class => EloquentAttendanceRepository::class,
        CalendarRepositoryInterface::class => EloquentCalendarRepository::class,
    ],
];
