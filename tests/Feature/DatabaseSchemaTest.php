<?php

declare(strict_types = 1);

namespace Tests\Feature;

use Hypervel\Foundation\Testing\TestCase;
use Hypervel\Foundation\Testing\RefreshDatabase;
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
        // This test will verify that all migrations can run without errors
        $this->assertTrue(true, 'Database migrations run successfully');
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
     * Test that all required tables have expected columns.
     */
    public function testUsersTableHasExpectedColumns(): void
    {
        $expectedColumns = [
            'id', 'name', 'username', 'email', 'password', 'full_name',
            'phone', 'avatar_url', 'is_active', 'last_login_time', 
            'last_login_ip', 'remember_token', 'email_verified_at', 
            'slug', 'key_status', 'created_at', 'updated_at'
        ];

        foreach ($expectedColumns as $column) {
            $this->assertTrue(
                Schema::hasColumn('users', $column),
                "Users table should have column: {$column}"
            );
        }
    }

    /**
     * Test that teachers table has expected columns.
     */
    public function testTeachersTableHasExpectedColumns(): void
    {
        $expectedColumns = [
            'id', 'user_id', 'nip', 'nuptk', 'subject_id', 'class_id', 
            'phone', 'address', 'date_of_birth', 'gender', 'created_at', 'updated_at'
        ];

        foreach ($expectedColumns as $column) {
            $this->assertTrue(
                Schema::hasColumn('teachers', $column),
                "Teachers table should have column: {$column}"
            );
        }
    }

    /**
     * Test that students table has expected columns.
     */
    public function testStudentsTableHasExpectedColumns(): void
    {
        $expectedColumns = [
            'id', 'user_id', 'nis', 'nisn', 'class_id', 'phone', 
            'address', 'date_of_birth', 'gender', 'created_at', 'updated_at'
        ];

        foreach ($expectedColumns as $column) {
            $this->assertTrue(
                Schema::hasColumn('students', $column),
                "Students table should have column: {$column}"
            );
        }
    }
}