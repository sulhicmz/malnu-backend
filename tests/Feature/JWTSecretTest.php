<?php

namespace Tests\Feature;

use App\Services\JWTService;
use PHPUnit\Framework\TestCase;

class JWTSecretTest extends TestCase
{
    public function test_jwt_secret_is_configured()
    {
        // Test that JWTService can be instantiated without errors
        $jwtService = new JWTService();
        
        // The service should have a secret configured
        $this->assertNotEmpty($jwtService->getSecretForTest());
    }
    
    public function test_jwt_secret_validation_in_production()
    {
        // This test would require setting the environment to production
        // and ensuring JWT_SECRET is empty to trigger the exception
        $this->markTestIncomplete('Environment-dependent test requires specific setup');
    }
}

// Add a method to JWTService to allow testing the secret
if (!method_exists('App\Services\JWTService', 'getSecretForTest')) {
    // This is just for documentation - the method would need to be added to JWTService
}