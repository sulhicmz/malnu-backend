<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;

use function filter_var;
use function in_array;
use function strlen;

class EnvironmentValidator
{
    private array $errors = [];

    private array $warnings = [];

    private bool $isProduction;

    private bool $isTesting;

    public function __construct()
    {
        $this->isProduction = getenv('APP_ENV') === 'production';
        $this->isTesting = getenv('APP_ENV') === 'testing';
    }

    public function validate(): void
    {
        if ($this->shouldValidate()) {
            $this->validateRequiredVariables();
            $this->validateOptionalVariables();
        }

        $this->outputValidationResults();
    }

    private function shouldValidate(): bool
    {
        if ($this->isTesting) {
            return false;
        }

        return getenv('ENV_VALIDATION_ENABLED') === 'true' || getenv('ENV_VALIDATION_ENABLED') === false;
    }

    private function validateRequiredVariables(): void
    {
        $this->validateAppKey();

        if (! $this->isTesting) {
            $this->validateJwtSecret();
        }
    }

    private function validateOptionalVariables(): void
    {
        $this->validateAppEnv();

        $this->validateAppDebug();

        $this->validateDatabaseConfiguration();

        $this->validateRedisConfiguration();

        $this->validateJwtConfiguration();

        $this->validateSessionConfiguration();

        $this->validateMailConfiguration();

        $this->validateUrlConfiguration();

        $this->validateSecurityHeadersConfiguration();
    }

    private function validateAppKey(): void
    {
        $appKey = getenv('APP_KEY');

        if ($appKey === false || empty($appKey)) {
            $this->errors[] = 'APP_KEY is required. Generate one using: php artisan key:generate';

            return;
        }

        $requiredLength = $this->isProduction ? 32 : 16;

        if (strlen($appKey) < $requiredLength) {
            $this->errors[] = "APP_KEY must be at least {$requiredLength} characters long. Current length: " . strlen($appKey);
        }
    }

    private function validateJwtSecret(): void
    {
        $jwtSecret = getenv('JWT_SECRET');

        if ($this->isProduction && ($jwtSecret === false || empty($jwtSecret))) {
            $this->errors[] = 'JWT_SECRET is required in production. Generate a secure secret using: openssl rand -hex 32';

            return;
        }

        if ($jwtSecret !== false && ! empty($jwtSecret)) {
            $placeholders = [
                'your-secret-key-here',
                'change-me',
                'your-jwt-secret',
                'jwt-secret-key',
                'secret',
                'password',
                'your-secure-jwt-secret-key-here',
            ];

            if (in_array(strtolower($jwtSecret), $placeholders, true)) {
                $this->errors[] = 'JWT_SECRET is using a placeholder value which is insecure. Generate a secure secret using: openssl rand -hex 32';

                return;
            }

            if (strlen($jwtSecret) < 32) {
                $this->errors[] = 'JWT_SECRET must be at least 32 characters long for security. Current length: ' . strlen($jwtSecret);
            }
        }
    }

    private function validateAppEnv(): void
    {
        $appEnv = getenv('APP_ENV');

        if ($appEnv !== false && ! empty($appEnv)) {
            $validEnvs = ['local', 'production', 'testing'];

            if (! in_array($appEnv, $validEnvs, true)) {
                $this->errors[] = 'APP_ENV must be one of: ' . implode(', ', $validEnvs) . ". Current value: {$appEnv}";
            }
        }
    }

    private function validateAppDebug(): void
    {
        $appDebug = getenv('APP_DEBUG');

        if ($appDebug !== false) {
            $isValid = $this->validateBoolean('APP_DEBUG', $appDebug);

            if (! $isValid) {
                return;
            }

            if ($this->isProduction && $this->parseBoolean($appDebug) === true) {
                $this->warnings[] = 'APP_DEBUG is enabled in production. This is a security risk and should be set to false.';
            }
        }
    }

    private function validateDatabaseConfiguration(): void
    {
        $dbConnection = getenv('DB_CONNECTION');

        if ($dbConnection !== false && $dbConnection !== 'sqlite') {
            $dbHost = getenv('DB_HOST');

            if ($this->isProduction && ($dbHost === false || empty($dbHost))) {
                $this->errors[] = 'DB_HOST is required in production when not using SQLite';
            }

            $dbDatabase = getenv('DB_DATABASE');

            if ($this->isProduction && ($dbDatabase === false || empty($dbDatabase))) {
                $this->errors[] = 'DB_DATABASE is required in production';
            }
        }
    }

    private function validateRedisConfiguration(): void
    {
        $redisHost = getenv('REDIS_HOST');
        $redisPort = getenv('REDIS_PORT');

        if ($this->isJwtBlacklistEnabled() || getenv('CACHE_DRIVER') === 'redis' || getenv('SESSION_DRIVER') === 'redis') {
            if ($redisHost === false || empty($redisHost)) {
                $this->errors[] = 'REDIS_HOST is required when JWT_BLACKLIST_ENABLED, CACHE_DRIVER, or SESSION_DRIVER is set to redis';
            }

            if ($redisPort !== false) {
                $this->validatePort('REDIS_PORT', $redisPort);
            }
        }
    }

    private function validateJwtConfiguration(): void
    {
        $jwtTtl = getenv('JWT_TTL');
        if ($jwtTtl !== false) {
            $this->validatePositiveInteger('JWT_TTL', $jwtTtl);
        }

        $jwtRefreshTtl = getenv('JWT_REFRESH_TTL');
        if ($jwtRefreshTtl !== false) {
            $this->validatePositiveInteger('JWT_REFRESH_TTL', $jwtRefreshTtl);
        }
    }

    private function validateSessionConfiguration(): void
    {
        $sessionLifetime = getenv('SESSION_LIFETIME');

        if ($sessionLifetime !== false) {
            $this->validatePositiveInteger('SESSION_LIFETIME', $sessionLifetime);
        }

        $sessionEncrypt = getenv('SESSION_ENCRYPT');

        if ($sessionEncrypt !== false) {
            $this->validateBoolean('SESSION_ENCRYPT', $sessionEncrypt);
        }
    }

    private function validateMailConfiguration(): void
    {
        $mailFromAddress = getenv('MAIL_FROM_ADDRESS');

        if ($mailFromAddress !== false && ! empty($mailFromAddress)) {
            if (filter_var($mailFromAddress, FILTER_VALIDATE_EMAIL) === false) {
                $this->errors[] = "MAIL_FROM_ADDRESS must be a valid email address. Current value: {$mailFromAddress}";
            }
        }
    }

    private function validateUrlConfiguration(): void
    {
        $appUrl = getenv('APP_URL');

        if ($appUrl !== false && ! empty($appUrl)) {
            if (filter_var($appUrl, FILTER_VALIDATE_URL) === false) {
                $this->errors[] = "APP_URL must be a valid URL. Current value: {$appUrl}";
            }
        }

        $frontendUrl = getenv('FRONTEND_URL');

        if ($frontendUrl !== false && ! empty($frontendUrl)) {
            if (filter_var($frontendUrl, FILTER_VALIDATE_URL) === false) {
                $this->errors[] = "FRONTEND_URL must be a valid URL. Current value: {$frontendUrl}";
            }
        }
    }

    private function validateSecurityHeadersConfiguration(): void
    {
        $securityHeadersEnabled = getenv('SECURITY_HEADERS_ENABLED');

        if ($securityHeadersEnabled !== false) {
            $this->validateBoolean('SECURITY_HEADERS_ENABLED', $securityHeadersEnabled);
        }

        $cspEnabled = getenv('CSP_ENABLED');

        if ($cspEnabled !== false) {
            $this->validateBoolean('CSP_ENABLED', $cspEnabled);
        }

        $hstsEnabled = getenv('HSTS_ENABLED');

        if ($hstsEnabled !== false) {
            $this->validateBoolean('HSTS_ENABLED', $hstsEnabled);
        }

        $hstsIncludeSubdomains = getenv('HSTS_INCLUDE_SUBDOMAINS');

        if ($hstsIncludeSubdomains !== false) {
            $this->validateBoolean('HSTS_INCLUDE_SUBDOMAINS', $hstsIncludeSubdomains);
        }

        $hstsPreload = getenv('HSTS_PRELOAD');

        if ($hstsPreload !== false) {
            $this->validateBoolean('HSTS_PRELOAD', $hstsPreload);
        }
    }

    private function validateBoolean(string $key, $value): bool
    {
        $validValues = ['true', 'false', '1', '0', 'yes', 'no', 'on', 'off', true, false, 1, 0];

        if (! in_array($value, $validValues, true)) {
            $this->errors[] = "{$key} must be a boolean value (true/false, 1/0, yes/no, on/off). Current value: {$value}";

            return false;
        }

        return true;
    }

    private function validatePositiveInteger(string $key, $value): void
    {
        if (! is_numeric($value) || (int) $value <= 0) {
            $this->errors[] = "{$key} must be a positive integer. Current value: {$value}";
        }
    }

    private function validatePort(string $key, $value): void
    {
        $port = (int) $value;

        if (! is_numeric($value) || $port < 1 || $port > 65535) {
            $this->errors[] = "{$key} must be a valid port number (1-65535). Current value: {$value}";
        }
    }

    private function isJwtBlacklistEnabled(): bool
    {
        $jwtBlacklistEnabled = getenv('JWT_BLACKLIST_ENABLED');

        if ($jwtBlacklistEnabled === false) {
            return false;
        }

        return $this->parseBoolean($jwtBlacklistEnabled) === true;
    }

    private function parseBoolean($value): ?bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if ($value === '1' || $value === 1 || strtolower($value) === 'true' || strtolower($value) === 'yes' || strtolower($value) === 'on') {
            return true;
        }

        if ($value === '0' || $value === 0 || strtolower($value) === 'false' || strtolower($value) === 'no' || strtolower($value) === 'off') {
            return false;
        }

        return null;
    }

    private function outputValidationResults(): void
    {
        foreach ($this->warnings as $warning) {
            echo "[WARNING] {$warning}\n";
        }

        if (! empty($this->errors)) {
            $errorMessage = "Environment validation failed:\n" . implode("\n", $this->errors);
            throw new RuntimeException($errorMessage);
        }
    }
}
