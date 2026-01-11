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
        // Add deleted_at to calendar tables
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

    /**
     * Reverse migrations.
     */
    public function down(): void
    {
        Schema::table('resource_bookings', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('calendar_shares', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('calendar_event_registrations', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('calendar_events', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('calendars', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });
    }
};
