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
        Schema::create('learning_activities', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('student_id');
            $table->string('activity_type', 50);
            $table->string('activity_subtype', 50)->nullable();
            $table->string('title', 255)->nullable();
            $table->text('description')->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->decimal('score', 5, 2)->nullable();
            $table->decimal('max_score', 5, 2)->nullable();
            $table->string('related_entity_type', 50)->nullable();
            $table->uuid('related_entity_id')->nullable();
            $table->timestamp('activity_date');
            $table->json('metadata')->nullable();
            $table->datetimes();

            $table->index('student_id');
            $table->index('activity_type');
            $table->index('activity_date');
            $table->foreign('student_id')->references('id')->on('school_management_students')->onDelete('cascade');
        });

        Schema::create('student_performance_metrics', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('student_id');
            $table->uuid('class_id')->nullable();
            $table->uuid('subject_id')->nullable();
            $table->string('semester', 20)->nullable();
            $table->decimal('gpa', 5, 2)->nullable();
            $table->decimal('attendance_rate', 5, 2)->nullable();
            $table->integer('total_activities')->default(0);
            $table->integer('assignments_completed')->default(0);
            $table->integer('assignments_score')->default(0);
            $table->integer('quizzes_completed')->default(0);
            $table->integer('quizzes_score')->default(0);
            $table->integer('exams_completed')->default(0);
            $table->integer('exams_score')->default(0);
            $table->decimal('engagement_score', 5, 2)->default(0);
            $table->timestamp('calculated_at');
            $table->json('metadata')->nullable();
            $table->datetimes();

            $table->unique(['student_id', 'semester']);
            $table->index('class_id');
            $table->index('subject_id');
            $table->index('calculated_at');
            $table->foreign('student_id')->references('id')->on('school_management_students')->onDelete('cascade');
            $table->foreign('class_id')->references('id')->on('school_management_classes')->onDelete('set null');
            $table->foreign('subject_id')->references('id')->on('school_management_subjects')->onDelete('set null');
        });

        Schema::create('learning_patterns', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('student_id');
            $table->string('pattern_type', 50);
            $table->string('pattern_value', 255);
            $table->string('pattern_frequency', 20)->default('weekly');
            $table->integer('occurrence_count')->default(1);
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->json('metrics')->nullable();
            $table->datetimes();

            $table->index('student_id');
            $table->index('pattern_type');
            $table->index('start_date');
            $table->foreign('student_id')->references('id')->on('school_management_students')->onDelete('cascade');
        });

        Schema::create('teaching_effectiveness', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('teacher_id');
            $table->uuid('class_id');
            $table->string('semester', 20)->nullable();
            $table->integer('student_count');
            $table->decimal('class_performance_improvement', 5, 2)->default(0);
            $table->decimal('student_satisfaction_score', 5, 2)->default(0);
            $table->integer('assignments_graded')->default(0);
            $table->integer('feedback_provided')->default(0);
            $table->text('notes')->nullable();
            $table->timestamp('evaluated_at');
            $table->datetimes();

            $table->index('teacher_id');
            $table->index('class_id');
            $table->index('semester');
            $table->index('evaluated_at');
            $table->foreign('teacher_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('class_id')->references('id')->on('school_management_classes')->onDelete('cascade');
        });

        Schema::create('early_warnings', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('student_id');
            $table->string('warning_type', 50);
            $table->string('severity', 20)->default('medium');
            $table->text('description');
            $table->json('indicators')->nullable();
            $table->json('recommendations')->nullable();
            $table->boolean('is_resolved')->default(false);
            $table->uuid('resolved_by')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->datetimes();

            $table->index('student_id');
            $table->index('warning_type');
            $table->index('severity');
            $table->index('is_resolved');
            $table->foreign('student_id')->references('id')->on('school_management_students')->onDelete('cascade');
            $table->foreign('resolved_by')->references('id')->on('users')->onDelete('set null');
        });

        Schema::create('intervention_recommendations', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('student_id');
            $table->uuid('early_warning_id')->nullable();
            $table->string('recommendation_type', 50);
            $table->text('description');
            $table->json('action_steps')->nullable();
            $table->string('priority', 20)->default('medium');
            $table->string('status', 20)->default('pending');
            $table->uuid('assigned_to')->nullable();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->datetimes();

            $table->index('student_id');
            $table->index('early_warning_id');
            $table->index('priority');
            $table->index('status');
            $table->foreign('student_id')->references('id')->on('school_management_students')->onDelete('cascade');
            $table->foreign('early_warning_id')->references('id')->on('early_warnings')->onDelete('set null');
            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');
        });

        Schema::create('knowledge_gaps', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('student_id');
            $table->uuid('subject_id')->nullable();
            $table->uuid('class_id')->nullable();
            $table->string('gap_type', 50);
            $table->string('topic', 255);
            $table->text('description');
            $table->string('mastery_level', 20)->default('unknown');
            $table->decimal('current_performance', 5, 2)->nullable();
            $table->decimal('target_performance', 5, 2)->nullable();
            $table->json('recommended_resources')->nullable();
            $table->boolean('is_resolved')->default(false);
            $table->timestamp('resolved_at')->nullable();
            $table->datetimes();

            $table->index('student_id');
            $table->index('subject_id');
            $table->index('class_id');
            $table->index('gap_type');
            $table->index('is_resolved');
            $table->foreign('student_id')->references('id')->on('school_management_students')->onDelete('cascade');
            $table->foreign('subject_id')->references('id')->on('school_management_subjects')->onDelete('set null');
            $table->foreign('class_id')->references('id')->on('school_management_classes')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('knowledge_gaps');
        Schema::dropIfExists('intervention_recommendations');
        Schema::dropIfExists('early_warnings');
        Schema::dropIfExists('teaching_effectiveness');
        Schema::dropIfExists('learning_patterns');
        Schema::dropIfExists('student_performance_metrics');
        Schema::dropIfExists('learning_activities');
    }
};