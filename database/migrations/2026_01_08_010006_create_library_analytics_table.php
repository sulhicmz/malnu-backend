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
        Schema::create('library_analytics', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('book_id')->nullable();
            $table->date('analytics_date');
            $table->integer('checkouts')->default(0);
            $table->integer('returns')->default(0);
            $table->integer('renewals')->default(0);
            $table->integer('holds_placed')->default(0);
            $table->integer('page_views')->default(0);
            $table->integer('unique_patrons')->default(0);
            $table->text('notes')->nullable();
            $table->datetimes();
            $table->foreign('book_id')->references('id')->on('books')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('library_analytics');
    }
};
