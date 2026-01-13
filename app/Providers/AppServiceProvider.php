<?php

declare(strict_types=1);

namespace App\Providers;

use Hyperf\Support\ServiceProvider;
use Hyperf\Config\Config;
use RuntimeException;

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
        
        // Skip validation in local and testing environments
        if (in_array($env, ['local', 'testing'])) {
            return;
        }

        $jwtSecret = env('JWT_SECRET');
        
        // Check if JWT_SECRET is set
        if (empty($jwtSecret)) {
            throw new RuntimeException(
                'JWT_SECRET is not set in production environment. ' .
                'Generate a secure secret using: openssl rand -hex 32'
            );
        }

        // Check for known placeholder values
        $placeholders = [
            'your-secret-key-here',
            'change-me',
            'your-jwt-secret',
            'jwt-secret-key',
            'secret',
            'password',
        ];

        if (in_array($jwtSecret, $placeholders)) {
            throw new RuntimeException(
                'JWT_SECRET is using a placeholder value in production. ' .
                'Generate a secure secret using: openssl rand -hex 32'
            );
        }

        // Check minimum length (HS256 requires at least 32 bytes/256 bits)
        if (strlen($jwtSecret) < 32) {
            throw new RuntimeException(
                'JWT_SECRET must be at least 32 characters long in production. ' .
                'Current length: ' . strlen($jwtSecret) . '. ' .
                'Generate a secure secret using: openssl rand -hex 32'
            );
        }
    }
}
