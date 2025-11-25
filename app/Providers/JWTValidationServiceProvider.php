<?php

declare(strict_types=1);

namespace App\Providers;

use Hyperf\Support\ServiceProvider;

class JWTValidationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->validateJWTSecret();
    }

    public function register(): void
    {
    }

    private function validateJWTSecret(): void
    {
        $jwtSecret = $_ENV['JWT_SECRET'] ?? null;
        
        // In production environment, JWT_SECRET must be set and not empty
        $appEnv = $_ENV['APP_ENV'] ?? 'production';
        if ($appEnv === 'production' && (empty($jwtSecret) || $jwtSecret === '')) {
            throw new \RuntimeException('JWT_SECRET must be configured in production environment for security reasons.');
        }
        
        // Additional check: ensure the secret is not the default placeholder
        if ($jwtSecret === 'default_secret_key_for_testing') {
            throw new \RuntimeException('JWT_SECRET cannot use default testing key in production environment.');
        }
    }
}