<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateGeneratedReportsTable extends Migration
{
    public function up(): void
    {
        Schema::create('generated_reports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('student_id', 36);
            $table->string('report_type', 50);
            $table->string('semester', 20)->nullable();
            $table->string('academic_year', 20)->nullable();
            $table->uuid('template_id')->nullable();
            $table->string('file_path', 500);
            $table->string('file_format', 10)->default('pdf');
            $table->integer('file_size')->nullable();
            $table->string('status', 20)->default('generated');
            $table->text('generation_data')->nullable();
            $table->boolean('is_published')->default(false);
            $table->datetime('published_at')->nullable();
            $table->string('created_by', 36)->nullable();
            $table->datetimes();

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('template_id')->references('id')->on('report_templates')->nullOnDelete();
            $table->index('student_id');
            $table->index('report_type');
            $table->index('semester');
            $table->index('academic_year');
            $table->index('status');
            $table->index('is_published');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('generated_reports');
    }
}
