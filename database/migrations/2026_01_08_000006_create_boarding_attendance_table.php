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
        Schema::create('boarding_attendance', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('student_id');
            $table->uuid('room_id');
            $table->uuid('hostel_id');
            $table->date('attendance_date');
            $table->dateTime('check_in_time')->nullable();
            $table->dateTime('check_out_time')->nullable();
            $table->string('status', 20)->default('present');
            $table->string('leave_type', 50)->nullable();
            $table->date('leave_start_date')->nullable();
            $table->date('leave_end_date')->nullable();
            $table->text('notes')->nullable();
            $table->datetimes();
            $table->foreign('student_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('room_id')->references('id')->on('rooms')->onDelete('cascade');
            $table->foreign('hostel_id')->references('id')->on('hostels')->onDelete('cascade');
            $table->unique(['student_id', 'attendance_date']);
            $table->index(['attendance_date', 'status']);
            $table->index(['hostel_id', 'attendance_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('boarding_attendance');
    }
};
