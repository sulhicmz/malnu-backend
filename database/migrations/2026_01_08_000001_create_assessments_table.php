<?php

declare(strict_types = 1);

use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Support\Facades\Schema;
use Hyperf\DbConnection\Db;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessments', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->string('title');
            $table->string('assessment_type', 50);
            $table->text('description')->nullable();
            $table->uuid('subject_id');
            $table->uuid('class_id');
            $table->datetime('start_time')->nullable();
            $table->datetime('end_time')->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->decimal('total_points', 10, 2)->default(100.00);
            $table->decimal('passing_grade', 5, 2)->default(60.00);
            $table->boolean('is_published')->default(false);
            $table->boolean('allow_retakes')->default(false);
            $table->integer('max_attempts')->default(1);
            $table->boolean('shuffle_questions')->default(false);
            $table->boolean('show_results_immediately')->default(true);
            $table->boolean('proctoring_enabled')->default(false);
            $table->uuid('rubric_id')->nullable();
            $table->uuid('created_by');
            $table->datetimes();
            
            $table->foreign('subject_id')->references('id')->on('subjects')->onDelete('cascade');
            $table->foreign('class_id')->references('id')->on('classes')->onDelete('cascade');
            $table->foreign('rubric_id')->references('id')->on('rubrics')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            
            $table->index(['subject_id', 'class_id']);
            $table->index('assessment_type');
            $table->index('start_time');
            $table->index('is_published');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessments');
    }
};
