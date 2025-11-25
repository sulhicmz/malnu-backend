<?php

declare(strict_types=1);

namespace App\Providers;

use Hyperf\Support\ServiceProvider;

class EnvironmentValidationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->validateJwtSecret();
    }

    private function validateJwtSecret(): void
    {
        $jwtSecret = $this->getEnvValue('JWT_SECRET');
        
        if (empty($jwtSecret)) {
            $envFile = $this->getEnvironmentFilePath();
            throw new \RuntimeException(
                "JWT_SECRET is not set in environment variables. " .
                "Please set JWT_SECRET in your {$envFile} file. " .
                "Use a secure random string of at least 32 bytes."
            );
        }

        // Additional validation: ensure the secret is not the example/placeholder value
        if ($jwtSecret === '458c26ec4d6e712f5d5ae3e55ae6508ec59c40e7604e5457386b5cac6ee34568') {
            throw new \RuntimeException(
                "JWT_SECRET is set to the default example value. " .
                "This is insecure for production use. Please set a unique JWT_SECRET in your .env file."
            );
        }

        // Validate minimum length for security
        if (strlen($jwtSecret) < 32) {
            throw new \RuntimeException(
                "JWT_SECRET must be at least 32 characters long for security. " .
                "Current length: " . strlen($jwtSecret)
            );
        }
    }

    private function getEnvValue(string $key, $default = null)
    {
        // Try multiple methods to get environment value
        $value = $_ENV[$key] ?? null;
        if ($value !== null) {
            return $value;
        }
        
        $value = $_SERVER[$key] ?? null;
        if ($value !== null) {
            return $value;
        }
        
        $value = getenv($key);
        if ($value !== false) {
            return $value;
        }
        
        return $default;
    }

    private function getEnvironmentFilePath(): string
    {
        $envPath = dirname(__DIR__, 2) . '/.env';
        return file_exists($envPath) ? '.env' : '.env.example';
    }

    public function register(): void
    {
        // No registration needed
    }
}