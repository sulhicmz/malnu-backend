<?php

declare(strict_types=1);

namespace App\Providers;

use Hyperf\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->validateEnvironment();
    }

    private function validateEnvironment(): void
    {
        if (env('APP_ENV') === 'testing') {
            return;
        }

        $jwtSecret = env('JWT_SECRET', '');

        $invalidValues = [
            'your-secret-key-here',
            'change-me',
            'secret',
            'your-secret-key',
        ];

        if (in_array($jwtSecret, $invalidValues, true)) {
            throw new \RuntimeException(
                'JWT_SECRET cannot be a placeholder value. Please generate a secure secret using: openssl rand -hex 32'
            );
        }

        if (empty($jwtSecret)) {
            throw new \RuntimeException(
                'JWT_SECRET is required. Please generate a secure secret using: openssl rand -hex 32'
            );
        }

        if (strlen($jwtSecret) < 32) {
            throw new \RuntimeException(
                'JWT_SECRET must be at least 32 characters long for security. Please generate a secure secret using: openssl rand -hex 32'
            );
        }
    }

    public function register(): void
    {
    }
}
