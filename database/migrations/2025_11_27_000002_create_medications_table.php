<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateMedicationsTable extends Migration
{
    public function up(): void
    {
        Schema::create('medications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('student_id');
            $table->string('medication_name');
            $table->string('dosage');
            $table->string('frequency');
            $table->string('administration_method')->default('oral');
            $table->text('instructions')->nullable();
            $table->string('prescribing_physician')->nullable();
            $table->string('prescription_number')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->time('administration_time')->nullable();
            $table->boolean('requires_refrigeration')->default(false);
            $table->enum('status', ['active', 'completed', 'discontinued', 'on_hold'])->default('active');
            $table->text('discontinuation_reason')->nullable();
            $table->uuid('school_nurse_id')->nullable();
            $table->boolean('parent_consent')->default(false);
            $table->date('parent_consent_date')->nullable();
            $table->text('notes')->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('school_nurse_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');

            $table->index('student_id');
            $table->index('status');
            $table->index('start_date');
            $table->index('end_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medications');
    }
}
