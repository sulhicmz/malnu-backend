<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateMedicalIncidentsTable extends Migration
{
    public function up(): void
    {
        Schema::create('medical_incidents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('student_id');
            $table->dateTime('incident_date');
            $table->string('incident_type');
            $table->text('description');
            $table->text('injury_details')->nullable();
            $table->enum('severity', ['minor', 'moderate', 'severe', 'life_threatening'])->default('moderate');
            $table->text('treatment_provided')->nullable();
            $table->uuid('reported_by')->nullable();
            $table->uuid('treated_by')->nullable();
            $table->text('follow_up_actions')->nullable();
            $table->date('follow_up_date')->nullable();
            $table->boolean('parent_notified')->default(false);
            $table->dateTime('parent_notification_date')->nullable();
            $table->string('location')->nullable();
            $table->enum('status', ['open', 'investigating', 'resolved', 'closed'])->default('open');
            $table->text('notes')->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('reported_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('treated_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');

            $table->index('student_id');
            $table->index('incident_date');
            $table->index('severity');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medical_incidents');
    }
}
