<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\EnvironmentValidator;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @internal
 * @coversNothing
 */
class EnvironmentValidationTest extends TestCase
{
    private array $originalEnv = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalEnv = $_ENV;

        $_ENV['ENV_VALIDATION_ENABLED'] = 'true';
        $_ENV['APP_ENV'] = 'testing';
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $_ENV = $this->originalEnv;
    }

    public function testAppKeyIsRequired()
    {
        unset($_ENV['APP_KEY']);
        $_ENV['APP_ENV'] = 'production';

        $validator = new EnvironmentValidator();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('APP_KEY is required for encryption');

        $validator->validate();
    }

    public function testAppKeyMustBeLongEnough()
    {
        $_ENV['APP_KEY'] = 'short';
        $_ENV['JWT_SECRET'] = str_repeat('a', 32);
        $_ENV['APP_ENV'] = 'production';

        $validator = new EnvironmentValidator();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('APP_KEY must be at least 32 characters long for secure encryption');

        $validator->validate();
    }

    public function testJwtSecretIsRequiredInProduction()
    {
        unset($_ENV['JWT_SECRET']);
        $_ENV['APP_KEY'] = str_repeat('a', 32);
        $_ENV['APP_ENV'] = 'production';

        $validator = new EnvironmentValidator();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('JWT_SECRET is required for JWT token signing');

        $validator->validate();
    }

    public function testJwtSecretNotRequiredInTesting()
    {
        unset($_ENV['JWT_SECRET']);
        $_ENV['APP_KEY'] = str_repeat('a', 32);
        $_ENV['APP_ENV'] = 'testing';

        $validator = new EnvironmentValidator();

        $this->expectNotToPerformAssertions();

        $validator->validate();
    }

    public function testJwtSecretMustBeLongEnough()
    {
        $_ENV['APP_KEY'] = str_repeat('a', 32);
        $_ENV['JWT_SECRET'] = 'short';
        $_ENV['APP_ENV'] = 'production';

        $validator = new EnvironmentValidator();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('JWT_SECRET must be at least 32 characters long for secure signing');

        $validator->validate();
    }

    public function testJwtSecretWithPlaceholderValue()
    {
        $_ENV['APP_KEY'] = str_repeat('a', 32);
        $_ENV['JWT_SECRET'] = 'your-secret-key-here';
        $_ENV['APP_ENV'] = 'production';

        $validator = new EnvironmentValidator();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('JWT_SECRET is using a default placeholder value');

        $validator->validate();
    }

    public function testAppEnvMustBeValid()
    {
        $_ENV['APP_KEY'] = str_repeat('a', 32);
        $_ENV['JWT_SECRET'] = str_repeat('b', 32);
        $_ENV['APP_ENV'] = 'invalid';

        $validator = new EnvironmentValidator();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('APP_ENV must be one of: local, production, testing');

        $validator->validate();
    }

    public function testAppDebugMustBeBoolean()
    {
        $_ENV['APP_KEY'] = str_repeat('a', 32);
        $_ENV['JWT_SECRET'] = str_repeat('b', 32);
        $_ENV['APP_ENV'] = 'production';
        $_ENV['APP_DEBUG'] = 'invalid';

        $validator = new EnvironmentValidator();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('APP_DEBUG must be a boolean value');

        $validator->validate();
    }

    public function testDbHostAndDatabaseRequiredInProductionWhenNotSqlite()
    {
        $_ENV['APP_KEY'] = str_repeat('a', 32);
        $_ENV['JWT_SECRET'] = str_repeat('b', 32);
        $_ENV['APP_ENV'] = 'production';
        $_ENV['DB_CONNECTION'] = 'mysql';
        unset($_ENV['DB_HOST'], $_ENV['DB_DATABASE']);

        $validator = new EnvironmentValidator();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('DB_HOST must not be empty');

        $validator->validate();
    }

    public function testRedisHostRequiredWhenBlacklistEnabled()
    {
        $_ENV['APP_KEY'] = str_repeat('a', 32);
        $_ENV['JWT_SECRET'] = str_repeat('b', 32);
        $_ENV['APP_ENV'] = 'production';
        $_ENV['JWT_BLACKLIST_ENABLED'] = 'true';
        unset($_ENV['REDIS_HOST']);

        $validator = new EnvironmentValidator();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('REDIS_HOST must not be empty');

        $validator->validate();
    }

    public function testRedisPortMustBeValid()
    {
        $_ENV['APP_KEY'] = str_repeat('a', 32);
        $_ENV['JWT_SECRET'] = str_repeat('b', 32);
        $_ENV['APP_ENV'] = 'production';
        $_ENV['REDIS_PORT'] = 'invalid';

        $validator = new EnvironmentValidator();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Port must be a positive integer');

        $validator->validate();
    }

    public function testRedisPortOutOfRange()
    {
        $_ENV['APP_KEY'] = str_repeat('a', 32);
        $_ENV['JWT_SECRET'] = str_repeat('b', 32);
        $_ENV['APP_ENV'] = 'production';
        $_ENV['REDIS_PORT'] = '99999';

        $validator = new EnvironmentValidator();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Port must be between 1 and 65535');

        $validator->validate();
    }

    public function testJwtTtlMustBePositiveInteger()
    {
        $_ENV['APP_KEY'] = str_repeat('a', 32);
        $_ENV['JWT_SECRET'] = str_repeat('b', 32);
        $_ENV['APP_ENV'] = 'production';
        $_ENV['JWT_TTL'] = 'invalid';

        $validator = new EnvironmentValidator();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('JWT_TTL must be a positive integer');

        $validator->validate();
    }

    public function testValidConfigurationPassesValidation()
    {
        $_ENV['APP_KEY'] = str_repeat('a', 32);
        $_ENV['JWT_SECRET'] = str_repeat('b', 32);
        $_ENV['APP_ENV'] = 'production';
        $_ENV['APP_DEBUG'] = 'false';
        $_ENV['DB_CONNECTION'] = 'sqlite';
        $_ENV['REDIS_HOST'] = 'localhost';
        $_ENV['REDIS_PORT'] = '6379';
        $_ENV['JWT_TTL'] = '30';
        $_ENV['JWT_REFRESH_TTL'] = '1440';
        $_ENV['SESSION_LIFETIME'] = '120';

        $validator = new EnvironmentValidator();

        $this->expectNotToPerformAssertions();

        $validator->validate();
    }

    public function testValidationCanBeDisabled()
    {
        $_ENV['ENV_VALIDATION_ENABLED'] = 'false';
        unset($_ENV['APP_KEY'], $_ENV['JWT_SECRET']);
        $_ENV['APP_ENV'] = 'production';

        $validator = new EnvironmentValidator();

        $this->expectNotToPerformAssertions();

        $validator->validate();
    }

    public function testAppUrlMustBeValid()
    {
        $_ENV['APP_KEY'] = str_repeat('a', 32);
        $_ENV['JWT_SECRET'] = str_repeat('b', 32);
        $_ENV['APP_ENV'] = 'production';
        $_ENV['APP_URL'] = 'invalid-url';

        $validator = new EnvironmentValidator();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('APP_URL must be a valid URL');

        $validator->validate();
    }
}
