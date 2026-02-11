<?php

declare(strict_types=1);

use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\DbConnection\Db;
use Hyperf\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('enrollments', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('student_id');
            $table->uuid('class_id');
            $table->string('academic_year', 9);
            $table->smallInteger('semester')->default(1);
            $table->date('enrollment_date');
            $table->date('withdrawal_date')->nullable();
            $table->string('enrollment_status', 20)->default('active');
            $table->text('notes')->nullable();
            $table->uuid('created_by')->nullable();
            $table->datetimes();
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('class_id')->references('id')->on('classes')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');

            $table->index(['student_id', 'academic_year']);
            $table->index(['class_id', 'academic_year']);
        });

        Schema::create('academic_years', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->string('year', 9)->unique();
            $table->string('name', 50);
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_current')->default(false);
            $table->boolean('is_active')->default(true);
            $table->datetimes();
        });

        Schema::create('promotions', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('student_id');
            $table->uuid('from_class_id');
            $table->uuid('to_class_id');
            $table->string('from_academic_year', 9);
            $table->string('to_academic_year', 9);
            $table->date('promotion_date');
            $table->string('status', 20)->default('promoted');
            $table->text('notes')->nullable();
            $table->uuid('approved_by')->nullable();
            $table->datetimes();
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('from_class_id')->references('id')->on('classes')->onDelete('cascade');
            $table->foreign('to_class_id')->references('id')->on('classes')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');

            $table->index(['student_id', 'from_academic_year']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('promotions');
        Schema::dropIfExists('academic_years');
        Schema::dropIfExists('enrollments');
    }
};
