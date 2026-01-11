<?php

declare(strict_types=1);

use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\DbConnection\Db;
use Hyperf\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Calendar table - represents different calendars (academic, staff, etc.)
        Schema::create('calendars', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->string('name', 255);
            $table->string('description', 500)->nullable();
            $table->string('color', 7)->default('#3b82f6'); // hex color
            $table->string('type', 50)->default('general'); // academic, staff, student, etc.
            $table->boolean('is_public')->default(false);
            $table->json('permissions')->nullable(); // role-based permissions
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();

            $table->datetimes();

            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['type', 'is_public']);
        });

        // Calendar events table
        Schema::create('calendar_events', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('calendar_id');
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->timestamp('start_date');
            $table->timestamp('end_date');
            $table->string('location', 255)->nullable();
            $table->string('category', 50)->default('event'); // event, holiday, exam, meeting, etc.
            $table->string('priority', 20)->default('medium'); // low, medium, high, critical
            $table->boolean('is_all_day')->default(false);
            $table->boolean('is_recurring')->default(false);
            $table->json('recurrence_pattern')->nullable(); // JSON for recurrence rules
            $table->timestamp('recurrence_end_date')->nullable();
            $table->integer('max_attendees')->nullable();
            $table->boolean('requires_registration')->default(false);
            $table->timestamp('registration_deadline')->nullable();
            $table->json('metadata')->nullable(); // additional event data
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();

            $table->datetimes();

            $table->foreign('calendar_id')->references('id')->on('calendars')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['calendar_id', 'start_date', 'end_date']);
            $table->index(['category', 'start_date']);
            $table->index(['is_recurring', 'recurrence_pattern']);
        });

        // Calendar event attendees/registrations
        Schema::create('calendar_event_registrations', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('event_id');
            $table->uuid('user_id');
            $table->string('status', 20)->default('registered'); // registered, confirmed, cancelled, attended
            $table->timestamp('registration_date');
            $table->timestamp('confirmation_date')->nullable();
            $table->json('additional_data')->nullable(); // special requirements, etc.

            $table->datetimes();

            $table->foreign('event_id')->references('id')->on('calendar_events')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['event_id', 'user_id'], 'unique_event_user');
            $table->index(['event_id', 'status']);
            $table->index(['user_id', 'status']);
        });

        // Calendar sharing permissions
        Schema::create('calendar_shares', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('calendar_id');
            $table->uuid('user_id');
            $table->string('permission_type', 20)->default('view'); // view, edit, admin
            $table->timestamp('expires_at')->nullable();

            $table->datetimes();

            $table->foreign('calendar_id')->references('id')->on('calendars')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['calendar_id', 'user_id'], 'unique_calendar_user_share');
        });

        // Resource booking (rooms, equipment)
        Schema::create('resource_bookings', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->string('resource_type', 50); // room, equipment, facility
            $table->string('resource_id', 255); // ID of the actual resource
            $table->uuid('event_id')->nullable();
            $table->uuid('booked_by');
            $table->timestamp('start_time');
            $table->timestamp('end_time');
            $table->string('purpose', 255)->nullable();
            $table->string('status', 20)->default('confirmed'); // pending, confirmed, cancelled, completed
            $table->json('booking_data')->nullable(); // additional booking information

            $table->datetimes();

            $table->foreign('event_id')->references('id')->on('calendar_events')->onDelete('set null');
            $table->foreign('booked_by')->references('id')->on('users')->onDelete('cascade');
            $table->index(['resource_type', 'resource_id', 'start_time', 'end_time']);
            $table->index(['booked_by', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resource_bookings');
        Schema::dropIfExists('calendar_shares');
        Schema::dropIfExists('calendar_event_registrations');
        Schema::dropIfExists('calendar_events');
        Schema::dropIfExists('calendars');
    }
};
