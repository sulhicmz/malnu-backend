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
        Schema::create('library_reading_program_participants', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('program_id');
            $table->uuid('patron_id');
            $table->date('enrollment_date');
            $table->date('completion_date')->nullable();
            $table->integer('books_read')->default(0);
            $table->enum('status', ['active', 'completed', 'withdrawn'])->default('active');
            $table->text('notes')->nullable();
            $table->datetimes();
            $table->foreign('program_id')->references('id')->on('library_reading_programs')->onDelete('cascade');
            $table->foreign('patron_id')->references('id')->on('library_patrons')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('library_reading_program_participants');
    }
};
