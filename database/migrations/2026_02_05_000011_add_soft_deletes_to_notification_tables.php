<?php

declare(strict_types=1);

use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable();
        });

        Schema::table('notification_recipients', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable();
        });

        Schema::table('notification_templates', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable();
        });

        Schema::table('notification_delivery_logs', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable();
        });

        Schema::table('notification_user_preferences', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('notification_recipients', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('notification_templates', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('notification_delivery_logs', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('notification_user_preferences', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });
    }
};
