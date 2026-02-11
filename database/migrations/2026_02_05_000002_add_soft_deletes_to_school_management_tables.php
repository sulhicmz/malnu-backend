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
        // School management tables
        Schema::table('parents', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable();
        });

        Schema::table('teachers', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable();
        });

        Schema::table('students', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable();
        });

        Schema::table('staff', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable();
        });

        Schema::table('classes', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable();
        });

        Schema::table('subjects', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable();
        });

        Schema::table('class_subjects', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable();
        });

        Schema::table('schedules', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable();
        });

        Schema::table('school_inventory', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('parents', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('teachers', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('staff', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('classes', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('subjects', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('class_subjects', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('schedules', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('school_inventory', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });
    }
};
