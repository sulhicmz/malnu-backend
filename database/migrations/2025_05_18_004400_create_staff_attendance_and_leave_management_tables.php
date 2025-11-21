<?php

declare(strict_types=1);

use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\DbConnection\Db;
use Hypervel\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Leave Types table
        Schema::create('leave_types', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('(UUID())'));
            $table->string('name', 100); // e.g., 'Sick Leave', 'Casual Leave', 'Annual Leave'
            $table->string('code', 20)->unique(); // e.g., 'SL', 'CL', 'AL'
            $table->text('description')->nullable();
            $table->integer('max_days_per_year')->nullable(); // Maximum allowed days per year
            $table->boolean('is_paid')->default(true); // Whether the leave is paid or unpaid
            $table->boolean('requires_approval')->default(true); // Whether approval is required
            $table->json('eligibility_criteria')->nullable(); // JSON for complex eligibility rules
            $table->boolean('is_active')->default(true);
            
            $table->datetimes();
        });

        // Staff Attendance table
        Schema::create('staff_attendances', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('(UUID())'));
            $table->uuid('staff_id'); // Links to either teachers or staff table
            $table->date('attendance_date');
            $table->time('check_in_time')->nullable();
            $table->time('check_out_time')->nullable();
            $table->enum('status', ['present', 'absent', 'late', 'early_departure', 'on_leave'])->default('absent');
            $table->text('notes')->nullable();
            $table->string('check_in_method', 20)->default('manual'); // manual, biometric, etc.
            $table->string('check_out_method', 20)->default('manual'); // manual, biometric, etc.
            
            $table->datetimes();
            $table->unique(['staff_id', 'attendance_date']);
            $table->foreign('staff_id')->references('id')->on('staff')->onDelete('cascade');
        });

        // Leave Requests table
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('(UUID())'));
            $table->uuid('staff_id'); // Links to either teachers or staff table
            $table->uuid('leave_type_id');
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('total_days');
            $table->text('reason');
            $table->text('comments')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
            $table->uuid('approved_by')->nullable(); // Who approved/rejected the request
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_comments')->nullable();
            $table->uuid('substitute_assigned_id')->nullable(); // Assigned substitute teacher
            
            $table->datetimes();
            $table->foreign('staff_id')->references('id')->on('staff')->onDelete('cascade');
            $table->foreign('leave_type_id')->references('id')->on('leave_types')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('substitute_assigned_id')->references('id')->on('teachers')->onDelete('set null');
        });

        // Leave Balances table
        Schema::create('leave_balances', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('(UUID())'));
            $table->uuid('staff_id'); // Links to either teachers or staff table
            $table->uuid('leave_type_id');
            $table->integer('current_balance'); // Current available days
            $table->integer('used_days')->default(0); // Days already used
            $table->integer('allocated_days')->default(0); // Total days allocated for the year
            $table->integer('carry_forward_days')->default(0); // Days carried forward from previous year
            $table->year('year'); // For which year the balance applies
            
            $table->datetimes();
            $table->unique(['staff_id', 'leave_type_id', 'year']);
            $table->foreign('staff_id')->references('id')->on('staff')->onDelete('cascade');
            $table->foreign('leave_type_id')->references('id')->on('leave_types')->onDelete('cascade');
        });

        // Substitute Teachers table
        Schema::create('substitute_teachers', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('(UUID())'));
            $table->uuid('teacher_id'); // Links to teachers table
            $table->boolean('is_active')->default(true);
            $table->json('available_subjects')->nullable(); // JSON array of subjects they can teach
            $table->json('available_classes')->nullable(); // JSON array of classes they can handle
            $table->text('special_notes')->nullable();
            $table->decimal('hourly_rate', 10, 2)->nullable(); // Payment rate
            
            $table->datetimes();
            $table->foreign('teacher_id')->references('id')->on('teachers')->onDelete('cascade');
        });

        // Substitute Assignments table
        Schema::create('substitute_assignments', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('(UUID())'));
            $table->uuid('leave_request_id'); // The leave request being covered
            $table->uuid('substitute_teacher_id'); // The substitute assigned
            $table->uuid('class_subject_id')->nullable(); // Specific class/subject being covered
            $table->date('assignment_date');
            $table->enum('status', ['assigned', 'completed', 'cancelled'])->default('assigned');
            $table->text('assignment_notes')->nullable();
            $table->decimal('payment_amount', 10, 2)->nullable(); // Amount to be paid to substitute
            
            $table->datetimes();
            $table->foreign('leave_request_id')->references('id')->on('leave_requests')->onDelete('cascade');
            $table->foreign('substitute_teacher_id')->references('id')->on('substitute_teachers')->onDelete('cascade');
            $table->foreign('class_subject_id')->references('id')->on('class_subjects')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('substitute_assignments');
        Schema::dropIfExists('substitute_teachers');
        Schema::dropIfExists('leave_balances');
        Schema::dropIfExists('leave_requests');
        Schema::dropIfExists('staff_attendances');
        Schema::dropIfExists('leave_types');
    }
};