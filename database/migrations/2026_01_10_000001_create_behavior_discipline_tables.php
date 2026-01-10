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
        Schema::create('behavior_categories', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->string('name', 100);
            $table->string('type', 20)->default('negative');
            $table->text('description')->nullable();
            $table->string('severity', 20)->default('low');
            $table->datetimes();
        });

        Schema::create('behavior_incidents', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('student_id');
            $table->uuid('category_id');
            $table->uuid('reported_by');
            $table->string('title', 200);
            $table->text('description');
            $table->date('incident_date');
            $table->time('incident_time')->nullable();
            $table->string('location', 100)->nullable();
            $table->string('severity', 20)->default('low');
            $table->string('status', 20)->default('reported');
            $table->text('evidence')->nullable();
            $table->datetimes();
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('behavior_categories')->onDelete('cascade');
            $table->foreign('reported_by')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('discipline_actions', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('incident_id');
            $table->uuid('assigned_by');
            $table->string('action_type', 100);
            $table->text('description');
            $table->date('action_date');
            $table->string('status', 20)->default('pending');
            $table->text('outcome')->nullable();
            $table->datetimes();
            $table->foreign('incident_id')->references('id')->on('behavior_incidents')->onDelete('cascade');
            $table->foreign('assigned_by')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('intervention_plans', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('student_id');
            $table->uuid('incident_id')->nullable();
            $table->uuid('created_by');
            $table->text('goals');
            $table->text('strategies');
            $table->text('timeline');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status', 20)->default('active');
            $table->text('evaluation')->nullable();
            $table->datetimes();
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('incident_id')->references('id')->on('behavior_incidents')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('behavior_notes', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('student_id');
            $table->uuid('noted_by');
            $table->string('note_type', 50)->default('observation');
            $table->text('content');
            $table->date('note_date');
            $table->boolean('is_positive')->default(false);
            $table->datetimes();
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('noted_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('behavior_notes');
        Schema::dropIfExists('intervention_plans');
        Schema::dropIfExists('discipline_actions');
        Schema::dropIfExists('behavior_incidents');
        Schema::dropIfExists('behavior_categories');
    }
};
