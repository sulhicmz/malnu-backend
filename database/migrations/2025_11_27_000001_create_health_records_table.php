<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateHealthRecordsTable extends Migration
{
    public function up(): void
    {
        Schema::create('health_records', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('student_id');
            $table->string('blood_type', 5)->nullable();
            $table->text('medical_history')->nullable();
            $table->text('chronic_conditions')->nullable();
            $table->text('previous_surgeries')->nullable();
            $table->text('family_medical_history')->nullable();
            $table->text('dietary_restrictions')->nullable();
            $table->text('physical_disabilities')->nullable();
            $table->text('notes')->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');

            $table->index('student_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('health_records');
    }
}
