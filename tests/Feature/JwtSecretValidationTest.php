<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Providers\AppServiceProvider;

class JwtSecretValidationTest extends TestCase
{
    public function test_jwt_secret_validation_with_empty_secret_in_production()
    {
        // Set environment to production and JWT_SECRET to empty
        putenv('APP_ENV=production');
        putenv('JWT_SECRET=');
        
        $provider = new AppServiceProvider();
        
        // This should throw an exception when boot is called with empty JWT_SECRET in production
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('JWT_SECRET is not set in .env file. Please set a secure JWT secret before deploying to production.');
        
        $provider->boot();
    }
    
    public function test_jwt_secret_validation_with_example_secret_in_production()
    {
        // Set environment to production and JWT_SECRET to the example value
        putenv('APP_ENV=production');
        putenv('JWT_SECRET=b759622f76ff2cd8098768e41a58eab2de4db374adba74a126c52cb84ee3502f');
        
        $provider = new AppServiceProvider();
        
        // This should throw an exception when boot is called with example JWT_SECRET in production
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('JWT_SECRET is using the default example value. Please set a unique secure JWT secret before deploying to production.');
        
        $provider->boot();
    }
    
    public function test_jwt_secret_validation_passes_in_development()
    {
        // Set environment to development and JWT_SECRET to empty
        putenv('APP_ENV=development');
        putenv('JWT_SECRET=');
        
        $provider = new AppServiceProvider();
        
        // This should not throw an exception in development environment
        $provider->boot();
        
        $this->assertTrue(true); // If we reach here, no exception was thrown
    }
    
    public function test_jwt_secret_validation_passes_with_valid_secret_in_production()
    {
        // Set environment to production and JWT_SECRET to a valid secret
        putenv('APP_ENV=production');
        putenv('JWT_SECRET=valid_secret_key_that_is_not_the_example_value');
        
        $provider = new AppServiceProvider();
        
        // This should not throw an exception with a valid secret
        $provider->boot();
        
        $this->assertTrue(true); // If we reach here, no exception was thrown
    }
}