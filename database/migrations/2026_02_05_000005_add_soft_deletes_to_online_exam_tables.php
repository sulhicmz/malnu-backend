<?php

declare(strict_types=1);

use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('online_exams', function (Blueprint $table) {
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

    public function down(): void
    {
        Schema::table('online_exams', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('question_banks', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('exam_questions', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('exam_answers', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('exam_results', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });
    }
};
