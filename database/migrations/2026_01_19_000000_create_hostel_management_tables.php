<?php

declare(strict_types=1);

use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Support\Facades\Schema;
use Hyperf\DbConnection\Db;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('hostels', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('location', 255)->nullable();
            $table->integer('total_capacity')->default(0);
            $table->string('warden_name', 100)->nullable();
            $table->string('warden_contact', 20)->nullable();
            $table->string('status', 50)->default('active');
            $table->timestamps();
        });

        Schema::create('hostel_rooms', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('hostel_id');
            $table->string('room_number', 20);
            $table->integer('capacity')->default(1);
            $table->integer('current_occupancy')->default(0);
            $table->string('room_type', 50)->default('standard');
            $table->text('amenities')->nullable();
            $table->string('floor', 20)->nullable();
            $table->string('status', 50)->default('available');
            $table->timestamps();

            $table->foreign('hostel_id')->references('id')->on('hostels')->onDelete('cascade');
            $table->index('hostel_id');
            $table->index('status');
            $table->index(['hostel_id', 'room_number']);
        });

        Schema::create('hostel_allocations', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('student_id');
            $table->uuid('room_id');
            $table->date('check_in_date');
            $table->date('check_out_date')->nullable();
            $table->string('status', 50)->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('room_id')->references('id')->on('hostel_rooms')->onDelete('cascade');
            $table->index('student_id');
            $table->index('room_id');
            $table->index('status');
            $table->index(['student_id', 'room_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hostel_allocations');
        Schema::dropIfExists('hostel_rooms');
        Schema::dropIfExists('hostels');
    }
};
