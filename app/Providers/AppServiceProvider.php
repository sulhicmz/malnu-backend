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
        $env = env('APP_ENV', 'local');
        $jwtSecret = env('JWT_SECRET', '');

        if (in_array($env, ['local', 'testing'])) {
            return;
        }

        if (empty($jwtSecret)) {
            throw new \RuntimeException(
                'JWT_SECRET cannot be empty in production environment. ' .
                'Generate a secure secret using: openssl rand -hex 32'
            );
        }

        $placeholders = [
            'your-secret-key-here',
            'change-me',
            'your-jwt-secret',
            'jwt-secret-key',
            'secret',
            'password',
            'your-secure-jwt-secret-key-here',
        ];

        if (in_array(strtolower($jwtSecret), $placeholders)) {
            throw new \RuntimeException(
                'JWT_SECRET is using a placeholder value which is insecure. ' .
                'Generate a secure secret using: openssl rand -hex 32'
            );
        }

        if (strlen($jwtSecret) < 32) {
            throw new \RuntimeException(
                'JWT_SECRET must be at least 32 characters long for security. ' .
                'Current length: ' . strlen($jwtSecret) . ' characters. ' .
                'Generate a secure secret using: openssl rand -hex 32'
            );
        }
    }
}
