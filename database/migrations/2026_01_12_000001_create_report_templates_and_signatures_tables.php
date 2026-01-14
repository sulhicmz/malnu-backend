<?php

declare (strict_types = 1);

use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Support\Facades\Schema;
use Hyperf\DbConnection\Db;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Report Templates
        Schema::create('report_templates', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->string('name', 200);
            $table->string('type', 50); // report_card, transcript, progress_report
            $table->string('grade_level', 50)->nullable(); // elementary, middle, high_school
            $table->text('content'); // HTML template content
            $table->text('css_styles')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->uuid('created_by')->nullable();
            $table->datetimes();
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });

        // Report Signatures
        Schema::create('report_signatures', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('report_id');
            $table->string('signer_name', 200);
            $table->string('signer_title', 200)->nullable(); // Principal, Teacher, etc.
            $table->string('signature_image_url', 500)->nullable(); // URL to signature image
            $table->timestamp('signed_at')->nullable();
            $table->text('notes')->nullable();
            $table->datetimes();
            $table->foreign('report_id')->references('id')->on('reports')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_signatures');
        Schema::dropIfExists('report_templates');
    }
};
