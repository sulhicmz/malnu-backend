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
        Schema::create('courses', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('virtual_class_id')->nullable();
            $table->string('name', 200);
            $table->text('description')->nullable();
            $table->string('code', 20)->unique();
            $table->enum('level', ['beginner', 'intermediate', 'advanced'])->default('beginner');
            $table->integer('duration_hours')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->datetimes();
            $table->foreign('virtual_class_id')->references('id')->on('virtual_classes')->onDelete('set null');
        });

        Schema::create('learning_paths', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->string('name', 200);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->datetimes();
        });

        Schema::create('learning_path_items', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('learning_path_id');
            $table->uuid('course_id');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_required')->default(true);
            $table->datetimes();
            $table->foreign('learning_path_id')->references('id')->on('learning_paths')->onDelete('cascade');
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
        });

        Schema::create('enrollments', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('course_id');
            $table->uuid('student_id');
            $table->timestamp('enrolled_at');
            $table->timestamp('completed_at')->nullable();
            $table->enum('status', ['active', 'completed', 'dropped'])->default('active');
            $table->datetimes();
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->unique(['course_id', 'student_id']);
        });

        Schema::create('course_progress', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('enrollment_id');
            $table->integer('total_lessons')->default(0);
            $table->integer('completed_lessons')->default(0);
            $table->integer('total_assignments')->default(0);
            $table->integer('completed_assignments')->default(0);
            $table->integer('total_quizzes')->default(0);
            $table->integer('completed_quizzes')->default(0);
            $table->decimal('progress_percentage', 5, 2)->default(0.00);
            $table->timestamp('last_activity_at')->nullable();
            $table->datetimes();
            $table->foreign('enrollment_id')->references('id')->on('enrollments')->onDelete('cascade');
        });

        Schema::create('certificates', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('course_id');
            $table->uuid('student_id');
            $table->string('certificate_number', 50)->unique();
            $table->timestamp('issued_at');
            $table->text('notes')->nullable();
            $table->datetimes();
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('certificates');
        Schema::dropIfExists('course_progress');
        Schema::dropIfExists('enrollments');
        Schema::dropIfExists('learning_path_items');
        Schema::dropIfExists('learning_paths');
        Schema::dropIfExists('courses');
    }
};
