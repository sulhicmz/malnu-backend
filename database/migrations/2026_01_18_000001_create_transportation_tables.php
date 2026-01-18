<?php

declare(strict_types=1);

use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Support\Facades\Schema;
use Hyperf\DbConnection\Db;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('transportation_routes', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->string('route_name');
            $table->text('route_description')->nullable();
            $table->string('start_location');
            $table->string('end_location');
            $table->string('status')->default('active');
            $table->time('start_time');
            $table->time('end_time');
            $table->decimal('distance_km', 8, 2)->nullable();
            $table->decimal('fuel_capacity', 8, 2)->nullable();
            $table->uuid('vehicle_id')->nullable();
            $table->uuid('driver_id')->nullable();
            $table->json('stops')->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
            
            $table->foreign('vehicle_id')->references('id')->on('transportation_vehicles')->onDelete('set null');
            $table->foreign('driver_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            
            $table->index(['status']);
            $table->index(['vehicle_id']);
            $table->index(['driver_id']);
        });

        Schema::create('transportation_registrations', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('student_id');
            $table->uuid('route_id')->nullable();
            $table->uuid('stop_id')->nullable();
            $table->string('pickup_location')->nullable();
            $table->string('dropoff_location')->nullable();
            $table->date('registration_date');
            $table->string('status')->default('pending');
            $table->json('emergency_contact')->nullable();
            $table->text('notes')->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
            
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('route_id')->references('id')->on('transportation_routes')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            
            $table->index(['student_id']);
            $table->index(['route_id']);
            $table->index(['status']);
        });

        Schema::create('transportation_vehicles', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->string('vehicle_number');
            $table->string('license_plate');
            $table->string('vehicle_type');
            $table->integer('capacity');
            $table->string('model')->nullable();
            $table->string('make')->nullable();
            $table->integer('year')->nullable();
            $table->string('status')->default('active');
            $table->decimal('fuel_consumption', 8, 2)->nullable();
            $table->date('last_maintenance_date')->nullable();
            $table->date('next_maintenance_date')->nullable();
            $table->text('notes')->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
            
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            
            $table->index(['license_plate']);
            $table->index(['status']);
        });

        Schema::create('transportation_drivers', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('user_id')->unique();
            $table->string('driver_license_number');
            $table->date('license_expiry_date');
            $table->string('status')->default('active');
            $table->date('background_check_date')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->text('notes')->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            
            $table->index(['status']);
        });

        Schema::create('transportation_assignments', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('student_id');
            $table->uuid('route_id');
            $table->uuid('stop_id')->nullable();
            $table->date('assignment_date');
            $table->string('status')->default('active');
            $table->text('notes')->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
            
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('route_id')->references('id')->on('transportation_routes')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            
            $table->index(['student_id']);
            $table->index(['route_id']);
            $table->index(['status']);
        });

        Schema::create('transportation_fees', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('student_id');
            $table->uuid('route_id')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('currency')->default('IDR');
            $table->string('academic_year');
            $table->string('semester')->nullable();
            $table->date('due_date');
            $table->date('paid_date')->nullable();
            $table->string('payment_status')->default('unpaid');
            $table->string('payment_method')->nullable();
            $table->string('transaction_id')->nullable();
            $table->text('notes')->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
            
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('route_id')->references('id')->on('transportation_routes')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            
            $table->index(['student_id']);
            $table->index(['route_id']);
            $table->index(['payment_status']);
            $table->index(['due_date']);
        });

        Schema::create('transportation_incidents', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('route_id')->nullable();
            $table->uuid('vehicle_id')->nullable();
            $table->uuid('driver_id')->nullable();
            $table->uuid('student_id')->nullable();
            $table->string('incident_type');
            $table->dateTime('incident_date');
            $table->text('description');
            $table->string('severity')->default('minor');
            $table->string('status')->default('open');
            $table->text('resolution')->nullable();
            $table->dateTime('resolved_date')->nullable();
            $table->json('reported_by')->nullable();
            $table->text('notes')->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
            
            $table->foreign('route_id')->references('id')->on('transportation_routes')->onDelete('set null');
            $table->foreign('vehicle_id')->references('id')->on('transportation_vehicles')->onDelete('set null');
            $table->foreign('driver_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            
            $table->index(['incident_date']);
            $table->index(['status']);
            $table->index(['severity']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transportation_routes');
        Schema::dropIfExists('transportation_registrations');
        Schema::dropIfExists('transportation_vehicles');
        Schema::dropIfExists('transportation_drivers');
        Schema::dropIfExists('transportation_assignments');
        Schema::dropIfExists('transportation_fees');
        Schema::dropIfExists('transportation_incidents');
    }
};
