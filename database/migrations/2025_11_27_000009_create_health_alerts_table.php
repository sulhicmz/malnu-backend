<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateHealthAlertsTable extends Migration
{
    public function up(): void
    {
        Schema::create('health_alerts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('student_id');
            $table->string('alert_type');
            $table->string('title');
            $table->text('message');
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->dateTime('alert_date');
            $table->dateTime('due_date')->nullable();
            $table->enum('status', ['pending', 'sent', 'acknowledged', 'resolved'])->default('pending');
            $table->dateTime('sent_date')->nullable();
            $table->text('recipients')->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');

            $table->index('student_id');
            $table->index('alert_type');
            $table->index('priority');
            $table->index('status');
            $table->index('alert_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('health_alerts');
    }
}
