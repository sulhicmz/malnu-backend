<?php

declare(strict_types=1);

namespace App\Providers;

use Hyperf\Support\ServiceProvider;
use RuntimeException;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->validateAppKey();
        $this->validateAppEnvironment();
        $this->validateDatabaseConfiguration();
        $this->validateCacheConfiguration();
        $this->validateJwtSecret();
        $this->validateJwtTtl();
    }

    public function register(): void
    {
    }

    private function validateAppKey(): void
    {
        $env = env('APP_ENV', 'local');
        $appKey = env('APP_KEY', '');

        if (in_array($env, ['local', 'testing'])) {
            return;
        }

        if (empty($appKey)) {
            throw new RuntimeException(
                'APP_KEY cannot be empty in production environment. '
                . 'Generate a secure key using: php artisan key:generate'
            );
        }

        $placeholders = [
            'your-app-key-here',
            'change-me',
            'your-app-key',
            'app-key',
            'some-random-string',
            'base64:',
            'base64:',
        ];

        foreach ($placeholders as $placeholder) {
            if (str_starts_with($appKey, $placeholder) || $appKey === $placeholder) {
                throw new RuntimeException(
                    'APP_KEY is using a placeholder value which is insecure. '
                    . 'Generate a secure key using: php artisan key:generate'
                );
            }
        }

        if (strlen($appKey) < 32) {
            throw new RuntimeException(
                'APP_KEY must be at least 32 characters long for security. '
                . 'Current length: ' . strlen($appKey) . ' characters. '
                . 'Generate a secure key using: php artisan key:generate'
            );
        }
    }

    private function validateAppEnvironment(): void
    {
        $env = env('APP_ENV', 'production');
        $validEnvironments = ['local', 'testing', 'staging', 'production'];

        if (! in_array($env, $validEnvironments)) {
            throw new RuntimeException(
                "APP_ENV environment variable has an invalid value: '{$env}'. "
                . 'Valid values are: ' . implode(', ', $validEnvironments) . '. '
                . 'Please set APP_ENV in your .env file.'
            );
        }
    }

    private function validateDatabaseConfiguration(): void
    {
        $dbConnection = env('DB_CONNECTION', 'mysql');
        $validDrivers = ['mysql', 'pgsql', 'postgres', 'sqlite'];

        if (! in_array($dbConnection, $validDrivers)) {
            throw new RuntimeException(
                "DB_CONNECTION environment variable has an invalid value: '{$dbConnection}'. "
                . 'Valid values are: ' . implode(', ', $validDrivers) . '. '
                . 'Please set DB_CONNECTION in your .env file.'
            );
        }

        if ($dbConnection !== 'sqlite') {
            $dbHost = env('DB_HOST');
            $dbPort = env('DB_PORT');
            $dbDatabase = env('DB_DATABASE');
            $dbUsername = env('DB_USERNAME');

            $missingVars = [];
            if (empty($dbHost)) {
                $missingVars[] = 'DB_HOST';
            }
            if (empty($dbPort)) {
                $missingVars[] = 'DB_PORT';
            }
            if (empty($dbDatabase)) {
                $missingVars[] = 'DB_DATABASE';
            }
            if (empty($dbUsername)) {
                $missingVars[] = 'DB_USERNAME';
            }

            if (! empty($missingVars)) {
                throw new RuntimeException(
                    "Required database configuration variables are missing for {$dbConnection}: "
                    . implode(', ', $missingVars) . '. '
                    . 'Please set these variables in your .env file.'
                );
            }
        }
    }

    private function validateCacheConfiguration(): void
    {
        $cacheDriver = env('CACHE_DRIVER', 'file');
        $validDrivers = ['file', 'redis', 'memcached', 'database', 'array'];

        if (! in_array($cacheDriver, $validDrivers)) {
            throw new RuntimeException(
                "CACHE_DRIVER environment variable has an invalid value: '{$cacheDriver}'. "
                . 'Valid values are: ' . implode(', ', $validDrivers) . '. '
                . 'Please set CACHE_DRIVER in your .env file.'
            );
        }
    }

    private function validateJwtSecret(): void
    {
        $env = env('APP_ENV', 'local');
        $jwtSecret = env('JWT_SECRET', '');

        if (in_array($env, ['local', 'testing'])) {
            return;
        }

        if (empty($jwtSecret)) {
            throw new RuntimeException(
                'JWT_SECRET cannot be empty in production environment. '
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
            'your-secure-jwt-secret-key-here',
        ];

        if (in_array(strtolower($jwtSecret), $placeholders)) {
            throw new RuntimeException(
                'JWT_SECRET is using a placeholder value which is insecure. '
                . 'Generate a secure secret using: openssl rand -hex 32'
            );
        }

        if (strlen($jwtSecret) < 32) {
            throw new RuntimeException(
                'JWT_SECRET must be at least 32 characters long for security. '
                . 'Current length: ' . strlen($jwtSecret) . ' characters. '
                . 'Generate a secure secret using: openssl rand -hex 32'
            );
        }
    }

    private function validateJwtTtl(): void
    {
        $jwtTtl = env('JWT_TTL', 30);
        $jwtRefreshTtl = env('JWT_REFRESH_TTL', 1440);

        if (! is_numeric($jwtTtl) || (int) $jwtTtl <= 0) {
            throw new RuntimeException(
                "JWT_TTL must be a positive integer. Current value: '{$jwtTtl}'. "
                . 'Please set a valid JWT_TTL in your .env file (e.g., 30 for 30 minutes).'
            );
        }

        if (! is_numeric($jwtRefreshTtl) || (int) $jwtRefreshTtl <= 0) {
            throw new RuntimeException(
                "JWT_REFRESH_TTL must be a positive integer. Current value: '{$jwtRefreshTtl}'. "
                . 'Please set a valid JWT_REFRESH_TTL in your .env file (e.g., 1440 for 24 hours).'
            );
        }

        if ((int) $jwtRefreshTtl <= (int) $jwtTtl) {
            throw new RuntimeException(
                'JWT_REFRESH_TTL must be greater than JWT_TTL. '
                . "Current values: JWT_TTL={$jwtTtl}, JWT_REFRESH_TTL={$jwtRefreshTtl}. "
                . 'Please adjust these values in your .env file.'
            );
        }
    }
}
