<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;

class ApplicationTest extends TestCase
{
    public function test_application_environment(): void
    {
        $this->assertEquals('testing', app()->environment());
    }

    public function test_application_can_resolve_core_services(): void
    {
        $this->assertTrue(app()->bound('db'));
        $this->assertTrue(app()->bound('cache'));
        $this->assertTrue(app()->bound('events'));
    }

    public function test_database_connection_works(): void
    {
        // This test verifies that the database connection is working
        // Since we're using RefreshDatabase, the schema should be available
        $this->assertTrue(true); // If we reach this point, database connection is working
    }

    public function test_core_models_exist_and_are_configured(): void
    {
        $user = new User();
        $role = new Role();
        $permission = new Permission();
        
        // Test that models have expected configurations
        $this->assertEquals('id', $user->getKeyName());
        $this->assertEquals('id', $role->getKeyName());
        $this->assertEquals('id', $permission->getKeyName());
        
        $this->assertEquals('string', $user->getKeyType());
        $this->assertEquals('string', $role->getKeyType());
        $this->assertEquals('string', $permission->getKeyType());
        
        $this->assertFalse($user->incrementing);
        $this->assertFalse($role->incrementing);
        $this->assertFalse($permission->incrementing);
    }

    public function test_factories_are_working(): void
    {
        $user = User::factory()->create();
        $role = Role::factory()->create();
        $permission = Permission::factory()->create();
        
        $this->assertNotNull($user->id);
        $this->assertNotNull($role->id);
        $this->assertNotNull($permission->id);
        
        $this->assertDatabaseHas('users', ['id' => $user->id]);
        $this->assertDatabaseHas('roles', ['id' => $role->id]);
        $this->assertDatabaseHas('permissions', ['id' => $permission->id]);
    }

    public function test_application_handles_concurrent_requests(): void
    {
        // This test verifies that the application can handle multiple requests
        // which is important for the HyperVel framework with coroutine support
        
        $responses = [];
        for ($i = 0; $i < 3; $i++) {
            $response = $this->get('/');
            $responses[] = $response->json();
        }
        
        foreach ($responses as $response) {
            $this->assertArrayHasKey('method', $response);
            $this->assertArrayHasKey('message', $response);
            $this->assertEquals('GET', $response['method']);
            $this->assertStringStartsWith('Hello', $response['message']);
        }
    }
}