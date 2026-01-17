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
        Schema::create('behavior_incidents', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('student_id')->comment('Reference to student');
            $table->uuid('reported_by')->comment('User who reported the incident');
            $table->uuid('behavior_category_id')->comment('Reference to behavior category');
            $table->date('incident_date')->comment('When the behavior occurred');
            $table->time('incident_time')->nullable()->comment('Time of the incident');
            $table->string('location', 255)->nullable()->comment('Location where incident occurred');
            $table->enum('severity', ['minor', 'moderate', 'severe', 'critical'])->default('moderate');
            $table->text('description')->nullable()->comment('Detailed description of the incident');
            $table->text('witnesses')->nullable()->comment('Witnesses to the incident');
            $table->text('action_taken')->nullable()->comment('Immediate action taken');
            $table->boolean('is_resolved')->default(false)->comment('Whether the incident has been resolved');
            $table->uuid('resolved_by')->nullable()->comment('User who resolved the incident');
            $table->timestamp('resolved_at')->nullable()->comment('When the incident was resolved');
            $table->text('resolution_notes')->nullable()->comment('Notes about the resolution');
            $table->boolean('parent_notified')->default(false)->comment('Whether parents were notified');
            $table->timestamp('parent_notified_at')->nullable()->comment('When parents were notified');
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('reported_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('behavior_category_id')->references('id')->on('behavior_categories')->onDelete('restrict');
            $table->foreign('resolved_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            $table->index('student_id');
            $table->index('incident_date');
            $table->index('is_resolved');
            $table->index('severity');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('behavior_incidents');
    }
};
