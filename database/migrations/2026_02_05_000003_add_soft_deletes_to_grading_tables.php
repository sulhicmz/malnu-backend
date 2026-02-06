<?php

declare(strict_types=1);

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
        // Grading and assessment tables
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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grades', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('competencies', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('reports', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('student_portfolios', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });
    }
};
