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
        Schema::create('library_patrons', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('user_id');
            $table->string('library_card_number', 20)->unique();
            $table->enum('status', ['active', 'suspended', 'expired'])->default('active');
            $table->date('membership_start_date');
            $table->date('membership_expiry_date')->nullable();
            $table->integer('max_loan_limit')->default(5);
            $table->integer('current_loans')->default(0);
            $table->decimal('total_fines', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->datetimes();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('library_patrons');
    }
};
