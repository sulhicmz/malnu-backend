<?php

declare(strict_types=1);

namespace App\Providers;

use Hyperf\Support\ServiceProvider;
use RuntimeException;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->validateEnvironment();
        $this->validateAppKey();
        $this->validateJwtSecret();
        $this->validateDatabaseConfig();
        $this->validateRedisConfig();
        $this->validateFrontendUrl();
    }

    public function register(): void
    {
    }

    private function validateEnvironment(): void
    {
        $env = env('APP_ENV', 'local');
        $validEnvironments = ['local', 'testing', 'production', 'staging'];

        if (!in_array($env, $validEnvironments)) {
            throw new RuntimeException(
                "APP_ENV value '{$env}' is invalid. Must be one of: " . implode(', ', $validEnvironments)
            );
        }
    }

    private function validateAppKey(): void
    {
        $env = env('APP_ENV', 'local');

        if (in_array($env, ['local', 'testing'])) {
            return;
        }

        $appKey = env('APP_KEY', '');

        if (empty($appKey)) {
            throw new RuntimeException(
                'APP_KEY cannot be empty in production environment. '
                . 'Generate a secure key using: php artisan key:generate'
            );
        }

        if (strlen($appKey) < 32) {
            throw new RuntimeException(
                'APP_KEY must be at least 32 characters long for security. '
                . 'Current length: ' . strlen($appKey) . ' characters. '
                . 'Generate a secure key using: php artisan key:generate'
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

    private function validateDatabaseConfig(): void
    {
        $connection = env('DB_CONNECTION', 'mysql');

        if ($connection === 'sqlite') {
            return;
        }

        $requiredFields = [
            'DB_HOST' => env('DB_HOST'),
            'DB_DATABASE' => env('DB_DATABASE'),
            'DB_USERNAME' => env('DB_USERNAME'),
        ];

        $missingFields = [];
        foreach ($requiredFields as $field => $value) {
            if (empty($value)) {
                $missingFields[] = $field;
            }
        }

        if (!empty($missingFields)) {
            throw new RuntimeException(
                'Database configuration is incomplete. Missing required fields: ' . implode(', ', $missingFields) .
                ' for DB_CONNECTION=' . $connection
            );
        }
    }

    private function validateRedisConfig(): void
    {
        $env = env('APP_ENV', 'local');
        $cacheDriver = env('CACHE_DRIVER', 'redis');
        $sessionDriver = env('SESSION_DRIVER', 'database');
        $queueDriver = env('QUEUE_CONNECTION', 'database');
        $rateLimitDriver = env('RATE_LIMIT_DRIVER', 'redis');

        $usesRedis = in_array($cacheDriver, ['redis']) ||
            in_array($sessionDriver, ['redis']) ||
            in_array($queueDriver, ['redis']) ||
            in_array($rateLimitDriver, ['redis']);

        if (!$usesRedis) {
            return;
        }

        $requiredFields = [
            'REDIS_HOST' => env('REDIS_HOST'),
            'REDIS_PORT' => env('REDIS_PORT'),
        ];

        $missingFields = [];
        foreach ($requiredFields as $field => $value) {
            if (empty($value)) {
                $missingFields[] = $field;
            }
        }

        if (!empty($missingFields)) {
            $error = 'Redis configuration is incomplete. Missing required fields: ' . implode(', ', $missingFields) .
                '. Redis is required by: ';

            $redisUsers = [];
            if ($cacheDriver === 'redis') {
                $redisUsers[] = 'CACHE_DRIVER';
            }
            if ($sessionDriver === 'redis') {
                $redisUsers[] = 'SESSION_DRIVER';
            }
            if ($queueDriver === 'redis') {
                $redisUsers[] = 'QUEUE_CONNECTION';
            }
            if ($rateLimitDriver === 'redis') {
                $redisUsers[] = 'RATE_LIMIT_DRIVER';
            }

            $error .= implode(', ', $redisUsers);

            throw new RuntimeException($error);
        }

        $redisPort = env('REDIS_PORT');
        if (!is_numeric($redisPort) || $redisPort < 1 || $redisPort > 65535) {
            throw new RuntimeException(
                'REDIS_PORT must be a valid port number between 1 and 65535. Current value: ' . $redisPort
            );
        }
    }

    private function validateFrontendUrl(): void
    {
        $frontendUrl = env('FRONTEND_URL');

        if (empty($frontendUrl)) {
            return;
        }

        if (!filter_var($frontendUrl, FILTER_VALIDATE_URL)) {
            throw new RuntimeException(
                'FRONTEND_URL must be a valid URL. Current value: ' . $frontendUrl
            );
        }

        $parsed = parse_url($frontendUrl);
        if (empty($parsed['scheme']) || !in_array($parsed['scheme'], ['http', 'https'])) {
            throw new RuntimeException(
                'FRONTEND_URL must use http or https protocol. Current value: ' . $frontendUrl
            );
        }
    }
}
