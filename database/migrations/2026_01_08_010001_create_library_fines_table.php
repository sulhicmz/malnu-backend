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
        Schema::create('library_fines', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('patron_id');
            $table->uuid('loan_id')->nullable();
            $table->enum('fine_type', ['overdue', 'lost', 'damaged', 'other']);
            $table->decimal('amount', 10, 2);
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->enum('payment_status', ['pending', 'partial', 'paid', 'waived'])->default('pending');
            $table->date('fine_date');
            $table->date('due_date')->nullable();
            $table->date('payment_date')->nullable();
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->datetimes();
            $table->foreign('patron_id')->references('id')->on('library_patrons')->onDelete('cascade');
            $table->foreign('loan_id')->references('id')->on('book_loans')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('library_fines');
    }
};
