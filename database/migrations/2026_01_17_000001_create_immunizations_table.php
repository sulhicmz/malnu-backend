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
        Schema::create('immunizations', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('student_id');
            $table->uuid('health_record_id')->nullable();
            $table->string('vaccine_name', 100);
            $table->string('vaccine_code', 50)->nullable();
            $table->string('manufacturer', 100)->nullable();
            $table->string('batch_number', 100)->nullable();
            $table->date('administration_date');
            $table->date('next_due_date')->nullable();
            $table->date('completed_date')->nullable();
            $table->string('dose', 50)->nullable();
            $table->string('route_of_administration', 50)->nullable();
            $table->string('site_of_administration', 100)->nullable();
            $table->text('notes')->nullable();
            $table->text('adverse_events')->nullable();
            $table->text('contraindications')->nullable();
            $table->boolean('is_series_complete')->default(false);
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
            
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('health_record_id')->references('id')->on('health_records')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('immunizations');
    }
};
