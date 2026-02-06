<?php

declare(strict_types=1);

use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('virtual_classes', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable();
        });

        Schema::table('learning_materials', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable();
        });

        Schema::table('assignments', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable();
        });

        Schema::table('quizzes', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable();
        });

        Schema::table('discussions', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable();
        });

        Schema::table('discussion_replies', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable();
        });

        Schema::table('video_conferences', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('virtual_classes', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('learning_materials', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('assignments', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('discussions', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('discussion_replies', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('video_conferences', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });
    }
};
