<?php

declare(strict_types=1);

use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hypervel\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add indexes to frequently queried columns in users table
        Schema::table('users', function (Blueprint $table) {
            // Index for email (frequently used for authentication)
            $table->index('email', 'idx_users_email');
            
            // Index for username (frequently used for login)
            $table->index('username', 'idx_users_username');
            
            // Index for status field
            $table->index('is_active', 'idx_users_is_active');
            
            // Index for creation date (common for ordering/filtering)
            $table->index('created_at', 'idx_users_created_at');
            
            // Index for last login time
            $table->index('last_login_time', 'idx_users_last_login_time');
            
            // Composite index for common query patterns (status and creation date)
            $table->index(['is_active', 'created_at'], 'idx_users_status_created');
            
            // Index for full_name (used for search)
            $table->index('full_name', 'idx_users_full_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_email');
            $table->dropIndex('idx_users_username');
            $table->dropIndex('idx_users_is_active');
            $table->dropIndex('idx_users_created_at');
            $table->dropIndex('idx_users_last_login_time');
            $table->dropIndex('idx_users_status_created');
            $table->dropIndex('idx_users_full_name');
        });
    }
};