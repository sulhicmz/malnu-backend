<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Permission;

class PermissionTest extends TestCase
{
    /**
     * Test Permission model can be created with required fields.
     */
    public function testPermissionCanBeCreated(): void
    {
        $permission = Permission::create([
            'name' => 'manage-students',
            'guard_name' => 'web',
        ]);

        $this->assertInstanceOf(Permission::class, $permission);
        $this->assertEquals('manage-students', $permission->name);
        $this->assertEquals('web', $permission->guard_name);
    }

    /**
     * Test Permission has correct primary key configuration.
     */
    public function testPermissionPrimaryKeyConfiguration(): void
    {
        $permission = new Permission();
        
        $this->assertEquals('id', $permission->getKeyName());
        $this->assertEquals('string', $permission->getKeyType());
        $this->assertFalse($permission->incrementing);
    }

    /**
     * Test Permission model fillable attributes.
     */
    public function testPermissionFillableAttributes(): void
    {
        $permission = new Permission();
        $fillable = ['name', 'guard_name'];
        
        $this->assertEquals($fillable, $permission->getFillable());
    }
}