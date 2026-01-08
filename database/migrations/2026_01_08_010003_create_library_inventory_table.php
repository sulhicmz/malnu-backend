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
        Schema::create('library_inventory', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('book_id');
            $table->enum('action_type', ['stock_take', 'weeding', 'addition', 'correction']);
            $table->integer('expected_quantity');
            $table->integer('actual_quantity');
            $table->integer('difference')->default(0);
            $table->text('notes')->nullable();
            $table->string('performed_by', 100);
            $table->date('inventory_date');
            $table->datetimes();
            $table->foreign('book_id')->references('id')->on('books')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('library_inventory');
    }
};
