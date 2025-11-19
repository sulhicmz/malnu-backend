<?php

declare(strict_types = 1);

namespace Tests\Feature;

use Tests\TestCase;
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
        // Verify that core tables exist in the database
        $this->assertTrue(Schema::hasTable('users'));
        $this->assertTrue(Schema::hasTable('roles'));
        $this->assertTrue(Schema::hasTable('permissions'));
        $this->assertTrue(Schema::hasTable('model_has_roles'));
        $this->assertTrue(Schema::hasTable('model_has_permissions'));
        $this->assertTrue(Schema::hasTable('role_has_permissions'));
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
        ];

        foreach ($requiredTables as $table) {
            $this->assertTrue(
                Schema::hasTable($table), 
                "Table {$table} should exist in database schema"
            );
        }
    }

    /**
     * Test that required columns exist in users table.
     */
    public function testUserTableHasRequiredColumns(): void
    {
        $requiredColumns = [
            'id',
            'name',
            'email',
            'password',
            'full_name',
            'phone',
            'avatar_url',
            'is_active',
            'last_login_time',
            'last_login_ip',
            'email_verified_at',
            'slug',
            'key_status',
        ];

        foreach ($requiredColumns as $column) {
            $this->assertTrue(
                Schema::hasColumn('users', $column),
                "Users table should have column {$column}"
            );
        }
    }

    /**
     * Test that required columns exist in roles table.
     */
    public function testRolesTableHasRequiredColumns(): void
    {
        $requiredColumns = [
            'id',
            'name',
            'guard_name',
        ];

        foreach ($requiredColumns as $column) {
            $this->assertTrue(
                Schema::hasColumn('roles', $column),
                "Roles table should have column {$column}"
            );
        }
    }

    /**
     * Test that required columns exist in permissions table.
     */
    public function testPermissionsTableHasRequiredColumns(): void
    {
        $requiredColumns = [
            'id',
            'name',
            'guard_name',
        ];

        foreach ($requiredColumns as $column) {
            $this->assertTrue(
                Schema::hasColumn('permissions', $column),
                "Permissions table should have column {$column}"
            );
        }
    }
}