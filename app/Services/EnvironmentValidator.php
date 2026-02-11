<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;

class EnvironmentValidator
{
    private array $errors = [];

    private array $warnings = [];

    private string $environment;

    private bool $isProduction;

    public function __construct()
    {
        $this->environment = env('APP_ENV', 'local');
        $this->isProduction = $this->environment === 'production';
    }

    public function validate(): array
    {
        $this->errors = [];
        $this->warnings = [];

        if ($this->shouldSkipValidation()) {
            return ['status' => 'skipped', 'reason' => 'Validation disabled or in testing environment'];
        }

        $this->validateAppKey();
        $this->validateJwtSecret();
        $this->validateAppEnvironment();
        $this->validateAppDebug();
        $this->validateDatabase();
        $this->validateRedis();
        $this->validateAppUrl();
        $this->validateJwtConfiguration();

        if (!empty($this->errors)) {
            throw new RuntimeException($this->formatErrors());
        }

        $result = ['status' => 'success'];

        if (!empty($this->warnings)) {
            $result['warnings'] = $this->warnings;
        }

        return $result;
    }

    private function shouldSkipValidation(): bool
    {
        $enabled = env('ENV_VALIDATION_ENABLED', 'true');
        $testing = env('APP_ENV') === 'testing';

        return $enabled === 'false' || $testing;
    }

    private function validateAppKey(): void
    {
        $appKey = env('APP_KEY', '');

        if (empty($appKey)) {
            $this->errors[] = 'APP_KEY is empty. Generate a secure key using: php artisan key:generate';
            return;
        }

        if ($this->isProduction && strlen($appKey) < 32) {
            $this->errors[] = sprintf(
                'APP_KEY must be at least 32 characters long in production. Current length: %d characters.',
                strlen($appKey)
            );
        }

        $placeholders = [
            'base64:your-secret-key-here',
            'your-app-key-here',
            'change-me',
            'secret-key',
            'some-random-string',
        ];

        if (in_array($appKey, $placeholders, true)) {
            $this->errors[] = 'APP_KEY is using a placeholder value which is insecure. Generate a secure key using: php artisan key:generate';
        }
    }

    private function validateJwtSecret(): void
    {
        $jwtSecret = env('JWT_SECRET', '');

        if (empty($jwtSecret)) {
            $this->errors[] = 'JWT_SECRET is empty. Generate a secure secret using: openssl rand -hex 32';
            return;
        }

        if ($this->isProduction && strlen($jwtSecret) < 32) {
            $this->errors[] = sprintf(
                'JWT_SECRET must be at least 32 characters long in production. Current length: %d characters.',
                strlen($jwtSecret)
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
            'jwt_secret_change_me',
            'default-secret',
        ];

        if (in_array(strtolower($jwtSecret), array_map('strtolower', $placeholders), true)) {
            $this->errors[] = 'JWT_SECRET is using a placeholder value which is insecure. Generate a secure secret using: openssl rand -hex 32';
        }
    }

    private function validateAppEnvironment(): void
    {
        $validEnvironments = ['local', 'production', 'testing'];
        $appEnv = env('APP_ENV', 'local');

        if (!in_array($appEnv, $validEnvironments, true)) {
            $this->errors[] = sprintf(
                'APP_ENV must be one of: %s. Current value: "%s"',
                implode(', ', $validEnvironments),
                $appEnv
            );
        }
    }

    private function validateAppDebug(): void
    {
        $appDebug = env('APP_DEBUG', 'true');

        if ($this->isProduction && strtolower($appDebug) === 'true') {
            $this->warnings[] = 'APP_DEBUG is set to true in production environment. This is a security risk. Set APP_DEBUG=false in production.';
        }

        if (!in_array(strtolower($appDebug), ['true', 'false'])) {
            $this->errors[] = sprintf('APP_DEBUG must be a boolean value (true or false). Current value: "%s"', $appDebug);
        }
    }

    private function validateDatabase(): void
    {
        $dbConnection = env('DB_CONNECTION', 'mysql');

        if ($dbConnection === 'sqlite') {
            $dbPath = env('DB_DATABASE', 'database/database.sqlite');
            $dbDir = dirname($dbPath);

            if (!is_dir($dbDir) && !@mkdir($dbDir, 0755, true)) {
                $this->errors[] = sprintf('SQLite database directory does not exist and cannot be created: %s', $dbDir);
            }

            if (!is_writable(dirname($dbPath))) {
                $this->warnings[] = sprintf('SQLite database directory may not be writable: %s', $dbDir);
            }

            return;
        }

        $dbHost = env('DB_HOST');
        $dbPort = env('DB_PORT');
        $dbDatabase = env('DB_DATABASE');

        if ($this->isProduction) {
            if (empty($dbHost)) {
                $this->errors[] = 'DB_HOST is required in production when DB_CONNECTION is not sqlite';
            }

            if (empty($dbDatabase)) {
                $this->errors[] = 'DB_DATABASE is required in production';
            }
        }

        if (!empty($dbPort)) {
            $port = (int) $dbPort;
            if ($port < 1 || $port > 65535) {
                $this->errors[] = sprintf('DB_PORT must be between 1 and 65535. Current value: %s', $dbPort);
            }
        }
    }

    private function validateRedis(): void
    {
        $jwtBlacklistEnabled = env('JWT_BLACKLIST_ENABLED', 'true');

        if (strtolower($jwtBlacklistEnabled) !== 'true') {
            return;
        }

        $redisHost = env('REDIS_HOST');
        $redisPort = env('REDIS_PORT');

        if ($this->isProduction && empty($redisHost)) {
            $this->errors[] = 'REDIS_HOST is required in production when JWT_BLACKLIST_ENABLED=true';
        }

        if (!empty($redisPort)) {
            $port = (int) $redisPort;
            if ($port < 1 || $port > 65535) {
                $this->errors[] = sprintf('REDIS_PORT must be between 1 and 65535. Current value: %s', $redisPort);
            }
        }
    }

    private function validateAppUrl(): void
    {
        $appUrl = env('APP_URL');

        if (empty($appUrl)) {
            $this->warnings[] = 'APP_URL is not set. This may cause issues with password reset links and other features that require absolute URLs.';
            return;
        }

        if (!filter_var($appUrl, FILTER_VALIDATE_URL)) {
            $this->errors[] = sprintf('APP_URL must be a valid URL. Current value: "%s"', $appUrl);
        }

        $allowedProtocols = ['http', 'https'];
        $protocol = parse_url($appUrl, PHP_URL_SCHEME);

        if ($protocol && !in_array($protocol, $allowedProtocols, true)) {
            $this->warnings[] = sprintf('APP_URL uses "%s://" protocol which is unusual. Expected: http or https.', $protocol);
        }
    }

    private function validateJwtConfiguration(): void
    {
        $jwtTtl = env('JWT_TTL');
        $jwtRefreshTtl = env('JWT_REFRESH_TTL');

        if (!empty($jwtTtl)) {
            $ttl = (int) $jwtTtl;
            if ($ttl <= 0) {
                $this->errors[] = sprintf('JWT_TTL must be a positive integer. Current value: %s', $jwtTtl);
            }

            if ($ttl > 43200) {
                $this->warnings[] = sprintf('JWT_TTL is very high (%d minutes = %d hours). Consider reducing for better security.', $ttl, $ttl / 60);
            }
        }

        if (!empty($jwtRefreshTtl)) {
            $refreshTtl = (int) $jwtRefreshTtl;
            if ($refreshTtl <= 0) {
                $this->errors[] = sprintf('JWT_REFRESH_TTL must be a positive integer. Current value: %s', $jwtRefreshTtl);
            }

            if ($refreshTtl > 525600) {
                $this->warnings[] = sprintf('JWT_REFRESH_TTL is very high (%d minutes = %d days). Consider reducing for better security.', $refreshTtl, $refreshTtl / 1440);
            }
        }
    }

    private function formatErrors(): string
    {
        $message = "Environment validation failed with the following errors:\n\n";

        foreach ($this->errors as $i => $error) {
            $message .= sprintf('%d. %s%s', $i + 1, $error, "\n");
        }

        if (!empty($this->warnings)) {
            $message .= "\nWarnings:\n";
            foreach ($this->warnings as $i => $warning) {
                $message .= sprintf('- %s%s', $warning, "\n");
            }
        }

        $message .= "\nPlease fix these errors and restart the application.";

        return $message;
    }
}