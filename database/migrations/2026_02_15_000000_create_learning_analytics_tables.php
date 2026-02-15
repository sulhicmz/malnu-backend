<?php

declare(strict_types=1);

use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Schema\Table;

class CreateLearningAnalyticsTables extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Learning Activities - Track all learning interactions
        Schema::create('learning_activities', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('student_id');
            $table->uuid('subject_id')->nullable();
            $table->string('activity_type', 50); // assignment, quiz, exam, attendance, participation
            $table->string('activity_name', 255);
            $table->text('description')->nullable();
            $table->decimal('score', 5, 2)->nullable();
            $table->decimal('max_score', 5, 2)->nullable();
            $table->timestamp('activity_date');
            $table->integer('duration_minutes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['student_id', 'activity_date']);
            $table->index(['student_id', 'activity_type']);
            $table->index(['subject_id', 'activity_date']);
        });

        // Student Performance Metrics - Aggregated performance data
        Schema::create('student_performance_metrics', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('student_id');
            $table->uuid('subject_id')->nullable();
            $table->string('metric_type', 50); // gpa, attendance_rate, engagement_score, completion_rate
            $table->decimal('value', 8, 4);
            $table->string('period_type', 20); // weekly, monthly, semester, yearly
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('previous_value', 8, 4)->nullable();
            $table->decimal('trend_percentage', 6, 2)->nullable();
            $table->json('breakdown')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'subject_id', 'metric_type', 'period_type', 'period_start']);
            $table->index(['student_id', 'metric_type', 'period_start']);
            $table->index(['subject_id', 'metric_type', 'period_start']);
        });

        // Learning Patterns - Track learning behaviors and patterns
        Schema::create('learning_patterns', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('student_id');
            $table->string('pattern_type', 50); // study_time_distribution, participation_trend, assignment_completion
            $table->json('pattern_data');
            $table->date('analysis_period_start');
            $table->date('analysis_period_end');
            $table->string('pattern_strength', 20)->default('moderate'); // strong, moderate, weak
            $table->text('insights')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'pattern_type', 'analysis_period_start']);
        });

        // Teaching Effectiveness Metrics
        Schema::create('teaching_effectiveness_metrics', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('teacher_id');
            $table->uuid('class_id')->nullable();
            $table->uuid('subject_id')->nullable();
            $table->decimal('class_average_improvement', 5, 2)->nullable();
            $table->decimal('student_engagement_score', 5, 2)->nullable();
            $table->decimal('assessment_quality_score', 5, 2)->nullable();
            $table->integer('total_students')->default(0);
            $table->integer('students_improved')->default(0);
            $table->string('period_type', 20);
            $table->date('period_start');
            $table->date('period_end');
            $table->timestamps();

            $table->index(['teacher_id', 'period_start']);
            $table->index(['class_id', 'period_start']);
        });

        // Early Warnings - At-risk student alerts
        Schema::create('early_warnings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('student_id');
            $table->string('warning_type', 50); // performance_decline, low_attendance, low_engagement, behavior_issue
            $table->string('severity', 20)->default('medium'); // low, medium, high, critical
            $table->text('description');
            $table->json('trigger_data');
            $table->string('status', 20)->default('active'); // active, acknowledged, resolved, dismissed
            $table->timestamp('triggered_at');
            $table->timestamp('acknowledged_at')->nullable();
            $table->uuid('acknowledged_by')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'status']);
            $table->index(['warning_type', 'severity', 'status']);
            $table->index(['triggered_at', 'status']);
        });

        // Knowledge Gaps - Subject mastery tracking
        Schema::create('knowledge_gaps', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('student_id');
            $table->uuid('subject_id');
            $table->string('topic_area', 255);
            $table->string('sub_topic', 255)->nullable();
            $table->decimal('mastery_level', 5, 2)->default(0.00); // 0-100
            $table->decimal('target_mastery_level', 5, 2)->default(70.00);
            $table->string('gap_status', 20)->default('identified'); // identified, improving, resolved, critical
            $table->integer('assessment_count')->default(0);
            $table->timestamp('last_assessed_at')->nullable();
            $table->text('recommended_resources')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'subject_id', 'gap_status']);
            $table->index(['student_id', 'mastery_level']);
            $table->index(['subject_id', 'gap_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('knowledge_gaps');
        Schema::dropIfExists('early_warnings');
        Schema::dropIfExists('teaching_effectiveness_metrics');
        Schema::dropIfExists('learning_patterns');
        Schema::dropIfExists('student_performance_metrics');
        Schema::dropIfExists('learning_activities');
    }
}
