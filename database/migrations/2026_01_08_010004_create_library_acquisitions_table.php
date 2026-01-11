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
        Schema::create('library_acquisitions', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->string('acquisition_number', 50)->unique();
            $table->string('title', 200);
            $table->string('author', 100)->nullable();
            $table->string('isbn', 20)->nullable();
            $table->string('publisher', 100)->nullable();
            $table->integer('quantity')->default(1);
            $table->decimal('unit_cost', 10, 2);
            $table->decimal('total_cost', 10, 2);
            $table->string('vendor', 100)->nullable();
            $table->date('order_date');
            $table->date('received_date')->nullable();
            $table->enum('status', ['ordered', 'received', 'cancelled'])->default('ordered');
            $table->text('notes')->nullable();
            $table->datetimes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('library_acquisitions');
    }
};
