<?php

declare(strict_types = 1);

use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Support\Facades\Schema;
use Hyperf\DbConnection\Db;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_types', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->string('name', 100);
            $table->string('code', 50)->unique();
            $table->text('description')->nullable();
            $table->string('category', 50)->default('tuition');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_mandatory')->default(true);
            $table->datetimes();
        });

        Schema::create('fee_structures', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('fee_type_id');
            $table->string('grade_level', 50);
            $table->string('academic_year', 10);
            $table->decimal('amount', 12, 2);
            $table->string('payment_schedule', 20)->default('monthly');
            $table->date('due_date')->nullable();
            $table->decimal('late_fee_percentage', 5, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->datetimes();
            $table->foreign('fee_type_id')->references('id')->on('fee_types')->onDelete('cascade');
            $table->index(['grade_level', 'academic_year', 'is_active']);
        });

        Schema::create('fee_invoices', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('student_id');
            $table->uuid('fee_structure_id');
            $table->string('invoice_number', 50)->unique();
            $table->date('issue_date');
            $table->date('due_date');
            $table->decimal('subtotal', 12, 2);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('late_fee', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('balance_amount', 12, 2);
            $table->string('status', 20)->default('pending');
            $table->text('notes')->nullable();
            $table->datetimes();
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('fee_structure_id')->references('id')->on('fee_structures')->onDelete('cascade');
            $table->index(['student_id', 'status']);
            $table->index(['invoice_number']);
        });

        Schema::create('fee_payments', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('invoice_id');
            $table->uuid('user_id');
            $table->string('payment_method', 50);
            $table->string('transaction_reference', 100)->nullable();
            $table->decimal('amount', 12, 2);
            $table->string('status', 20)->default('pending');
            $table->text('payment_gateway_response')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->datetimes();
            $table->foreign('invoice_id')->references('id')->on('fee_invoices')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['invoice_id', 'status']);
            $table->index(['transaction_reference']);
        });

        Schema::create('fee_waivers', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('invoice_id')->nullable();
            $table->uuid('student_id');
            $table->string('waiver_type', 50);
            $table->string('waiver_code', 50)->unique();
            $table->decimal('discount_percentage', 5, 2);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->text('reason');
            $table->date('valid_from');
            $table->date('valid_until')->nullable();
            $table->string('status', 20)->default('active');
            $table->uuid('approved_by')->nullable();
            $table->datetimes();
            $table->foreign('invoice_id')->references('id')->on('fee_invoices')->onDelete('set null');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['student_id', 'status']);
            $table->index(['waiver_code']);
        });

        Schema::create('payment_methods', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->string('name', 50);
            $table->string('code', 20)->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('requires_online_payment')->default(false);
            $table->text('configuration')->nullable();
            $table->datetimes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
        Schema::dropIfExists('fee_waivers');
        Schema::dropIfExists('fee_payments');
        Schema::dropIfExists('fee_invoices');
        Schema::dropIfExists('fee_structures');
        Schema::dropIfExists('fee_types');
    }
};
