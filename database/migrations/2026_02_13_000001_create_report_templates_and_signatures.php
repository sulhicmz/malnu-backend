<?php

declare(strict_types=1);

use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\DbConnection\Db;
use Hyperf\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Report templates table - customizable templates for different report types
        Schema::create('report_templates', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->string('type', 50); // report_card, transcript, progress_report
            $table->string('grade_level', 50)->nullable(); // elementary, middle, high_school, or null for all
            $table->longText('header_template'); // HTML header template
            $table->longText('content_template'); // HTML main content template
            $table->longText('footer_template'); // HTML footer template
            $table->longText('css_styles')->nullable(); // Custom CSS styles
            $table->json('placeholders')->nullable(); // Available placeholder variables
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();

            $table->datetimes();

            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['type', 'grade_level']);
            $table->index(['is_active', 'is_default']);
        });

        // Report signatures table - digital signatures for official documents
        Schema::create('report_signatures', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('report_id');
            $table->string('signer_name', 255);
            $table->string('signer_title', 100); // Principal, Teacher, etc.
            $table->string('signature_image_url', 500)->nullable();
            $table->timestamp('signed_at')->nullable();
            $table->text('notes')->nullable();
            $table->uuid('signed_by')->nullable(); // User who applied the signature

            $table->datetimes();

            $table->foreign('report_id')->references('id')->on('reports')->onDelete('cascade');
            $table->foreign('signed_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['report_id', 'signer_title']);
        });

        // Add file_path and file_type to existing reports table
        Schema::table('reports', function (Blueprint $table) {
            $table->string('file_path', 500)->nullable()->after('principal_notes');
            $table->string('file_type', 20)->default('html')->after('file_path'); // html, pdf
            $table->uuid('template_id')->nullable()->after('file_type');
            $table->foreign('template_id')->references('id')->on('report_templates')->onDelete('set null');
            $table->index(['student_id', 'semester', 'academic_year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropForeign(['template_id']);
            $table->dropIndex(['student_id', 'semester', 'academic_year']);
            $table->dropColumn(['file_path', 'file_type', 'template_id']);
        });

        Schema::dropIfExists('report_signatures');
        Schema::dropIfExists('report_templates');
    }
};
