<?php

declare(strict_types=1);

use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Support\Facades\Schema;
use Hyperf\DbConnection\Db;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('health_alerts', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('student_id');
            $table->uuid('health_record_id')->nullable();
            $table->string('alert_type'); // medication_due, immunization_overdue, allergy_alert, health_concern
            $table->string('priority')->default('medium'); // low, medium, high, critical
            $table->text('message');
            $table->timestamp('due_date')->nullable();
            $table->string('status')->default('pending'); // pending, sent, acknowledged, resolved
            $table->timestamp('sent_date')->nullable();
            $table->timestamp('acknowledged_date')->nullable();
            $table->timestamp('resolved_date')->nullable();
            $table->text('notes')->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
            
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('health_record_id')->references('id')->on('health_records')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            
            $table->index('student_id');
            $table->index('alert_type');
            $table->index('status');
            $table->index('due_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('health_alerts');
    }
};
