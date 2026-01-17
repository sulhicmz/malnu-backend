<?php

declare(strict_types=1);

use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Schema\Schema;

return new class
{
    public function up(): void
    {
        Schema::create('transportation_routes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('route_name');
            $table->text('route_description')->nullable();
            $table->string('origin')->comment();
            $table->string('destination')->comment();
            $table->json('stops')->comment();
            $table->time('departure_time');
            $table->time('arrival_time');
            $table->integer('capacity');
            $table->integer('current_enrollment')->default(0);
            $table->string('bus_number')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->index(['origin', 'destination']);
            $table->index('status');
        });

        Schema::create('transportation_registrations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('student_id');
            $table->uuid('route_id')->nullable();
            $table->uuid('bus_stop_id')->nullable();
            $table->date('registration_date');
            $table->date('expiry_date')->nullable();
            $table->enum('status', ['active', 'expired', 'cancelled'])->default('active');
            $table->decimal('fee_amount', 10, 2)->default(0.00);
            $table->boolean('fee_paid')->default(false);
            $table->string('payment_status')->default('pending');
            $table->text('special_requirements')->nullable();
            $table->text('parent_notes')->nullable();
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('route_id')->references('id')->on('transportation_routes')->onDelete('set null');
            $table->index(['student_id', 'status']);
            $table->index('route_id');
        });

        Schema::create('transportation_vehicles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('vehicle_number');
            $table->string('vehicle_type')->default('bus');
            $table->string('license_plate')->unique();
            $table->integer('capacity')->default(50);
            $table->string('make')->nullable();
            $table->string('model')->nullable();
            $table->integer('year')->nullable();
            $table->enum('status', ['active', 'maintenance', 'retired'])->default('active');
            $table->string('insurance_number')->nullable();
            $table->date('insurance_expiry')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('vehicle_number');
            $table->index('status');
        });

        Schema::create('transportation_drivers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('driver_name');
            $table->string('phone_number');
            $table->string('email')->nullable();
            $table->string('license_number')->unique();
            $table->date('license_expiry')->nullable();
            $table->uuid('user_id')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->text('certifications')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->index('license_number');
            $table->index('status');
        });

        Schema::create('transportation_assignments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('registration_id');
            $table->uuid('driver_id')->nullable();
            $table->uuid('vehicle_id')->nullable();
            $table->date('assignment_date')->nullable();
            $table->date('assignment_end')->nullable();
            $table->enum('assignment_type', ['route', 'vehicle', 'both'])->default('route');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('registration_id')->references('id')->on('transportation_registrations')->onDelete('cascade');
            $table->foreign('driver_id')->references('id')->on('transportation_drivers')->onDelete('set null');
            $table->foreign('vehicle_id')->references('id')->on('transportation_vehicles')->onDelete('set null');
            $table->index(['registration_id', 'assignment_date']);
        });

        Schema::create('transportation_fees', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('registration_id');
            $table->string('fee_type')->default('transportation');
            $table->decimal('amount', 10, 2);
            $table->string('currency')->default('IDR');
            $table->date('due_date')->nullable();
            $table->date('paid_date')->nullable();
            $table->enum('payment_status', ['pending', 'paid', 'overdue', 'waived'])->default('pending');
            $table->string('payment_method')->nullable();
            $table->string('transaction_reference')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->foreign('registration_id')->references('id')->on('transportation_registrations')->onDelete('cascade');
            $table->index(['registration_id', 'payment_status']);
        });

        Schema::create('transportation_incidents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('driver_id')->nullable();
            $table->uuid('vehicle_id')->nullable();
            $table->uuid('registration_id')->nullable();
            $table->date('incident_date');
            $table->time('incident_time')->nullable();
            $table->enum('incident_type', ['accident', 'delay', 'breakdown', 'safety_violation', 'other']);
            $table->text('description');
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->enum('status', ['reported', 'investigating', 'resolved', 'closed'])->default('reported');
            $table->text('action_taken')->nullable();
            $table->text('witnesses')->nullable();
            $table->uuid('reported_by')->nullable();
            $table->timestamps();

            $table->foreign('driver_id')->references('id')->on('transportation_drivers')->onDelete('set null');
            $table->foreign('vehicle_id')->references('id')->on('transportation_vehicles')->onDelete('set null');
            $table->foreign('registration_id')->references('id')->on('transportation_registrations')->onDelete('set null');
            $table->foreign('reported_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['incident_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transportation_incidents');
        Schema::dropIfExists('transportation_fees');
        Schema::dropIfExists('transportation_assignments');
        Schema::dropIfExists('transportation_drivers');
        Schema::dropIfExists('transportation_vehicles');
        Schema::dropIfExists('transportation_registrations');
        Schema::dropIfExists('transportation_routes');
    }
};
