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
        Schema::create('meal_plans', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status', 20)->default('active');
            $table->datetimes();
        });

        Schema::create('student_meal_preferences', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('student_id');
            $table->uuid('meal_plan_id')->nullable();
            $table->boolean('requires_special_diet')->default(false);
            $table->text('dietary_restrictions')->nullable();
            $table->text('allergies')->nullable();
            $table->boolean('subsidy_eligible')->default(false);
            $table->decimal('subsidy_amount', 10, 2)->default(0.00);
            $table->datetimes();
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('meal_plan_id')->references('id')->on('meal_plans')->onDelete('set null');
            $table->unique(['student_id', 'meal_plan_id']);
        });

        Schema::create('cafeteria_inventories', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->string('item_name', 100);
            $table->string('category', 50);
            $table->integer('quantity')->default(0);
            $table->string('unit', 20);
            $table->decimal('unit_cost', 10, 2);
            $table->uuid('vendor_id')->nullable();
            $table->date('expiry_date')->nullable();
            $table->integer('minimum_stock_level')->default(0);
            $table->text('notes')->nullable();
            $table->datetimes();
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('set null');
        });

        Schema::create('meal_payments', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('student_id');
            $table->decimal('amount', 10, 2);
            $table->decimal('subsidy_amount', 10, 2)->default(0.00);
            $table->decimal('amount_paid', 10, 2);
            $table->string('payment_method', 50);
            $table->string('transaction_reference', 100)->nullable();
            $table->date('payment_date');
            $table->string('status', 20)->default('pending');
            $table->text('notes')->nullable();
            $table->datetimes();
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
        });

        Schema::create('vendors', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->string('name', 100);
            $table->string('contact_person', 100);
            $table->string('phone', 20);
            $table->string('email', 100);
            $table->text('address');
            $table->string('status', 20)->default('active');
            $table->datetimes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendors');
        Schema::dropIfExists('meal_payments');
        Schema::dropIfExists('cafeteria_inventories');
        Schema::dropIfExists('student_meal_preferences');
        Schema::dropIfExists('meal_plans');
    }
};
