<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Contracts\AuthServiceInterface;
use App\Contracts\JWTServiceInterface;
use App\Contracts\TokenBlacklistServiceInterface;
use App\Http\Controllers\Api\AuthController;
use App\Http\Middleware\JWTMiddleware;
use App\Http\Middleware\RoleMiddleware;
use PHPUnit\Framework\TestCase;

class DependencyInjectionTest extends TestCase
{
    public function test_auth_controller_uses_dependency_injection()
    {
        $reflection = new \ReflectionClass(AuthController::class);
        $constructor = $reflection->getConstructor();
        
        $this->assertNotNull($constructor, 'AuthController should have a constructor');
        
        $parameters = $constructor->getParameters();
        
        $this->assertCount(1, $parameters, 'AuthController constructor should have 1 parameter');
        $this->assertSame(AuthServiceInterface::class, $parameters[0]->getType()->getName(), 'AuthController should type-hint AuthServiceInterface');
        
        $this->assertStringNotContainsString('new', file_get_contents($reflection->getFileName()), 'AuthController should not use new keyword for service instantiation');
    }

    public function test_jwt_middleware_uses_dependency_injection()
    {
        $reflection = new \ReflectionClass(JWTMiddleware::class);
        $constructor = $reflection->getConstructor();
        
        $this->assertNotNull($constructor, 'JWTMiddleware should have a constructor');
        
        $parameters = $constructor->getParameters();
        
        $this->assertGreaterThanOrEqual(2, $parameters, 'JWTMiddleware constructor should have at least 2 parameters');
        
        $hasAuthServiceParam = false;
        foreach ($parameters as $param) {
            if ($param->getType() && $param->getType()->getName() === AuthServiceInterface::class) {
                $hasAuthServiceParam = true;
                break;
            }
        }
        
        $this->assertTrue($hasAuthServiceParam, 'JWTMiddleware should inject AuthServiceInterface');
        $this->assertStringNotContainsString('new AuthService', file_get_contents($reflection->getFileName()), 'JWTMiddleware should not use new AuthService()');
    }

    public function test_role_middleware_uses_dependency_injection()
    {
        $reflection = new \ReflectionClass(RoleMiddleware::class);
        $constructor = $reflection->getConstructor();
        
        $this->assertNotNull($constructor, 'RoleMiddleware should have a constructor');
        
        $parameters = $constructor->getParameters();
        
        $this->assertCount(1, $parameters, 'RoleMiddleware constructor should have 1 parameter');
        $this->assertSame(AuthServiceInterface::class, $parameters[0]->getType()->getName(), 'RoleMiddleware should type-hint AuthServiceInterface');
        
        $this->assertStringNotContainsString('new AuthService', file_get_contents($reflection->getFileName()), 'RoleMiddleware should not use new AuthService()');
    }

    public function test_auth_service_uses_dependency_injection()
    {
        $reflection = new \ReflectionClass(\App\Services\AuthService::class);
        $constructor = $reflection->getConstructor();
        
        $this->assertNotNull($constructor, 'AuthService should have a constructor');
        
        $parameters = $constructor->getParameters();
        
        $this->assertCount(3, $parameters, 'AuthService constructor should have 3 parameters');
        
        $paramTypes = array_map(function($param) {
            return $param->getType() ? $param->getType()->getName() : null;
        }, $parameters);
        
        $this->assertContains(JWTServiceInterface::class, $paramTypes, 'AuthService should inject JWTServiceInterface');
        $this->assertContains(TokenBlacklistServiceInterface::class, $paramTypes, 'AuthService should inject TokenBlacklistServiceInterface');
        $this->assertContains(\App\Services\EmailService::class, $paramTypes, 'AuthService should inject EmailService');
        
        $content = file_get_contents($reflection->getFileName());
        $this->assertStringNotContainsString('new JWTService()', $content, 'AuthService should not use new JWTService()');
        $this->assertStringNotContainsString('new TokenBlacklistService()', $content, 'AuthService should not use new TokenBlacklistService()');
        $this->assertStringNotContainsString('new EmailService()', $content, 'AuthService should not use new EmailService()');
    }

    public function test_service_bindings_exist()
    {
        $dependencies = require base_path('config/dependencies.php');
        
        $this->assertIsArray($dependencies, 'dependencies.php should return an array');
        
        $this->assertArrayHasKey(AuthServiceInterface::class, $dependencies, 'AuthServiceInterface should be bound in dependencies.php');
        $this->assertSame(\App\Services\AuthService::class, $dependencies[AuthServiceInterface::class], 'AuthServiceInterface should bind to AuthService');
        
        $this->assertArrayHasKey(JWTServiceInterface::class, $dependencies, 'JWTServiceInterface should be bound in dependencies.php');
        $this->assertSame(\App\Services\JWTService::class, $dependencies[JWTServiceInterface::class], 'JWTServiceInterface should bind to JWTService');
        
        $this->assertArrayHasKey(TokenBlacklistServiceInterface::class, $dependencies, 'TokenBlacklistServiceInterface should be bound in dependencies.php');
        $this->assertSame(\App\Services\TokenBlacklistService::class, $dependencies[TokenBlacklistServiceInterface::class], 'TokenBlacklistServiceInterface should bind to TokenBlacklistService');
    }
}
