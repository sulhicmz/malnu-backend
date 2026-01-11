<?php

declare (strict_types = 1);

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
        Schema::table('students', function (Blueprint $table) {
            $table->index('class_id');
            $table->index('status');
        });

        Schema::table('teachers', function (Blueprint $table) {
            $table->index('status');
        });

        Schema::table('staff', function (Blueprint $table) {
            $table->index('status');
        });

        Schema::table('staff_attendances', function (Blueprint $table) {
            $table->index('status');
        });

        Schema::table('ppdb_registrations', function (Blueprint $table) {
            $table->index('status');
            $table->index('registration_date');
        });

        Schema::table('learning_materials', function (Blueprint $table) {
            $table->index('virtual_class_id');
            $table->index('material_type');
        });

        Schema::table('assignments', function (Blueprint $table) {
            $table->index('virtual_class_id');
            $table->index('due_date');
        });

        Schema::table('quizzes', function (Blueprint $table) {
            $table->index('virtual_class_id');
        });

        Schema::table('exam_results', function (Blueprint $table) {
            $table->index('exam_id');
            $table->index('student_id');
        });

        Schema::table('students', function (Blueprint $table) {
            $table->index(['class_id', 'status']);
        });

        Schema::table('ppdb_registrations', function (Blueprint $table) {
            $table->index(['status', 'registration_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropIndex(['class_id', 'status']);
            $table->dropIndex('status');
            $table->dropIndex('class_id');
        });

        Schema::table('teachers', function (Blueprint $table) {
            $table->dropIndex('status');
        });

        Schema::table('staff', function (Blueprint $table) {
            $table->dropIndex('status');
        });

        Schema::table('staff_attendances', function (Blueprint $table) {
            $table->dropIndex('status');
        });

        Schema::table('ppdb_registrations', function (Blueprint $table) {
            $table->dropIndex(['status', 'registration_date']);
            $table->dropIndex('registration_date');
            $table->dropIndex('status');
        });

        Schema::table('learning_materials', function (Blueprint $table) {
            $table->dropIndex('material_type');
            $table->dropIndex('virtual_class_id');
        });

        Schema::table('assignments', function (Blueprint $table) {
            $table->dropIndex('due_date');
            $table->dropIndex('virtual_class_id');
        });

        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropIndex('virtual_class_id');
        });

        Schema::table('exam_results', function (Blueprint $table) {
            $table->dropIndex('student_id');
            $table->dropIndex('exam_id');
        });
    }
};
