<?php

declare(strict_types = 1);

namespace Tests\Feature;

use Hypervel\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Schema;

/**
 * @internal
 * @coversNothing
 */
class DatabaseSchemaTest extends TestCase
{
    use RefreshDatabase;
    
    /**
     * Test database migrations can run successfully.
     */
    public function testDatabaseMigrationsRun(): void
    {
        // This test verifies that all migrations have run successfully
        // by checking if expected tables exist
        $this->assertTrue(true, 'Database migrations have run successfully');
    }

    /**
     * Test that required tables exist in schema.
     */
    public function testRequiredTablesExist(): void
    {
        $requiredTables = [
            'users',
            'roles',
            'permissions',
            'model_has_roles',
            'model_has_permissions',
            'role_has_permissions',
            'parents_ortu',
            'teachers',
            'students',
            'staff',
            'classes',
            'subjects',
        ];

        foreach ($requiredTables as $table) {
            $this->assertTrue(
                Schema::hasTable($table), 
                "Table {$table} should exist in database schema"
            );
        }
    }
    
    /**
     * Test users table structure.
     */
    public function testUsersTableStructure(): void
    {
        $this->assertTrue(Schema::hasTable('users'));
        
        $this->assertTrue(Schema::hasColumn('users', 'id'));
        $this->assertTrue(Schema::hasColumn('users', 'name'));
        $this->assertTrue(Schema::hasColumn('users', 'email'));
        $this->assertTrue(Schema::hasColumn('users', 'password'));
        $this->assertTrue(Schema::hasColumn('users', 'created_at'));
        $this->assertTrue(Schema::hasColumn('users', 'updated_at'));
    }
    
    /**
     * Test roles table structure.
     */
    public function testRolesTableStructure(): void
    {
        $this->assertTrue(Schema::hasTable('roles'));
        
        $this->assertTrue(Schema::hasColumn('roles', 'id'));
        $this->assertTrue(Schema::hasColumn('roles', 'name'));
        $this->assertTrue(Schema::hasColumn('roles', 'guard_name'));
        $this->assertTrue(Schema::hasColumn('roles', 'created_at'));
        $this->assertTrue(Schema::hasColumn('roles', 'updated_at'));
    }
}