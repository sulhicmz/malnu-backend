<?php

declare(strict_types = 1);

namespace Tests\Feature;

use Hypervel\Foundation\Testing\TestCase;
use Hypervel\Support\Facades\Schema;

/**
 * @internal
 * @coversNothing
 */
class DatabaseSchemaTest extends TestCase
{
    /**
     * Test database migrations can run successfully.
     */
    public function testDatabaseMigrationsRun(): void
    {
        // Verify that the users table exists
        $this->assertTrue(Schema::hasTable('users'), 'Users table should exist');
        
        // Verify that essential tables exist
        $this->assertTrue(Schema::hasTable('roles'), 'Roles table should exist');
        $this->assertTrue(Schema::hasTable('permissions'), 'Permissions table should exist');
        $this->assertTrue(Schema::hasTable('model_has_roles'), 'Model has roles table should exist');
        $this->assertTrue(Schema::hasTable('model_has_permissions'), 'Model has permissions table should exist');
        $this->assertTrue(Schema::hasTable('role_has_permissions'), 'Role has permissions table should exist');
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
            'parents',
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
     * Test that users table has required columns.
     */
    public function testUsersTableHasRequiredColumns(): void
    {
        $requiredColumns = [
            'id',
            'name',
            'email',
            'password',
            'created_at',
            'updated_at',
        ];
        
        foreach ($requiredColumns as $column) {
            $this->assertTrue(
                Schema::hasColumn('users', $column),
                "Users table should have {$column} column"
            );
        }
    }
    
    /**
     * Test that roles table has required columns.
     */
    public function testRolesTableHasRequiredColumns(): void
    {
        $requiredColumns = [
            'id',
            'name',
            'guard_name',
            'created_at',
            'updated_at',
        ];
        
        foreach ($requiredColumns as $column) {
            $this->assertTrue(
                Schema::hasColumn('roles', $column),
                "Roles table should have {$column} column"
            );
        }
    }
}