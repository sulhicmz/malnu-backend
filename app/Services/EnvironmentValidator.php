<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;

class EnvironmentValidator
{
    private array $errors = [];

    private array $placeholders = [
        'your-secret-key-here',
        'change-me',
        'your-jwt-secret',
        'jwt-secret-key',
        'secret',
        'password',
        'your-secure-jwt-secret-key-here',
        'your-app-key-here',
        'base64:',
    ];

    private array $validEnvironments = ['local', 'testing', 'staging', 'production'];

    private array $validDbDrivers = ['mysql', 'pgsql', 'postgres', 'sqlite'];

    private array $validCacheDrivers = ['file', 'redis', 'memcached', 'database', 'array'];

    public function validateAll(): void
    {
        if (! $this->shouldValidate()) {
            return;
        }

        $this->validateAppEnvironment();
        $this->validateAppKey();
        $this->validateJwtSecret();
        $this->validateJwtTtl();
        $this->validateDatabaseConfiguration();
        $this->validateCacheConfiguration();
        $this->validateAppDebug();
        $this->validateJwtBlacklist();
        $this->validateUrls();

        if (! empty($this->errors)) {
            throw new RuntimeException(
                "Environment validation failed:\n\n".implode("\n", $this->errors)
            );
        }
    }

    private function shouldValidate(): bool
    {
        $env = env('APP_ENV', 'local');
        $validationEnabled = env('ENV_VALIDATION_ENABLED', 'true');

        if ($env === 'testing') {
            return false;
        }

        return filter_var($validationEnabled, FILTER_VALIDATE_BOOLEAN);
    }

    private function addError(string $error): void
    {
        $this->errors[] = '- '.$error;
    }

    private function isProduction(): bool
    {
        return env('APP_ENV', 'local') === 'production';
    }

    private function validateAppEnvironment(): void
    {
        $env = env('APP_ENV', 'production');

        if (! in_array($env, $this->validEnvironments)) {
            $this->addError(
                "APP_ENV has invalid value '{$env}'. Valid values: ".
                implode(', ', $this->validEnvironments)
            );
        }
    }

    private function validateAppKey(): void
    {
        if (! $this->isProduction()) {
            return;
        }

        $appKey = env('APP_KEY', '');

        if (empty($appKey)) {
            $this->addError(
                'APP_KEY cannot be empty in production. Generate using: php artisan key:generate'
            );

            return;
        }

        foreach ($this->placeholders as $placeholder) {
            if (str_starts_with($appKey, $placeholder) || $appKey === $placeholder) {
                $this->addError(
                    'APP_KEY is using a placeholder value. Generate using: php artisan key:generate'
                );

                return;
            }
        }

        if (strlen($appKey) < 32) {
            $this->addError(
                'APP_KEY must be at least 32 characters. Current: '.strlen($appKey).' chars'
            );
        }
    }

    private function validateJwtSecret(): void
    {
        if (! $this->isProduction()) {
            return;
        }

        $jwtSecret = env('JWT_SECRET', '');

        if (empty($jwtSecret)) {
            $this->addError(
                'JWT_SECRET cannot be empty in production. Generate using: openssl rand -hex 32'
            );

            return;
        }

        foreach ($this->placeholders as $placeholder) {
            if (str_starts_with(strtolower($jwtSecret), $placeholder) ||
                strtolower($jwtSecret) === $placeholder) {
                $this->addError(
                    'JWT_SECRET is using a placeholder value. Generate using: openssl rand -hex 32'
                );

                return;
            }
        }

        if (strlen($jwtSecret) < 32) {
            $this->addError(
                'JWT_SECRET must be at least 32 characters. Current: '.strlen($jwtSecret).' chars'
            );
        }
    }

    private function validateJwtTtl(): void
    {
        $jwtTtl = env('JWT_TTL', 30);
        $jwtRefreshTtl = env('JWT_REFRESH_TTL', 1440);

        if (! is_numeric($jwtTtl) || (int) $jwtTtl <= 0) {
            $this->addError(
                "JWT_TTL must be a positive integer. Current: '{$jwtTtl}'"
            );
        }

        if (! is_numeric($jwtRefreshTtl) || (int) $jwtRefreshTtl <= 0) {
            $this->addError(
                "JWT_REFRESH_TTL must be a positive integer. Current: '{$jwtRefreshTtl}'"
            );
        }

        if (is_numeric($jwtTtl) && is_numeric($jwtRefreshTtl) &&
            (int) $jwtRefreshTtl <= (int) $jwtTtl) {
            $this->addError(
                "JWT_REFRESH_TTL ({$jwtRefreshTtl}) must be greater than JWT_TTL ({$jwtTtl})"
            );
        }
    }

    private function validateDatabaseConfiguration(): void
    {
        $dbConnection = env('DB_CONNECTION', 'mysql');

        if (! in_array($dbConnection, $this->validDbDrivers)) {
            $this->addError(
                "DB_CONNECTION has invalid value '{$dbConnection}'. Valid: ".
                implode(', ', $this->validDbDrivers)
            );

            return;
        }

        if ($dbConnection !== 'sqlite') {
            $required = ['DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME'];
            $missing = [];

            foreach ($required as $var) {
                if (empty(env($var))) {
                    $missing[] = $var;
                }
            }

            if (! empty($missing)) {
                $this->addError(
                    "Missing database vars for {$dbConnection}: ".implode(', ', $missing)
                );
            }
        }
    }

    private function validateCacheConfiguration(): void
    {
        $cacheDriver = env('CACHE_DRIVER', 'file');

        if (! in_array($cacheDriver, $this->validCacheDrivers)) {
            $this->addError(
                "CACHE_DRIVER has invalid value '{$cacheDriver}'. Valid: ".
                implode(', ', $this->validCacheDrivers)
            );
        }
    }

    private function validateAppDebug(): void
    {
        $appDebug = env('APP_DEBUG', 'false');

        if ($this->isProduction() && filter_var($appDebug, FILTER_VALIDATE_BOOLEAN)) {
            $this->addError(
                'APP_DEBUG is enabled in production. This is a security risk. Set APP_DEBUG=false'
            );
        }
    }

    private function validateJwtBlacklist(): void
    {
        $blacklistEnabled = env('JWT_BLACKLIST_ENABLED', 'false');

        if (! filter_var($blacklistEnabled, FILTER_VALIDATE_BOOLEAN)) {
            return;
        }

        $required = ['REDIS_HOST', 'REDIS_PORT'];
        $missing = [];

        foreach ($required as $var) {
            if (empty(env($var))) {
                $missing[] = $var;
            }
        }

        if (! empty($missing)) {
            $this->addError(
                'JWT_BLACKLIST_ENABLED is true but required Redis vars missing: '.implode(', ', $missing)
            );
        }
    }

    private function validateUrls(): void
    {
        $urls = [
            'APP_URL' => env('APP_URL'),
            'FRONTEND_URL' => env('FRONTEND_URL'),
        ];

        foreach ($urls as $var => $url) {
            if ($url && ! filter_var($url, FILTER_VALIDATE_URL)) {
                $this->addError("{$var} must be a valid URL. Current: '{$url}'");
            }
        }
    }
}
