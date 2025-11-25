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
        $jwtSecret = $_ENV['JWT_SECRET'] ?? getenv('JWT_SECRET') ?? null;
        
        if (empty($jwtSecret) || $jwtSecret === '') {
            throw new \InvalidArgumentException('JWT_SECRET environment variable is not set or is empty. Please configure it in your .env file.');
        }
    }
}