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
        // Add indexes for frequently queried columns in users table
        Schema::table('users', function (Blueprint $table) {
            $table->index(['email'], 'idx_users_email');
            $table->index(['username'], 'idx_users_username');
            $table->index(['is_active'], 'idx_users_status');
            $table->index(['created_at'], 'idx_users_created_at');
        });

        // Add indexes for frequently queried columns in students table
        Schema::table('students', function (Blueprint $table) {
            $table->index(['user_id'], 'idx_students_user_id');
            $table->index(['class_id'], 'idx_students_class_id');
            $table->index(['nisn'], 'idx_students_nisn');
            $table->index(['status'], 'idx_students_status');
            $table->index(['created_at'], 'idx_students_created_at');
        });

        // Add indexes for frequently queried columns in teachers table
        Schema::table('teachers', function (Blueprint $table) {
            $table->index(['user_id'], 'idx_teachers_user_id');
            $table->index(['nip'], 'idx_teachers_nip');
            $table->index(['status'], 'idx_teachers_status');
            $table->index(['created_at'], 'idx_teachers_created_at');
        });

        // Add indexes for frequently queried columns in classes table
        Schema::table('classes', function (Blueprint $table) {
            $table->index(['name'], 'idx_classes_name');
            $table->index(['level'], 'idx_classes_level');
            $table->index(['academic_year'], 'idx_classes_academic_year');
            $table->index(['homeroom_teacher_id'], 'idx_classes_homeroom_teacher_id');
        });

        // Add indexes for frequently queried columns in subjects table
        Schema::table('subjects', function (Blueprint $table) {
            $table->index(['code'], 'idx_subjects_code');
            $table->index(['name'], 'idx_subjects_name');
        });

        // Add indexes for frequently queried columns in grades table
        Schema::table('grades', function (Blueprint $table) {
            $table->index(['student_id'], 'idx_grades_student_id');
            $table->index(['subject_id'], 'idx_grades_subject_id');
            $table->index(['class_id'], 'idx_grades_class_id');
            $table->index(['semester'], 'idx_grades_semester');
            $table->index(['grade_type'], 'idx_grades_grade_type');
            $table->index(['created_at'], 'idx_grades_created_at');
        });

        // Add composite indexes for common query patterns
        Schema::table('grades', function (Blueprint $table) {
            $table->index(['student_id', 'subject_id'], 'idx_grades_student_subject');
            $table->index(['student_id', 'semester'], 'idx_grades_student_semester');
        });

        Schema::table('competencies', function (Blueprint $table) {
            $table->index(['student_id'], 'idx_competencies_student_id');
            $table->index(['subject_id'], 'idx_competencies_subject_id');
            $table->index(['semester'], 'idx_competencies_semester');
            $table->index(['student_id', 'subject_id'], 'idx_competencies_student_subject');
        });

        Schema::table('reports', function (Blueprint $table) {
            $table->index(['student_id'], 'idx_reports_student_id');
            $table->index(['class_id'], 'idx_reports_class_id');
            $table->index(['semester'], 'idx_reports_semester');
            $table->index(['academic_year'], 'idx_reports_academic_year');
            $table->index(['is_published'], 'idx_reports_published');
        });

        Schema::table('class_subjects', function (Blueprint $table) {
            $table->index(['class_id'], 'idx_class_subjects_class_id');
            $table->index(['subject_id'], 'idx_class_subjects_subject_id');
            $table->index(['teacher_id'], 'idx_class_subjects_teacher_id');
        });

        Schema::table('schedules', function (Blueprint $table) {
            $table->index(['class_subject_id'], 'idx_schedules_class_subject_id');
            $table->index(['day_of_week'], 'idx_schedules_day_of_week');
            $table->index(['start_time'], 'idx_schedules_start_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove indexes
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['idx_users_email']);
            $table->dropIndex(['idx_users_username']);
            $table->dropIndex(['idx_users_status']);
            $table->dropIndex(['idx_users_created_at']);
        });

        Schema::table('students', function (Blueprint $table) {
            $table->dropIndex(['idx_students_user_id']);
            $table->dropIndex(['idx_students_class_id']);
            $table->dropIndex(['idx_students_nisn']);
            $table->dropIndex(['idx_students_status']);
            $table->dropIndex(['idx_students_created_at']);
        });

        Schema::table('teachers', function (Blueprint $table) {
            $table->dropIndex(['idx_teachers_user_id']);
            $table->dropIndex(['idx_teachers_nip']);
            $table->dropIndex(['idx_teachers_status']);
            $table->dropIndex(['idx_teachers_created_at']);
        });

        Schema::table('classes', function (Blueprint $table) {
            $table->dropIndex(['idx_classes_name']);
            $table->dropIndex(['idx_classes_level']);
            $table->dropIndex(['idx_classes_academic_year']);
            $table->dropIndex(['idx_classes_homeroom_teacher_id']);
        });

        Schema::table('subjects', function (Blueprint $table) {
            $table->dropIndex(['idx_subjects_code']);
            $table->dropIndex(['idx_subjects_name']);
        });

        Schema::table('grades', function (Blueprint $table) {
            $table->dropIndex(['idx_grades_student_id']);
            $table->dropIndex(['idx_grades_subject_id']);
            $table->dropIndex(['idx_grades_class_id']);
            $table->dropIndex(['idx_grades_semester']);
            $table->dropIndex(['idx_grades_grade_type']);
            $table->dropIndex(['idx_grades_created_at']);
            $table->dropIndex(['idx_grades_student_subject']);
            $table->dropIndex(['idx_grades_student_semester']);
        });

        Schema::table('competencies', function (Blueprint $table) {
            $table->dropIndex(['idx_competencies_student_id']);
            $table->dropIndex(['idx_competencies_subject_id']);
            $table->dropIndex(['idx_competencies_semester']);
            $table->dropIndex(['idx_competencies_student_subject']);
        });

        Schema::table('reports', function (Blueprint $table) {
            $table->dropIndex(['idx_reports_student_id']);
            $table->dropIndex(['idx_reports_class_id']);
            $table->dropIndex(['idx_reports_semester']);
            $table->dropIndex(['idx_reports_academic_year']);
            $table->dropIndex(['idx_reports_published']);
        });

        Schema::table('class_subjects', function (Blueprint $table) {
            $table->dropIndex(['idx_class_subjects_class_id']);
            $table->dropIndex(['idx_class_subjects_subject_id']);
            $table->dropIndex(['idx_class_subjects_teacher_id']);
        });

        Schema::table('schedules', function (Blueprint $table) {
            $table->dropIndex(['idx_schedules_class_subject_id']);
            $table->dropIndex(['idx_schedules_day_of_week']);
            $table->dropIndex(['idx_schedules_start_time']);
        });
    }
};