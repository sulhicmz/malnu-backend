<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add indexes to users table
        Schema::table('users', function (Blueprint $table) {
            $table->index(['email'], 'idx_users_email');
            $table->index(['is_active'], 'idx_users_status');
            $table->index(['created_at'], 'idx_users_created_at');
            $table->index(['updated_at'], 'idx_users_updated_at');
        });

        // Add indexes to students table
        Schema::table('students', function (Blueprint $table) {
            $table->index(['user_id'], 'idx_students_user_id');
            $table->index(['class_id'], 'idx_students_class_id');
            $table->index(['parent_id'], 'idx_students_parent_id');
            $table->index(['status'], 'idx_students_status');
            $table->index(['created_at'], 'idx_students_created_at');
        });

        // Add indexes to teachers table
        Schema::table('teachers', function (Blueprint $table) {
            $table->index(['user_id'], 'idx_teachers_user_id');
            $table->index(['created_at'], 'idx_teachers_created_at');
        });

        // Add indexes to classes table
        Schema::table('classes', function (Blueprint $table) {
            $table->index(['name'], 'idx_classes_name');
            $table->index(['created_at'], 'idx_classes_created_at');
        });

        // Add indexes to subjects table
        Schema::table('subjects', function (Blueprint $table) {
            $table->index(['name'], 'idx_subjects_name');
            $table->index(['created_at'], 'idx_subjects_created_at');
        });

        // Add indexes to grades table
        Schema::table('grades', function (Blueprint $table) {
            $table->index(['student_id'], 'idx_grades_student_id');
            $table->index(['created_by'], 'idx_grades_created_by');
            $table->index(['subject_id'], 'idx_grades_subject_id');
            $table->index(['created_at'], 'idx_grades_created_at');
        });

        // Add indexes to exams table
        Schema::table('exams', function (Blueprint $table) {
            $table->index(['created_by'], 'idx_exams_created_by');
            $table->index(['created_at'], 'idx_exams_created_at');
        });

        // Add indexes to exam_results table
        Schema::table('exam_results', function (Blueprint $table) {
            $table->index(['student_id'], 'idx_exam_results_student_id');
            $table->index(['exam_id'], 'idx_exam_results_exam_id');
            $table->index(['created_at'], 'idx_exam_results_created_at');
        });

        // Add indexes to parent_ortu table
        Schema::table('parent_ortu', function (Blueprint $table) {
            $table->index(['user_id'], 'idx_parent_ortu_user_id');
            $table->index(['created_at'], 'idx_parent_ortu_created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove indexes from users table
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['idx_users_email']);
            $table->dropIndex(['idx_users_status']);
            $table->dropIndex(['idx_users_created_at']);
            $table->dropIndex(['idx_users_updated_at']);
        });

        // Remove indexes from students table
        Schema::table('students', function (Blueprint $table) {
            $table->dropIndex(['idx_students_user_id']);
            $table->dropIndex(['idx_students_class_id']);
            $table->dropIndex(['idx_students_parent_id']);
            $table->dropIndex(['idx_students_status']);
            $table->dropIndex(['idx_students_created_at']);
        });

        // Remove indexes from teachers table
        Schema::table('teachers', function (Blueprint $table) {
            $table->dropIndex(['idx_teachers_user_id']);
            $table->dropIndex(['idx_teachers_created_at']);
        });

        // Remove indexes from classes table
        Schema::table('classes', function (Blueprint $table) {
            $table->dropIndex(['idx_classes_name']);
            $table->dropIndex(['idx_classes_created_at']);
        });

        // Remove indexes from subjects table
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropIndex(['idx_subjects_name']);
            $table->dropIndex(['idx_subjects_created_at']);
        });

        // Remove indexes from grades table
        Schema::table('grades', function (Blueprint $table) {
            $table->dropIndex(['idx_grades_student_id']);
            $table->dropIndex(['idx_grades_created_by']);
            $table->dropIndex(['idx_grades_subject_id']);
            $table->dropIndex(['idx_grades_created_at']);
        });

        // Remove indexes from exams table
        Schema::table('exams', function (Blueprint $table) {
            $table->dropIndex(['idx_exams_created_by']);
            $table->dropIndex(['idx_exams_created_at']);
        });

        // Remove indexes from exam_results table
        Schema::table('exam_results', function (Blueprint $table) {
            $table->dropIndex(['idx_exam_results_student_id']);
            $table->dropIndex(['idx_exam_results_exam_id']);
            $table->dropIndex(['idx_exam_results_created_at']);
        });

        // Remove indexes from parent_ortu table
        Schema::table('parent_ortu', function (Blueprint $table) {
            $table->dropIndex(['idx_parent_ortu_user_id']);
            $table->dropIndex(['idx_parent_ortu_created_at']);
        });
    }
};