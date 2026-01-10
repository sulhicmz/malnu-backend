<?php

declare(strict_types=1);

namespace App\Http;

use Hyperf\Foundation\Http\Kernel as HttpKernel;

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
            // \Hyperf\Router\Middleware\SubstituteBindings::class,
            // \Hyperf\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            // \Hyperf\Session\Middleware\StartSession::class,
            // \Hyperf\View\Middleware\ShareErrorsFromSession::class,
            // \App\Http\Middleware\VerifyCsrfToken::class,
        ],

        'api' => [
            \App\Http\Middleware\InputSanitizationMiddleware::class,
            // 'throttle:60,1,api',
            // \Hyperf\Router\Middleware\SubstituteBindings::class,
            'role' => \App\Http\Middleware\RoleMiddleware::class,
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
        'can' => \Hyperf\Auth\Middleware\Authorize::class,
        'throttle' => \Hyperf\Router\Middleware\ThrottleRequests::class,
        'bindings' => \Hyperf\Router\Middleware\SubstituteBindings::class,
        'signed' => \App\Http\Middleware\ValidateSignature::class,
        'security.headers' => \App\Http\Middleware\SecurityHeaders::class,
        'jwt' => \App\Http\Middleware\JWTMiddleware::class,
        'role' => \App\Http\Middleware\RoleMiddleware::class,
        'input.sanitization' => \App\Http\Middleware\InputSanitizationMiddleware::class,
        'rate.limit' => \App\Http\Middleware\RateLimitingMiddleware::class,
    ];

    /**
     * The priority-sorted list of middleware.
     *
     * Forces non-global middleware to always be in the given order.
     *
     * @var string[]
     */
    protected array $middlewarePriority = [
        // \Hyperf\Router\Middleware\ThrottleRequests::class,
        // \Hyperf\Router\Middleware\SubstituteBindings::class,
        // \Hyperf\Session\Middleware\StartSession::class,
        // \Hyperf\View\Middleware\ShareErrorsFromSession::class,
        // \App\Http\Middleware\VerifyCsrfToken::class,
    ];
}
