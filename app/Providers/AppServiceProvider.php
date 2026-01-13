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

    public function register(): void
    {
    }

    private function validateEnvironment(): void
    {
        $appEnv = env('APP_ENV', 'local');

        if ($appEnv === 'testing') {
            return;
        }

        $jwtSecret = env('JWT_SECRET', '');

        if (empty($jwtSecret)) {
            if ($appEnv !== 'local') {
                throw new \RuntimeException('JWT_SECRET environment variable is required. Generate one using: openssl rand -hex 32');
            }
        }

        $placeholders = [
            'your-secret-key-here',
            'change-me',
            'secret',
            'password',
            'jwt-secret',
        ];

        if (in_array(strtolower((string)$jwtSecret), $placeholders)) {
            if ($appEnv !== 'local') {
                throw new \RuntimeException('JWT_SECRET cannot be a placeholder value. Generate a secure secret using: openssl rand -hex 32');
            }
        }

        if (strlen((string)$jwtSecret) < 32) {
            if ($appEnv !== 'local') {
                throw new \RuntimeException('JWT_SECRET must be at least 32 characters long. Generate a secure secret using: openssl rand -hex 32');
            }
        }
    }
}
