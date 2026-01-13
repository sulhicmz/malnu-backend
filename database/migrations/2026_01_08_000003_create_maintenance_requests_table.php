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
        Schema::create('maintenance_requests', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('hostel_id');
            $table->uuid('room_id')->nullable();
            $table->uuid('reported_by');
            $table->string('type', 50);
            $table->string('priority', 20)->default('medium');
            $table->text('description');
            $table->string('status', 20)->default('pending');
            $table->text('resolution_notes')->nullable();
            $table->date('resolved_at')->nullable();
            $table->uuid('resolved_by')->nullable();
            $table->datetimes();
            $table->foreign('hostel_id')->references('id')->on('hostels')->onDelete('cascade');
            $table->foreign('room_id')->references('id')->on('rooms')->onDelete('set null');
            $table->foreign('reported_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('resolved_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['hostel_id', 'status']);
            $table->index(['reported_by', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_requests');
    }
};
