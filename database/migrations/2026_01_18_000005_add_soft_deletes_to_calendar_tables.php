<?php

declare(strict_types=1);

use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Schema\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('calendars', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable();
        });

        Schema::table('calendar_events', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable();
        });

        Schema::table('calendar_event_registrations', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable();
        });

        Schema::table('calendar_shares', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable();
        });

        Schema::table('resource_bookings', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('calendars', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('calendar_events', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('calendar_event_registrations', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('calendar_shares', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('resource_bookings', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });
    }
};
