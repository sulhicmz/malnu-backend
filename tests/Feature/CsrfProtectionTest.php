<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class CsrfProtectionTest extends TestCase
{
    public function testCsrfMiddlewareClassExists()
    {
        $this->assertTrue(class_exists(\App\Http\Middleware\VerifyCsrfToken::class));
    }

    public function testCsrfMiddlewareImplementsMiddlewareInterface()
    {
        $middleware = new \ReflectionClass(\App\Http\Middleware\VerifyCsrfToken::class);
        $this->assertTrue($middleware->implementsInterface(\Psr\Http\Server\MiddlewareInterface::class));
    }

    public function testCsrfMiddlewareHasProcessMethod()
    {
        $middleware = new \ReflectionClass(\App\Http\Middleware\VerifyCsrfToken::class);
        $this->assertTrue($middleware->hasMethod('process'));
    }

    public function testGetExcludedRoutesReturnsArray()
    {
        $reflection = new \ReflectionClass(\App\Http\Middleware\VerifyCsrfToken::class);
        $method = $reflection->getMethod('getExcludedRoutes');
        $method->setAccessible(true);

        $middleware = new \ReflectionClass(\App\Http\Middleware\VerifyCsrfToken::class);
        $instance = $reflection->newInstanceWithoutConstructor();

        $result = $method->invoke($instance);

        $this->assertIsArray($result);
        $this->assertContains('api/*', $result);
    }

    public function testMatchesPatternWithExactMatch()
    {
        $reflection = new \ReflectionClass(\App\Http\Middleware\VerifyCsrfToken::class);
        $method = $reflection->getMethod('matchesPattern');
        $method->setAccessible(true);

        $instance = $reflection->newInstanceWithoutConstructor();

        $this->assertTrue($method->invoke($instance, '/api/test', '/api/test'));
        $this->assertFalse($method->invoke($instance, '/api/test', '/api/other'));
    }

    public function testMatchesPatternWithWildcard()
    {
        $reflection = new \ReflectionClass(\App\Http\Middleware\VerifyCsrfToken::class);
        $method = $reflection->getMethod('matchesPattern');
        $method->setAccessible(true);

        $instance = $reflection->newInstanceWithoutConstructor();

        $this->assertTrue($method->invoke($instance, '/api/test', 'api/*'));
        $this->assertTrue($method->invoke($instance, '/api/users', 'api/*'));
        $this->assertFalse($method->invoke($instance, '/web/test', 'api/*'));
    }

    public function testVerifyTokenReturnsTrueWhenSessionNotAvailable()
    {
        $reflection = new \ReflectionClass(\App\Http\Middleware\VerifyCsrfToken::class);
        $method = $reflection->getMethod('verifyToken');
        $method->setAccessible(true);

        $instance = $reflection->newInstanceWithoutConstructor();

        $this->assertTrue($method->invoke($instance, 'some-token'));
    }
}
