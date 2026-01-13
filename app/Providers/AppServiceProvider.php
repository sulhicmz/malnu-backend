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
        $appEnv = env('APP_ENV', 'local');

        $placeholders = [
            'your-secret-key-here',
            'change-me',
            'your-jwt-secret',
            'jwt-secret-key',
            'secret',
        ];

        if ($appEnv === 'production') {
            if (empty($jwtSecret)) {
                throw new \RuntimeException(
                    'JWT_SECRET environment variable is required in production. ' .
                    'Generate a secure secret using: openssl rand -hex 32'
                );
            }

            if (in_array($jwtSecret, $placeholders, true)) {
                throw new \RuntimeException(
                    'JWT_SECRET cannot use placeholder values in production. ' .
                    'Generate a secure secret using: openssl rand -hex 32'
                );
            }

            if (strlen($jwtSecret) < 32) {
                throw new \RuntimeException(
                    'JWT_SECRET must be at least 32 characters in production. ' .
                    'Generate a secure secret using: openssl rand -hex 32'
                );
            }
        }
    }
}
