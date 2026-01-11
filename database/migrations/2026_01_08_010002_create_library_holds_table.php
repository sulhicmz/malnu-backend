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
        Schema::create('library_holds', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('book_id');
            $table->uuid('patron_id');
            $table->enum('hold_type', ['hold', 'recall'])->default('hold');
            $table->enum('status', ['pending', 'ready', 'fulfilled', 'cancelled', 'expired'])->default('pending');
            $table->date('request_date');
            $table->date('ready_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->date('fulfilled_date')->nullable();
            $table->integer('priority')->default(1);
            $table->text('notes')->nullable();
            $table->datetimes();
            $table->foreign('book_id')->references('id')->on('books')->onDelete('cascade');
            $table->foreign('patron_id')->references('id')->on('library_patrons')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('library_holds');
    }
};
