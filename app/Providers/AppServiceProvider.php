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

        // Skip validation in local and testing environments
        if (in_array($env, ['local', 'testing'], true)) {
            return;
        }

        // Check if JWT_SECRET is empty
        if (empty($jwtSecret)) {
            throw new \RuntimeException(
                'JWT_SECRET is not set in environment variables. ' .
                'Please generate a secure JWT secret using: openssl rand -hex 32'
            );
        }

        // Check for known insecure placeholder values
        $insecurePlaceholders = [
            'your-secret-key-here',
            'change-me',
            'your-jwt-secret',
            'jwt-secret-key',
            'secret',
            'password',
            '1234567890',
            'default-secret-key',
            'replace-with-your-secret',
        ];

        if (in_array(strtolower($jwtSecret), $insecurePlaceholders, true)) {
            throw new \RuntimeException(
                'JWT_SECRET is using an insecure placeholder value. ' .
                'Please generate a secure JWT secret using: openssl rand -hex 32'
            );
        }

        // Enforce minimum length of 32 characters for HS256 algorithm
        if (strlen($jwtSecret) < 32) {
            throw new \RuntimeException(
                'JWT_SECRET is too short (minimum 32 characters required for HS256). ' .
                'Please generate a secure JWT secret using: openssl rand -hex 32'
            );
        }
    }
}
