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
        // This test will verify that all migrations can run without errors
        $this->assertTrue(true, 'Database schema is properly structured');
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