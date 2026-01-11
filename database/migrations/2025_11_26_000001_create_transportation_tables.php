<?php

declare(strict_types=1);

use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Support\Facades\Schema;
use Hyperf\DbConnection\Db;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transport_vehicles', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->string('vehicle_number', 50)->unique();
            $table->string('license_plate', 20)->unique();
            $table->string('type', 50)->default('bus');
            $table->integer('capacity');
            $table->string('make', 100)->nullable();
            $table->string('model', 100)->nullable();
            $table->integer('year')->nullable();
            $table->string('color', 50)->nullable();
            $table->enum('status', ['active', 'maintenance', 'retired'])->default('active');
            $table->text('description')->nullable();
            $table->date('insurance_expiry')->nullable();
            $table->date('registration_expiry')->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            
            $table->datetimes();
            
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['status', 'type']);
        });

        Schema::create('transport_stops', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->string('name', 255);
            $table->text('address');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->string('landmark', 255)->nullable();
            $table->integer('estimated_time')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            
            $table->datetimes();
            
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['latitude', 'longitude']);
            $table->index('is_active');
        });

        Schema::create('transport_routes', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->string('route_number', 50)->unique();
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->string('start_location', 255);
            $table->string('end_location', 255);
            $table->time('departure_time');
            $table->time('arrival_time');
            $table->integer('total_duration')->nullable();
            $table->decimal('total_distance', 10, 2)->nullable();
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->json('stop_sequence')->nullable();
            $table->integer('capacity')->default(40);
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            
            $table->datetimes();
            
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            $table->index('status');
        });

        Schema::create('transport_route_stops', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('route_id');
            $table->uuid('stop_id');
            $table->integer('sequence_order');
            $table->time('pickup_time')->nullable();
            $table->time('dropoff_time')->nullable();
            $table->decimal('distance_from_start', 10, 2)->nullable();
            $table->integer('estimated_duration')->nullable();
            
            $table->datetimes();
            
            $table->foreign('route_id')->references('id')->on('transport_routes')->onDelete('cascade');
            $table->foreign('stop_id')->references('id')->on('transport_stops')->onDelete('cascade');
            $table->unique(['route_id', 'stop_id'], 'unique_route_stop');
            $table->index(['route_id', 'sequence_order']);
        });

        Schema::create('transport_drivers', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('user_id')->nullable();
            $table->string('name', 255);
            $table->string('phone', 20);
            $table->string('license_number', 50)->unique();
            $table->date('license_expiry');
            $table->string('license_type', 50);
            $table->enum('status', ['active', 'inactive', 'on_leave'])->default('active');
            $table->text('address')->nullable();
            $table->string('emergency_contact', 20)->nullable();
            $table->date('hire_date')->nullable();
            $table->text('notes')->nullable();
            $table->json('certifications')->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            
            $table->datetimes();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            $table->index('status');
        });

        Schema::create('transport_assignments', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('route_id');
            $table->uuid('student_id');
            $table->uuid('pickup_stop_id')->nullable();
            $table->uuid('dropoff_stop_id')->nullable();
            $table->enum('trip_type', ['both', 'morning', 'afternoon'])->default('both');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->enum('status', ['active', 'inactive', 'terminated'])->default('active');
            $table->decimal('fee_amount', 10, 2)->nullable();
            $table->text('notes')->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            
            $table->datetimes();
            
            $table->foreign('route_id')->references('id')->on('transport_routes')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('pickup_stop_id')->references('id')->on('transport_stops')->onDelete('set null');
            $table->foreign('dropoff_stop_id')->references('id')->on('transport_stops')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            $table->unique(['route_id', 'student_id'], 'unique_route_student');
            $table->index(['route_id', 'status']);
            $table->index(['student_id', 'status']);
        });

        Schema::create('transport_schedules', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('route_id');
            $table->uuid('vehicle_id');
            $table->uuid('driver_id');
            $table->enum('shift', ['morning', 'afternoon', 'both'])->default('both');
            $table->enum('day_type', ['weekday', 'weekend', 'all'])->default('all');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->date('effective_start_date');
            $table->date('effective_end_date')->nullable();
            $table->json('metadata')->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            
            $table->datetimes();
            
            $table->foreign('route_id')->references('id')->on('transport_routes')->onDelete('cascade');
            $table->foreign('vehicle_id')->references('id')->on('transport_vehicles')->onDelete('cascade');
            $table->foreign('driver_id')->references('id')->on('transport_drivers')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['route_id', 'status']);
            $table->index(['driver_id', 'status']);
            $table->index(['vehicle_id', 'status']);
        });

        Schema::create('transport_attendance', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('assignment_id');
            $table->uuid('student_id');
            $table->uuid('route_id');
            $table->enum('trip_type', ['morning', 'afternoon']);
            $table->date('attendance_date');
            $table->timestamp('pickup_time')->nullable();
            $table->timestamp('dropoff_time')->nullable();
            $table->enum('pickup_status', ['present', 'absent', 'late'])->nullable();
            $table->enum('dropoff_status', ['present', 'absent', 'missed'])->nullable();
            $table->text('notes')->nullable();
            $table->uuid('recorded_by')->nullable();
            
            $table->datetimes();
            
            $table->foreign('assignment_id')->references('id')->on('transport_assignments')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('route_id')->references('id')->on('transport_routes')->onDelete('cascade');
            $table->foreign('recorded_by')->references('id')->on('users')->onDelete('set null');
            $table->unique(['assignment_id', 'attendance_date', 'trip_type'], 'unique_attendance_record');
            $table->index(['student_id', 'attendance_date']);
            $table->index(['route_id', 'attendance_date']);
        });

        Schema::create('transport_tracking', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('vehicle_id');
            $table->uuid('route_id');
            $table->uuid('schedule_id')->nullable();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->integer('speed')->nullable();
            $table->string('direction', 50)->nullable();
            $table->enum('status', ['moving', 'stopped', 'idle', 'out_of_service'])->default('idle');
            $table->timestamp('recorded_at');
            
            $table->datetimes();
            
            $table->foreign('vehicle_id')->references('id')->on('transport_vehicles')->onDelete('cascade');
            $table->foreign('route_id')->references('id')->on('transport_routes')->onDelete('cascade');
            $table->foreign('schedule_id')->references('id')->on('transport_schedules')->onDelete('set null');
            $table->index(['vehicle_id', 'recorded_at']);
            $table->index(['route_id', 'recorded_at']);
        });

        Schema::create('transport_fees', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('student_id');
            $table->uuid('route_id')->nullable();
            $table->uuid('assignment_id')->nullable();
            $table->string('fee_type', 50)->default('monthly');
            $table->decimal('amount', 10, 2);
            $table->date('due_date');
            $table->date('paid_date')->nullable();
            $table->enum('status', ['pending', 'paid', 'overdue', 'cancelled'])->default('pending');
            $table->string('payment_method', 50)->nullable();
            $table->string('transaction_reference', 100)->nullable();
            $table->text('notes')->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            
            $table->datetimes();
            
            $table->foreign('student_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('route_id')->references('id')->on('transport_routes')->onDelete('set null');
            $table->foreign('assignment_id')->references('id')->on('transport_assignments')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['student_id', 'status']);
            $table->index(['due_date', 'status']);
        });

        Schema::create('transport_notifications', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('route_id')->nullable();
            $table->uuid('vehicle_id')->nullable();
            $table->uuid('student_id')->nullable();
            $table->string('notification_type', 50);
            $table->string('title', 255);
            $table->text('message');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->boolean('is_sent')->default(false);
            $table->timestamp('sent_at')->nullable();
            $table->json('recipient_ids')->nullable();
            $table->json('metadata')->nullable();
            $table->uuid('created_by')->nullable();
            
            $table->datetimes();
            
            $table->foreign('route_id')->references('id')->on('transport_routes')->onDelete('cascade');
            $table->foreign('vehicle_id')->references('id')->on('transport_vehicles')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['route_id', 'is_sent']);
            $table->index(['vehicle_id', 'is_sent']);
            $table->index(['student_id', 'is_sent']);
            $table->index(['notification_type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transport_notifications');
        Schema::dropIfExists('transport_fees');
        Schema::dropIfExists('transport_tracking');
        Schema::dropIfExists('transport_attendance');
        Schema::dropIfExists('transport_schedules');
        Schema::dropIfExists('transport_assignments');
        Schema::dropIfExists('transport_drivers');
        Schema::dropIfExists('transport_route_stops');
        Schema::dropIfExists('transport_routes');
        Schema::dropIfExists('transport_stops');
        Schema::dropIfExists('transport_vehicles');
    }
};
