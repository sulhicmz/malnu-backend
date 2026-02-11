<?php

declare(strict_types=1);

use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->index('class_id', 'idx_students_class_id');
            $table->index('parent_id', 'idx_students_parent_id');
        });

        Schema::table('teachers', function (Blueprint $table) {
            $table->index('status', 'idx_teachers_status');
        });

        Schema::table('staff', function (Blueprint $table) {
            $table->index('status', 'idx_staff_status');
        });

        Schema::table('marketplace_products', function (Blueprint $table) {
            $table->index('created_by', 'idx_marketplace_products_created_by');
            $table->index('is_active', 'idx_marketplace_products_is_active');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->index('transaction_type', 'idx_transactions_type');
            $table->index('status', 'idx_transactions_status');
        });

        Schema::table('staff_attendances', function (Blueprint $table) {
            $table->index('staff_id', 'idx_staff_attendances_staff_id');
            $table->index('attendance_date', 'idx_staff_attendances_date');
        });

        Schema::table('leave_requests', function (Blueprint $table) {
            $table->index('staff_id', 'idx_leave_requests_staff_id');
            $table->index('status', 'idx_leave_requests_status');
            $table->index('start_date', 'idx_leave_requests_start_date');
        });

        Schema::table('learning_materials', function (Blueprint $table) {
            $table->index('virtual_class_id', 'idx_learning_materials_class_id');
            $table->index('material_type', 'idx_learning_materials_type');
        });

        Schema::table('assignments', function (Blueprint $table) {
            $table->index('virtual_class_id', 'idx_assignments_class_id');
            $table->index('due_date', 'idx_assignments_due_date');
        });

        Schema::table('quizzes', function (Blueprint $table) {
            $table->index('virtual_class_id', 'idx_quizzes_class_id');
        });

        Schema::table('exam_results', function (Blueprint $table) {
            $table->index('exam_id', 'idx_exam_results_exam_id');
            $table->index('student_id', 'idx_exam_results_student_id');
        });

        Schema::table('virtual_classes', function (Blueprint $table) {
            $table->index('class_id', 'idx_virtual_classes_class_id');
        });

        Schema::table('calendars', function (Blueprint $table) {
            $table->index('owner_id', 'idx_calendars_owner_id');
        });

        Schema::table('calendar_events', function (Blueprint $table) {
            $table->index('calendar_id', 'idx_calendar_events_calendar_id');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropIndex('idx_students_class_id');
            $table->dropIndex('idx_students_parent_id');
        });

        Schema::table('teachers', function (Blueprint $table) {
            $table->dropIndex('idx_teachers_status');
        });

        Schema::table('staff', function (Blueprint $table) {
            $table->dropIndex('idx_staff_status');
        });

        Schema::table('marketplace_products', function (Blueprint $table) {
            $table->dropIndex('idx_marketplace_products_created_by');
            $table->dropIndex('idx_marketplace_products_is_active');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex('idx_transactions_type');
            $table->dropIndex('idx_transactions_status');
        });

        Schema::table('staff_attendances', function (Blueprint $table) {
            $table->dropIndex('idx_staff_attendances_staff_id');
            $table->dropIndex('idx_staff_attendances_date');
        });

        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropIndex('idx_leave_requests_staff_id');
            $table->dropIndex('idx_leave_requests_status');
            $table->dropIndex('idx_leave_requests_start_date');
        });

        Schema::table('learning_materials', function (Blueprint $table) {
            $table->dropIndex('idx_learning_materials_class_id');
            $table->dropIndex('idx_learning_materials_type');
        });

        Schema::table('assignments', function (Blueprint $table) {
            $table->dropIndex('idx_assignments_class_id');
            $table->dropIndex('idx_assignments_due_date');
        });

        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropIndex('idx_quizzes_class_id');
        });

        Schema::table('exam_results', function (Blueprint $table) {
            $table->dropIndex('idx_exam_results_exam_id');
            $table->dropIndex('idx_exam_results_student_id');
        });

        Schema::table('virtual_classes', function (Blueprint $table) {
            $table->dropIndex('idx_virtual_classes_class_id');
        });

        Schema::table('calendars', function (Blueprint $table) {
            $table->dropIndex('idx_calendars_owner_id');
        });

        Schema::table('calendar_events', function (Blueprint $table) {
            $table->dropIndex('idx_calendar_events_calendar_id');
        });
    }
};
