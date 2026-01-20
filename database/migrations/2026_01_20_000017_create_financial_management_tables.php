<?php

declare(strict_types=1);

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
        // Fee Types
        Schema::create('fee_types', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->string('name', 100);
            $table->string('code', 20)->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->uuid('created_by')->nullable();
            $table->datetimes();
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });

        // Fee Structures
        Schema::create('fee_structures', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('fee_type_id');
            $table->string('name', 200);
            $table->decimal('amount', 10, 2);
            $table->string('academic_year', 10);
            $table->string('student_class', 50)->nullable();
            $table->string('student_type', 50)->nullable();
            $table->date('due_date')->nullable();
            $table->boolean('is_recurring')->default(false);
            $table->string('recurrence_frequency', 20)->nullable();
            $table->boolean('is_active')->default(true);
            $table->uuid('created_by')->nullable();
            $table->datetimes();
            $table->foreign('fee_type_id')->references('id')->on('fee_types')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });

        // Invoices
        Schema::create('invoices', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('student_id');
            $table->uuid('fee_structure_id');
            $table->string('invoice_number', 50)->unique();
            $table->date('issue_date')->nullable();
            $table->date('due_date')->nullable();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2);
            $table->string('status', 20)->default('pending');
            $table->string('payment_status', 20)->default('unpaid');
            $table->text('notes')->nullable();
            $table->uuid('created_by')->nullable();
            $table->datetimes();
            $table->foreign('student_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('fee_structure_id')->references('id')->on('fee_structures')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });

        // Invoice Items
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('invoice_id');
            $table->uuid('fee_type_id')->nullable();
            $table->string('description', 200)->nullable();
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('amount', 10, 2);
            $table->datetimes();
            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');
            $table->foreign('fee_type_id')->references('id')->on('fee_types')->onDelete('set null');
        });

        // Payments
        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('invoice_id');
            $table->uuid('student_id');
            $table->decimal('amount', 10, 2);
            $table->string('payment_method', 50);
            $table->string('payment_reference', 100)->nullable();
            $table->date('payment_date')->nullable();
            $table->string('status', 20)->default('pending');
            $table->string('transaction_id', 100)->nullable();
            $table->text('notes')->nullable();
            $table->uuid('created_by')->nullable();
            $table->datetimes();
            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });

        // Expenses
        Schema::create('expenses', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->string('category', 50);
            $table->decimal('amount', 10, 2);
            $table->date('expense_date')->nullable();
            $table->text('description')->nullable();
            $table->string('vendor', 200)->nullable();
            $table->string('payment_method', 50)->nullable();
            $table->string('reference_number', 100)->nullable();
            $table->string('approval_status', 20)->default('pending');
            $table->uuid('approved_by')->nullable();
            $table->uuid('created_by')->nullable();
            $table->datetimes();
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('fee_structures');
        Schema::dropIfExists('fee_types');
    }
};
