<?php

declare(strict_types=1);

use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hypervel\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add indexes to users table
        Schema::table('users', function (Blueprint $table) {
            // Index on frequently queried columns
            $table->index(['email'], 'idx_users_email');
            $table->index(['is_active'], 'idx_users_status');
            $table->index(['created_at'], 'idx_users_created_at');
            $table->index(['username'], 'idx_users_username');
            $table->index(['last_login_time'], 'idx_users_last_login');
        });

        // Add indexes to common relationship tables if they exist
        // Check if tables exist before adding indexes
        if (Schema::hasTable('model_has_roles')) {
            Schema::table('model_has_roles', function (Blueprint $table) {
                $table->index(['model_id', 'model_type'], 'idx_model_has_roles_model');
                $table->index(['role_id'], 'idx_model_has_roles_role');
            });
        }

        if (Schema::hasTable('model_has_permissions')) {
            Schema::table('model_has_permissions', function (Blueprint $table) {
                $table->index(['model_id', 'model_type'], 'idx_model_has_permissions_model');
                $table->index(['permission_id'], 'idx_model_has_permissions_permission');
            });
        }

        if (Schema::hasTable('student')) {
            Schema::table('student', function (Blueprint $table) {
                $table->index(['user_id'], 'idx_student_user_id');
                $table->index(['nis'], 'idx_student_nis');
            });
        }

        if (Schema::hasTable('teacher')) {
            Schema::table('teacher', function (Blueprint $table) {
                $table->index(['user_id'], 'idx_teacher_user_id');
            });
        }

        if (Schema::hasTable('parent_ortu')) {
            Schema::table('parent_ortu', function (Blueprint $table) {
                $table->index(['user_id'], 'idx_parent_ortu_user_id');
            });
        }

        if (Schema::hasTable('staff')) {
            Schema::table('staff', function (Blueprint $table) {
                $table->index(['user_id'], 'idx_staff_user_id');
            });
        }

        if (Schema::hasTable('learning_materials')) {
            Schema::table('learning_materials', function (Blueprint $table) {
                $table->index(['created_by'], 'idx_learning_materials_created_by');
                $table->index(['created_at'], 'idx_learning_materials_created_at');
            });
        }

        if (Schema::hasTable('assignments')) {
            Schema::table('assignments', function (Blueprint $table) {
                $table->index(['created_by'], 'idx_assignments_created_by');
                $table->index(['created_at'], 'idx_assignments_created_at');
            });
        }

        if (Schema::hasTable('grades')) {
            Schema::table('grades', function (Blueprint $table) {
                $table->index(['created_by'], 'idx_grades_created_by');
                $table->index(['student_id'], 'idx_grades_student');
            });
        }

        if (Schema::hasTable('discussions')) {
            Schema::table('discussions', function (Blueprint $table) {
                $table->index(['created_by'], 'idx_discussions_created_by');
                $table->index(['created_at'], 'idx_discussions_created_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['idx_users_email']);
            $table->dropIndex(['idx_users_status']);
            $table->dropIndex(['idx_users_created_at']);
            $table->dropIndex(['idx_users_username']);
            $table->dropIndex(['idx_users_last_login']);
        });

        if (Schema::hasTable('model_has_roles')) {
            Schema::table('model_has_roles', function (Blueprint $table) {
                $table->dropIndex(['idx_model_has_roles_model']);
                $table->dropIndex(['idx_model_has_roles_role']);
            });
        }

        if (Schema::hasTable('model_has_permissions')) {
            Schema::table('model_has_permissions', function (Blueprint $table) {
                $table->dropIndex(['idx_model_has_permissions_model']);
                $table->dropIndex(['idx_model_has_permissions_permission']);
            });
        }

        if (Schema::hasTable('student')) {
            Schema::table('student', function (Blueprint $table) {
                $table->dropIndex(['idx_student_user_id']);
                $table->dropIndex(['idx_student_nis']);
            });
        }

        if (Schema::hasTable('teacher')) {
            Schema::table('teacher', function (Blueprint $table) {
                $table->dropIndex(['idx_teacher_user_id']);
            });
        }

        if (Schema::hasTable('parent_ortu')) {
            Schema::table('parent_ortu', function (Blueprint $table) {
                $table->dropIndex(['idx_parent_ortu_user_id']);
            });
        }

        if (Schema::hasTable('staff')) {
            Schema::table('staff', function (Blueprint $table) {
                $table->dropIndex(['idx_staff_user_id']);
            });
        }

        if (Schema::hasTable('learning_materials')) {
            Schema::table('learning_materials', function (Blueprint $table) {
                $table->dropIndex(['idx_learning_materials_created_by']);
                $table->dropIndex(['idx_learning_materials_created_at']);
            });
        }

        if (Schema::hasTable('assignments')) {
            Schema::table('assignments', function (Blueprint $table) {
                $table->dropIndex(['idx_assignments_created_by']);
                $table->dropIndex(['idx_assignments_created_at']);
            });
        }

        if (Schema::hasTable('grades')) {
            Schema::table('grades', function (Blueprint $table) {
                $table->dropIndex(['idx_grades_created_by']);
                $table->dropIndex(['idx_grades_student']);
            });
        }

        if (Schema::hasTable('discussions')) {
            Schema::table('discussions', function (Blueprint $table) {
                $table->dropIndex(['idx_discussions_created_by']);
                $table->dropIndex(['idx_discussions_created_at']);
            });
        }
    }
};