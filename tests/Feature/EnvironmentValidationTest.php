<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\EnvironmentValidator;

class EnvironmentValidationTest extends TestCase
{
    private EnvironmentValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new EnvironmentValidator();
    }

    public function test_app_key_required(): void
    {
        $originalKey = $_ENV['APP_KEY'] ?? null;
        unset($_ENV['APP_KEY']);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('APP_KEY is required');

        $this->validator->validate();

        if ($originalKey !== null) {
            $_ENV['APP_KEY'] = $originalKey;
        }
    }

    public function test_app_key_minimum_length(): void
    {
        $originalKey = $_ENV['APP_KEY'] ?? null;
        $_ENV['APP_KEY'] = 'short';

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('APP_KEY must be at least 32 characters');

        $this->validator->validate();

        if ($originalKey !== null) {
            $_ENV['APP_KEY'] = $originalKey;
        }
    }

    public function test_jwt_secret_required(): void
    {
        $originalSecret = $_ENV['JWT_SECRET'] ?? null;
        unset($_ENV['JWT_SECRET']);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('JWT_SECRET is required');

        $this->validator->validate();

        if ($originalSecret !== null) {
            $_ENV['JWT_SECRET'] = $originalSecret;
        }
    }

    public function test_jwt_secret_not_placeholder(): void
    {
        $originalSecret = $_ENV['JWT_SECRET'] ?? null;
        $_ENV['JWT_SECRET'] = 'your-secret-key-here';

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('JWT_SECRET is using a placeholder value');

        $this->validator->validate();

        if ($originalSecret !== null) {
            $_ENV['JWT_SECRET'] = $originalSecret;
        }
    }

    public function test_jwt_secret_minimum_length(): void
    {
        $originalSecret = $_ENV['JWT_SECRET'] ?? null;
        $_ENV['JWT_SECRET'] = 'short';

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('JWT_SECRET must be at least 32 characters');

        $this->validator->validate();

        if ($originalSecret !== null) {
            $_ENV['JWT_SECRET'] = $originalSecret;
        }
    }

    public function test_app_env_valid_values(): void
    {
        $originalEnv = $_ENV['APP_ENV'] ?? null;
        $_ENV['APP_ENV'] = 'invalid';

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('APP_ENV must be one of');

        $this->validator->validate();

        if ($originalEnv !== null) {
            $_ENV['APP_ENV'] = $originalEnv;
        }
    }

    public function test_app_debug_must_be_boolean(): void
    {
        $originalDebug = $_ENV['APP_DEBUG'] ?? null;
        $_ENV['APP_DEBUG'] = 'not-a-boolean';

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('APP_DEBUG must be a boolean value');

        $this->validator->validate();

        if ($originalDebug !== null) {
            $_ENV['APP_DEBUG'] = $originalDebug;
        }
    }

    public function test_valid_configuration(): void
    {
        $originalValues = [
            'APP_KEY' => $_ENV['APP_KEY'] ?? null,
            'JWT_SECRET' => $_ENV['JWT_SECRET'] ?? null,
            'APP_ENV' => $_ENV['APP_ENV'] ?? null,
            'APP_DEBUG' => $_ENV['APP_DEBUG'] ?? null,
        ];

        $_ENV['APP_KEY'] = str_repeat('a', 32);
        $_ENV['JWT_SECRET'] = str_repeat('b', 32);
        $_ENV['APP_ENV'] = 'local';
        $_ENV['APP_DEBUG'] = 'false';

        try {
            $this->validator->validate();
            $this->assertTrue(true);
        } catch (\RuntimeException $e) {
            $this->fail('Valid configuration should not throw exception: ' . $e->getMessage());
        } finally {
            foreach ($originalValues as $key => $value) {
                if ($value !== null) {
                    $_ENV[$key] = $value;
                } else {
                    unset($_ENV[$key]);
                }
            }
        }
    }

    public function test_app_debug_warning_in_production(): void
    {
        $originalEnv = $_ENV['APP_ENV'] ?? null;
        $originalDebug = $_ENV['APP_DEBUG'] ?? null;
        $originalKey = $_ENV['APP_KEY'] ?? null;
        $originalSecret = $_ENV['JWT_SECRET'] ?? null;

        $_ENV['APP_ENV'] = 'production';
        $_ENV['APP_DEBUG'] = 'true';
        $_ENV['APP_KEY'] = str_repeat('a', 32);
        $_ENV['JWT_SECRET'] = str_repeat('b', 32);

        try {
            $this->validator->validate();
            $this->assertTrue(true);
        } catch (\RuntimeException $e) {
            if (!str_contains($e->getMessage(), 'APP_DEBUG is enabled in production')) {
                $this->fail('Expected warning about APP_DEBUG in production');
            }
        } finally {
            if ($originalEnv !== null) {
                $_ENV['APP_ENV'] = $originalEnv;
            }
            if ($originalDebug !== null) {
                $_ENV['APP_DEBUG'] = $originalDebug;
            }
            if ($originalKey !== null) {
                $_ENV['APP_KEY'] = $originalKey;
            }
            if ($originalSecret !== null) {
                $_ENV['JWT_SECRET'] = $originalSecret;
            }
        }
    }

    public function test_database_config_required_in_production(): void
    {
        $originalEnv = $_ENV['APP_ENV'] ?? null;
        $originalConnection = $_ENV['DB_CONNECTION'] ?? null;
        $originalHost = $_ENV['DB_HOST'] ?? null;
        $originalKey = $_ENV['APP_KEY'] ?? null;
        $originalSecret = $_ENV['JWT_SECRET'] ?? null;

        $_ENV['APP_ENV'] = 'production';
        $_ENV['DB_CONNECTION'] = 'mysql';
        unset($_ENV['DB_HOST']);
        $_ENV['APP_KEY'] = str_repeat('a', 32);
        $_ENV['JWT_SECRET'] = str_repeat('b', 32);

        try {
            $this->validator->validate();
            $this->fail('Should have thrown exception for missing DB_HOST in production');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('DB_HOST is required in production', $e->getMessage());
        } finally {
            if ($originalEnv !== null) {
                $_ENV['APP_ENV'] = $originalEnv;
            }
            if ($originalConnection !== null) {
                $_ENV['DB_CONNECTION'] = $originalConnection;
            }
            if ($originalHost !== null) {
                $_ENV['DB_HOST'] = $originalHost;
            }
            if ($originalKey !== null) {
                $_ENV['APP_KEY'] = $originalKey;
            }
            if ($originalSecret !== null) {
                $_ENV['JWT_SECRET'] = $originalSecret;
            }
        }
    }

    public function test_redis_config_required_when_blacklist_enabled(): void
    {
        $originalBlacklist = $_ENV['JWT_BLACKLIST_ENABLED'] ?? null;
        $originalHost = $_ENV['REDIS_HOST'] ?? null;
        $originalKey = $_ENV['APP_KEY'] ?? null;
        $originalSecret = $_ENV['JWT_SECRET'] ?? null;

        $_ENV['JWT_BLACKLIST_ENABLED'] = 'true';
        unset($_ENV['REDIS_HOST']);
        $_ENV['APP_KEY'] = str_repeat('a', 32);
        $_ENV['JWT_SECRET'] = str_repeat('b', 32);

        try {
            $this->validator->validate();
            $this->fail('Should have thrown exception for missing REDIS_HOST when blacklist enabled');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('REDIS_HOST is required', $e->getMessage());
        } finally {
            if ($originalBlacklist !== null) {
                $_ENV['JWT_BLACKLIST_ENABLED'] = $originalBlacklist;
            }
            if ($originalHost !== null) {
                $_ENV['REDIS_HOST'] = $originalHost;
            }
            if ($originalKey !== null) {
                $_ENV['APP_KEY'] = $originalKey;
            }
            if ($originalSecret !== null) {
                $_ENV['JWT_SECRET'] = $originalSecret;
            }
        }
    }

    public function test_jwt_ttl_must_be_integer(): void
    {
        $originalKey = $_ENV['APP_KEY'] ?? null;
        $originalSecret = $_ENV['JWT_SECRET'] ?? null;
        $originalTtl = $_ENV['JWT_TTL'] ?? null;

        $_ENV['APP_KEY'] = str_repeat('a', 32);
        $_ENV['JWT_SECRET'] = str_repeat('b', 32);
        $_ENV['JWT_TTL'] = 'not-an-integer';

        try {
            $this->validator->validate();
            $this->fail('Should have thrown exception for non-integer JWT_TTL');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('JWT_TTL must be an integer', $e->getMessage());
        } finally {
            if ($originalKey !== null) {
                $_ENV['APP_KEY'] = $originalKey;
            }
            if ($originalSecret !== null) {
                $_ENV['JWT_SECRET'] = $originalSecret;
            }
            if ($originalTtl !== null) {
                $_ENV['JWT_TTL'] = $originalTtl;
            }
        }
    }

    public function test_url_validation(): void
    {
        $originalKey = $_ENV['APP_KEY'] ?? null;
        $originalSecret = $_ENV['JWT_SECRET'] ?? null;
        $originalUrl = $_ENV['APP_URL'] ?? null;

        $_ENV['APP_KEY'] = str_repeat('a', 32);
        $_ENV['JWT_SECRET'] = str_repeat('b', 32);
        $_ENV['APP_URL'] = 'not-a-valid-url';

        try {
            $this->validator->validate();
            $this->fail('Should have thrown exception for invalid APP_URL');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('APP_URL must be a valid URL', $e->getMessage());
        } finally {
            if ($originalKey !== null) {
                $_ENV['APP_KEY'] = $originalKey;
            }
            if ($originalSecret !== null) {
                $_ENV['JWT_SECRET'] = $originalSecret;
            }
            if ($originalUrl !== null) {
                $_ENV['APP_URL'] = $originalUrl;
            }
        }
    }

    public function test_port_validation(): void
    {
        $originalKey = $_ENV['APP_KEY'] ?? null;
        $originalSecret = $_ENV['JWT_SECRET'] ?? null;
        $originalPort = $_ENV['REDIS_PORT'] ?? null;

        $_ENV['APP_KEY'] = str_repeat('a', 32);
        $_ENV['JWT_SECRET'] = str_repeat('b', 32);
        $_ENV['REDIS_PORT'] = '99999';

        try {
            $this->validator->validate();
            $this->fail('Should have thrown exception for port out of range');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('REDIS_PORT must be between 1 and 65535', $e->getMessage());
        } finally {
            if ($originalKey !== null) {
                $_ENV['APP_KEY'] = $originalKey;
            }
            if ($originalSecret !== null) {
                $_ENV['JWT_SECRET'] = $originalSecret;
            }
            if ($originalPort !== null) {
                $_ENV['REDIS_PORT'] = $originalPort;
            }
        }
    }

    public function test_skips_validation_in_testing(): void
    {
        $originalKey = $_ENV['APP_KEY'] ?? null;
        $originalEnv = $_ENV['APP_ENV'] ?? null;

        $_ENV['APP_ENV'] = 'testing';
        unset($_ENV['APP_KEY']);

        try {
            $this->validator->validate();
            $this->assertTrue(true);
        } catch (\RuntimeException $e) {
            $this->fail('Should skip validation in testing environment');
        } finally {
            if ($originalKey !== null) {
                $_ENV['APP_KEY'] = $originalKey;
            }
            if ($originalEnv !== null) {
                $_ENV['APP_ENV'] = $originalEnv;
            }
        }
    }
}