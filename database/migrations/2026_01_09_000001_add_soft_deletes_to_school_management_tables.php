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
        // Add deleted_at to users table
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable();
        });

        // Add deleted_at to students table
        Schema::table('students', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable();
        });

        // Add deleted_at to teachers table
        Schema::table('teachers', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable();
        });

        // Add deleted_at to staff table
        Schema::table('staff', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable();
        });

        // Add deleted_at to parents table
        Schema::table('parents', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable();
        });

        // Add deleted_at to classes table
        Schema::table('classes', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable();
        });

        // Add deleted_at to subjects table
        Schema::table('subjects', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable();
        });

        // Add deleted_at to class_subjects table
        Schema::table('class_subjects', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable();
        });

        // Add deleted_at to schedules table
        Schema::table('schedules', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable();
        });

        // Add deleted_at to school_inventory table
        Schema::table('school_inventory', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable();
        });
    }

    /**
     * Reverse migrations.
     */
    public function down(): void
    {
        Schema::table('school_inventory', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('schedules', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('class_subjects', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('subjects', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('classes', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('parents', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('staff', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('teachers', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });
    }
};
