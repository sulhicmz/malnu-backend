<?php

declare (strict_types = 1);

use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Support\Facades\Schema;
use Hyperf\DbConnection\Db;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compliance_requirements', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->string('name', 200);
            $table->text('description')->nullable();
            $table->string('category', 100);
            $table->string('regulatory_body', 100)->nullable();
            $table->string('status', 50)->default('pending');
            $table->date('due_date')->nullable();
            $table->date('completion_date')->nullable();
            $table->uuid('responsible_staff_id')->nullable();
            $table->string('priority', 20)->default('medium');
            $table->text('notes')->nullable();
            $table->string('document_path', 500)->nullable();
            $table->datetimes();
            $table->foreign('responsible_staff_id')->references('id')->on('staff')->onDelete('set null');
        });

        Schema::create('accreditation_standards', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->string('name', 200);
            $table->string('accreditation_body', 100);
            $table->string('standard_code', 50)->nullable();
            $table->text('description')->nullable();
            $table->string('status', 50)->default('in_progress');
            $table->date('assessment_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->uuid('coordinator_id')->nullable();
            $table->text('evidence_notes')->nullable();
            $table->string('report_path', 500)->nullable();
            $table->datetimes();
            $table->foreign('coordinator_id')->references('id')->on('staff')->onDelete('set null');
        });

        Schema::create('policies_and_procedures', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->string('title', 200);
            $table->string('category', 100);
            $table->string('policy_number', 50)->nullable();
            $table->text('content');
            $table->integer('version')->default(1);
            $table->date('effective_date');
            $table->date('review_date')->nullable();
            $table->string('status', 50)->default('active');
            $table->uuid('author_id');
            $table->uuid('approver_id')->nullable();
            $table->text('change_summary')->nullable();
            $table->string('document_path', 500)->nullable();
            $table->datetimes();
            $table->foreign('author_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('approver_id')->references('id')->on('users')->onDelete('set null');
        });

        Schema::create('staff_evaluations', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('staff_id');
            $table->uuid('evaluator_id');
            $table->date('evaluation_date');
            $table->string('evaluation_type', 50);
            $table->string('academic_year', 9);
            $table->decimal('overall_score', 5, 2)->nullable();
            $table->string('rating', 20)->nullable();
            $table->text('strengths')->nullable();
            $table->text('areas_for_improvement')->nullable();
            $table->text('goals')->nullable();
            $table->string('status', 50)->default('draft');
            $table->uuid('reviewer_id')->nullable();
            $table->date('review_date')->nullable();
            $table->text('feedback')->nullable();
            $table->datetimes();
            $table->foreign('staff_id')->references('id')->on('staff')->onDelete('cascade');
            $table->foreign('evaluator_id')->references('id')->on('staff')->onDelete('cascade');
            $table->foreign('reviewer_id')->references('id')->on('staff')->onDelete('set null');
        });

        Schema::create('professional_development', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('staff_id');
            $table->string('title', 200);
            $table->string('training_type', 50);
            $table->string('provider', 100)->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->integer('duration_hours')->nullable();
            $table->string('location', 200)->nullable();
            $table->text('description')->nullable();
            $table->string('status', 50)->default('planned');
            $table->string('certificate_path', 500)->nullable();
            $table->decimal('cost', 10, 2)->nullable();
            $table->boolean('internal')->default(true);
            $table->datetimes();
            $table->foreign('staff_id')->references('id')->on('staff')->onDelete('cascade');
        });

        Schema::create('budget_allocations', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->string('budget_code', 50)->unique();
            $table->string('name', 200);
            $table->string('category', 100);
            $table->string('department', 100)->nullable();
            $table->string('academic_year', 9);
            $table->decimal('allocated_amount', 15, 2);
            $table->decimal('spent_amount', 15, 2)->default(0);
            $table->decimal('remaining_amount', 15, 2)->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status', 50)->default('active');
            $table->uuid('manager_id')->nullable();
            $table->text('notes')->nullable();
            $table->datetimes();
            $table->foreign('manager_id')->references('id')->on('staff')->onDelete('set null');
        });

        Schema::create('expenses', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('budget_allocation_id');
            $table->string('description', 200);
            $table->decimal('amount', 15, 2);
            $table->date('expense_date');
            $table->string('category', 100);
            $table->string('payment_method', 50)->nullable();
            $table->string('vendor', 100)->nullable();
            $table->uuid('requester_id');
            $table->uuid('approver_id')->nullable();
            $table->string('status', 50)->default('pending');
            $table->string('receipt_path', 500)->nullable();
            $table->text('justification')->nullable();
            $table->datetimes();
            $table->foreign('budget_allocation_id')->references('id')->on('budget_allocations')->onDelete('cascade');
            $table->foreign('requester_id')->references('id')->on('staff')->onDelete('cascade');
            $table->foreign('approver_id')->references('id')->on('staff')->onDelete('set null');
        });

        Schema::create('inventory_items', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->string('name', 200);
            $table->string('code', 50)->unique();
            $table->string('category', 100);
            $table->string('type', 50);
            $table->integer('quantity')->default(0);
            $table->integer('minimum_quantity')->default(1);
            $table->string('unit', 20)->default('pcs');
            $table->decimal('unit_cost', 10, 2)->nullable();
            $table->string('location', 200)->nullable();
            $table->string('condition', 50)->default('good');
            $table->date('purchase_date')->nullable();
            $table->date('last_maintenance')->nullable();
            $table->uuid('responsible_staff_id')->nullable();
            $table->string('status', 50)->default('available');
            $table->text('specifications')->nullable();
            $table->datetimes();
            $table->foreign('responsible_staff_id')->references('id')->on('staff')->onDelete('set null');
        });

        Schema::create('vendor_contracts', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->string('vendor_name', 200);
            $table->string('contact_person', 100)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('phone', 20)->nullable();
            $table->text('address')->nullable();
            $table->string('service_type', 100);
            $table->string('contract_number', 50)->unique();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->decimal('contract_value', 15, 2)->nullable();
            $table->string('status', 50)->default('active');
            $table->uuid('manager_id')->nullable();
            $table->text('terms_and_conditions')->nullable();
            $table->string('document_path', 500)->nullable();
            $table->datetimes();
            $table->foreign('manager_id')->references('id')->on('staff')->onDelete('set null');
        });

        Schema::create('institutional_metrics', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->string('metric_name', 200);
            $table->string('metric_type', 100);
            $table->string('category', 100);
            $table->decimal('value', 15, 2)->nullable();
            $table->string('unit', 50)->nullable();
            $table->date('metric_date');
            $table->string('academic_year', 9);
            $table->string('comparison_period', 50)->nullable();
            $table->decimal('previous_value', 15, 2)->nullable();
            $table->decimal('target_value', 15, 2)->nullable();
            $table->string('trend', 20)->nullable();
            $table->text('notes')->nullable();
            $table->uuid('data_source_staff_id')->nullable();
            $table->datetimes();
            $table->foreign('data_source_staff_id')->references('id')->on('staff')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('institutional_metrics');
        Schema::dropIfExists('vendor_contracts');
        Schema::dropIfExists('inventory_items');
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('budget_allocations');
        Schema::dropIfExists('professional_development');
        Schema::dropIfExists('staff_evaluations');
        Schema::dropIfExists('policies_and_procedures');
        Schema::dropIfExists('accreditation_standards');
        Schema::dropIfExists('compliance_requirements');
    }
};
