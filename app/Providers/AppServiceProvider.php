<?php

declare(strict_types=1);

namespace App\Providers;

class AppServiceProvider
{
    public function boot(): void
    {
        // Validate JWT_SECRET is not empty in production
        $this->validateJwtSecret();
    }

    public function register(): void
    {
    }
    
    /**
     * Validate that JWT_SECRET is set and not empty in production environment
     */
    private function validateJwtSecret(): void
    {
        $env = $_ENV['APP_ENV'] ?? $_SERVER['APP_ENV'] ?? 'production';
        $jwtSecret = $_ENV['JWT_SECRET'] ?? $_SERVER['JWT_SECRET'] ?? '';
        
        if ($env === 'production' && (empty($jwtSecret) || $jwtSecret === 'b759622f76ff2cd8098768e41a58eab2de4db374adba74a126c52cb84ee3502f')) {
            // If JWT_SECRET is empty or still using the example value, throw an exception
            if (empty($jwtSecret)) {
                throw new \RuntimeException('JWT_SECRET is not set in .env file. Please set a secure JWT secret before deploying to production.');
            }
            
            if ($jwtSecret === 'b759622f76ff2cd8098768e41a58eab2de4db374adba74a126c52cb84ee3502f') {
                throw new \RuntimeException('JWT_SECRET is using the default example value. Please set a unique secure JWT secret before deploying to production.');
            }
        }
    }
}
