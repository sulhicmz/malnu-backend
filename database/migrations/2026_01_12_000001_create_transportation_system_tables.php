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
            $table->string('plate_number', 50)->unique();
            $table->string('vehicle_type', 50)->default('bus'); // bus, van, minibus
            $table->string('make', 100);
            $table->string('model', 100);
            $table->integer('year')->nullable();
            $table->integer('capacity')->default(20); // seating capacity
            $table->string('color', 50)->nullable();
            $table->string('vin', 100)->nullable(); // Vehicle Identification Number
            $table->string('registration_number', 100)->nullable();
            $table->date('registration_expiry')->nullable();
            $table->date('insurance_expiry')->nullable();
            $table->date('inspection_expiry')->nullable();
            $table->string('fuel_type', 50)->default('diesel'); // diesel, petrol, electric
            $table->string('status', 20)->default('active'); // active, maintenance, retired
            $table->string('current_location', 255)->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->decimal('last_odometer', 10, 2)->default(0);
            $table->json('maintenance_history')->nullable();
            $table->json('gps_device_info')->nullable(); // GPS device ID and configuration
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            
            $table->datetimes();
            
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['status', 'vehicle_type']);
            $table->index(['plate_number']);
        });

        Schema::create('transport_drivers', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->string('employee_id', 50)->unique()->nullable();
            $table->string('name', 255);
            $table->string('phone', 20);
            $table->string('email', 255)->nullable();
            $table->string('license_number', 100)->unique();
            $table->string('license_type', 50); // commercial, bus, etc.
            $table->date('license_expiry');
            $table->string('address', 500)->nullable();
            $table->date('hire_date');
            $table->string('status', 20)->default('active'); // active, inactive, on_leave
            $table->json('certifications')->nullable();
            $table->json('emergency_contacts')->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            
            $table->datetimes();
            
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['status', 'license_expiry']);
            $table->index(['employee_id']);
        });

        Schema::create('transport_routes', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->string('route_name', 255);
            $table->string('route_number', 50)->unique();
            $table->text('description')->nullable();
            $table->string('route_type', 50)->default('regular'); // regular, express, special
            $table->string('status', 20)->default('active'); // active, inactive
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('total_stops')->default(0);
            $table->decimal('total_distance', 8, 2)->default(0); // in km
            $table->integer('estimated_duration')->default(0); // in minutes
            $table->json('route_coordinates')->nullable(); // Array of lat,lng points
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            
            $table->datetimes();
            
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['status', 'route_type']);
            $table->index(['route_number']);
        });

        Schema::create('transport_stops', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('route_id');
            $table->string('stop_name', 255);
            $table->text('description')->nullable();
            $table->text('address')->nullable();
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->integer('stop_order'); // Order along the route
            $table->time('arrival_time')->nullable(); // Expected arrival time
            $table->time('departure_time')->nullable(); // Expected departure time
            $table->json('pickup_point')->nullable(); // Specific pickup location details
            $table->boolean('is_morning')->default(true); // Morning route stop
            $table->boolean('is_afternoon')->default(true); // Afternoon route stop
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            
            $table->datetimes();
            
            $table->foreign('route_id')->references('id')->on('transport_routes')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['route_id', 'stop_order']);
            $table->index(['latitude', 'longitude']);
        });

        Schema::create('transport_assignments', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('student_id');
            $table->uuid('route_id');
            $table->uuid('stop_id');
            $table->uuid('vehicle_id')->nullable();
            $table->uuid('driver_id')->nullable();
            $table->date('effective_date'); // When this assignment starts
            $table->date('end_date')->nullable(); // When this assignment ends
            $table->string('status', 20)->default('active'); // active, inactive, cancelled
            $table->string('session_type', 20)->default('both'); // morning, afternoon, both
            $table->string('fee_status', 20)->default('pending'); // pending, paid, exempt
            $table->decimal('monthly_fee', 10, 2)->default(0);
            $table->json('additional_info')->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            
            $table->datetimes();
            
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('route_id')->references('id')->on('transport_routes')->onDelete('cascade');
            $table->foreign('stop_id')->references('id')->on('transport_stops')->onDelete('cascade');
            $table->foreign('vehicle_id')->references('id')->on('transport_vehicles')->onDelete('set null');
            $table->foreign('driver_id')->references('id')->on('transport_drivers')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['student_id', 'status']);
            $table->index(['route_id', 'stop_id']);
            $table->index(['vehicle_id', 'driver_id']);
            $table->unique(['student_id', 'route_id', 'effective_date'], 'unique_student_route_date');
        });

        Schema::create('transport_schedules', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('route_id');
            $table->uuid('vehicle_id');
            $table->uuid('driver_id');
            $table->string('day_of_week', 20); // monday, tuesday, etc.
            $table->string('session_type', 20); // morning, afternoon
            $table->time('departure_time');
            $table->time('first_stop_arrival')->nullable();
            $table->time('last_stop_arrival')->nullable();
            $table->string('status', 20)->default('active');
            $table->json('schedule_notes')->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            
            $table->datetimes();
            
            $table->foreign('route_id')->references('id')->on('transport_routes')->onDelete('cascade');
            $table->foreign('vehicle_id')->references('id')->on('transport_vehicles')->onDelete('cascade');
            $table->foreign('driver_id')->references('id')->on('transport_drivers')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['route_id', 'day_of_week', 'session_type']);
            $table->index(['vehicle_id', 'day_of_week']);
        });

        Schema::create('transport_attendance', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('assignment_id');
            $table->uuid('student_id');
            $table->date('attendance_date');
            $table->string('session_type', 20); // morning, afternoon
            $table->string('boarding_status', 20)->default('pending'); // pending, boarded, missed, excused
            $table->time('boarding_time')->nullable();
            $table->time('alighting_time')->nullable();
            $table->uuid('boarding_stop_id')->nullable();
            $table->uuid('alighting_stop_id')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('parent_notified')->default(false);
            $table->timestamp('notification_time')->nullable();
            $table->uuid('recorded_by')->nullable();
            
            $table->datetimes();
            
            $table->foreign('assignment_id')->references('id')->on('transport_assignments')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('boarding_stop_id')->references('id')->on('transport_stops')->onDelete('set null');
            $table->foreign('alighting_stop_id')->references('id')->on('transport_stops')->onDelete('set null');
            $table->foreign('recorded_by')->references('id')->on('users')->onDelete('set null');
            $table->unique(['assignment_id', 'attendance_date', 'session_type'], 'unique_assignment_date_session');
            $table->index(['student_id', 'attendance_date']);
            $table->index(['attendance_date', 'boarding_status']);
        });

        Schema::create('transport_incidents', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('vehicle_id')->nullable();
            $table->uuid('driver_id')->nullable();
            $table->uuid('route_id')->nullable();
            $table->string('incident_type', 50); // accident, breakdown, delay, medical, safety_issue
            $table->string('severity', 20); // minor, moderate, major, critical
            $table->timestamp('incident_time');
            $table->text('description');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('location_address', 500)->nullable();
            $table->text('actions_taken')->nullable();
            $table->text('follow_up_required')->nullable();
            $table->boolean('parent_notified')->default(false);
            $table->timestamp('notification_time')->nullable();
            $table->boolean('police_reported')->default(false);
            $table->string('police_report_number', 100)->nullable();
            $table->integer('students_involved')->default(0);
            $table->json('student_ids')->nullable();
            $table->json('evidence_photos')->nullable();
            $table->string('status', 20)->default('open'); // open, under_investigation, resolved, closed
            $table->uuid('reported_by')->nullable();
            $table->uuid('resolved_by')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->json('resolution_details')->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            
            $table->datetimes();
            
            $table->foreign('vehicle_id')->references('id')->on('transport_vehicles')->onDelete('set null');
            $table->foreign('driver_id')->references('id')->on('transport_drivers')->onDelete('set null');
            $table->foreign('route_id')->references('id')->on('transport_routes')->onDelete('set null');
            $table->foreign('reported_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('resolved_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['incident_time', 'status']);
            $table->index(['vehicle_id', 'incident_time']);
            $table->index(['severity', 'status']);
        });

        Schema::create('transport_tracking', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('vehicle_id');
            $table->uuid('route_id')->nullable();
            $table->uuid('driver_id')->nullable();
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->decimal('speed', 8, 2)->default(0); // in km/h
            $table->decimal('heading', 8, 2)->nullable(); // direction in degrees
            $table->decimal('altitude', 10, 2)->nullable(); // in meters
            $table->string('status', 20)->default('moving'); // moving, stopped, idle
            $table->integer('ignition_on')->default(1); // 1 = on, 0 = off
            $table->decimal('odometer', 10, 2)->default(0);
            $table->json('additional_data')->nullable(); // fuel level, battery, etc.
            $table->timestamp('recorded_at');
            $table->timestamp('created_at')->useCurrent();
            
            $table->foreign('vehicle_id')->references('id')->on('transport_vehicles')->onDelete('cascade');
            $table->foreign('route_id')->references('id')->on('transport_routes')->onDelete('set null');
            $table->foreign('driver_id')->references('id')->on('transport_drivers')->onDelete('set null');
            $table->index(['vehicle_id', 'recorded_at']);
            $table->index(['recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transport_tracking');
        Schema::dropIfExists('transport_incidents');
        Schema::dropIfExists('transport_attendance');
        Schema::dropIfExists('transport_schedules');
        Schema::dropIfExists('transport_assignments');
        Schema::dropIfExists('transport_stops');
        Schema::dropIfExists('transport_routes');
        Schema::dropIfExists('transport_drivers');
        Schema::dropIfExists('transport_vehicles');
    }
};
