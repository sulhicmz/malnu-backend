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
        Schema::create('health_screenings', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('student_id');
            $table->uuid('health_record_id')->nullable();
            $table->date('screening_date');
            $table->string('screening_type', 50)->nullable();
            $table->string('height')->nullable();
            $table->string('weight')->nullable();
            $table->string('vision_left')->nullable();
            $table->string('vision_right')->nullable();
            $table->string('hearing_left')->nullable();
            $table->string('hearing_right')->nullable();
            $table->string('blood_pressure')->nullable();
            $table->string('heart_rate')->nullable();
            $table->text('notes')->nullable();
            $table->text('recommendations')->nullable();
            $table->boolean('requires_follow_up')->default(false);
            $table->uuid('screened_by')->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
            
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('health_record_id')->references('id')->on('health_records')->onDelete('set null');
            $table->foreign('screened_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('health_screenings');
    }
};
