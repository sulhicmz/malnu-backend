<?php

declare(strict_types=1);

use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Schema\Schema;
use Hyperf\DbConnection\Db;

class CreateStudentAttendancesTable extends Db
{
    public function up(): void
    {
        Schema::create('student_attendances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('student_id');
            $table->uuid('class_id');
            $table->uuid('teacher_id')->nullable();
            $table->date('attendance_date');
            $table->enum('status', ['present', 'absent', 'late', 'excused'])->default('present');
            $table->string('notes')->nullable();
            $table->time('check_in_time')->nullable();
            $table->time('check_out_time')->nullable();
            $table->uuid('marked_by');
            $table->timestamps();

            $table->index('student_id');
            $table->index('class_id');
            $table->index('attendance_date');
            $table->index('status');
            $table->index(['student_id', 'attendance_date']);
            $table->index(['class_id', 'attendance_date']);

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('class_id')->references('id')->on('class_models')->onDelete('cascade');
            $table->foreign('teacher_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('marked_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_attendances');
    }
}
