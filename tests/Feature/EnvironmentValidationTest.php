<?php

declare(strict_types=1);

namespace Tests\Feature;

use RuntimeException;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class EnvironmentValidationTest extends TestCase
{
    #[Test]
    public function test_valid_configuration_passes_validation(): void
    {
        $_ENV['APP_ENV'] = 'testing';
        $_ENV['ENV_VALIDATION_ENABLED'] = 'false';
        $_ENV['APP_KEY'] = str_repeat('a', 32);
        $_ENV['JWT_SECRET'] = str_repeat('b', 32);

        $this->expectNotToThrow(RuntimeException::class);
    }

    #[Test]
    public function test_missing_required_variables_cause_validation_failure(): void
    {
        $_ENV['APP_ENV'] = 'production';
        $_ENV['ENV_VALIDATION_ENABLED'] = 'true';

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/Required environment variable.*is not set/');
    }

    #[Test]
    public function test_invalid_app_env_causes_error(): void
    {
        $_ENV['APP_ENV'] = 'invalid';
        $_ENV['APP_KEY'] = str_repeat('a', 32);
        $_ENV['JWT_SECRET'] = str_repeat('b', 32);
        $_ENV['ENV_VALIDATION_ENABLED'] = 'true';

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/APP_ENV must be one of/');
    }

    #[Test]
    public function test_short_app_key_in_production_causes_error(): void
    {
        $_ENV['APP_ENV'] = 'production';
        $_ENV['APP_KEY'] = 'short';
        $_ENV['JWT_SECRET'] = str_repeat('b', 32);
        $_ENV['ENV_VALIDATION_ENABLED'] = 'true';

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/APP_KEY must be at least 32 characters/');
    }

    #[Test]
    public function test_jwt_secret_placeholder_causes_error(): void
    {
        $_ENV['APP_ENV'] = 'production';
        $_ENV['APP_KEY'] = str_repeat('a', 32);
        $_ENV['JWT_SECRET'] = 'your-jwt-secret';
        $_ENV['ENV_VALIDATION_ENABLED'] = 'true';

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/JWT_SECRET is using a placeholder value/');
    }

    #[Test]
    public function test_short_jwt_secret_in_production_causes_error(): void
    {
        $_ENV['APP_ENV'] = 'production';
        $_ENV['APP_KEY'] = str_repeat('a', 32);
        $_ENV['JWT_SECRET'] = 'short';
        $_ENV['ENV_VALIDATION_ENABLED'] = 'true';

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/JWT_SECRET must be at least 32 characters/');
    }

    #[Test]
    public function test_app_debug_true_in_production_generates_warning(): void
    {
        $_ENV['APP_ENV'] = 'production';
        $_ENV['APP_DEBUG'] = 'true';
        $_ENV['APP_KEY'] = str_repeat('a', 32);
        $_ENV['JWT_SECRET'] = str_repeat('b', 32);
        $_ENV['ENV_VALIDATION_ENABLED'] = 'true';

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/APP_DEBUG is enabled in production/');
    }

    #[Test]
    public function test_invalid_boolean_value_causes_error(): void
    {
        $_ENV['APP_ENV'] = 'production';
        $_ENV['APP_DEBUG'] = 'invalid';
        $_ENV['APP_KEY'] = str_repeat('a', 32);
        $_ENV['JWT_SECRET'] = str_repeat('b', 32);
        $_ENV['ENV_VALIDATION_ENABLED'] = 'true';

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/APP_DEBUG must be a boolean value/');
    }

    #[Test]
    public function test_invalid_integer_value_causes_error(): void
    {
        $_ENV['APP_ENV'] = 'production';
        $_ENV['JWT_TTL'] = 'invalid';
        $_ENV['APP_KEY'] = str_repeat('a', 32);
        $_ENV['JWT_SECRET'] = str_repeat('b', 32);
        $_ENV['ENV_VALIDATION_ENABLED'] = 'true';

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/JWT_TTL must be a numeric value/');
    }

    #[Test]
    public function test_jwt_ttl_below_minimum_causes_error(): void
    {
        $_ENV['APP_ENV'] = 'production';
        $_ENV['JWT_TTL'] = '0';
        $_ENV['APP_KEY'] = str_repeat('a', 32);
        $_ENV['JWT_SECRET'] = str_repeat('b', 32);
        $_ENV['ENV_VALIDATION_ENABLED'] = 'true';

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/JWT_TTL must be at least 1/');
    }

    #[Test]
    public function test_jwt_ttl_above_maximum_causes_error(): void
    {
        $_ENV['APP_ENV'] = 'production';
        $_ENV['JWT_TTL'] = '2000';
        $_ENV['APP_KEY'] = str_repeat('a', 32);
        $_ENV['JWT_SECRET'] = str_repeat('b', 32);
        $_ENV['ENV_VALIDATION_ENABLED'] = 'true';

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/JWT_TTL must be at most 1440/');
    }

    #[Test]
    public function test_invalid_url_causes_error(): void
    {
        $_ENV['APP_ENV'] = 'production';
        $_ENV['FRONTEND_URL'] = 'not-a-valid-url';
        $_ENV['APP_KEY'] = str_repeat('a', 32);
        $_ENV['JWT_SECRET'] = str_repeat('b', 32);
        $_ENV['ENV_VALIDATION_ENABLED'] = 'true';

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/FRONTEND_URL must be a valid URL/');
    }

    #[Test]
    public function test_invalid_email_causes_error(): void
    {
        $_ENV['APP_ENV'] = 'production';
        $_ENV['MAIL_FROM_ADDRESS'] = 'not-a-valid-email';
        $_ENV['APP_KEY'] = str_repeat('a', 32);
        $_ENV['JWT_SECRET'] = str_repeat('b', 32);
        $_ENV['ENV_VALIDATION_ENABLED'] = 'true';

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/MAIL_FROM_ADDRESS must be a valid email address/');
    }

    #[Test]
    public function test_missing_database_host_causes_error_in_production(): void
    {
        $_ENV['APP_ENV'] = 'production';
        $_ENV['DB_CONNECTION'] = 'mysql';
        $_ENV['DB_HOST'] = '';
        $_ENV['DB_DATABASE'] = 'test';
        $_ENV['DB_USERNAME'] = 'test';
        $_ENV['DB_PASSWORD'] = 'test';
        $_ENV['APP_KEY'] = str_repeat('a', 32);
        $_ENV['JWT_SECRET'] = str_repeat('b', 32);
        $_ENV['ENV_VALIDATION_ENABLED'] = 'true';

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/DB_HOST is required/');
    }

    #[Test]
    public function test_missing_redis_host_when_blacklist_enabled_causes_error(): void
    {
        $_ENV['APP_ENV'] = 'production';
        $_ENV['JWT_BLACKLIST_ENABLED'] = 'true';
        $_ENV['REDIS_HOST'] = '';
        $_ENV['APP_KEY'] = str_repeat('a', 32);
        $_ENV['JWT_SECRET'] = str_repeat('b', 32);
        $_ENV['ENV_VALIDATION_ENABLED'] = 'true';

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/REDIS_HOST is required/');
    }

    #[Test]
    public function test_redis_not_required_when_blacklist_disabled(): void
    {
        $_ENV['APP_ENV'] = 'production';
        $_ENV['JWT_BLACKLIST_ENABLED'] = 'false';
        $_ENV['APP_KEY'] = str_repeat('a', 32);
        $_ENV['JWT_SECRET'] = str_repeat('b', 32);
        $_ENV['ENV_VALIDATION_ENABLED'] = 'true';

        $this->expectNotToThrow(RuntimeException::class);
    }

    #[Test]
    public function test_sqlite_connection_does_not_require_host(): void
    {
        $_ENV['APP_ENV'] = 'production';
        $_ENV['DB_CONNECTION'] = 'sqlite';
        $_ENV['APP_KEY'] = str_repeat('a', 32);
        $_ENV['JWT_SECRET'] = str_repeat('b', 32);
        $_ENV['ENV_VALIDATION_ENABLED'] = 'true';

        $this->expectNotToThrow(RuntimeException::class);
    }

    #[Test]
    public function test_validation_disabled_skips_all_checks(): void
    {
        $_ENV['APP_ENV'] = 'production';
        $_ENV['APP_KEY'] = '';
        $_ENV['JWT_SECRET'] = '';
        $_ENV['ENV_VALIDATION_ENABLED'] = 'false';

        $this->expectNotToThrow(RuntimeException::class);
    }

    #[Test]
    public function test_testing_environment_skips_validation(): void
    {
        $_ENV['APP_ENV'] = 'testing';
        $_ENV['APP_KEY'] = '';
        $_ENV['JWT_SECRET'] = '';
        $_ENV['ENV_VALIDATION_ENABLED'] = 'true';

        $this->expectNotToThrow(RuntimeException::class);
    }
}
