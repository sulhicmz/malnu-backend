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
        // Add indexes to users table for frequently queried columns
        Schema::table('users', function (Blueprint $table) {
            $table->index(['email'], 'idx_users_email');
            $table->index(['is_active'], 'idx_users_status');
            $table->index(['created_at'], 'idx_users_created_at');
            $table->index(['updated_at'], 'idx_users_updated_at');
        });

        // Add indexes to roles table
        Schema::table('roles', function (Blueprint $table) {
            $table->index(['name'], 'idx_roles_name');
            $table->index(['guard_name'], 'idx_roles_guard_name');
        });

        // Add indexes to permissions table
        Schema::table('permissions', function (Blueprint $table) {
            $table->index(['name'], 'idx_permissions_name');
            $table->index(['guard_name'], 'idx_permissions_guard_name');
        });

        // Add composite indexes for common relationship queries
        Schema::table('model_has_roles', function (Blueprint $table) {
            $table->index(['model_id', 'model_type'], 'idx_model_has_roles_composite');
        });

        Schema::table('model_has_permissions', function (Blueprint $table) {
            $table->index(['model_id', 'model_type'], 'idx_model_has_permissions_composite');
        });

        // Add indexes to other common tables if they exist
        if (Schema::hasTable('students')) {
            Schema::table('students', function (Blueprint $table) {
                $table->index(['user_id'], 'idx_students_user_id');
                $table->index(['nis'], 'idx_students_nis');
                $table->index(['class_id'], 'idx_students_class_id');
            });
        }

        if (Schema::hasTable('teachers')) {
            Schema::table('teachers', function (Blueprint $table) {
                $table->index(['user_id'], 'idx_teachers_user_id');
                $table->index(['nip'], 'idx_teachers_nip');
            });
        }

        if (Schema::hasTable('grades')) {
            Schema::table('grades', function (Blueprint $table) {
                $table->index(['student_id'], 'idx_grades_student_id');
                $table->index(['subject_id'], 'idx_grades_subject_id');
                $table->index(['created_at'], 'idx_grades_created_at');
            });
        }

        if (Schema::hasTable('learning_materials')) {
            Schema::table('learning_materials', function (Blueprint $table) {
                $table->index(['created_by'], 'idx_learning_materials_created_by');
                $table->index(['subject_id'], 'idx_learning_materials_subject_id');
                $table->index(['created_at'], 'idx_learning_materials_created_at');
            });
        }

        if (Schema::hasTable('assignments')) {
            Schema::table('assignments', function (Blueprint $table) {
                $table->index(['created_by'], 'idx_assignments_created_by');
                $table->index(['subject_id'], 'idx_assignments_subject_id');
                $table->index(['due_date'], 'idx_assignments_due_date');
                $table->index(['created_at'], 'idx_assignments_created_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove indexes
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['idx_users_email']);
            $table->dropIndex(['idx_users_status']);
            $table->dropIndex(['idx_users_created_at']);
            $table->dropIndex(['idx_users_updated_at']);
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->dropIndex(['idx_roles_name']);
            $table->dropIndex(['idx_roles_guard_name']);
        });

        Schema::table('permissions', function (Blueprint $table) {
            $table->dropIndex(['idx_permissions_name']);
            $table->dropIndex(['idx_permissions_guard_name']);
        });

        Schema::table('model_has_roles', function (Blueprint $table) {
            $table->dropIndex(['idx_model_has_roles_composite']);
        });

        Schema::table('model_has_permissions', function (Blueprint $table) {
            $table->dropIndex(['idx_model_has_permissions_composite']);
        });

        if (Schema::hasTable('students')) {
            Schema::table('students', function (Blueprint $table) {
                $table->dropIndex(['idx_students_user_id']);
                $table->dropIndex(['idx_students_nis']);
                $table->dropIndex(['idx_students_class_id']);
            });
        }

        if (Schema::hasTable('teachers')) {
            Schema::table('teachers', function (Blueprint $table) {
                $table->dropIndex(['idx_teachers_user_id']);
                $table->dropIndex(['idx_teachers_nip']);
            });
        }

        if (Schema::hasTable('grades')) {
            Schema::table('grades', function (Blueprint $table) {
                $table->dropIndex(['idx_grades_student_id']);
                $table->dropIndex(['idx_grades_subject_id']);
                $table->dropIndex(['idx_grades_created_at']);
            });
        }

        if (Schema::hasTable('learning_materials')) {
            Schema::table('learning_materials', function (Blueprint $table) {
                $table->dropIndex(['idx_learning_materials_created_by']);
                $table->dropIndex(['idx_learning_materials_subject_id']);
                $table->dropIndex(['idx_learning_materials_created_at']);
            });
        }

        if (Schema::hasTable('assignments')) {
            Schema::table('assignments', function (Blueprint $table) {
                $table->dropIndex(['idx_assignments_created_by']);
                $table->dropIndex(['idx_assignments_subject_id']);
                $table->dropIndex(['idx_assignments_due_date']);
                $table->dropIndex(['idx_assignments_created_at']);
            });
        }
    }
};