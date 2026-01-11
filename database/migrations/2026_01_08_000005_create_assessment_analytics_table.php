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
        Schema::create('assessment_analytics', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('assessment_id');
            $table->uuid('student_id')->nullable();
            $table->integer('total_participants')->default(0);
            $table->integer('completed_count')->default(0);
            $table->decimal('average_score', 10, 2)->nullable();
            $table->decimal('highest_score', 10, 2)->nullable();
            $table->decimal('lowest_score', 10, 2)->nullable();
            $table->integer('pass_rate')->default(0);
            $table->decimal('average_time_minutes', 10, 2)->nullable();
            $table->json('question_performance')->nullable();
            $table->json('learning_outcomes')->nullable();
            $table->datetimes();
            
            $table->foreign('assessment_id')->references('id')->on('assessments')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            
            $table->index('assessment_id');
            $table->index('student_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_analytics');
    }
};
