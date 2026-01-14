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
        $env = env('APP_ENV', 'local');
        $enabled = env('ENV_VALIDATION_ENABLED', 'true');

        if ($enabled !== 'true' && $enabled !== true) {
            return;
        }

        if (in_array($env, ['testing'])) {
            return;
        }

        $this->validateRequiredVariables();
        $this->validateAppEnv();
        $this->validateAppKey();
        $this->validateJwtSecret();
        $this->validateBooleanVariables();
        $this->validateIntegerVariables();
        $this->validateUrlVariables();
        $this->validateEmailVariables();
        $this->validateDatabaseConfiguration($env);
        $this->validateRedisConfiguration();

        if (!empty($this->errors)) {
            $this->throwValidationException();
        }

        if (!empty($this->warnings)) {
            $this->logWarnings();
        }
    }

    private function validateRequiredVariables(): void
    {
        $required = ['APP_KEY', 'JWT_SECRET'];

        foreach ($required as $var) {
            $value = env($var);
            if ($value === null || $value === '') {
                $this->errors[$var] = "Required environment variable {$var} is not set.";
            }
        }
    }

    private function validateAppEnv(): void
    {
        $appEnv = env('APP_ENV', 'local');
        $validEnvs = ['local', 'production', 'testing'];

        if (!in_array($appEnv, $validEnvs)) {
            $this->errors['APP_ENV'] = "APP_ENV must be one of: " . implode(', ', $validEnvs) . ". Current value: {$appEnv}";
        }
    }

    private function validateAppKey(): void
    {
        $appKey = env('APP_KEY', '');
        $env = env('APP_ENV', 'local');

        if ($env === 'production' && strlen($appKey) < 32) {
            $this->errors['APP_KEY'] = "APP_KEY must be at least 32 characters in production. Current length: " . strlen($appKey) . " characters. Generate a secure key using: php artisan key:generate";
        }

        if (strlen($appKey) < 16) {
            $this->warnings['APP_KEY'] = "APP_KEY is shorter than recommended (16 characters). Current length: " . strlen($appKey) . " characters";
        }
    }

    private function validateJwtSecret(): void
    {
        $jwtSecret = env('JWT_SECRET', '');
        $env = env('APP_ENV', 'local');

        $placeholders = [
            'your-secret-key-here',
            'change-me',
            'your-jwt-secret',
            'jwt-secret-key',
            'secret',
            'password',
            'your-secure-jwt-secret-key-here',
        ];

        if (in_array(strtolower($jwtSecret), array_map('strtolower', $placeholders))) {
            $this->errors['JWT_SECRET'] = "JWT_SECRET is using a placeholder value which is insecure. Generate a secure secret using: openssl rand -hex 32";
        }

        if ($env === 'production' && strlen($jwtSecret) < 32) {
            $this->errors['JWT_SECRET'] = "JWT_SECRET must be at least 32 characters in production. Current length: " . strlen($jwtSecret) . " characters. Generate a secure secret using: openssl rand -hex 32";
        }

        if (strlen($jwtSecret) > 0 && strlen($jwtSecret) < 16) {
            $this->warnings['JWT_SECRET'] = "JWT_SECRET is shorter than recommended (16 characters). Current length: " . strlen($jwtSecret) . " characters";
        }
    }

    private function validateBooleanVariables(): void
    {
        $booleanVars = ['APP_DEBUG', 'JWT_BLACKLIST_ENABLED', 'REQUEST_LOGGING_ENABLED'];

        foreach ($booleanVars as $var) {
            $value = env($var);

            if ($value === null) {
                continue;
            }

            if (!in_array(strtolower($value), ['true', 'false', '1', '0', ''])) {
                $this->errors[$var] = "{$var} must be a boolean value (true/false, 1/0). Current value: {$value}";
            }
        }

        $appDebug = env('APP_DEBUG', 'false');
        $env = env('APP_ENV', 'local');

        if ($env === 'production' && in_array(strtolower($appDebug), ['true', '1'])) {
            $this->warnings['APP_DEBUG'] = "APP_DEBUG is enabled in production environment. This is a security risk and should be disabled.";
        }
    }

    private function validateIntegerVariables(): void
    {
        $integerVars = [
            'JWT_TTL' => ['min' => 1, 'max' => 1440],
            'JWT_REFRESH_TTL' => ['min' => 60, 'max' => 43200],
            'SESSION_LIFETIME' => ['min' => 5, 'max' => 10080],
            'REDIS_PORT' => ['min' => 1, 'max' => 65535],
            'DB_PORT' => ['min' => 1, 'max' => 65535],
            'MAIL_PORT' => ['min' => 1, 'max' => 65535],
        ];

        foreach ($integerVars as $var => $rules) {
            $value = env($var);

            if ($value === null || $value === '') {
                continue;
            }

            if (!is_numeric($value)) {
                $this->errors[$var] = "{$var} must be a numeric value. Current value: {$value}";
                continue;
            }

            $intValue = (int)$value;

            if ($intValue < $rules['min']) {
                $this->errors[$var] = "{$var} must be at least {$rules['min']}. Current value: {$intValue}";
            }

            if ($intValue > $rules['max']) {
                $this->errors[$var] = "{$var} must be at most {$rules['max']}. Current value: {$intValue}";
            }
        }
    }

    private function validateUrlVariables(): void
    {
        $urlVars = ['FRONTEND_URL'];

        foreach ($urlVars as $var) {
            $value = env($var);

            if ($value === null || $value === '') {
                continue;
            }

            if (!filter_var($value, FILTER_VALIDATE_URL)) {
                $this->errors[$var] = "{$var} must be a valid URL. Current value: {$value}";
            }
        }
    }

    private function validateEmailVariables(): void
    {
        $emailVars = ['MAIL_FROM_ADDRESS'];

        foreach ($emailVars as $var) {
            $value = env($var);

            if ($value === null || $value === '') {
                continue;
            }

            if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $this->errors[$var] = "{$var} must be a valid email address. Current value: {$value}";
            }
        }
    }

    private function validateDatabaseConfiguration(string $env): void
    {
        $dbConnection = env('DB_CONNECTION', 'mysql');

        if ($dbConnection === 'sqlite') {
            return;
        }

        $requiredVars = ['DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME'];

        foreach ($requiredVars as $var) {
            $value = env($var);

            if ($value === null || $value === '') {
                $this->errors[$var] = "{$var} is required for {$dbConnection} database connection in {$env} environment.";
            }
        }
    }

    private function validateRedisConfiguration(): void
    {
        $jwtBlacklistEnabled = env('JWT_BLACKLIST_ENABLED', 'false');

        if (!in_array(strtolower($jwtBlacklistEnabled), ['true', '1'])) {
            return;
        }

        $requiredVars = ['REDIS_HOST', 'REDIS_PORT'];

        foreach ($requiredVars as $var) {
            $value = env($var);

            if ($value === null || $value === '') {
                $this->errors[$var] = "{$var} is required when JWT_BLACKLIST_ENABLED is true.";
            }
        }
    }

    private function throwValidationException(): void
    {
        $message = "Environment validation failed:\n\n";

        foreach ($this->errors as $var => $error) {
            $message .= "- {$var}: {$error}\n";
        }

        if (!empty($this->warnings)) {
            $message .= "\nWarnings:\n";
            foreach ($this->warnings as $var => $warning) {
                $message .= "- {$var}: {$warning}\n";
            }
        }

        throw new RuntimeException($message);
    }

    private function logWarnings(): void
    {
        foreach ($this->warnings as $var => $warning) {
            error_log("[Environment Validation Warning] {$var}: {$warning}");
        }
    }
}
