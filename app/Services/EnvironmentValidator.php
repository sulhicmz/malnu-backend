<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;

class EnvironmentValidator
{
    private array $errors = [];
    private array $warnings = [];

    public function validate(): void
    {
        $this->errors = [];
        $this->warnings = [];

        $this->validateRequiredVariables();
        $this->validateOptionalVariables();
        $this->validateTypeSpecificRules();

        $this->report();
    }

    private function validateRequiredVariables(): void
    {
        $this->validateAppKey();
        $this->validateJwtSecret();
    }

    private function validateOptionalVariables(): void
    {
        $this->validateAppEnv();
        $this->validateAppDebug();
        $this->validateDatabaseConfig();
        $this->validateRedisConfig();
    }

    private function validateTypeSpecificRules(): void
    {
        $this->validateIntegerVariables();
        $this->validateBooleanVariables();
        $this->validateUrlVariables();
        $this->validatePortVariables();
    }

    private function validateAppKey(): void
    {
        $key = config('app.key');

        if ($key === null || $key === '') {
            $this->errors[] = 'APP_KEY is required for encryption. Generate one using: php artisan key:generate';
            return;
        }

        if (strlen($key) < 32) {
            $this->errors[] = 'APP_KEY must be at least 32 characters for secure encryption. Current length: ' . strlen($key);
        }
    }

    private function validateJwtSecret(): void
    {
        $secret = config('jwt.secret');

        if ($secret === null || $secret === '') {
            $this->errors[] = 'JWT_SECRET is required for JWT token signing. Generate one using: openssl rand -hex 32';
            return;
        }

        if ($secret === 'your-secret-key-here' || $secret === 'secret') {
            $this->errors[] = 'JWT_SECRET is using a placeholder value. Please generate a secure secret using: openssl rand -hex 32';
        }

        if (strlen($secret) < 32) {
            $this->errors[] = 'JWT_SECRET must be at least 32 characters for secure signing. Current length: ' . strlen($secret);
        }
    }

    private function validateAppEnv(): void
    {
        $env = config('app.env');

        if ($env !== null && !in_array($env, ['local', 'production', 'testing'], true)) {
            $this->errors[] = "APP_ENV must be one of: local, production, testing. Current value: {$env}";
        }
    }

    private function validateAppDebug(): void
    {
        $debug = config('app.debug');

        if ($debug !== null && !$this->isBoolean($debug)) {
            $this->errors[] = "APP_DEBUG must be a boolean value (true or false). Current value: {$debug}";
        }

        if ($this->isBooleanTrue($debug) && $this->isProduction()) {
            $this->warnings[] = 'APP_DEBUG is enabled in production environment. This is a security risk.';
        }
    }

    private function validateDatabaseConfig(): void
    {
        if (!$this->isProduction()) {
            return;
        }

        $connection = config('database.default', 'sqlite');

        if ($connection === 'sqlite') {
            return;
        }

        $host = config('database.connections.mysql.host');
        $database = config('database.connections.mysql.database');

        if ($host === null || $host === '') {
            $this->errors[] = 'DB_HOST is required in production when not using SQLite';
        }

        if ($database === null || $database === '') {
            $this->errors[] = 'DB_DATABASE is required in production';
        }
    }

    private function validateRedisConfig(): void
    {
        $blacklistEnabled = config('jwt.blacklist_enabled', true);

        if (!$this->isBooleanTrue($blacklistEnabled)) {
            return;
        }

        $host = config('redis.default.host');
        $port = config('redis.default.port');

        if ($host === null || $host === '') {
            $this->errors[] = 'REDIS_HOST is required when JWT_BLACKLIST_ENABLED is true';
        }

        if ($port === null || $port === '') {
            $this->errors[] = 'REDIS_PORT is required when JWT_BLACKLIST_ENABLED is true';
        }
    }

    private function validateIntegerVariables(): void
    {
        $intVariables = [
            'JWT_TTL' => ['config' => 'jwt.ttl', 'min' => 1, 'default' => 30],
            'JWT_REFRESH_TTL' => ['config' => 'jwt.refresh_ttl', 'min' => 1, 'default' => 1440],
            'SESSION_LIFETIME' => ['config' => 'session.lifetime', 'min' => 1, 'default' => 120],
        ];

        foreach ($intVariables as $var => $config) {
            $value = config($config['config']);

            if ($value !== null && !$this->isInteger($value)) {
                $this->errors[] = "{$var} must be an integer. Current value: {$value}";
            } elseif ($value !== null && (int) $value < $config['min']) {
                $this->errors[] = "{$var} must be at least {$config['min']}. Current value: {$value}";
            }
        }
    }

    private function validateBooleanVariables(): void
    {
        $boolVariables = [
            'JWT_BLACKLIST_ENABLED' => 'jwt.blacklist_enabled',
            'SESSION_ENCRYPT' => 'session.encrypt',
            'SECURITY_HEADERS_ENABLED' => 'security.headers.enabled',
            'CSP_ENABLED' => 'security.csp.enabled',
            'HSTS_ENABLED' => 'security.hsts.enabled',
            'HSTS_INCLUDE_SUBDOMAINS' => 'security.hsts.include_subdomains',
            'HSTS_PRELOAD' => 'security.hsts.preload',
        ];

        foreach ($boolVariables as $var => $configKey) {
            $value = config($configKey);

            if ($value !== null && !$this->isBoolean($value)) {
                $this->errors[] = "{$var} must be a boolean value (true or false). Current value: {$value}";
            }
        }
    }

    private function validateUrlVariables(): void
    {
        $urlVariables = [
            'APP_URL' => 'app.url',
            'FRONTEND_URL' => 'frontend.url',
            'MAIL_FROM_ADDRESS' => 'mail.from.address',
        ];

        foreach ($urlVariables as $var => $configKey) {
            $value = config($configKey);

            if ($value === null || $value === '') {
                continue;
            }

            if ($var === 'MAIL_FROM_ADDRESS') {
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->errors[] = "{$var} must be a valid email address. Current value: {$value}";
                }
            } else {
                if (!filter_var($value, FILTER_VALIDATE_URL)) {
                    $this->errors[] = "{$var} must be a valid URL. Current value: {$value}";
                }
            }
        }
    }

    private function validatePortVariables(): void
    {
        $portVariables = [
            'DB_PORT' => ['config' => 'database.connections.mysql.port', 'required' => false],
            'REDIS_PORT' => ['config' => 'redis.default.port', 'required' => true],
            'MAIL_PORT' => ['config' => 'mail.port', 'required' => false],
        ];

        foreach ($portVariables as $var => $config) {
            $value = config($config['config']);

            if ($value === null || $value === '') {
                if ($config['required']) {
                    $this->errors[] = "{$var} is required";
                }
                continue;
            }

            if (!$this->isInteger($value)) {
                $this->errors[] = "{$var} must be an integer. Current value: {$value}";
            } elseif ((int) $value < 1 || (int) $value > 65535) {
                $this->errors[] = "{$var} must be between 1 and 65535. Current value: {$value}";
            }
        }
    }

    private function isProduction(): bool
    {
        return env('APP_ENV') === 'production';
    }

    private function isBoolean($value): bool
    {
        return in_array(strtolower((string) $value), ['true', 'false', '1', '0', 'yes', 'no'], true);
    }

    private function isBooleanTrue($value): bool
    {
        return in_array(strtolower((string) $value), ['true', '1', 'yes'], true);
    }

    private function isInteger($value): bool
    {
        return is_numeric($value) && (int) $value == $value;
    }

    private function report(): void
    {
        if (empty($this->errors) && empty($this->warnings)) {
            return;
        }

        $messages = [];

        if (!empty($this->errors)) {
            $messages[] = "\n🔴 Environment Validation Errors:";
            foreach ($this->errors as $error) {
                $messages[] = "  - {$error}";
            }
        }

        if (!empty($this->warnings)) {
            $messages[] = "\n🟡 Environment Validation Warnings:";
            foreach ($this->warnings as $warning) {
                $messages[] = "  - {$warning}";
            }
        }

        $message = implode("\n", $messages);

        if (!empty($this->errors)) {
            throw new RuntimeException($message);
        }

        if (!empty($this->warnings)) {
            error_log($message);
        }
    }
}