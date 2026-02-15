<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Contracts\AuthServiceInterface;
use Hypervel\Container\Container;
use HyperfTestFramework\TestCase;
use ReflectionClass;

/**
 * @internal
 * @coversNothing
 */
class DependencyInjectionTest extends TestCase
{
    public function testAuthControllerUsesDependencyInjection()
    {
        $container = new Container();

        $controller = $container->get(\App\Http\Controllers\Api\AuthController::class);
        $reflection = new ReflectionClass($controller);

        $constructor = $reflection->getConstructor();

        $this->assertNotNull($constructor, 'AuthController should have a constructor');

        $authServiceParam = null;
        foreach ($constructor->getParameters() as $param) {
            if ($param->getType()->getName() === AuthServiceInterface::class) {
                $authServiceParam = $param;
                break;
            }
        }

        $this->assertNotNull($authServiceParam, 'AuthController should inject AuthServiceInterface');
        $this->assertTrue($authServiceParam->allowsNull(), 'AuthServiceInterface should be nullable to allow container injection');
    }

    public function testJWTMiddlewareUsesDependencyInjection()
    {
        $container = new Container();

        $middleware = $container->get(\App\Http\Middleware\JWTMiddleware::class);
        $reflection = new ReflectionClass($middleware);

        $constructor = $reflection->getConstructor();

        $this->assertNotNull($constructor, 'JWTMiddleware should have a constructor');

        $authServiceParam = null;
        foreach ($constructor->getParameters() as $param) {
            if ($param->getType()->getName() === AuthServiceInterface::class) {
                $authServiceParam = $param;
                break;
            }
        }

        $this->assertNotNull($authServiceParam, 'JWTMiddleware should inject AuthServiceInterface');
        $this->assertTrue($authServiceParam->allowsNull(), 'AuthServiceInterface should be nullable to allow container injection');
    }

    public function testRoleMiddlewareUsesDependencyInjection()
    {
        $container = new Container();

        $middleware = $container->get(\App\Http\Middleware\RoleMiddleware::class);
        $reflection = new ReflectionClass($middleware);

        $constructor = $reflection->getConstructor();

        $this->assertNotNull($constructor, 'RoleMiddleware should have a constructor');

        $authServiceParam = null;
        foreach ($constructor->getParameters() as $param) {
            if ($param->getType()->getName() === AuthServiceInterface::class) {
                $authServiceParam = $param;
                break;
            }
        }

        $this->assertNotNull($authServiceParam, 'RoleMiddleware should inject AuthServiceInterface');
        $this->assertTrue($authServiceParam->allowsNull(), 'AuthServiceInterface should be nullable to allow container injection');
    }

    public function testAuthServiceUsesDependencyInjection()
    {
        $container = new Container();

        $service = $container->get(\App\Services\AuthService::class);
        $reflection = new ReflectionClass($service);

        $constructor = $reflection->getConstructor();

        $this->assertNotNull($constructor, 'AuthService should have a constructor');

        $expectedParams = [
            \App\Contracts\JWTServiceInterface::class,
            \App\Contracts\TokenBlacklistServiceInterface::class,
            \App\Contracts\EmailServiceInterface::class,
        ];

        $actualParams = [];
        foreach ($constructor->getParameters() as $param) {
            $actualParams[] = $param->getType()->getName();
        }

        foreach ($expectedParams as $expected) {
            $this->assertContains($expected, $actualParams, 'AuthService should inject ' . $expected);
        }
    }

    public function testServiceBindingsExist()
    {
        $config = require base_path('config/dependencies.php');

        $this->assertIsArray($config, 'config/dependencies.php should return an array');
        $this->assertArrayHasKey(ContainerInterface::class, $config, 'Service bindings should exist');

        $bindings = $config[ContainerInterface::class];

        $this->assertArrayHasKey(\App\Contracts\AuthServiceInterface::class, $bindings, 'AuthServiceInterface should be bound');
        $this->assertArrayHasKey(\App\Contracts\JWTServiceInterface::class, $bindings, 'JWTServiceInterface should be bound');
        $this->assertArrayHasKey(\App\Contracts\TokenBlacklistServiceInterface::class, $bindings, 'TokenBlacklistServiceInterface should be bound');
        $this->assertArrayHasKey(\App\Contracts\EmailServiceInterface::class, $bindings, 'EmailServiceInterface should be bound');

        $this->assertEquals(\App\Services\AuthService::class, $bindings[\App\Contracts\AuthServiceInterface::class], 'AuthService should be bound to AuthService');
        $this->assertEquals(\App\Services\JWTService::class, $bindings[\App\Contracts\JWTServiceInterface::class], 'JWTService should be bound to JWTService');
        $this->assertEquals(\App\Services\TokenBlacklistService::class, $bindings[\App\Contracts\TokenBlacklistServiceInterface::class], 'TokenBlacklistService should be bound to TokenBlacklistService');
        $this->assertEquals(\App\Services\EmailService::class, $bindings[\App\Contracts\EmailServiceInterface::class], 'EmailService should be bound to EmailService');
    }
}
