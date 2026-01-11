<?php

declare(strict_types=1);

use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Support\Facades\Schema;
use Hyperf\DbConnection\Db;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('hostel_id');
            $table->string('room_number', 20);
            $table->string('floor', 10)->nullable();
            $table->string('room_type', 50)->default('shared');
            $table->integer('capacity')->default(4);
            $table->integer('current_occupancy')->default(0);
            $table->boolean('is_available')->default(true);
            $table->text('amenities')->nullable();
            $table->text('description')->nullable();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->datetimes();
            $table->foreign('hostel_id')->references('id')->on('hostels')->onDelete('cascade');
            $table->unique(['hostel_id', 'room_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
