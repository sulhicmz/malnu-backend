<?php

declare(strict_types=1);

use App\Contracts\AuthServiceInterface;
use App\Contracts\EmailServiceInterface;
use App\Contracts\JWTServiceInterface;
use App\Contracts\TokenBlacklistServiceInterface;
use App\Contracts\UserRepositoryInterface;
use App\Contracts\AttendanceRepositoryInterface;
use App\Contracts\CalendarRepositoryInterface;
use App\Services\AuthService;
use App\Services\EmailService;
use App\Services\JWTService;
use App\Services\TokenBlacklistService;
use App\Repositories\EloquentUserRepository;
use App\Repositories\EloquentAttendanceRepository;
use App\Repositories\EloquentCalendarRepository;
use App\Models\User;
use App\Models\Attendance\StudentAttendance;
use App\Models\Calendar\Calendar;
use App\Models\Calendar\CalendarEvent;
use Psr\Container\ContainerInterface;

return [
    ContainerInterface::class => [
        AuthServiceInterface::class => AuthService::class,
        JWTServiceInterface::class => JWTService::class,
        TokenBlacklistServiceInterface::class => TokenBlacklistService::class,
        EmailServiceInterface::class => EmailService::class,
        UserRepositoryInterface::class => EloquentUserRepository::class,
        AttendanceRepositoryInterface::class => EloquentAttendanceRepository::class,
        CalendarRepositoryInterface::class => EloquentCalendarRepository::class,
    ],
];
