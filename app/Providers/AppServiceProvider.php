<?php

declare(strict_types=1);

namespace App\Providers;

use Hyperf\Support\Facades\Config;
use Hyperf\Support\ServiceProvider;
use RuntimeException;

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
        $env = Config::get('app.env', 'local');

        if ($env === 'testing') {
            return;
        }

        $jwtSecret = Config::get('jwt.secret', '');

        if ($env === 'production') {
            if (empty($jwtSecret)) {
                throw new RuntimeException(
                    'JWT_SECRET is not set. Generate a secure secret using: openssl rand -hex 32'
                );
            }

            $placeholders = [
                'your-secret-key-here',
                'change-me',
                'your-jwt-secret',
                'jwt-secret-key',
                'secret',
                'password',
            ];

            if (in_array(strtolower($jwtSecret), $placeholders, true)) {
                throw new RuntimeException(
                    'JWT_SECRET is using a placeholder value. Generate a secure secret using: openssl rand -hex 32'
                );
            }

            if (strlen($jwtSecret) < 32) {
                throw new RuntimeException(
                    'JWT_SECRET must be at least 32 characters long. Generate a secure secret using: openssl rand -hex 32'
                );
            }
        }
    }
}
