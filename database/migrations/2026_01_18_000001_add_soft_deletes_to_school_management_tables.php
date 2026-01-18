<?php

declare(strict_types=1);

use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Schema\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable();
        });

        Schema::table('students', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable();
        });

        Schema::table('teachers', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable();
        });

        Schema::table('staff', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable();
        });

        Schema::table('parents', function (Blueprint $table) {
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

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('teachers', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('staff', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('parents', function (Blueprint $table) {
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
