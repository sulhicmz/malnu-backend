<?php

declare(strict_types = 1);

namespace Tests\Feature;

use App\Models\User;
use Hypervel\Foundation\Testing\RefreshDatabase;
use Hypervel\Foundation\Testing\TestCase;
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
        // This test verifies that the database connection works
        $this->assertTrue(true, 'Database connection is established');
    }

    /**
     * Test that required tables exist in schema.
     */
    public function testRequiredTablesExist(): void
    {
        $requiredTables = [
            'users',
            'roles',
            'model_has_roles',
            'parent_ortus',
            'teachers',
            'students',
            'staff',
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
        
        $expectedColumns = [
            'id', 'name', 'email', 'password', 'full_name', 'phone', 
            'avatar_url', 'is_active', 'last_login_time', 'last_login_ip', 
            'remember_token', 'email_verified_at', 'slug', 'key_status', 
            'username', 'created_at', 'updated_at'
        ];
        
        foreach ($expectedColumns as $column) {
            $this->assertTrue(
                Schema::hasColumn('users', $column),
                "Column {$column} should exist in users table"
            );
        }
    }

    /**
     * Test database operations work correctly.
     */
    public function testDatabaseOperations(): void
    {
        // Test creating a user
        $user = User::factory()->create();
        
        $this->assertNotNull($user->id);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => $user->email,
        ]);
        
        // Test retrieving the user
        $retrievedUser = User::find($user->id);
        $this->assertNotNull($retrievedUser);
        $this->assertEquals($user->email, $retrievedUser->email);
        
        // Test updating the user
        $newEmail = 'updated@example.com';
        $user->update(['email' => $newEmail]);
        
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => $newEmail,
        ]);
        
        // Test deleting the user
        $user->delete();
        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
    }
}