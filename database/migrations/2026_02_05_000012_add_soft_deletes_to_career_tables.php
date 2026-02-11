<?php

declare(strict_types=1);

use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('career_assessments', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable();
        });

        Schema::table('counseling_sessions', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable();
        });

        Schema::table('industry_partners', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable();
        });

        Schema::table('ai_tutor_sessions', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('career_assessments', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('counseling_sessions', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('industry_partners', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('ai_tutor_sessions', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });
    }
};
