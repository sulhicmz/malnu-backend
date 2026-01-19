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
        Schema::create('learning_progress', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('course_enrollment_id');
            $table->uuid('learning_material_id')->nullable();
            $table->uuid('assignment_id')->nullable();
            $table->uuid('quiz_id')->nullable();
            $table->enum('status', ['not_started', 'in_progress', 'completed'])->default('not_started');
            $table->decimal('score', 5, 2)->nullable();
            $table->integer('time_spent_minutes')->default(0);
            $table->integer('attempts')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('last_accessed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->foreign('course_enrollment_id')->references('id')->on('course_enrollments')->onDelete('cascade');
            $table->foreign('learning_material_id')->references('id')->on('learning_materials')->onDelete('cascade');
            $table->foreign('assignment_id')->references('id')->on('assignments')->onDelete('cascade');
            $table->foreign('quiz_id')->references('id')->on('quizzes')->onDelete('cascade');
            $table->unique(['course_enrollment_id', 'learning_material_id']);
            $table->unique(['course_enrollment_id', 'assignment_id']);
            $table->unique(['course_enrollment_id', 'quiz_id']);
            $table->index(['course_enrollment_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('learning_progress');
    }
};
