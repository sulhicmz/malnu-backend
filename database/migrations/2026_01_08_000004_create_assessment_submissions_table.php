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
        Schema::create('assessment_submissions', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('assessment_id');
            $table->uuid('student_id');
            $table->datetime('started_at')->nullable();
            $table->datetime('submitted_at')->nullable();
            $table->integer('time_spent_minutes')->default(0);
            $table->decimal('score', 10, 2)->nullable();
            $table->decimal('percentage', 5, 2)->nullable();
            $table->boolean('passed')->default(false);
            $table->text('feedback')->nullable();
            $table->json('answers')->nullable();
            $table->integer('attempt_number')->default(1);
            $table->string('status', 20)->default('in_progress');
            $table->datetimes();
            
            $table->foreign('assessment_id')->references('id')->on('assessments')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            
            $table->index(['assessment_id', 'student_id']);
            $table->index('status');
            $table->index('submitted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_submissions');
    }
};
