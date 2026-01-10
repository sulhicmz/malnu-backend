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
        Schema::create('hostel_rooms', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->string('room_number', 20)->unique();
            $table->string('building', 50);
            $table->string('floor', 20);
            $table->integer('capacity')->default(2);
            $table->string('type', 30)->default('standard');
            $table->string('gender')->default('male');
            $table->enum('status', ['available', 'occupied', 'maintenance', 'out_of_service'])->default('available');
            $table->text('facilities')->nullable();
            $table->text('description')->nullable();
            $table->datetimes();
        });

        Schema::create('hostel_assignments', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('student_id');
            $table->uuid('room_id');
            $table->date('assigned_date');
            $table->date('checkout_date')->nullable();
            $table->text('notes')->nullable();
            $table->enum('assignment_status', ['active', 'completed', 'transferred', 'cancelled'])->default('active');
            $table->datetimes();
            $table->foreign('student_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('room_id')->references('id')->on('hostel_rooms')->onDelete('cascade');
            $table->index(['student_id', 'assignment_status']);
        });

        Schema::create('hostel_facilities', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->enum('type', ['common_area', 'recreational', 'maintenance', 'security', 'study', 'dining', 'laundry']);
            $table->enum('status', ['active', 'maintenance', 'out_of_service'])->default('active');
            $table->string('location')->nullable();
            $table->integer('capacity')->nullable();
            $table->datetimes();
        });

        Schema::create('hostel_visitors', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('student_id');
            $table->string('visitor_name', 100);
            $table->string('visitor_relation', 50);
            $table->string('purpose_of_visit', 200);
            $table->date('visit_date');
            $table->time('check_in_time');
            $table->time('check_out_time')->nullable();
            $table->enum('approval_status', ['pending', 'approved', 'rejected', 'checked_in', 'checked_out'])->default('pending');
            $table->text('notes')->nullable();
            $table->datetimes();
            $table->foreign('student_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['student_id', 'visit_date']);
        });

        Schema::create('hostel_meals', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->date('meal_date');
            $table->enum('meal_type', ['breakfast', 'lunch', 'dinner']);
            $table->string('menu')->nullable();
            $table->integer('total_servings');
            $table->integer('servings_consumed')->default(0);
            $table->text('dietary_requirements')->nullable();
            $table->datetimes();
        });

        Schema::create('hostel_attendance', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('student_id');
            $table->date('attendance_date');
            $table->time('check_in_time');
            $table->time('check_out_time')->nullable();
            $table->enum('status', ['present', 'absent', 'late', 'excused'])->default('present');
            $table->text('notes')->nullable();
            $table->datetimes();
            $table->foreign('student_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['student_id', 'attendance_date']);
        });

        Schema::create('hostel_maintenance', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('room_id');
            $table->uuid('facility_id')->nullable();
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->date('reported_date');
            $table->date('scheduled_date')->nullable();
            $table->date('completed_date')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->uuid('assigned_to')->nullable();
            $table->text('completion_notes')->nullable();
            $table->datetimes();
            $table->foreign('room_id')->references('id')->on('hostel_rooms')->onDelete('cascade');
            $table->foreign('facility_id')->references('id')->on('hostel_facilities')->onDelete('set null');
            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');
            $table->index(['room_id', 'status']);
        });

        Schema::create('hostel_discipline', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('student_id');
            $table->date('incident_date');
            $table->enum('incident_type', ['minor', 'moderate', 'major', 'severe']);
            $table->text('description');
            $table->text('action_taken')->nullable();
            $table->enum('disciplinary_action', ['warning', 'probation', 'suspension', 'expulsion', 'community_service', 'counseling'])->nullable();
            $table->uuid('reported_by')->nullable();
            $table->text('notes')->nullable();
            $table->datetimes();
            $table->foreign('student_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('reported_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['student_id', 'incident_date']);
        });

        Schema::create('hostel_leaves', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('student_id');
            $table->enum('leave_type', ['weekend', 'holiday', 'medical', 'family_emergency', 'other']);
            $table->date('start_date');
            $table->date('end_date');
            $table->text('reason')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->uuid('approved_by')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('return_notes')->nullable();
            $table->datetimes();
            $table->foreign('student_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['student_id', 'start_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hostel_leaves');
        Schema::dropIfExists('hostel_discipline');
        Schema::dropIfExists('hostel_maintenance');
        Schema::dropIfExists('hostel_attendance');
        Schema::dropIfExists('hostel_meals');
        Schema::dropIfExists('hostel_visitors');
        Schema::dropIfExists('hostel_assignments');
        Schema::dropIfExists('hostel_facilities');
        Schema::dropIfExists('hostel_rooms');
    }
}
