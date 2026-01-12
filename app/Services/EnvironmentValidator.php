<?php

declare(strict_types=1);

namespace App\Services;

use Exception;

class EnvironmentValidator
{
    private array $errors = [];

    private array $warnings = [];

    private bool $isProduction = false;

    private bool $isTesting = false;

    public function __construct()
    {
        $this->isProduction = env('APP_ENV') === 'production';
        $this->isTesting = env('APP_ENV') === 'testing';
    }

    public function validate(): void
    {
        $this->reset();

        $validationEnabled = env('ENV_VALIDATION_ENABLED', 'true');

        if (in_array($validationEnabled, ['false', '0', false, 0], true) && ! $this->isProduction) {
            $this->addWarning('Environment validation is disabled. This is not recommended for security.');

            return;
        }

        if ($this->isTesting) {
            $this->addWarning('Skipping validation in testing environment.');

            return;
        }

        $this->validateRequiredVariables();
        $this->validateOptionalVariables();

        if (! empty($this->errors)) {
            $this->throwValidationException();
        }

        if (! empty($this->warnings)) {
            $this->displayWarnings();
        }
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getWarnings(): array
    {
        return $this->warnings;
    }

    private function reset(): void
    {
        $this->errors = [];
        $this->warnings = [];
    }

    private function validateRequiredVariables(): void
    {
        $this->validateAppKey();
        $this->validateJwtSecret();
        $this->validateAppEnv();
    }

    private function validateOptionalVariables(): void
    {
        $this->validateAppDebug();
        $this->validateDatabase();
        $this->validateRedis();
        $this->validateJwtSettings();
        $this->validateSessionSettings();
        $this->validateAppUrl();
        $this->validateMailSettings();
    }

    private function validateAppKey(): void
    {
        $appKey = env('APP_KEY', '');

        if (empty($appKey)) {
            $this->addError('APP_KEY is required. Generate one using: php artisan key:generate');

            return;
        }

        $minLength = $this->isProduction ? 32 : 16;
        if (strlen($appKey) < $minLength) {
            $this->addError(sprintf(
                'APP_KEY must be at least %d characters. Current length: %d. Generate a new key using: php artisan key:generate',
                $minLength,
                strlen($appKey)
            ));
        }
    }

    private function validateJwtSecret(): void
    {
        $jwtSecret = env('JWT_SECRET', '');

        if (empty($jwtSecret)) {
            if ($this->isProduction) {
                $this->addError('JWT_SECRET is required in production. Generate one using: openssl rand -hex 32');
            } else {
                $this->addWarning('JWT_SECRET is not set. Using default for development. Set it for production using: openssl rand -hex 32');
            }

            return;
        }

        $defaultPlaceholders = ['your-secret-key-here', 'change-me', 'secret', 'test-secret'];
        if (in_array(strtolower($jwtSecret), $defaultPlaceholders, true)) {
            $this->addError('JWT_SECRET is using a placeholder value. Generate a secure secret using: openssl rand -hex 32');

            return;
        }

        $minLength = $this->isProduction ? 32 : 16;
        if (strlen($jwtSecret) < $minLength) {
            $this->addError(sprintf(
                'JWT_SECRET must be at least %d characters. Current length: %d. Generate a new secret using: openssl rand -hex 32',
                $minLength,
                strlen($jwtSecret)
            ));
        }
    }

    private function validateAppEnv(): void
    {
        $appEnv = env('APP_ENV', '');

        if (empty($appEnv)) {
            $this->addError('APP_ENV is required. Valid values: local, production, testing');

            return;
        }

        $validEnvironments = ['local', 'production', 'testing'];
        if (! in_array($appEnv, $validEnvironments, true)) {
            $this->addError(sprintf(
                'APP_ENV must be one of: %s. Current value: %s',
                implode(', ', $validEnvironments),
                $appEnv
            ));
        }
    }

    private function validateAppDebug(): void
    {
        $appDebug = env('APP_DEBUG', 'true');

        if (! $this->isBoolean($appDebug)) {
            $this->addError(sprintf(
                'APP_DEBUG must be a boolean value (true/false, 1/0, yes/no, on/off). Current value: %s',
                $appDebug
            ));

            return;
        }

        if ($this->isProduction && $this->parseBoolean($appDebug)) {
            $this->addError('APP_DEBUG should be false in production for security.');
        }
    }

    private function validateDatabase(): void
    {
        $dbConnection = env('DB_CONNECTION', 'sqlite');

        if ($dbConnection === 'sqlite') {
            return;
        }

        if (! $this->isProduction) {
            return;
        }

        $dbHost = env('DB_HOST', '');
        $dbDatabase = env('DB_DATABASE', '');

        if (empty($dbHost)) {
            $this->addError('DB_HOST is required in production when not using SQLite.');
        }

        if (empty($dbDatabase)) {
            $this->addError('DB_DATABASE is required in production when not using SQLite.');
        }
    }

    private function validateRedis(): void
    {
        $jwtBlacklistEnabled = env('JWT_BLACKLIST_ENABLED', 'true');

        if (! $this->parseBoolean($jwtBlacklistEnabled)) {
            return;
        }

        $redisHost = env('REDIS_HOST', '');
        $redisPort = env('REDIS_PORT', '6379');

        if (empty($redisHost)) {
            $this->addError('REDIS_HOST is required when JWT_BLACKLIST_ENABLED is true.');
        }

        if (! $this->isPort($redisPort)) {
            $this->addError(sprintf(
                'REDIS_PORT must be a valid port number (1-65535). Current value: %s',
                $redisPort
            ));
        }
    }

    private function validateJwtSettings(): void
    {
        $jwtTtl = env('JWT_TTL', '30');
        $jwtRefreshTtl = env('JWT_REFRESH_TTL', '1440');

        if (! $this->isPositiveInteger($jwtTtl)) {
            $this->addError(sprintf(
                'JWT_TTL must be a positive integer. Current value: %s',
                $jwtTtl
            ));
        }

        if (! $this->isPositiveInteger($jwtRefreshTtl)) {
            $this->addError(sprintf(
                'JWT_REFRESH_TTL must be a positive integer. Current value: %s',
                $jwtRefreshTtl
            ));
        }

        if ($this->isPositiveInteger($jwtTtl) && $this->isPositiveInteger($jwtRefreshTtl)) {
            $ttl = (int) $jwtTtl;
            $refreshTtl = (int) $jwtRefreshTtl;

            if ($refreshTtl <= $ttl) {
                $this->addError(sprintf(
                    'JWT_REFRESH_TTL (%d) must be greater than JWT_TTL (%d).',
                    $refreshTtl,
                    $ttl
                ));
            }
        }
    }

    private function validateSessionSettings(): void
    {
        $sessionLifetime = env('SESSION_LIFETIME', '120');

        if (! $this->isPositiveInteger($sessionLifetime)) {
            $this->addError(sprintf(
                'SESSION_LIFETIME must be a positive integer. Current value: %s',
                $sessionLifetime
            ));
        }
    }

    private function validateAppUrl(): void
    {
        $appUrl = env('APP_URL', '');

        if (empty($appUrl)) {
            $this->addWarning('APP_URL is not set. Consider setting it for proper URL generation.');

            return;
        }

        if (! $this->isUrl($appUrl)) {
            $this->addError(sprintf(
                'APP_URL must be a valid URL. Current value: %s',
                $appUrl
            ));
        }
    }

    private function validateMailSettings(): void
    {
        $mailFromAddress = env('MAIL_FROM_ADDRESS', '');

        if (empty($mailFromAddress)) {
            return;
        }

        if (! $this->isEmail($mailFromAddress)) {
            $this->addError(sprintf(
                'MAIL_FROM_ADDRESS must be a valid email address. Current value: %s',
                $mailFromAddress
            ));
        }
    }

    private function addError(string $message): void
    {
        $this->errors[] = $message;
    }

    private function addWarning(string $message): void
    {
        $this->warnings[] = $message;
    }

    private function throwValidationException(): void
    {
        $errorMessage = "Environment variable validation failed:\n\n";
        $errorMessage .= implode("\n", array_map(fn ($error) => "• {$error}", $this->errors));

        throw new Exception($errorMessage);
    }

    private function displayWarnings(): void
    {
        if (empty($this->warnings)) {
            return;
        }

        echo "\n=== Environment Validation Warnings ===\n";
        echo implode("\n", array_map(fn ($warning) => "• {$warning}", $this->warnings));
        echo "\n=======================================\n\n";
    }

    private function isBoolean(mixed $value): bool
    {
        if (is_bool($value)) {
            return true;
        }

        if (is_string($value)) {
            $lower = strtolower($value);
            if (in_array($lower, ['true', 'false', '1', '0', 'yes', 'no', 'on', 'off'], true)) {
                return true;
            }
        }

        if (is_numeric($value)) {
            return in_array((int) $value, [0, 1], true);
        }

        return false;
    }

    private function parseBoolean(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value !== 0;
        }

        if (is_string($value)) {
            $lower = strtolower($value);
            return in_array($lower, ['true', '1', 'yes', 'on'], true);
        }

        return false;
    }

    private function isPositiveInteger(mixed $value): bool
    {
        if (! is_numeric($value)) {
            return false;
        }

        $int = (int) $value;
        return $int > 0 && (string) $int === (string) $value;
    }

    private function isPort(mixed $value): bool
    {
        if (! $this->isPositiveInteger($value)) {
            return false;
        }

        $port = (int) $value;

        return $port >= 1 && $port <= 65535;
    }

    private function isUrl(mixed $value): bool
    {
        if (! is_string($value)) {
            return false;
        }

        return filter_var($value, FILTER_VALIDATE_URL) !== false;
    }

    private function isEmail(mixed $value): bool
    {
        if (! is_string($value)) {
            return false;
        }

        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }
}
