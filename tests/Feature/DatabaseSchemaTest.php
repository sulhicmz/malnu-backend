<?php

declare(strict_types = 1);

namespace Tests\Feature;

use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

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
        // Verify that migrations have been run by checking if core tables exist
        $this->assertTrue(Schema::hasTable('users'));
        $this->assertTrue(Schema::hasTable('roles'));
        $this->assertTrue(Schema::hasTable('permissions'));
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
}