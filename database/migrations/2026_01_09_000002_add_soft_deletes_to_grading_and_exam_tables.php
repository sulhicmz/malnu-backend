<?php

declare(strict_types=1);

use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run migrations.
     */
    public function up(): void
    {
        // Add deleted_at to grading tables
        Schema::table('grades', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable();
        });

        Schema::table('competencies', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable();
        });

        Schema::table('reports', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable();
        });

        Schema::table('student_portfolios', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable();
        });

        // Add deleted_at to online exam tables
        Schema::table('exams', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable();
        });

        Schema::table('question_banks', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable();
        });

        Schema::table('exam_questions', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable();
        });

        Schema::table('exam_answers', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable();
        });

        Schema::table('exam_results', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable();
        });
    }

    /**
     * Reverse migrations.
     */
    public function down(): void
    {
        Schema::table('exam_results', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('exam_answers', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('exam_questions', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('question_banks', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('exams', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('student_portfolios', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('reports', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('competencies', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('grades', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });
    }
};
