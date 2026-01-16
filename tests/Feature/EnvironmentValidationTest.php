<?php

declare(strict_types=1);

namespace Tests\Feature;

use RuntimeException;
use PHPUnit\Framework\TestCase;
use App\Services\EnvironmentValidator;

class EnvironmentValidationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        putenv('ENV_VALIDATION_ENABLED=true');
        putenv('APP_ENV=local');
        putenv('APP_DEBUG=true');
        putenv('APP_KEY=' . str_repeat('a', 32));
        putenv('JWT_SECRET=' . str_repeat('b', 32));
        putenv('DB_CONNECTION=sqlite');
        putenv('DB_DATABASE=:memory:');
        putenv('JWT_BLACKLIST_ENABLED=false');
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        putenv('APP_ENV');
        putenv('APP_DEBUG');
        putenv('APP_KEY');
        putenv('JWT_SECRET');
        putenv('DB_CONNECTION');
        putenv('DB_DATABASE');
        putenv('REDIS_HOST');
        putenv('REDIS_PORT');
        putenv('APP_URL');
        putenv('JWT_TTL');
        putenv('JWT_REFRESH_TTL');
        putenv('ENV_VALIDATION_ENABLED');
    }

    public function test_valid_configuration_passes(): void
    {
        putenv('APP_ENV=local');
        putenv('APP_KEY=' . str_repeat('a', 32));
        putenv('JWT_SECRET=' . str_repeat('b', 32));

        $validator = new EnvironmentValidator();
        $result = $validator->validate();

        $this->assertArrayHasKey('status', $result);
        $this->assertEquals('success', $result['status']);
    }

    public function test_validation_can_be_disabled(): void
    {
        putenv('ENV_VALIDATION_ENABLED=false');

        $validator = new EnvironmentValidator();
        $result = $validator->validate();

        $this->assertEquals('skipped', $result['status']);
        $this->assertArrayHasKey('reason', $result);
    }

    public function test_validation_skipped_in_testing_environment(): void
    {
        putenv('APP_ENV=testing');

        $validator = new EnvironmentValidator();
        $result = $validator->validate();

        $this->assertEquals('skipped', $result['status']);
    }

    public function test_empty_app_key_fails(): void
    {
        putenv('APP_KEY=');
        putenv('APP_ENV=production');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/APP_KEY is empty/');

        $validator = new EnvironmentValidator();
        $validator->validate();
    }

    public function test_short_app_key_fails_in_production(): void
    {
        putenv('APP_KEY=' . str_repeat('a', 16));
        putenv('APP_ENV=production');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/APP_KEY must be at least 32 characters/');

        $validator = new EnvironmentValidator();
        $validator->validate();
    }

    public function test_placeholder_app_key_fails(): void
    {
        putenv('APP_KEY=base64:your-secret-key-here');
        putenv('APP_ENV=production');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/APP_KEY is using a placeholder value/');

        $validator = new EnvironmentValidator();
        $validator->validate();
    }

    public function test_empty_jwt_secret_fails(): void
    {
        putenv('JWT_SECRET=');
        putenv('APP_ENV=production');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/JWT_SECRET is empty/');

        $validator = new EnvironmentValidator();
        $validator->validate();
    }

    public function test_short_jwt_secret_fails_in_production(): void
    {
        putenv('JWT_SECRET=' . str_repeat('a', 16));
        putenv('APP_ENV=production');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/JWT_SECRET must be at least 32 characters/');

        $validator = new EnvironmentValidator();
        $validator->validate();
    }

    public function test_placeholder_jwt_secret_fails(): void
    {
        putenv('JWT_SECRET=your-secret-key-here');
        putenv('APP_ENV=production');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/JWT_SECRET is using a placeholder value/');

        $validator = new EnvironmentValidator();
        $validator->validate();
    }

    public function test_invalid_app_env_fails(): void
    {
        putenv('APP_ENV=invalid');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/APP_ENV must be one of/');

        $validator = new EnvironmentValidator();
        $validator->validate();
    }

    public function test_invalid_app_debug_value_fails(): void
    {
        putenv('APP_DEBUG=maybe');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/APP_DEBUG must be a boolean value/');

        $validator = new EnvironmentValidator();
        $validator->validate();
    }

    public function test_debug_true_in_production_generates_warning(): void
    {
        putenv('APP_ENV=production');
        putenv('APP_DEBUG=true');
        putenv('APP_KEY=' . str_repeat('a', 32));
        putenv('JWT_SECRET=' . str_repeat('b', 32));

        $validator = new EnvironmentValidator();
        $result = $validator->validate();

        $this->assertEquals('success', $result['status']);
        $this->assertArrayHasKey('warnings', $result);
        $this->assertArrayHasKey(0, $result['warnings']);
        $this->assertStringContainsString('APP_DEBUG is set to true in production', $result['warnings'][0]);
    }

    public function test_invalid_app_url_fails(): void
    {
        putenv('APP_URL=not-a-valid-url');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/APP_URL must be a valid URL/');

        $validator = new EnvironmentValidator();
        $validator->validate();
    }

    public function test_db_host_required_in_production(): void
    {
        putenv('APP_ENV=production');
        putenv('DB_CONNECTION=mysql');
        putenv('DB_HOST=');
        putenv('APP_KEY=' . str_repeat('a', 32));
        putenv('JWT_SECRET=' . str_repeat('b', 32));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/DB_HOST is required in production/');

        $validator = new EnvironmentValidator();
        $validator->validate();
    }

    public function test_invalid_db_port_fails(): void
    {
        putenv('DB_PORT=99999');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/DB_PORT must be between 1 and 65535/');

        $validator = new EnvironmentValidator();
        $validator->validate();
    }

    public function test_redis_host_required_when_blacklist_enabled(): void
    {
        putenv('APP_ENV=production');
        putenv('JWT_BLACKLIST_ENABLED=true');
        putenv('REDIS_HOST=');
        putenv('APP_KEY=' . str_repeat('a', 32));
        putenv('JWT_SECRET=' . str_repeat('b', 32));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/REDIS_HOST is required in production/');

        $validator = new EnvironmentValidator();
        $validator->validate();
    }

    public function test_invalid_redis_port_fails(): void
    {
        putenv('REDIS_PORT=70000');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/REDIS_PORT must be between 1 and 65535/');

        $validator = new EnvironmentValidator();
        $validator->validate();
    }

    public function test_negative_jwt_ttl_fails(): void
    {
        putenv('JWT_TTL=-10');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/JWT_TTL must be a positive integer/');

        $validator = new EnvironmentValidator();
        $validator->validate();
    }

    public function test_negative_jwt_refresh_ttl_fails(): void
    {
        putenv('JWT_REFRESH_TTL=-5');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/JWT_REFRESH_TTL must be a positive integer/');

        $validator = new EnvironmentValidator();
        $validator->validate();
    }

    public function test_very_high_jwt_ttl_generates_warning(): void
    {
        putenv('JWT_TTL=43201');

        $validator = new EnvironmentValidator();
        $result = $validator->validate();

        $this->assertEquals('success', $result['status']);
        $this->assertArrayHasKey('warnings', $result);
        $warningFound = false;

        foreach ($result['warnings'] as $warning) {
            if (str_contains($warning, 'JWT_TTL is very high')) {
                $warningFound = true;
                break;
            }
        }

        $this->assertTrue($warningFound, 'Warning about high JWT_TTL should be generated');
    }

    public function test_empty_app_url_generates_warning(): void
    {
        putenv('APP_URL=');

        $validator = new EnvironmentValidator();
        $result = $validator->validate();

        $this->assertEquals('success', $result['status']);
        $this->assertArrayHasKey('warnings', $result);
        $warningFound = false;

        foreach ($result['warnings'] as $warning) {
            if (str_contains($warning, 'APP_URL is not set')) {
                $warningFound = true;
                break;
            }
        }

        $this->assertTrue($warningFound, 'Warning about missing APP_URL should be generated');
    }
}