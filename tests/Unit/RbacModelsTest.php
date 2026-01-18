<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Role;
use App\Models\Permission;
use App\Models\ModelHasRole;
use App\Models\ModelHasPermission;
use App\Models\User;
use Hyperf\Foundation\Testing\TestCase;

/**
 * @internal
 * @coversNothing
 */
class RbacModelsTest extends TestCase
{
    /**
     * Test role model configuration.
     */
    public function testRoleModelConfiguration(): void
    {
        $role = new Role();
        
        $this->assertEquals('id', $role->getKeyName());
        $this->assertIsArray($role->getFillable());
    }

    /**
     * Test role relationships.
     */
    public function testRoleRelationships(): void
    {
        $role = new Role();
        
        $usersRelation = $role->users();
        $this->assertEquals('role_id', $usersRelation->getRelatedPivotKeyName());
        
        $permissionsRelation = $role->permissions();
        $this->assertEquals('role_id', $permissionsRelation->getRelatedPivotKeyName());
    }

    /**
     * Test permission model configuration.
     */
    public function testPermissionModelConfiguration(): void
    {
        $permission = new Permission();
        
        $this->assertEquals('id', $permission->getKeyName());
        $this->assertIsArray($permission->getFillable());
    }

    /**
     * Test permission relationships.
     */
    public function testPermissionRelationships(): void
    {
        $permission = new Permission();
        
        $rolesRelation = $permission->roles();
        $this->assertEquals('permission_id', $rolesRelation->getRelatedPivotKeyName());
    }

    /**
     * Test model has role model configuration.
     */
    public function testModelHasRoleModelConfiguration(): void
    {
        $modelHasRole = new ModelHasRole();
        
        $this->assertEquals('id', $modelHasRole->getKeyName());
        $this->assertIsArray($modelHasRole->getFillable());
    }

    /**
     * Test model has permission model configuration.
     */
    public function testModelHasPermissionModelConfiguration(): void
    {
        $modelHasPermission = new ModelHasPermission();
        
        $this->assertEquals('id', $modelHasPermission->getKeyName());
        $this->assertIsArray($modelHasPermission->getFillable());
    }
}
