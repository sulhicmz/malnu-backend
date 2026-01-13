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
        Schema::create('incidents', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('hostel_id');
            $table->uuid('student_id')->nullable();
            $table->uuid('room_id')->nullable();
            $table->string('incident_type', 50);
            $table->string('severity', 20)->default('medium');
            $table->text('description');
            $table->dateTime('incident_date');
            $table->string('status', 20)->default('open');
            $table->text('action_taken')->nullable();
            $table->text('disciplinary_action')->nullable();
            $table->uuid('reported_by');
            $table->uuid('resolved_by')->nullable();
            $table->date('resolved_at')->nullable();
            $table->datetimes();
            $table->foreign('hostel_id')->references('id')->on('hostels')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('room_id')->references('id')->on('rooms')->onDelete('set null');
            $table->foreign('reported_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('resolved_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['hostel_id', 'status']);
            $table->index(['student_id', 'incident_date']);
            $table->index(['incident_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incidents');
    }
};
