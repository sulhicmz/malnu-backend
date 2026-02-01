<?php

declare(strict_types=1);

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateFinancialManagementTables extends Migration
{
    public function up(): void
    {
        Schema::create('fee_types', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->string('name')->unique();
            $table->string('code')->unique()->comment('Fee type code for reference');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('created_by', 36)->nullable();
            $table->string('updated_by', 36)->nullable();
            $table->timestamps();
        });

        Schema::create('fee_structures', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->string('fee_type_id', 36);
            $table->string('name');
            $table->decimal('amount', 10, 2);
            $table->string('frequency')->comment('monthly, quarterly, annually, one_time');
            $table->string('student_type')->nullable()->comment('day_student, boarder, all');
            $table->string('student_class_id', 36)->nullable();
            $table->integer('academic_year')->nullable();
            $table->date('due_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('created_by', 36)->nullable();
            $table->string('updated_by', 36)->nullable();
            $table->timestamps();

            $table->foreign('fee_type_id')->references('id')->on('fee_types')->onDelete('cascade');
            $table->index(['is_active', 'academic_year']);
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->string('student_id', 36);
            $table->string('fee_structure_id', 36);
            $table->string('invoice_number')->unique();
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->enum('status', ['unpaid', 'partial', 'paid', 'overdue'])->default('unpaid');
            $table->date('issue_date');
            $table->date('due_date');
            $table->date('paid_date')->nullable();
            $table->text('notes')->nullable();
            $table->string('created_by', 36)->nullable();
            $table->string('updated_by', 36)->nullable();
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('fee_structure_id')->references('id')->on('fee_structures')->onDelete('cascade');
            $table->index(['student_id', 'status']);
            $table->index(['status', 'due_date']);
        });

        Schema::create('invoice_items', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->string('invoice_id', 36);
            $table->string('fee_type_id', 36);
            $table->string('description');
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('amount', 10, 2);
            $table->timestamps();

            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');
            $table->foreign('fee_type_id')->references('id')->on('fee_types')->onDelete('restrict');
            $table->index('invoice_id');
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->string('invoice_id', 36);
            $table->string('student_id', 36);
            $table->decimal('amount', 10, 2);
            $table->enum('payment_method', ['cash', 'bank_transfer', 'card', 'e_wallet', 'check'])->default('cash');
            $table->string('reference_number')->nullable()->comment('Transaction reference or receipt number');
            $table->date('payment_date');
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'completed', 'failed', 'refunded'])->default('completed');
            $table->string('created_by', 36)->nullable();
            $table->string('updated_by', 36)->nullable();
            $table->timestamps();

            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['invoice_id', 'status']);
            $table->index(['student_id', 'payment_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('fee_structures');
        Schema::dropIfExists('fee_types');
    }
}
