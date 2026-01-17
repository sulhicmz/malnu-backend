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
        Schema::create('health_alerts', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('student_id');
            $table->uuid('health_record_id')->nullable();
            $table->string('alert_type', 50)->nullable();
            $table->string('alert_title', 100)->nullable();
            $table->text('alert_message')->nullable();
            $table->timestamp('alert_date')->nullable();
            $table->boolean('is_read')->default(false);
            $table->boolean('requires_action')->default(false);
            $table->string('action_required')->nullable();
            $table->text('action_notes')->nullable();
            $table->boolean('is_critical')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
            
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('health_record_id')->references('id')->on('health_records')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('health_alerts');
    }
};
