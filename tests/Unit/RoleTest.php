<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Role;
use App\Models\User;
use Hypervel\Foundation\Testing\TestCase;

class RoleTest extends TestCase
{
    public function testRoleCanBeCreated()
    {
        $role = new Role();
        
        $this->assertInstanceOf(Role::class, $role);
        $this->assertEquals('id', $role->getKeyName());
        $this->assertEquals('string', $role->getKeyType());
        $this->assertFalse($role->incrementing);
    }

    public function testRoleHasPermissionsRelationship()
    {
        $role = new Role();
        $relation = $role->permissions();
        
        $this->assertEquals('role_has_permissions', $relation->getTable());
        $this->assertEquals('role_id', $relation->getForeignKeyName());
        $this->assertEquals('permission_id', $relation->getRelatedKey());
    }

    public function testRoleAttributes()
    {
        $role = new Role();
        
        $fillable = [
            'name', 'guard_name'
        ];
        
        $modelFillable = $role->getFillable();
        
        foreach ($fillable as $attribute) {
            $this->assertContains($attribute, $modelFillable);
        }
    }

    public function testRoleCasts()
    {
        $role = new Role();
        $casts = $role->getCasts();
        
        $expectedCasts = [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
        
        foreach ($expectedCasts as $attribute => $cast) {
            $this->assertArrayHasKey($attribute, $casts);
            $this->assertEquals($cast, $casts[$attribute]);
        }
    }

    public function testRoleAssignToMethod()
    {
        $role = new Role();
        $role->id = 'test-role-id';
        
        $this->assertTrue(method_exists($role, 'assignTo'));
    }
}