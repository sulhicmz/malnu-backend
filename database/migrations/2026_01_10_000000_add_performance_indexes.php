<?php

declare(strict_types=1);

use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Schema\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->index(['class_id', 'status', 'name'], 'idx_students_class_status_name');
        });

        Schema::table('teachers', function (Blueprint $table) {
            $table->index(['status', 'name'], 'idx_teachers_status_name');
        });

        Schema::table('leave_requests', function (Blueprint $table) {
            $table->index(['staff_id', 'status', 'created_at'], 'idx_leave_requests_staff_status_created');
            $table->index(['start_date', 'end_date'], 'idx_leave_requests_dates');
        });

        Schema::table('staff_attendances', function (Blueprint $table) {
            $table->index(['attendance_date', 'status'], 'idx_staff_attendances_date_status');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index(['is_active', 'email'], 'idx_users_active_email');
        });
    }

    public function down()
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropIndex('idx_students_class_status_name');
        });

        Schema::table('teachers', function (Blueprint $table) {
            $table->dropIndex('idx_teachers_status_name');
        });

        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropIndex('idx_leave_requests_staff_status_created');
            $table->dropIndex('idx_leave_requests_dates');
        });

        Schema::table('staff_attendances', function (Blueprint $table) {
            $table->dropIndex('idx_staff_attendances_date_status');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_active_email');
        });
    }
};
