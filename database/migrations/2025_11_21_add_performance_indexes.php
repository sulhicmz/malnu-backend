<?php

declare(strict_types=1);

use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add indexes to improve query performance for common queries
        
        // Indexes for leave_requests table
        Schema::table('leave_requests', function (Blueprint $table) {
            // Index for common filter: staff_id
            $table->index('staff_id', 'idx_leave_requests_staff_id');
            
            // Index for common filter: status
            $table->index('status', 'idx_leave_requests_status');
            
            // Index for common filter: leave_type_id
            $table->index('leave_type_id', 'idx_leave_requests_leave_type_id');
            
            // Composite index for date range queries
            $table->index(['start_date', 'end_date'], 'idx_leave_requests_date_range');
            
            // Composite index for status and date (common combination filter)
            $table->index(['status', 'created_at'], 'idx_leave_requests_status_created');
            
            // Index for approved_by (for approval queries)
            $table->index('approved_by', 'idx_leave_requests_approved_by');
        });

        // Indexes for leave_balances table
        Schema::table('leave_balances', function (Blueprint $table) {
            // Index for common filter: staff_id
            $table->index('staff_id', 'idx_leave_balances_staff_id');
            
            // Index for common filter: leave_type_id
            $table->index('leave_type_id', 'idx_leave_balances_leave_type_id');
            
            // Composite index for year-based queries
            $table->index(['staff_id', 'year'], 'idx_leave_balances_staff_year');
        });

        // Indexes for staff_attendances table
        Schema::table('staff_attendances', function (Blueprint $table) {
            // Index for common filter: staff_id
            $table->index('staff_id', 'idx_staff_attendances_staff_id');
            
            // Index for date-based queries
            $table->index('attendance_date', 'idx_staff_attendances_date');
            
            // Composite index for staff and date (common combination filter)
            $table->index(['staff_id', 'attendance_date'], 'idx_staff_attendances_staff_date');
            
            // Index for status-based queries
            $table->index('status', 'idx_staff_attendances_status');
        });

        // Indexes for leave_types table
        Schema::table('leave_types', function (Blueprint $table) {
            // Index for code (common lookup)
            $table->index('code', 'idx_leave_types_code');
            
            // Index for active status
            $table->index('is_active', 'idx_leave_types_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropIndex('idx_leave_requests_staff_id');
            $table->dropIndex('idx_leave_requests_status');
            $table->dropIndex('idx_leave_requests_leave_type_id');
            $table->dropIndex('idx_leave_requests_date_range');
            $table->dropIndex('idx_leave_requests_status_created');
            $table->dropIndex('idx_leave_requests_approved_by');
        });

        Schema::table('leave_balances', function (Blueprint $table) {
            $table->dropIndex('idx_leave_balances_staff_id');
            $table->dropIndex('idx_leave_balances_leave_type_id');
            $table->dropIndex('idx_leave_balances_staff_year');
        });

        Schema::table('staff_attendances', function (Blueprint $table) {
            $table->dropIndex('idx_staff_attendances_staff_id');
            $table->dropIndex('idx_staff_attendances_date');
            $table->dropIndex('idx_staff_attendances_staff_date');
            $table->dropIndex('idx_staff_attendances_status');
        });

        Schema::table('leave_types', function (Blueprint $table) {
            $table->dropIndex('idx_leave_types_code');
            $table->dropIndex('idx_leave_types_active');
        });
    }
};