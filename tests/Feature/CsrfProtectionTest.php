<?php

declare(strict_types=1);

namespace Tests\Feature;

use ReflectionClass;
use Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
class CsrfProtectionTest extends TestCase
{
    public function testCsrfMiddlewareClassExists()
    {
        $this->assertTrue(class_exists(\App\Http\Middleware\VerifyCsrfToken::class));
    }

    public function testCsrfMiddlewareExtendsHypervelBase()
    {
        $middleware = new ReflectionClass(\App\Http\Middleware\VerifyCsrfToken::class);
        $parentClass = $middleware->getParentClass();

        $this->assertNotNull($parentClass);
        $this->assertEquals(\Hypervel\Foundation\Http\Middleware\VerifyCsrfToken::class, $parentClass->getName());
    }

    public function testCsrfMiddlewareHasExceptProperty()
    {
        $middleware = new ReflectionClass(\App\Http\Middleware\VerifyCsrfToken::class);
        $this->assertTrue($middleware->hasProperty('except'));
    }

    public function testApiRoutesAreExcludedFromCsrf()
    {
        $reflection = new ReflectionClass(\App\Http\Middleware\VerifyCsrfToken::class);
        $middleware = $reflection->newInstanceWithoutConstructor();

        $property = $reflection->getProperty('except');
        $property->setAccessible(true);

        $except = $property->getValue($middleware);

        $this->assertIsArray($except);
        $this->assertContains('api/*', $except);
    }
}
