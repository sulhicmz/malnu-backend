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
        Schema::create('meal_plans', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('hostel_id');
            $table->uuid('student_id');
            $table->string('plan_type', 50);
            $table->string('dietary_requirements', 255)->nullable();
            $table->text('allergies')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->datetimes();
            $table->foreign('hostel_id')->references('id')->on('hostels')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['student_id', 'is_active']);
            $table->index(['hostel_id', 'plan_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meal_plans');
    }
};
