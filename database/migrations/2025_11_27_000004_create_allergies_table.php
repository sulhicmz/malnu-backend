<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateAllergiesTable extends Migration
{
    public function up(): void
    {
        Schema::create('allergies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('student_id');
            $table->string('allergen');
            $table->enum('allergy_type', ['food', 'medication', 'environmental', 'insect', 'other'])->default('other');
            $table->enum('severity', ['mild', 'moderate', 'severe', 'life_threatening'])->default('moderate');
            $table->text('reactions')->nullable();
            $table->date('diagnosis_date')->nullable();
            $table->text('emergency_protocol')->nullable();
            $table->boolean('requires_epipen')->default(false);
            $table->text('treatment_plan')->nullable();
            $table->text('notes')->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');

            $table->index('student_id');
            $table->index('allergy_type');
            $table->index('severity');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('allergies');
    }
}
