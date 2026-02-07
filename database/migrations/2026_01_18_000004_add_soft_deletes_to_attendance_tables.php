<?php

declare(strict_types=1);

use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Schema\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('leave_types', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable();
        });

        Schema::table('leave_balances', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable();
        });

        Schema::table('staff_attendance', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable();
        });

        Schema::table('leave_requests', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable();
        });

        Schema::table('substitute_teachers', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable();
        });

        Schema::table('substitute_assignments', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('leave_types', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('leave_balances', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('staff_attendance', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('substitute_teachers', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('substitute_assignments', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });
    }
};
