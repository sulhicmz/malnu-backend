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

    /**
     * Validate JWT_SECRET is properly configured
     * This prevents developers from accidentally using placeholder values in production
     */
    private function validateJwtSecret(): void
    {
        $appEnv = env('APP_ENV', 'local');
        $jwtSecret = env('JWT_SECRET', '');

        // Skip validation in local and testing environments
        if (in_array($appEnv, ['local', 'testing'])) {
            return;
        }

        // Common placeholder values to reject
        $placeholderValues = [
            'your-secret-key-here',
            'change-me',
            'your-jwt-secret',
            'jwt-secret-key',
            'secret',
            'password',
        ];

        // Check if JWT_SECRET is empty
        if (empty($jwtSecret)) {
            throw new \RuntimeException(
                'JWT_SECRET is not configured in production. ' .
                'Please generate a secure secret using: openssl rand -hex 32'
            );
        }

        // Check if JWT_SECRET is using a placeholder value
        if (in_array(strtolower(trim($jwtSecret)), $placeholderValues)) {
            throw new \RuntimeException(
                'JWT_SECRET is using a placeholder value which is not secure. ' .
                'Please generate a secure secret using: openssl rand -hex 32'
            );
        }

        // Check if JWT_SECRET is too short (minimum 32 characters for HS256)
        if (strlen($jwtSecret) < 32) {
            throw new \RuntimeException(
                'JWT_SECRET must be at least 32 characters long for HS256 algorithm. ' .
                'Current length: ' . strlen($jwtSecret) . '. ' .
                'Please generate a secure secret using: openssl rand -hex 32'
            );
        }
    }
}
