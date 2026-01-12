<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\EnvironmentValidator;
use Exception;
use Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
class EnvironmentValidationTest extends TestCase
{
    private EnvironmentValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new EnvironmentValidator();
    }

    public function testValidAppKeyPassesValidation()
    {
        $appKey = str_repeat('a', 32);
        putenv("APP_KEY={$appKey}");
        putenv('APP_ENV=local');
        putenv('APP_DEBUG=true');
        putenv('JWT_SECRET=' . str_repeat('b', 32));

        try {
            $this->validator->validate();
            $this->assertTrue(true, 'Validation should pass with valid APP_KEY');
        } catch (Exception $e) {
            $this->fail('Validation should not throw exception with valid APP_KEY: ' . $e->getMessage());
        } finally {
            putenv('APP_KEY');
            putenv('APP_ENV');
            putenv('APP_DEBUG');
            putenv('JWT_SECRET');
        }
    }

    public function testMissingAppKeyFailsValidation()
    {
        putenv('APP_KEY=');
        putenv('APP_ENV=local');
        putenv('APP_DEBUG=true');
        putenv('JWT_SECRET=' . str_repeat('b', 32));

        try {
            $this->validator->validate();
            $this->fail('Validation should fail with missing APP_KEY');
        } catch (Exception $e) {
            $this->assertStringContainsString('APP_KEY is required', $e->getMessage());
        } finally {
            putenv('APP_KEY');
            putenv('APP_ENV');
            putenv('APP_DEBUG');
            putenv('JWT_SECRET');
        }
    }

    public function testShortAppKeyFailsInProduction()
    {
        $appKey = str_repeat('a', 16);
        putenv("APP_KEY={$appKey}");
        putenv('APP_ENV=production');
        putenv('APP_DEBUG=false');
        putenv('JWT_SECRET=' . str_repeat('b', 32));

        try {
            $this->validator->validate();
            $this->fail('Validation should fail with short APP_KEY in production');
        } catch (Exception $e) {
            $this->assertStringContainsString('APP_KEY must be at least 32 characters', $e->getMessage());
        } finally {
            putenv('APP_KEY');
            putenv('APP_ENV');
            putenv('APP_DEBUG');
            putenv('JWT_SECRET');
        }
    }

    public function testShortAppKeyPassesInLocal()
    {
        $appKey = str_repeat('a', 16);
        putenv("APP_KEY={$appKey}");
        putenv('APP_ENV=local');
        putenv('APP_DEBUG=true');
        putenv('JWT_SECRET=' . str_repeat('b', 32));

        try {
            $this->validator->validate();
            $this->assertTrue(true, 'Short APP_KEY should pass in local environment');
        } catch (Exception $e) {
            $this->fail('Validation should pass with short APP_KEY in local: ' . $e->getMessage());
        } finally {
            putenv('APP_KEY');
            putenv('APP_ENV');
            putenv('APP_DEBUG');
            putenv('JWT_SECRET');
        }
    }

    public function testValidJwtSecretPassesValidation()
    {
        putenv('APP_KEY=' . str_repeat('a', 32));
        putenv('APP_ENV=local');
        putenv('APP_DEBUG=true');
        putenv('JWT_SECRET=' . str_repeat('b', 32));

        try {
            $this->validator->validate();
            $this->assertTrue(true, 'Validation should pass with valid JWT_SECRET');
        } catch (Exception $e) {
            $this->fail('Validation should not throw exception with valid JWT_SECRET: ' . $e->getMessage());
        } finally {
            putenv('APP_KEY');
            putenv('APP_ENV');
            putenv('APP_DEBUG');
            putenv('JWT_SECRET');
        }
    }

    public function testPlaceholderJwtSecretFails()
    {
        $placeholders = ['your-secret-key-here', 'change-me', 'secret', 'test-secret'];

        foreach ($placeholders as $placeholder) {
            putenv('APP_KEY=' . str_repeat('a', 32));
            putenv('APP_ENV=local');
            putenv('APP_DEBUG=true');
            putenv("JWT_SECRET={$placeholder}");

            try {
                $this->validator->validate();
                $this->fail("Validation should fail with placeholder JWT_SECRET: {$placeholder}");
            } catch (Exception $e) {
                $this->assertStringContainsString('JWT_SECRET is using a placeholder value', $e->getMessage());
            } finally {
                putenv('APP_KEY');
                putenv('APP_ENV');
                putenv('APP_DEBUG');
                putenv('JWT_SECRET');
            }
        }
    }

    public function testMissingJwtSecretInProductionFails()
    {
        putenv('APP_KEY=' . str_repeat('a', 32));
        putenv('APP_ENV=production');
        putenv('APP_DEBUG=false');
        putenv('JWT_SECRET=');

        try {
            $this->validator->validate();
            $this->fail('Validation should fail with missing JWT_SECRET in production');
        } catch (Exception $e) {
            $this->assertStringContainsString('JWT_SECRET is required in production', $e->getMessage());
        } finally {
            putenv('APP_KEY');
            putenv('APP_ENV');
            putenv('APP_DEBUG');
            putenv('JWT_SECRET');
        }
    }

    public function testValidAppEnvPasses()
    {
        $validEnvs = ['local', 'production', 'testing'];

        foreach ($validEnvs as $env) {
            putenv('APP_KEY=' . str_repeat('a', 32));
            putenv("APP_ENV={$env}");
            putenv('APP_DEBUG=true');
            putenv('JWT_SECRET=' . str_repeat('b', 32));

            try {
                $this->validator->validate();
                $this->assertTrue(true, "APP_ENV={$env} should pass validation");
            } catch (Exception $e) {
                $this->fail("Validation should pass with valid APP_ENV {$env}: " . $e->getMessage());
            } finally {
                putenv('APP_KEY');
                putenv('APP_ENV');
                putenv('APP_DEBUG');
                putenv('JWT_SECRET');
            }
        }
    }

    public function testInvalidAppEnvFails()
    {
        $invalidEnvs = ['staging', 'dev', 'invalid'];

        foreach ($invalidEnvs as $env) {
            putenv('APP_KEY=' . str_repeat('a', 32));
            putenv("APP_ENV={$env}");
            putenv('APP_DEBUG=true');
            putenv('JWT_SECRET=' . str_repeat('b', 32));

            try {
                $this->validator->validate();
                $this->fail("Validation should fail with invalid APP_ENV: {$env}");
            } catch (Exception $e) {
                $this->assertStringContainsString('APP_ENV must be one of', $e->getMessage());
            } finally {
                putenv('APP_KEY');
                putenv('APP_ENV');
                putenv('APP_DEBUG');
                putenv('JWT_SECRET');
            }
        }
    }

    public function testValidAppDebugBooleanValues()
    {
        $validValues = ['true', 'false', '1', '0', 'yes', 'no', 'on', 'off'];

        foreach ($validValues as $value) {
            putenv('APP_KEY=' . str_repeat('a', 32));
            putenv('APP_ENV=local');
            putenv("APP_DEBUG={$value}");
            putenv('JWT_SECRET=' . str_repeat('b', 32));

            try {
                $this->validator->validate();
                $this->assertTrue(true, "APP_DEBUG={$value} should pass validation");
            } catch (Exception $e) {
                $this->fail("Validation should pass with valid APP_DEBUG {$value}: " . $e->getMessage());
            } finally {
                putenv('APP_KEY');
                putenv('APP_ENV');
                putenv('APP_DEBUG');
                putenv('JWT_SECRET');
            }
        }
    }

    public function testInvalidAppDebugFails()
    {
        putenv('APP_KEY=' . str_repeat('a', 32));
        putenv('APP_ENV=local');
        putenv('APP_DEBUG=invalid');
        putenv('JWT_SECRET=' . str_repeat('b', 32));

        try {
            $this->validator->validate();
            $this->fail('Validation should fail with invalid APP_DEBUG');
        } catch (Exception $e) {
            $this->assertStringContainsString('APP_DEBUG must be a boolean value', $e->getMessage());
        } finally {
            putenv('APP_KEY');
            putenv('APP_ENV');
            putenv('APP_DEBUG');
            putenv('JWT_SECRET');
        }
    }

    public function testAppDebugTrueInProductionFails()
    {
        putenv('APP_KEY=' . str_repeat('a', 32));
        putenv('APP_ENV=production');
        putenv('APP_DEBUG=true');
        putenv('JWT_SECRET=' . str_repeat('b', 32));

        try {
            $this->validator->validate();
            $this->fail('Validation should fail with APP_DEBUG=true in production');
        } catch (Exception $e) {
            $this->assertStringContainsString('APP_DEBUG should be false in production', $e->getMessage());
        } finally {
            putenv('APP_KEY');
            putenv('APP_ENV');
            putenv('APP_DEBUG');
            putenv('JWT_SECRET');
        }
    }

    public function testValidPortPasses()
    {
        $validPorts = ['1', '80', '443', '6379', '65535'];

        foreach ($validPorts as $port) {
            putenv('APP_KEY=' . str_repeat('a', 32));
            putenv('APP_ENV=local');
            putenv('APP_DEBUG=true');
            putenv('JWT_SECRET=' . str_repeat('b', 32));
            putenv('REDIS_HOST=localhost');
            putenv("REDIS_PORT={$port}");

            try {
                $this->validator->validate();
                $this->assertTrue(true, "REDIS_PORT={$port} should pass validation");
            } catch (Exception $e) {
                $this->fail("Validation should pass with valid port {$port}: " . $e->getMessage());
            } finally {
                putenv('APP_KEY');
                putenv('APP_ENV');
                putenv('APP_DEBUG');
                putenv('JWT_SECRET');
                putenv('REDIS_HOST');
                putenv('REDIS_PORT');
            }
        }
    }

    public function testInvalidPortFails()
    {
        $invalidPorts = ['0', '-1', '65536', 'abc', '1.5'];

        foreach ($invalidPorts as $port) {
            putenv('APP_KEY=' . str_repeat('a', 32));
            putenv('APP_ENV=local');
            putenv('APP_DEBUG=true');
            putenv('JWT_SECRET=' . str_repeat('b', 32));
            putenv('REDIS_HOST=localhost');
            putenv("REDIS_PORT={$port}");

            try {
                $this->validator->validate();
                $this->fail("Validation should fail with invalid port: {$port}");
            } catch (Exception $e) {
                $this->assertStringContainsString('must be a valid port number', $e->getMessage());
            } finally {
                putenv('APP_KEY');
                putenv('APP_ENV');
                putenv('APP_DEBUG');
                putenv('JWT_SECRET');
                putenv('REDIS_HOST');
                putenv('REDIS_PORT');
            }
        }
    }

    public function testValidUrlPasses()
    {
        $validUrls = [
            'http://localhost',
            'https://example.com',
            'https://api.example.com',
        ];

        foreach ($validUrls as $url) {
            putenv('APP_KEY=' . str_repeat('a', 32));
            putenv('APP_ENV=local');
            putenv('APP_DEBUG=true');
            putenv('JWT_SECRET=' . str_repeat('b', 32));
            putenv("APP_URL={$url}");

            try {
                $this->validator->validate();
                $this->assertTrue(true, "APP_URL={$url} should pass validation");
            } catch (Exception $e) {
                $this->fail("Validation should pass with valid URL {$url}: " . $e->getMessage());
            } finally {
                putenv('APP_KEY');
                putenv('APP_ENV');
                putenv('APP_DEBUG');
                putenv('JWT_SECRET');
                putenv('APP_URL');
            }
        }
    }

    public function testInvalidUrlFails()
    {
        putenv('APP_KEY=' . str_repeat('a', 32));
        putenv('APP_ENV=local');
        putenv('APP_DEBUG=true');
        putenv('JWT_SECRET=' . str_repeat('b', 32));
        putenv('APP_URL=not-a-valid-url');

        try {
            $this->validator->validate();
            $this->fail('Validation should fail with invalid URL');
        } catch (Exception $e) {
            $this->assertStringContainsString('APP_URL must be a valid URL', $e->getMessage());
        } finally {
            putenv('APP_KEY');
            putenv('APP_ENV');
            putenv('APP_DEBUG');
            putenv('JWT_SECRET');
            putenv('APP_URL');
        }
    }

    public function testValidEmailPasses()
    {
        putenv('APP_KEY=' . str_repeat('a', 32));
        putenv('APP_ENV=local');
        putenv('APP_DEBUG=true');
        putenv('JWT_SECRET=' . str_repeat('b', 32));
        putenv('MAIL_FROM_ADDRESS=test@example.com');

        try {
            $this->validator->validate();
            $this->assertTrue(true, 'Valid email should pass validation');
        } catch (Exception $e) {
            $this->fail('Validation should pass with valid email: ' . $e->getMessage());
        } finally {
            putenv('APP_KEY');
            putenv('APP_ENV');
            putenv('APP_DEBUG');
            putenv('JWT_SECRET');
            putenv('MAIL_FROM_ADDRESS');
        }
    }

    public function testInvalidEmailFails()
    {
        putenv('APP_KEY=' . str_repeat('a', 32));
        putenv('APP_ENV=local');
        putenv('APP_DEBUG=true');
        putenv('JWT_SECRET=' . str_repeat('b', 32));
        putenv('MAIL_FROM_ADDRESS=not-an-email');

        try {
            $this->validator->validate();
            $this->fail('Validation should fail with invalid email');
        } catch (Exception $e) {
            $this->assertStringContainsString('MAIL_FROM_ADDRESS must be a valid email address', $e->getMessage());
        } finally {
            putenv('APP_KEY');
            putenv('APP_ENV');
            putenv('APP_DEBUG');
            putenv('JWT_SECRET');
            putenv('MAIL_FROM_ADDRESS');
        }
    }

    public function testValidationCanBeDisabled()
    {
        putenv('ENV_VALIDATION_ENABLED=false');
        putenv('APP_KEY=');
        putenv('APP_ENV=local');

        try {
            $this->validator->validate();
            $this->assertTrue(true, 'Validation should not throw when disabled');
            $this->assertNotEmpty($this->validator->getWarnings());
            $this->assertStringContainsString('disabled', $this->validator->getWarnings()[0]);
        } catch (Exception $e) {
            $this->fail('Validation should not throw exception when disabled: ' . $e->getMessage());
        } finally {
            putenv('ENV_VALIDATION_ENABLED');
            putenv('APP_KEY');
            putenv('APP_ENV');
        }
    }

    public function testJwtTtlMustBePositiveInteger()
    {
        $invalidTtls = ['0', '-1', 'abc', '1.5'];

        foreach ($invalidTtls as $ttl) {
            putenv('APP_KEY=' . str_repeat('a', 32));
            putenv('APP_ENV=local');
            putenv('APP_DEBUG=true');
            putenv('JWT_SECRET=' . str_repeat('b', 32));
            putenv("JWT_TTL={$ttl}");

            try {
                $this->validator->validate();
                $this->fail("Validation should fail with invalid JWT_TTL: {$ttl}");
            } catch (Exception $e) {
                $this->assertStringContainsString('JWT_TTL must be a positive integer', $e->getMessage());
            } finally {
                putenv('APP_KEY');
                putenv('APP_ENV');
                putenv('APP_DEBUG');
                putenv('JWT_SECRET');
                putenv('JWT_TTL');
            }
        }
    }

    public function testJwtRefreshTtlMustBeGreaterThanTtl()
    {
        putenv('APP_KEY=' . str_repeat('a', 32));
        putenv('APP_ENV=local');
        putenv('APP_DEBUG=true');
        putenv('JWT_SECRET=' . str_repeat('b', 32));
        putenv('JWT_TTL=60');
        putenv('JWT_REFRESH_TTL=30');

        try {
            $this->validator->validate();
            $this->fail('Validation should fail when JWT_REFRESH_TTL is not greater than JWT_TTL');
        } catch (Exception $e) {
            $this->assertStringContainsString('JWT_REFRESH_TTL', $e->getMessage());
            $this->assertStringContainsString('must be greater than', $e->getMessage());
        } finally {
            putenv('APP_KEY');
            putenv('APP_ENV');
            putenv('APP_DEBUG');
            putenv('JWT_SECRET');
            putenv('JWT_TTL');
            putenv('JWT_REFRESH_TTL');
        }
    }

    public function testDatabaseConfigValidatedInProduction()
    {
        putenv('APP_KEY=' . str_repeat('a', 32));
        putenv('APP_ENV=production');
        putenv('APP_DEBUG=false');
        putenv('JWT_SECRET=' . str_repeat('b', 32));
        putenv('DB_CONNECTION=mysql');
        putenv('DB_HOST=');
        putenv('DB_DATABASE=');

        try {
            $this->validator->validate();
            $this->fail('Validation should fail with missing DB_HOST and DB_DATABASE in production');
        } catch (Exception $e) {
            $errors = $e->getMessage();
            $this->assertStringContainsString('DB_HOST is required', $errors);
            $this->assertStringContainsString('DB_DATABASE is required', $errors);
        } finally {
            putenv('APP_KEY');
            putenv('APP_ENV');
            putenv('APP_DEBUG');
            putenv('JWT_SECRET');
            putenv('DB_CONNECTION');
            putenv('DB_HOST');
            putenv('DB_DATABASE');
        }
    }

    public function testSqliteDatabaseSkipsHostValidation()
    {
        putenv('APP_KEY=' . str_repeat('a', 32));
        putenv('APP_ENV=production');
        putenv('APP_DEBUG=false');
        putenv('JWT_SECRET=' . str_repeat('b', 32));
        putenv('DB_CONNECTION=sqlite');

        try {
            $this->validator->validate();
            $this->assertTrue(true, 'SQLite connection should skip DB_HOST validation');
        } catch (Exception $e) {
            $this->fail('SQLite should skip DB_HOST validation: ' . $e->getMessage());
        } finally {
            putenv('APP_KEY');
            putenv('APP_ENV');
            putenv('APP_DEBUG');
            putenv('JWT_SECRET');
            putenv('DB_CONNECTION');
        }
    }
}
