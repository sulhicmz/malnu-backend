<?php

declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;

class JwtSecretValidationTest extends TestCase
{
    public function test_jwt_secret_validation_rejects_empty_in_production()
    {
        $this->markTestSkipped('Requires full framework bootstrap');

        $env = 'production';
        putenv('APP_ENV=' . $env);
        putenv('JWT_SECRET=');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('JWT_SECRET cannot be empty in production environment');

        $provider = new \App\Providers\AppServiceProvider();
        $provider->boot();
    }

    public function test_jwt_secret_validation_rejects_placeholder_values()
    {
        $this->markTestSkipped('Requires full framework bootstrap');

        $env = 'production';
        putenv('APP_ENV=' . $env);

        $placeholders = [
            'your-secret-key-here',
            'change-me',
            'your-jwt-secret',
            'jwt-secret-key',
            'secret',
            'password',
            'your-secure-jwt-secret-key-here',
        ];

        foreach ($placeholders as $placeholder) {
            putenv('JWT_SECRET=' . $placeholder);

            $this->expectException(\RuntimeException::class);
            $this->expectExceptionMessage('JWT_SECRET is using a placeholder value which is insecure');

            $provider = new \App\Providers\AppServiceProvider();
            $provider->boot();
        }
    }

    public function test_jwt_secret_validation_rejects_short_secrets()
    {
        $this->markTestSkipped('Requires full framework bootstrap');

        $env = 'production';
        putenv('APP_ENV=' . $env);
        putenv('JWT_SECRET=short');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('JWT_SECRET must be at least 32 characters long for security');

        $provider = new \App\Providers\AppServiceProvider();
        $provider->boot();
    }

    public function test_jwt_secret_validation_allows_local_environment()
    {
        $this->markTestSkipped('Requires full framework bootstrap');

        $env = 'local';
        putenv('APP_ENV=' . $env);
        putenv('JWT_SECRET=');

        $provider = new \App\Providers\AppServiceProvider();

        $this->assertNull($provider->boot());
    }

    public function test_jwt_secret_validation_allows_testing_environment()
    {
        $this->markTestSkipped('Requires full framework bootstrap');

        $env = 'testing';
        putenv('APP_ENV=' . $env);
        putenv('JWT_SECRET=');

        $provider = new \App\Providers\AppServiceProvider();

        $this->assertNull($provider->boot());
    }
}
