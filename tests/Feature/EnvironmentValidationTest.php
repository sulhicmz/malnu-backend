<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\EnvironmentValidator;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use function putenv;
use function getenv;

class EnvironmentValidationTest extends TestCase
{
    private array $backupEnv = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->backupEnv = [
            'APP_ENV' => getenv('APP_ENV'),
            'APP_DEBUG' => getenv('APP_DEBUG'),
            'APP_KEY' => getenv('APP_KEY'),
            'JWT_SECRET' => getenv('JWT_SECRET'),
            'DB_CONNECTION' => getenv('DB_CONNECTION'),
            'DB_HOST' => getenv('DB_HOST'),
            'DB_DATABASE' => getenv('DB_DATABASE'),
            'REDIS_HOST' => getenv('REDIS_HOST'),
            'REDIS_PORT' => getenv('REDIS_PORT'),
            'JWT_BLACKLIST_ENABLED' => getenv('JWT_BLACKLIST_ENABLED'),
            'CACHE_DRIVER' => getenv('CACHE_DRIVER'),
            'SESSION_DRIVER' => getenv('SESSION_DRIVER'),
            'JWT_TTL' => getenv('JWT_TTL'),
            'JWT_REFRESH_TTL' => getenv('JWT_REFRESH_TTL'),
            'SESSION_LIFETIME' => getenv('SESSION_LIFETIME'),
            'SESSION_ENCRYPT' => getenv('SESSION_ENCRYPT'),
            'MAIL_FROM_ADDRESS' => getenv('MAIL_FROM_ADDRESS'),
            'APP_URL' => getenv('APP_URL'),
            'FRONTEND_URL' => getenv('FRONTEND_URL'),
            'SECURITY_HEADERS_ENABLED' => getenv('SECURITY_HEADERS_ENABLED'),
            'CSP_ENABLED' => getenv('CSP_ENABLED'),
            'HSTS_ENABLED' => getenv('HSTS_ENABLED'),
            'HSTS_INCLUDE_SUBDOMAINS' => getenv('HSTS_INCLUDE_SUBDOMAINS'),
            'HSTS_PRELOAD' => getenv('HSTS_PRELOAD'),
            'ENV_VALIDATION_ENABLED' => getenv('ENV_VALIDATION_ENABLED'),
        ];
    }

    protected function tearDown(): void
    {
        foreach ($this->backupEnv as $key => $value) {
            if ($value === false) {
                putenv($key);
            } else {
                putenv("{$key}={$value}");
            }
        }

        parent::tearDown();
    }

    public function test_fails_when_app_key_is_missing()
    {
        putenv('APP_ENV=local');
        putenv('APP_DEBUG=true');
        putenv('APP_KEY=');
        putenv('JWT_SECRET=test-secret-key-that-is-long-enough-for-security-purposes');
        putenv('ENV_VALIDATION_ENABLED=true');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('APP_KEY is required');

        new EnvironmentValidator();
    }

    public function test_fails_when_app_key_is_too_short_in_production()
    {
        putenv('APP_ENV=production');
        putenv('APP_DEBUG=false');
        putenv('APP_KEY=short-key');
        putenv('JWT_SECRET=test-secret-key-that-is-long-enough-for-security-purposes');
        putenv('ENV_VALIDATION_ENABLED=true');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('APP_KEY must be at least 32 characters long');

        new EnvironmentValidator();
    }

    public function test_passes_with_valid_app_key_in_production()
    {
        putenv('APP_ENV=production');
        putenv('APP_DEBUG=false');
        putenv('APP_KEY=' . str_repeat('a', 32));
        putenv('JWT_SECRET=' . str_repeat('b', 32));
        putenv('ENV_VALIDATION_ENABLED=true');

        $validator = new EnvironmentValidator();
        $this->assertInstanceOf(EnvironmentValidator::class, $validator);
    }

    public function test_fails_when_jwt_secret_uses_placeholder()
    {
        putenv('APP_ENV=production');
        putenv('APP_DEBUG=false');
        putenv('APP_KEY=' . str_repeat('a', 32));
        putenv('JWT_SECRET=change-me');
        putenv('ENV_VALIDATION_ENABLED=true');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('JWT_SECRET is using a placeholder value');

        new EnvironmentValidator();
    }

    public function test_fails_when_jwt_secret_is_too_short()
    {
        putenv('APP_ENV=production');
        putenv('APP_DEBUG=false');
        putenv('APP_KEY=' . str_repeat('a', 32));
        putenv('JWT_SECRET=short');
        putenv('ENV_VALIDATION_ENABLED=true');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('JWT_SECRET must be at least 32 characters long');

        new EnvironmentValidator();
    }

    public function test_passes_when_jwt_secret_is_valid()
    {
        putenv('APP_ENV=production');
        putenv('APP_DEBUG=false');
        putenv('APP_KEY=' . str_repeat('a', 32));
        putenv('JWT_SECRET=' . str_repeat('b', 32));
        putenv('ENV_VALIDATION_ENABLED=true');

        $validator = new EnvironmentValidator();
        $this->assertInstanceOf(EnvironmentValidator::class, $validator);
    }

    public function test_fails_with_invalid_app_env()
    {
        putenv('APP_ENV=invalid-env');
        putenv('APP_DEBUG=true');
        putenv('APP_KEY=' . str_repeat('a', 32));
        putenv('JWT_SECRET=' . str_repeat('b', 32));
        putenv('ENV_VALIDATION_ENABLED=true');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('APP_ENV must be one of: local, production, testing');

        new EnvironmentValidator();
    }

    public function test_passes_with_valid_app_env_values()
    {
        foreach (['local', 'production', 'testing'] as $env) {
            putenv('APP_ENV=' . $env);
            putenv('APP_DEBUG=true');
            putenv('APP_KEY=' . str_repeat('a', 32));
            putenv('JWT_SECRET=' . str_repeat('b', 32));
            putenv('ENV_VALIDATION_ENABLED=true');

            $validator = new EnvironmentValidator();
            $this->assertInstanceOf(EnvironmentValidator::class, $validator);
        }
    }

    public function test_fails_with_invalid_app_debug_value()
    {
        putenv('APP_ENV=local');
        putenv('APP_DEBUG=not-a-boolean');
        putenv('APP_KEY=' . str_repeat('a', 32));
        putenv('JWT_SECRET=' . str_repeat('b', 32));
        putenv('ENV_VALIDATION_ENABLED=true');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('APP_DEBUG must be a boolean value');

        new EnvironmentValidator();
    }

    public function test_passes_with_valid_boolean_values()
    {
        foreach (['true', 'false', '1', '0', 'yes', 'no', 'on', 'off'] as $value) {
            putenv('APP_ENV=local');
            putenv('APP_DEBUG=' . $value);
            putenv('APP_KEY=' . str_repeat('a', 32));
            putenv('JWT_SECRET=' . str_repeat('b', 32));
            putenv('ENV_VALIDATION_ENABLED=true');

            $validator = new EnvironmentValidator();
            $this->assertInstanceOf(EnvironmentValidator::class, $validator);
        }
    }

    public function test_fails_with_invalid_redis_port()
    {
        putenv('APP_ENV=local');
        putenv('APP_DEBUG=true');
        putenv('APP_KEY=' . str_repeat('a', 32));
        putenv('JWT_SECRET=' . str_repeat('b', 32));
        putenv('JWT_BLACKLIST_ENABLED=true');
        putenv('REDIS_HOST=localhost');
        putenv('REDIS_PORT=99999');
        putenv('ENV_VALIDATION_ENABLED=true');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('REDIS_PORT must be a valid port number');

        new EnvironmentValidator();
    }

    public function test_passes_with_valid_redis_port()
    {
        putenv('APP_ENV=local');
        putenv('APP_DEBUG=true');
        putenv('APP_KEY=' . str_repeat('a', 32));
        putenv('JWT_SECRET=' . str_repeat('b', 32));
        putenv('JWT_BLACKLIST_ENABLED=true');
        putenv('REDIS_HOST=localhost');
        putenv('REDIS_PORT=6379');
        putenv('ENV_VALIDATION_ENABLED=true');

        $validator = new EnvironmentValidator();
        $this->assertInstanceOf(EnvironmentValidator::class, $validator);
    }

    public function test_fails_with_invalid_jwt_ttl()
    {
        putenv('APP_ENV=local');
        putenv('APP_DEBUG=true');
        putenv('APP_KEY=' . str_repeat('a', 32));
        putenv('JWT_SECRET=' . str_repeat('b', 32));
        putenv('JWT_TTL=-1');
        putenv('ENV_VALIDATION_ENABLED=true');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('JWT_TTL must be a positive integer');

        new EnvironmentValidator();
    }

    public function test_passes_with_valid_jwt_ttl()
    {
        putenv('APP_ENV=local');
        putenv('APP_DEBUG=true');
        putenv('APP_KEY=' . str_repeat('a', 32));
        putenv('JWT_SECRET=' . str_repeat('b', 32));
        putenv('JWT_TTL=30');
        putenv('ENV_VALIDATION_ENABLED=true');

        $validator = new EnvironmentValidator();
        $this->assertInstanceOf(EnvironmentValidator::class, $validator);
    }

    public function test_fails_with_invalid_email_address()
    {
        putenv('APP_ENV=local');
        putenv('APP_DEBUG=true');
        putenv('APP_KEY=' . str_repeat('a', 32));
        putenv('JWT_SECRET=' . str_repeat('b', 32));
        putenv('MAIL_FROM_ADDRESS=not-an-email');
        putenv('ENV_VALIDATION_ENABLED=true');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('MAIL_FROM_ADDRESS must be a valid email address');

        new EnvironmentValidator();
    }

    public function test_passes_with_valid_email_address()
    {
        putenv('APP_ENV=local');
        putenv('APP_DEBUG=true');
        putenv('APP_KEY=' . str_repeat('a', 32));
        putenv('JWT_SECRET=' . str_repeat('b', 32));
        putenv('MAIL_FROM_ADDRESS=test@example.com');
        putenv('ENV_VALIDATION_ENABLED=true');

        $validator = new EnvironmentValidator();
        $this->assertInstanceOf(EnvironmentValidator::class, $validator);
    }

    public function test_fails_with_invalid_url()
    {
        putenv('APP_ENV=local');
        putenv('APP_DEBUG=true');
        putenv('APP_KEY=' . str_repeat('a', 32));
        putenv('JWT_SECRET=' . str_repeat('b', 32));
        putenv('APP_URL=not-a-url');
        putenv('ENV_VALIDATION_ENABLED=true');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('APP_URL must be a valid URL');

        new EnvironmentValidator();
    }

    public function test_passes_with_valid_url()
    {
        putenv('APP_ENV=local');
        putenv('APP_DEBUG=true');
        putenv('APP_KEY=' . str_repeat('a', 32));
        putenv('JWT_SECRET=' . str_repeat('b', 32));
        putenv('APP_URL=https://example.com');
        putenv('ENV_VALIDATION_ENABLED=true');

        $validator = new EnvironmentValidator();
        $this->assertInstanceOf(EnvironmentValidator::class, $validator);
    }

    public function test_skips_validation_when_disabled()
    {
        putenv('APP_ENV=local');
        putenv('APP_DEBUG=true');
        putenv('APP_KEY=');
        putenv('JWT_SECRET=');
        putenv('ENV_VALIDATION_ENABLED=false');

        $validator = new EnvironmentValidator();
        $this->assertInstanceOf(EnvironmentValidator::class, $validator);
    }

    public function test_requires_db_host_in_production_without_sqlite()
    {
        putenv('APP_ENV=production');
        putenv('APP_DEBUG=false');
        putenv('APP_KEY=' . str_repeat('a', 32));
        putenv('JWT_SECRET=' . str_repeat('b', 32));
        putenv('DB_CONNECTION=mysql');
        putenv('DB_HOST=');
        putenv('ENV_VALIDATION_ENABLED=true');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('DB_HOST is required in production');

        new EnvironmentValidator();
    }

    public function test_requires_redis_host_when_blacklist_enabled()
    {
        putenv('APP_ENV=local');
        putenv('APP_DEBUG=true');
        putenv('APP_KEY=' . str_repeat('a', 32));
        putenv('JWT_SECRET=' . str_repeat('b', 32));
        putenv('JWT_BLACKLIST_ENABLED=true');
        putenv('REDIS_HOST=');
        putenv('ENV_VALIDATION_ENABLED=true');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('REDIS_HOST is required when JWT_BLACKLIST_ENABLED');

        new EnvironmentValidator();
    }
}
