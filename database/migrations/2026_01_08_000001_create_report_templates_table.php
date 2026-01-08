<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateReportTemplatesTable extends Migration
{
    public function up(): void
    {
        Schema::create('report_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 255);
            $table->string('type', 50);
            $table->text('html_template');
            $table->json('variables');
            $table->string('grade_level', 50)->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('created_by', 36)->nullable();
            $table->datetimes();

            $table->index('type');
            $table->index('grade_level');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_templates');
    }
}
