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
        Schema::create('health_incidents', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('health_record_id')->nullable();
            $table->string('incident_type', 50)->nullable();
            $table->date('incident_date');
            $table->date('reported_date');
            $table->text('description')->nullable();
            $table->text('injuries')->nullable();
            $table->text('treatment_provided')->nullable();
            $table->text('follow_up_actions')->nullable();
            $table->text('outcome')->nullable();
            $table->uuid('treated_by')->nullable();
            $table->string('severity', 20)->nullable();
            $table->boolean('requires_medical_attention')->default(false);
            $table->boolean('resolved')->default(false);
            $table->text('resolution_notes')->nullable();
            $table->uuid('reported_by')->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
            
            $table->foreign('health_record_id')->references('id')->on('health_records')->onDelete('cascade');
            $table->foreign('reported_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('health_incidents');
    }
};
