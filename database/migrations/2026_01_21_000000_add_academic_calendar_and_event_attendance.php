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
        Schema::create('academic_terms', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->string('name', 255);
            $table->string('academic_year', 20);
            $table->integer('term_number')->default(1);
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_current')->default(false);
            $table->boolean('is_enrollment_open')->default(true);
            $table->text('notes')->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            
            $table->datetimes();
            
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            $table->unique(['academic_year', 'term_number']);
            $table->index(['start_date', 'end_date']);
            $table->index('is_current');
        });

        Schema::create('holidays', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('academic_term_id')->nullable();
            $table->string('name', 255);
            $table->date('start_date');
            $table->date('end_date');
            $table->string('type', 50)->default('public');
            $table->boolean('is_school_wide')->default(true);
            $table->text('description')->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            
            $table->datetimes();
            
            $table->foreign('academic_term_id')->references('id')->on('academic_terms')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['start_date', 'end_date']);
            $table->index('type');
            $table->index('is_school_wide');
        });

        Schema::create('event_attendance', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('event_id');
            $table->uuid('user_id');
            $table->timestamp('check_in_time')->nullable();
            $table->timestamp('check_out_time')->nullable();
            $table->string('status', 20)->default('not_attended');
            $table->text('notes')->nullable();
            $table->json('additional_data')->nullable();
            
            $table->datetimes();
            
            $table->foreign('event_id')->references('id')->on('calendar_events')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['event_id', 'user_id'], 'unique_event_user_attendance');
            $table->index(['event_id', 'status']);
            $table->index(['user_id', 'check_in_time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_attendance');
        Schema::dropIfExists('holidays');
        Schema::dropIfExists('academic_terms');
    }
};
