<?php

declare(strict_types=1);

use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Support\Facades\Schema;
use Hyperf\DbConnection\Db;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('transport_vehicles', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->string('plate_number', 50)->unique();
            $table->string('vehicle_type', 50);
            $table->integer('capacity');
            $table->string('make', 100)->nullable();
            $table->string('model', 100)->nullable();
            $table->year('manufacture_year')->nullable();
            $table->string('registration_number', 100)->nullable();
            $table->date('registration_expiry')->nullable();
            $table->string('insurance_number', 100)->nullable();
            $table->date('insurance_expiry')->nullable();
            $table->string('inspection_number', 100)->nullable();
            $table->date('inspection_expiry')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('status', 20)->default('available');
            $table->timestamps();
            $table->index(['plate_number']);
            $table->index(['status', 'is_active']);
        });

        Schema::create('transport_drivers', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('user_id')->nullable();
            $table->string('name');
            $table->string('license_number', 100)->unique();
            $table->date('license_expiry');
            $table->string('phone', 20);
            $table->string('address')->nullable();
            $table->string('certification_type', 100)->nullable();
            $table->date('certification_expiry')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('status', 20)->default('available');
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->index(['user_id']);
            $table->index(['license_number']);
            $table->index(['status', 'is_active']);
        });

        Schema::create('transport_stops', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->string('name');
            $table->string('address');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('type', 20)->default('pickup');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['latitude', 'longitude']);
        });

        Schema::create('transport_routes', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->string('name');
            $table->string('code', 20)->unique();
            $table->text('description')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->integer('capacity');
            $table->string('status', 20)->default('active');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['code']);
            $table->index(['status', 'is_active']);
        });

        Schema::create('transport_route_stops', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('route_id');
            $table->uuid('stop_id');
            $table->integer('stop_order');
            $table->time('arrival_time')->nullable();
            $table->time('departure_time')->nullable();
            $table->decimal('fare', 10, 2)->default(0);
            $table->timestamps();
            $table->foreign('route_id')->references('id')->on('transport_routes')->onDelete('cascade');
            $table->foreign('stop_id')->references('id')->on('transport_stops')->onDelete('cascade');
            $table->index(['route_id']);
            $table->index(['stop_id']);
            $table->unique(['route_id', 'stop_id']);
        });

        Schema::create('transport_schedules', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('route_id');
            $table->uuid('vehicle_id');
            $table->uuid('driver_id');
            $table->string('day_of_week', 20);
            $table->time('departure_time');
            $table->time('arrival_time');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->foreign('route_id')->references('id')->on('transport_routes')->onDelete('cascade');
            $table->foreign('vehicle_id')->references('id')->on('transport_vehicles')->onDelete('cascade');
            $table->foreign('driver_id')->references('id')->on('transport_drivers')->onDelete('cascade');
            $table->index(['route_id']);
            $table->index(['vehicle_id']);
            $table->index(['driver_id']);
            $table->index(['day_of_week', 'is_active']);
        });

        Schema::create('transport_assignments', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('student_id');
            $table->uuid('route_id');
            $table->uuid('pickup_stop_id')->nullable();
            $table->uuid('dropoff_stop_id')->nullable();
            $table->string('session_type', 20)->default('both');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->decimal('fee', 10, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->foreign('student_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('route_id')->references('id')->on('transport_routes')->onDelete('cascade');
            $table->foreign('pickup_stop_id')->references('id')->on('transport_stops')->onDelete('set null');
            $table->foreign('dropoff_stop_id')->references('id')->on('transport_stops')->onDelete('set null');
            $table->index(['student_id']);
            $table->index(['route_id']);
            $table->index(['pickup_stop_id']);
            $table->index(['dropoff_stop_id']);
            $table->index(['is_active']);
        });

        Schema::create('transport_attendance', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('assignment_id');
            $table->uuid('student_id');
            $table->uuid('route_id');
            $table->date('attendance_date');
            $table->time('boarding_time')->nullable();
            $table->time('alighting_time')->nullable();
            $table->string('status', 20)->default('absent');
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->foreign('assignment_id')->references('id')->on('transport_assignments')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('route_id')->references('id')->on('transport_routes')->onDelete('cascade');
            $table->index(['assignment_id']);
            $table->index(['student_id']);
            $table->index(['route_id']);
            $table->index(['attendance_date']);
        });

        Schema::create('transport_tracking', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('vehicle_id');
            $table->uuid('route_id')->nullable();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->decimal('speed', 6, 2)->default(0);
            $table->decimal('heading', 5, 2)->default(0);
            $table->decimal('odometer', 12, 2)->default(0);
            $table->timestamp('recorded_at');
            $table->timestamps();
            $table->foreign('vehicle_id')->references('id')->on('transport_vehicles')->onDelete('cascade');
            $table->foreign('route_id')->references('id')->on('transport_routes')->onDelete('set null');
            $table->index(['vehicle_id', 'recorded_at']);
            $table->index(['route_id', 'recorded_at']);
        });

        Schema::create('transport_incidents', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('vehicle_id')->nullable();
            $table->uuid('route_id')->nullable();
            $table->uuid('driver_id')->nullable();
            $table->string('incident_type', 50);
            $table->string('severity', 20)->default('low');
            $table->text('description');
            $table->timestamp('incident_time');
            $table->string('location')->nullable();
            $table->string('status', 20)->default('open');
            $table->text('resolution')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            $table->foreign('vehicle_id')->references('id')->on('transport_vehicles')->onDelete('set null');
            $table->foreign('route_id')->references('id')->on('transport_routes')->onDelete('set null');
            $table->foreign('driver_id')->references('id')->on('transport_drivers')->onDelete('set null');
            $table->index(['vehicle_id']);
            $table->index(['route_id']);
            $table->index(['driver_id']);
            $table->index(['status']);
            $table->index(['incident_time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transport_incidents');
        Schema::dropIfExists('transport_tracking');
        Schema::dropIfExists('transport_attendance');
        Schema::dropIfExists('transport_assignments');
        Schema::dropIfExists('transport_schedules');
        Schema::dropIfExists('transport_route_stops');
        Schema::dropIfExists('transport_routes');
        Schema::dropIfExists('transport_stops');
        Schema::dropIfExists('transport_drivers');
        Schema::dropIfExists('transport_vehicles');
    }
};