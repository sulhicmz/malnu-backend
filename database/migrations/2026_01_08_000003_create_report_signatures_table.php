<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateReportSignaturesTable extends Migration
{
    public function up(): void
    {
        Schema::create('report_signatures', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 255);
            $table->string('title', 255)->nullable();
            $table->string('signature_type', 50);
            $table->binary('signature_image')->nullable();
            $table->string('signature_image_path', 500)->nullable();
            $table->boolean('is_default')->default(false);
            $table->text('metadata')->nullable();
            $table->string('created_by', 36)->nullable();
            $table->datetimes();

            $table->index('signature_type');
            $table->index('is_default');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_signatures');
    }
}
