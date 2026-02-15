<?php

declare(strict_types=1);

namespace App\Http;

use Hypervel\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array<int, class-string|string>
     */
    protected array $middleware = [
        \App\Http\Middleware\RequestLoggingMiddleware::class,
        \App\Http\Middleware\SecurityHeaders::class,
        // \App\Http\Middleware\TrimStrings::class,
        // \App\Http\Middleware\ConvertEmptyStringsToNull::class
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array<string, array<int, class-string|string>>
     */
    protected array $middlewareGroups = [
        'web' => [
            \Hypervel\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Hypervel\Session\Middleware\StartSession::class,
            \Hypervel\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
        ],

        'api' => [
            \App\Http\Middleware\InputSanitizationMiddleware::class,
            // 'throttle:60,1,api',
            // \Hypervel\Router\Middleware\SubstituteBindings::class,
        ],
    ];

    /**
     * The application's middleware aliases.
     *
     * Aliases may be used instead of class names to conveniently assign middleware to routes and groups.
     *
     * @var array<string, class-string|string>
     */
    protected array $middlewareAliases = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'can' => \Hypervel\Auth\Middleware\Authorize::class,
        'throttle' => \Hypervel\Router\Middleware\ThrottleRequests::class,
        'bindings' => \Hypervel\Router\Middleware\SubstituteBindings::class,
        'signed' => \App\Http\Middleware\ValidateSignature::class,
        'security.headers' => \App\Http\Middleware\SecurityHeaders::class,
        'request.logging' => \App\Http\Middleware\RequestLoggingMiddleware::class,
        'jwt' => \App\Http\Middleware\JWTMiddleware::class,
        'input.sanitization' => \App\Http\Middleware\InputSanitizationMiddleware::class,
        'rate.limit' => \App\Http\Middleware\RateLimitingMiddleware::class,
        'csrf' => \App\Http\Middleware\VerifyCsrfToken::class,
        'role' => \App\Http\Middleware\RoleMiddleware::class,
        'mobile' => \App\Http\Middleware\MobileMiddleware::class,
        'cache.response' => \App\Http\Middleware\CacheResponse::class,
    ];

    /**
     * The priority-sorted list of middleware.
     *
     * Forces non-global middleware to always be in the given order.
     *
     * @var string[]
     */
    protected array $middlewarePriority = [
        \Hypervel\Router\Middleware\ThrottleRequests::class,
        \Hypervel\Router\Middleware\SubstituteBindings::class,
        \Hypervel\Session\Middleware\StartSession::class,
        \Hypervel\View\Middleware\ShareErrorsFromSession::class,
        \App\Http\Middleware\VerifyCsrfToken::class,
    ];
}
