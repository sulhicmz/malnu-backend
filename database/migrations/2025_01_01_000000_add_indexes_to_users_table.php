<?php

declare(strict_types=1);

use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add indexes to frequently queried columns
        Schema::table('users', function (Blueprint $table) {
            // Indexes for common queries
            $table->index(['email'], 'idx_users_email');
            $table->index(['is_active'], 'idx_users_status');
            $table->index(['created_at'], 'idx_users_created_at');
            $table->index(['username'], 'idx_users_username');
            $table->index(['full_name'], 'idx_users_full_name');
            
            // Composite indexes for common query patterns
            $table->index(['is_active', 'created_at'], 'idx_users_status_created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop the indexes
            $table->dropIndex(['idx_users_email']);
            $table->dropIndex(['idx_users_status']);
            $table->dropIndex(['idx_users_created_at']);
            $table->dropIndex(['idx_users_username']);
            $table->dropIndex(['idx_users_full_name']);
            $table->dropIndex(['idx_users_status_created_at']);
        });
    }
};