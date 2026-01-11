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
        Schema::create('library_spaces', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->string('space_name', 100);
            $table->enum('space_type', ['study_room', 'computer_lab', 'meeting_room', 'collaborative_area']);
            $table->integer('capacity')->default(1);
            $table->enum('availability', ['available', 'booked', 'maintenance'])->default('available');
            $table->text('equipment')->nullable();
            $table->text('amenities')->nullable();
            $table->text('rules')->nullable();
            $table->datetimes();
        });

        Schema::create('library_space_bookings', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('space_id');
            $table->uuid('user_id');
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->enum('status', ['confirmed', 'cancelled', 'completed'])->default('confirmed');
            $table->integer('attendees')->default(1);
            $table->text('purpose')->nullable();
            $table->text('notes')->nullable();
            $table->datetimes();
            $table->foreign('space_id')->references('id')->on('library_spaces')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('library_space_bookings');
        Schema::dropIfExists('library_spaces');
    }
};
