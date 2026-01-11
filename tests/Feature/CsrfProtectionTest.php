<?php

declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;

class CsrfProtectionTest extends TestCase
{
    public function test_verify_csrf_token_middleware_class_exists()
    {
        $this->assertTrue(class_exists(\App\Http\Middleware\VerifyCsrfToken::class));
    }

    public function test_verify_csrf_token_has_except_property()
    {
        $middleware = new \App\Http\Middleware\VerifyCsrfToken();
        
        $reflection = new \ReflectionClass($middleware);
        $property = $reflection->getProperty('except');
        $property->setAccessible(true);
        $except = $property->getValue($middleware);
        
        $this->assertIsArray($except);
        $this->assertContains('api/*', $except);
        $this->assertContains('csp-report', $except);
    }

    public function test_api_routes_are_excluded_from_csrf()
    {
        $middleware = new \App\Http\Middleware\VerifyCsrfToken();
        
        $reflection = new \ReflectionClass($middleware);
        $property = $reflection->getProperty('except');
        $property->setAccessible(true);
        $except = $property->getValue($middleware);
        
        $this->assertContains('api/*', $except);
        $this->assertContains('csp-report', $except);
    }
}
