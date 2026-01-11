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
        Schema::create('marc_records', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('book_id');
            $table->string('leader', 24)->nullable();
            $table->string('control_number', 20)->nullable();
            $table->text('fields')->nullable();
            $table->enum('record_type', ['language_material', 'manuscript', 'cartographic', 'projected', 'sound_recording', 'music', 'visual']);
            $table->string('bibliographic_level', 50)->nullable();
            $table->text('cataloging_notes')->nullable();
            $table->datetimes();
            $table->foreign('book_id')->references('id')->on('books')->onDelete('cascade');
        });

        Schema::create('marc_fields', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('marc_record_id');
            $table->string('tag', 3);
            $table->string('indicator1', 1)->nullable();
            $table->string('indicator2', 1)->nullable();
            $table->text('data')->nullable();
            $table->datetimes();
            $table->foreign('marc_record_id')->references('id')->on('marc_records')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marc_fields');
        Schema::dropIfExists('marc_records');
    }
};
