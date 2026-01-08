<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateImmunizationsTable extends Migration
{
    public function up(): void
    {
        Schema::create('immunizations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('student_id');
            $table->string('vaccine_name');
            $table->string('vaccine_type')->nullable();
            $table->string('manufacturer')->nullable();
            $table->string('lot_number')->nullable();
            $table->date('administration_date');
            $table->date('next_due_date')->nullable();
            $table->string('administering_facility')->nullable();
            $table->string('administering_physician')->nullable();
            $table->enum('status', ['completed', 'due', 'overdue', 'exempt', 'not_applicable'])->default('completed');
            $table->text('exemption_reason')->nullable();
            $table->text('notes')->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');

            $table->index('student_id');
            $table->index('vaccine_name');
            $table->index('status');
            $table->index('administration_date');
            $table->index('next_due_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('immunizations');
    }
}
