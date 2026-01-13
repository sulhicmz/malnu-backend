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
        $env = config('app.env', 'local');
        $jwtSecret = config('jwt.secret', '');

        if (in_array($env, ['local', 'testing'])) {
            return;
        }

        if (empty($jwtSecret)) {
            throw new \RuntimeException(
                'JWT_SECRET is not set in production environment. '
                . 'Generate a secure secret using: openssl rand -hex 32'
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

        if (in_array(strtolower(trim($jwtSecret)), $placeholders)) {
            throw new \RuntimeException(
                'JWT_SECRET is using a placeholder value in production environment. '
                . 'Generate a secure secret using: openssl rand -hex 32'
            );
        }

        if (strlen($jwtSecret) < 32) {
            throw new \RuntimeException(
                'JWT_SECRET must be at least 32 characters long in production environment. '
                . 'Generate a secure secret using: openssl rand -hex 32'
            );
        }
    }
}
