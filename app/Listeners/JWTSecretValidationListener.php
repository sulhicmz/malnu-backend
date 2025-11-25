<?php

declare(strict_types=1);

namespace App\Listeners;

class JWTSecretValidationListener
{
    public function listen(): array
    {
        // Listen to application boot event - using a generic approach
        return [
            'BootApplication',
        ];
    }

    public function process(object $event): void
    {
        $jwtSecret = $_ENV['JWT_SECRET'] ?? null;
        $appEnv = $_ENV['APP_ENV'] ?? 'production';
        
        // Validate JWT_SECRET is not empty in production
        if ($appEnv === 'production' && (empty($jwtSecret) || $jwtSecret === 'df6d0298690b99308d618d0362675ce0935cf223e39a70d91805524167ffcfb5')) {
            error_log('CRITICAL: JWT_SECRET is not properly configured. Please set a secure JWT_SECRET in your .env file.');
            throw new \RuntimeException('CRITICAL: JWT_SECRET is not properly configured. Please set a secure JWT_SECRET in your .env file.');
        }
    }
}