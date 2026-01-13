<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use RuntimeException;

class JwtSecretValidationTest extends TestCase
{
    public function test_empty_jwt_secret_throws_exception_in_production()
    {
        putenv('JWT_SECRET');
        putenv('APP_ENV', 'production');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('JWT_SECRET environment variable is required');

        $provider = new \App\Providers\AppServiceProvider(app());
        $provider->boot();
    }

    public function test_placeholder_jwt_secret_throws_exception_in_production()
    {
        putenv('JWT_SECRET', 'your-secret-key-here');
        putenv('APP_ENV', 'production');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('JWT_SECRET cannot be a placeholder value');

        $provider = new \App\Providers\AppServiceProvider(app());
        $provider->boot();
    }

    public function test_short_jwt_secret_throws_exception_in_production()
    {
        putenv('JWT_SECRET', 'short');
        putenv('APP_ENV', 'production');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('JWT_SECRET must be at least 32 characters long');

        $provider = new \App\Providers\AppServiceProvider(app());
        $provider->boot();
    }

    public function test_valid_jwt_secret_passes_validation()
    {
        putenv('JWT_SECRET', 'this-is-a-very-long-and-secure-jwt-secret-key-for-testing');
        putenv('APP_ENV', 'production');

        $provider = new \App\Providers\AppServiceProvider(app());
        $provider->boot();

        $this->assertTrue(true);
    }

    public function test_empty_jwt_secret_allowed_in_local()
    {
        putenv('JWT_SECRET');
        putenv('APP_ENV', 'local');

        $provider = new \App\Providers\AppServiceProvider(app());
        $provider->boot();

        $this->assertTrue(true);
    }

    public function test_placeholder_jwt_secret_allowed_in_local()
    {
        putenv('JWT_SECRET', 'your-secret-key-here');
        putenv('APP_ENV', 'local');

        $provider = new \App\Providers\AppServiceProvider(app());
        $provider->boot();

        $this->assertTrue(true);
    }

    public function test_validation_skipped_in_testing_environment()
    {
        putenv('JWT_SECRET');
        putenv('APP_ENV', 'testing');

        $provider = new \App\Providers\AppServiceProvider(app());
        $provider->boot();

        $this->assertTrue(true);
    }
}
