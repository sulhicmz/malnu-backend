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
        Schema::create('intervention_plans', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('student_id')->comment('Reference to student');
            $table->uuid('created_by')->comment('User who created the plan');
            $table->uuid('assigned_to')->nullable()->comment('User assigned to oversee the plan');
            $table->string('title', 255)->comment('Title of the intervention plan');
            $table->text('description')->nullable()->comment('Detailed description of the intervention');
            $table->text('goals')->nullable()->comment('Behavioral goals for the student');
            $table->text('strategies')->nullable()->comment('Strategies to achieve goals');
            $table->enum('status', ['draft', 'active', 'completed', 'cancelled'])->default('draft');
            $table->date('start_date')->nullable()->comment('When the intervention plan starts');
            $table->date('end_date')->nullable()->comment('When the intervention plan ends');
            $table->date('review_date')->nullable()->comment('When the plan will be reviewed');
            $table->text('notes')->nullable()->comment('Additional notes about the intervention');
            $table->boolean('is_successful')->nullable()->comment('Whether the intervention was successful');
            $table->uuid('updated_by')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            $table->index('student_id');
            $table->index('status');
            $table->index('start_date');
            $table->index('end_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('intervention_plans');
    }
};
