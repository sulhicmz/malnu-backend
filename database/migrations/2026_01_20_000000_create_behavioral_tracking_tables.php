<?php

declare(strict_types=1);

use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Support\Facades\Schema;
use Hyperf\DbConnection\Db;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('behavioral_incidents', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('student_id');
            $table->uuid('reported_by')->nullable()->comment('User ID of person reporting incident');
            $table->string('incident_type', 50);
            $table->string('severity', 20);
            $table->text('description');
            $table->text('action_taken')->nullable();
            $table->timestamp('incident_date');
            $table->boolean('is_resolved')->default(false);
            $table->timestamp('resolved_at')->nullable();
            $table->uuid('resolved_by')->nullable();
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('reported_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('resolved_by')->references('id')->on('users')->onDelete('set null');

            $table->index(['student_id', 'incident_date']);
            $table->index('severity');
        });

        Schema::create('psychological_assessments', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('student_id');
            $table->uuid('assessed_by')->nullable()->comment('User ID of counselor/teacher');
            $table->string('assessment_type', 50);
            $table->json('assessment_data')->nullable()->comment('Flexible JSON storage for assessment responses');
            $table->integer('score')->nullable();
            $table->integer('max_score')->nullable();
            $table->text('notes')->nullable();
            $table->string('recommendations', 500)->nullable();
            $table->boolean('is_confidential')->default(true);
            $table->timestamp('assessment_date');
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('assessed_by')->references('id')->on('users')->onDelete('set null');

            $table->index(['student_id', 'assessment_type']);
            $table->index('assessment_date');
        });

        Schema::create('counselor_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('student_id');
            $table->uuid('counselor_id')->comment('User ID of counselor (teacher with counselor role)');
            $table->timestamp('session_date');
            $table->integer('duration_minutes')->nullable();
            $table->string('session_type', 50);
            $table->text('session_notes')->nullable();
            $table->text('observations')->nullable();
            $table->string('follow_up_required', 10)->default('pending')->comment('pending, completed, not_required');
            $table->timestamp('follow_up_date')->nullable();
            $table->boolean('is_private')->default(true);
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('counselor_id')->references('id')->on('users')->onDelete('cascade');

            $table->index(['student_id', 'session_date']);
            $table->index('counselor_id');
        });

        Schema::create('behavioral_interventions', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('incident_id')->nullable();
            $table->uuid('student_id');
            $table->uuid('intervention_by')->comment('User ID who created intervention');
            $table->string('intervention_type', 50);
            $table->text('description');
            $table->string('status', 20)->default('planned')->comment('planned, in_progress, completed, cancelled');
            $table->timestamp('planned_date')->nullable();
            $table->timestamp('completed_date')->nullable();
            $table->text('outcome')->nullable();
            $table->text('parent_notified')->nullable();
            $table->boolean('is_effective')->nullable();
            $table->timestamps();

            $table->foreign('incident_id')->references('id')->on('behavioral_incidents')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('intervention_by')->references('id')->on('users')->onDelete('cascade');

            $table->index(['student_id', 'status']);
            $table->index('incident_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('behavioral_interventions');
        Schema::dropIfExists('counselor_sessions');
        Schema::dropIfExists('psychological_assessments');
        Schema::dropIfExists('behavioral_incidents');
    }
};
