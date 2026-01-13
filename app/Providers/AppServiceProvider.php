<?php

declare(strict_types=1);

namespace App\Providers;

use Hyperf\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->validateJwtSecret();
    }

    public function register(): void
    {
    }

    private function validateJwtSecret(): void
    {
        $jwtSecret = env('JWT_SECRET');
        $appEnv = env('APP_ENV', 'production');

        if ($appEnv === 'testing') {
            return;
        }

        $insecureValues = [
            '',
            'your-secret-key-here',
            'secret',
            'password',
            'test-secret',
            'jwt-secret',
        ];

        if (in_array($jwtSecret, $insecureValues, true)) {
            throw new \RuntimeException(
                'CRITICAL SECURITY ERROR: JWT_SECRET is using an insecure placeholder value. ' .
                'Please generate a secure JWT secret using: openssl rand -hex 32 ' .
                'and set it in your environment variables. ' .
                'Never use default or placeholder values in production.'
            );
        }

        if (strlen($jwtSecret) < 32) {
            throw new \RuntimeException(
                'CRITICAL SECURITY ERROR: JWT_SECRET is too short (must be at least 32 characters). ' .
                'Please generate a secure JWT secret using: openssl rand -hex 32'
            );
        }
    }
}
