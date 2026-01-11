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

        if (! empty($this->errors)) {
            $message = "Environment validation failed:\n\n";
            foreach ($this->errors as $error) {
                $message .= "  ❌ {$error}\n";
            }

            if (! empty($this->warnings)) {
                $message .= "\nWarnings:\n";
                foreach ($this->warnings as $warning) {
                    $message .= "  ⚠️  {$warning}\n";
                }
            }

            throw new RuntimeException($message);
        }

        if (! empty($this->warnings)) {
            foreach ($this->warnings as $warning) {
                echo "⚠️  {$warning}\n";
            }
        }
    }

    private function validateRequiredVariables(): void
    {
        $appEnv = $_ENV['APP_ENV'] ?? 'production';
        $testing = $appEnv === 'testing';

        $this->validateRequired('APP_KEY', fn ($value) => $this->validateAppKey($value, $testing));
        $this->validateOptional('APP_ENV', fn ($value) => $this->validateAppEnv($value), false);

        if (! $testing) {
            $this->validateRequired('JWT_SECRET', fn ($value) => $this->validateJwtSecret($value));
        }
    }

    private function validateOptionalVariables(): void
    {
        $appEnv = $_ENV['APP_ENV'] ?? 'production';
        $isProduction = $appEnv === 'production';

        $dbConnection = $_ENV['DB_CONNECTION'] ?? 'sqlite';

        $this->validateOptional('APP_DEBUG', fn ($value) => $this->validateBoolean($value, 'APP_DEBUG'));

        if ($isProduction && $dbConnection !== 'sqlite') {
            $this->validateOptional('DB_HOST', fn ($value) => $this->validateNonEmpty($value, 'DB_HOST'));
            $this->validateOptional('DB_DATABASE', fn ($value) => $this->validateNonEmpty($value, 'DB_DATABASE'));
        }

        $jwtBlacklistEnabled = $this->getBoolean('JWT_BLACKLIST_ENABLED', true);
        if ($jwtBlacklistEnabled) {
            $this->validateOptional('REDIS_HOST', fn ($value) => $this->validateNonEmpty($value, 'REDIS_HOST'));
            $this->validateOptional('REDIS_PORT', fn ($value) => $this->validatePort($value));
        }

        $this->validateOptional('APP_URL', fn ($value) => $this->validateUrl($value, 'APP_URL'));
        $this->validateOptional('JWT_TTL', fn ($value) => $this->validatePositiveInteger($value, 'JWT_TTL'));
        $this->validateOptional('JWT_REFRESH_TTL', fn ($value) => $this->validatePositiveInteger($value, 'JWT_REFRESH_TTL'));
        $this->validateOptional('SESSION_LIFETIME', fn ($value) => $this->validatePositiveInteger($value, 'SESSION_LIFETIME'));
        $this->validateOptional('REDIS_PORT', fn ($value) => $this->validatePort($value));
    }

    private function validateRequired(string $key, callable $validator): void
    {
        $value = $_ENV[$key] ?? null;

        if ($value === null || $value === '') {
            $this->errors[] = "{$key} is required but not set";

            return;
        }

        $error = $validator($value);
        if ($error !== null) {
            $this->errors[] = $error;
        }
    }

    private function validateOptional(string $key, callable $validator, bool $asWarning = true): void
    {
        $value = $_ENV[$key] ?? null;

        if ($value === null || $value === '') {
            return;
        }

        $error = $validator($value);
        if ($error !== null) {
            if ($asWarning) {
                $this->warnings[] = $error;
            } else {
                $this->errors[] = $error;
            }
        }
    }

    private function validateAppKey(?string $value, bool $testing): ?string
    {
        if (empty($value)) {
            return 'APP_KEY is required for encryption';
        }

        if (! $testing && strlen($value) < 32) {
            return 'APP_KEY must be at least 32 characters long for secure encryption';
        }

        return null;
    }

    private function validateJwtSecret(?string $value): ?string
    {
        if (empty($value)) {
            return 'JWT_SECRET is required for JWT token signing';
        }

        if ($value === 'your-secret-key-here' || $value === 'your-jwt-secret-key') {
            return 'JWT_SECRET is using a default placeholder value. Generate a secure secret using: openssl rand -hex 32';
        }

        if (strlen($value) < 32) {
            return 'JWT_SECRET must be at least 32 characters long for secure signing';
        }

        return null;
    }

    private function validateAppEnv(?string $value): ?string
    {
        $validEnvs = ['local', 'production', 'testing'];

        if (! in_array($value, $validEnvs, true)) {
            return 'APP_ENV must be one of: ' . implode(', ', $validEnvs);
        }

        return null;
    }

    private function validateBoolean(?string $value, string $key): ?string
    {
        $validValues = ['true', 'false', '1', '0', 'yes', 'no', 'on', 'off'];

        if (! in_array(strtolower($value), $validValues, true)) {
            return "{$key} must be a boolean value (true/false, 1/0, yes/no, on/off)";
        }

        return null;
    }

    private function validatePositiveInteger(?string $value, string $key): ?string
    {
        if (! ctype_digit($value)) {
            return "{$key} must be a positive integer";
        }

        $intValue = (int) $value;
        if ($intValue <= 0) {
            return "{$key} must be greater than 0";
        }

        return null;
    }

    private function validatePort(?string $value): ?string
    {
        if (! ctype_digit($value)) {
            return 'Port must be a positive integer';
        }

        $port = (int) $value;
        if ($port < 1 || $port > 65535) {
            return 'Port must be between 1 and 65535';
        }

        return null;
    }

    private function validateUrl(?string $value, string $key): ?string
    {
        if (! filter_var($value, FILTER_VALIDATE_URL)) {
            return "{$key} must be a valid URL";
        }

        return null;
    }

    private function validateNonEmpty(?string $value, string $key): ?string
    {
        if (empty($value)) {
            return "{$key} must not be empty";
        }

        return null;
    }

    private function getBoolean(string $key, bool $default): bool
    {
        $value = $_ENV[$key] ?? null;

        if ($value === null) {
            return $default;
        }

        return in_array(strtolower($value), ['true', '1', 'yes', 'on'], true);
    }
}
