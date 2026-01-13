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
        Schema::create('health_records', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('student_id');
            $table->uuid('hostel_id');
            $table->string('record_type', 50);
            $table->text('description');
            $table->date('checkup_date');
            $table->string('severity', 20)->default('low');
            $table->text('medication')->nullable();
            $table->text('notes')->nullable();
            $table->uuid('recorded_by');
            $table->datetimes();
            $table->foreign('student_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('hostel_id')->references('id')->on('hostels')->onDelete('cascade');
            $table->foreign('recorded_by')->references('id')->on('users')->onDelete('cascade');
            $table->index(['student_id', 'checkup_date']);
            $table->index(['hostel_id', 'record_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('health_records');
    }
};
