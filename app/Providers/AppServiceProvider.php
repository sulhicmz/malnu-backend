<?php

declare(strict_types=1);

namespace App\Providers;

use Hyperf\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Validate JWT_SECRET is not empty in production
        $jwtSecret = env('JWT_SECRET');
        $appEnv = env('APP_ENV', 'local');
        
        if ($appEnv !== 'local' && (empty($jwtSecret) || $jwtSecret === '')) {
            throw new \RuntimeException('JWT_SECRET is not configured. Please set JWT_SECRET in your environment configuration.');
        }
    }

    public function register(): void
    {
    }
}
