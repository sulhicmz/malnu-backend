<?php

declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use RuntimeException;

class EnvironmentValidationTest extends TestCase
{
    private array $originalEnv = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalEnv = [
            'APP_ENV' => env('APP_ENV'),
            'APP_DEBUG' => env('APP_DEBUG'),
            'APP_KEY' => env('APP_KEY'),
            'JWT_SECRET' => env('JWT_SECRET'),
            'JWT_TTL' => env('JWT_TTL'),
            'JWT_REFRESH_TTL' => env('JWT_REFRESH_TTL'),
            'JWT_BLACKLIST_ENABLED' => env('JWT_BLACKLIST_ENABLED'),
            'DB_CONNECTION' => env('DB_CONNECTION'),
            'DB_HOST' => env('DB_HOST'),
            'DB_PORT' => env('DB_PORT'),
            'DB_DATABASE' => env('DB_DATABASE'),
            'DB_USERNAME' => env('DB_USERNAME'),
            'CACHE_DRIVER' => env('CACHE_DRIVER'),
            'REDIS_HOST' => env('REDIS_HOST'),
            'REDIS_PORT' => env('REDIS_PORT'),
            'APP_URL' => env('APP_URL'),
            'FRONTEND_URL' => env('FRONTEND_URL'),
            'ENV_VALIDATION_ENABLED' => env('ENV_VALIDATION_ENABLED'),
        ];
    }

    protected function tearDown(): void
    {
        foreach ($this->originalEnv as $key => $value) {
            if ($value === null) {
                putenv($key);
            } else {
                putenv($key.'='.$value);
            }
        }

        parent::tearDown();
    }

    private function runValidation(): void
    {
        $validator = new \App\Services\EnvironmentValidator();
        $validator->validateAll();
    }

    public function testSkipsValidationWhenDisabled()
    {
        putenv('APP_ENV=local');
        putenv('ENV_VALIDATION_ENABLED=false');

        $this->expectNotToPerformAssertions();
        $this->runValidation();
    }

    public function testSkipsValidationInTestingEnvironment()
    {
        putenv('APP_ENV=testing');
        putenv('ENV_VALIDATION_ENABLED=true');

        $this->expectNotToPerformAssertions();
        $this->runValidation();
    }

    public function testValidatesAppEnvironment()
    {
        putenv('APP_ENV=invalid');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("APP_ENV has invalid value 'invalid'");

        $this->runValidation();
    }

    public function testAcceptsValidAppEnvironments()
    {
        $validEnvs = ['local', 'testing', 'staging', 'production'];

        foreach ($validEnvs as $env) {
            putenv('APP_ENV='.$env);
            putenv('ENV_VALIDATION_ENABLED=false');

            $this->expectNotToPerformAssertions();
            $this->runValidation();
        }
    }

    public function testRejectsEmptyAppKeyInProduction()
    {
        putenv('APP_ENV=production');
        putenv('APP_KEY=');
        putenv('ENV_VALIDATION_ENABLED=true');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('APP_KEY cannot be empty in production');

        $this->runValidation();
    }

    public function testRejectsPlaceholderAppKeyInProduction()
    {
        putenv('APP_ENV=production');
        putenv('APP_KEY=your-secret-key-here');
        putenv('ENV_VALIDATION_ENABLED=true');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('APP_KEY is using a placeholder value');

        $this->runValidation();
    }

    public function testRejectsShortAppKeyInProduction()
    {
        putenv('APP_ENV=production');
        putenv('APP_KEY=shortkey');
        putenv('ENV_VALIDATION_ENABLED=true');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('APP_KEY must be at least 32 characters');

        $this->runValidation();
    }

    public function testAllowsMissingAppKeyInDevelopment()
    {
        putenv('APP_ENV=local');
        putenv('APP_KEY=');
        putenv('ENV_VALIDATION_ENABLED=true');

        $this->expectNotToPerformAssertions();
        $this->runValidation();
    }

    public function testRejectsEmptyJwtSecretInProduction()
    {
        putenv('APP_ENV=production');
        putenv('JWT_SECRET=');
        putenv('ENV_VALIDATION_ENABLED=true');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('JWT_SECRET cannot be empty in production');

        $this->runValidation();
    }

    public function testRejectsPlaceholderJwtSecretInProduction()
    {
        putenv('APP_ENV=production');
        putenv('JWT_SECRET=change-me');
        putenv('ENV_VALIDATION_ENABLED=true');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('JWT_SECRET is using a placeholder value');

        $this->runValidation();
    }

    public function testRejectsShortJwtSecretInProduction()
    {
        putenv('APP_ENV=production');
        putenv('JWT_SECRET=short');
        putenv('ENV_VALIDATION_ENABLED=true');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('JWT_SECRET must be at least 32 characters');

        $this->runValidation();
    }

    public function testAllowsMissingJwtSecretInDevelopment()
    {
        putenv('APP_ENV=local');
        putenv('JWT_SECRET=');
        putenv('ENV_VALIDATION_ENABLED=true');

        $this->expectNotToPerformAssertions();
        $this->runValidation();
    }

    public function testRejectsInvalidJwtTtl()
    {
        putenv('JWT_TTL=invalid');
        putenv('ENV_VALIDATION_ENABLED=true');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("JWT_TTL must be a positive integer");

        $this->runValidation();
    }

    public function testRejectsNegativeJwtTtl()
    {
        putenv('JWT_TTL=-5');
        putenv('ENV_VALIDATION_ENABLED=true');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("JWT_TTL must be a positive integer");

        $this->runValidation();
    }

    public function testRejectsInvalidJwtRefreshTtl()
    {
        putenv('JWT_TTL=30');
        putenv('JWT_REFRESH_TTL=invalid');
        putenv('ENV_VALIDATION_ENABLED=true');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("JWT_REFRESH_TTL must be a positive integer");

        $this->runValidation();
    }

    public function testRejectsRefreshTtlNotGreaterThanTtl()
    {
        putenv('JWT_TTL=30');
        putenv('JWT_REFRESH_TTL=15');
        putenv('ENV_VALIDATION_ENABLED=true');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('JWT_REFRESH_TTL (15) must be greater than JWT_TTL (30)');

        $this->runValidation();
    }

    public function testAcceptsValidJwtTtlValues()
    {
        putenv('JWT_TTL=30');
        putenv('JWT_REFRESH_TTL=1440');
        putenv('ENV_VALIDATION_ENABLED=true');

        $this->expectNotToPerformAssertions();
        $this->runValidation();
    }

    public function testRejectsInvalidDatabaseConnection()
    {
        putenv('DB_CONNECTION=invalid');
        putenv('ENV_VALIDATION_ENABLED=true');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("DB_CONNECTION has invalid value 'invalid'");

        $this->runValidation();
    }

    public function testRejectsMissingDatabaseVarsForMysql()
    {
        putenv('DB_CONNECTION=mysql');
        putenv('DB_HOST=');
        putenv('DB_PORT=3306');
        putenv('DB_DATABASE=test');
        putenv('DB_USERNAME=user');
        putenv('ENV_VALIDATION_ENABLED=true');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Missing database vars for mysql: DB_HOST');

        $this->runValidation();
    }

    public function testAcceptsSqliteDatabaseWithoutHostPort()
    {
        putenv('DB_CONNECTION=sqlite');
        putenv('DB_HOST=');
        putenv('DB_PORT=');
        putenv('ENV_VALIDATION_ENABLED=true');

        $this->expectNotToPerformAssertions();
        $this->runValidation();
    }

    public function testRejectsInvalidCacheDriver()
    {
        putenv('CACHE_DRIVER=invalid');
        putenv('ENV_VALIDATION_ENABLED=true');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("CACHE_DRIVER has invalid value 'invalid'");

        $this->runValidation();
    }

    public function testAcceptsValidCacheDrivers()
    {
        $validDrivers = ['file', 'redis', 'memcached', 'database', 'array'];

        foreach ($validDrivers as $driver) {
            putenv('CACHE_DRIVER='.$driver);
            putenv('ENV_VALIDATION_ENABLED=false');

            $this->expectNotToPerformAssertions();
            $this->runValidation();
        }
    }

    public function testRejectsAppDebugEnabledInProduction()
    {
        putenv('APP_ENV=production');
        putenv('APP_DEBUG=true');
        putenv('ENV_VALIDATION_ENABLED=true');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('APP_DEBUG is enabled in production');

        $this->runValidation();
    }

    public function testAllowsAppDebugInDevelopment()
    {
        putenv('APP_ENV=local');
        putenv('APP_DEBUG=true');
        putenv('ENV_VALIDATION_ENABLED=true');

        $this->expectNotToPerformAssertions();
        $this->runValidation();
    }

    public function testRejectsMissingRedisVarsWhenBlacklistEnabled()
    {
        putenv('JWT_BLACKLIST_ENABLED=true');
        putenv('REDIS_HOST=');
        putenv('REDIS_PORT=');
        putenv('ENV_VALIDATION_ENABLED=true');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('JWT_BLACKLIST_ENABLED is true but required Redis vars missing');

        $this->runValidation();
    }

    public function testAllowsMissingRedisVarsWhenBlacklistDisabled()
    {
        putenv('JWT_BLACKLIST_ENABLED=false');
        putenv('REDIS_HOST=');
        putenv('REDIS_PORT=');
        putenv('ENV_VALIDATION_ENABLED=true');

        $this->expectNotToPerformAssertions();
        $this->runValidation();
    }

    public function testRejectsInvalidAppUrl()
    {
        putenv('APP_URL=not-a-url');
        putenv('ENV_VALIDATION_ENABLED=true');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("APP_URL must be a valid URL");

        $this->runValidation();
    }

    public function testAcceptsValidAppUrl()
    {
        putenv('APP_URL=https://example.com');
        putenv('FRONTEND_URL=https://app.example.com');
        putenv('ENV_VALIDATION_ENABLED=true');

        $this->expectNotToPerformAssertions();
        $this->runValidation();
    }

    public function testCollectsMultipleValidationErrors()
    {
        putenv('APP_ENV=invalid');
        putenv('APP_DEBUG=true');
        putenv('JWT_TTL=invalid');
        putenv('ENV_VALIDATION_ENABLED=true');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Environment validation failed');

        try {
            $this->runValidation();
        } catch (RuntimeException $e) {
            $this->assertStringContainsString("APP_ENV has invalid value 'invalid'", $e->getMessage());
            $this->assertStringContainsString("JWT_TTL must be a positive integer", $e->getMessage());

            throw $e;
        }
    }
}
