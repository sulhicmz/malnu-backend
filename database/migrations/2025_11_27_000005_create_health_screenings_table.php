<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateHealthScreeningsTable extends Migration
{
    public function up(): void
    {
        Schema::create('health_screenings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('student_id');
            $table->string('screening_type');
            $table->date('screening_date');
            $table->text('results')->nullable();
            $table->text('findings')->nullable();
            $table->enum('status', ['normal', 'abnormal', 'needs_follow_up', 'incomplete'])->default('normal');
            $table->text('recommendations')->nullable();
            $table->date('follow_up_date')->nullable();
            $table->string('performed_by')->nullable();
            $table->text('notes')->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');

            $table->index('student_id');
            $table->index('screening_type');
            $table->index('screening_date');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('health_screenings');
    }
}
