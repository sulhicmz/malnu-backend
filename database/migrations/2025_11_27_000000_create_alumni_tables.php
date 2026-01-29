<?php

declare(strict_types=1);

use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Support\Facades\Schema;
use Hyperf\DbConnection\Db;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Alumni profiles table - Linked to graduated students
        Schema::create('alumni_profiles', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('student_id')->nullable();
            $table->uuid('user_id')->nullable();
            $table->string('graduation_year', 4);
            $table->string('degree', 100)->nullable();
            $table->string('field_of_study', 100)->nullable();
            $table->text('bio')->nullable();
            $table->boolean('public_profile')->default(false);
            $table->boolean('allow_contact')->default(false);
            $table->boolean('privacy_consent')->default(false);
            $table->datetimes();
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->nullable();
        });

        // Alumni career tracking table
        Schema::create('alumni_careers', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('alumni_id');
            $table->string('company', 255)->nullable();
            $table->string('position', 255)->nullable();
            $table->string('industry', 100)->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('current_job')->default(false);
            $table->string('location', 255)->nullable();
            $table->text('description')->nullable();
            $table->datetimes();
            $table->foreign('alumni_id')->references('id')->on('alumni_profiles')->onDelete('cascade');
        });

        // Alumni achievements table
        Schema::create('alumni_achievements', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('alumni_id');
            $table->string('achievement_type', 50);
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->date('achievement_date')->nullable();
            $table->string('link', 500)->nullable();
            $table->datetimes();
            $table->foreign('alumni_id')->references('id')->on('alumni_profiles')->onDelete('cascade');
        });

        // Alumni mentorship program table
        Schema::create('alumni_mentorships', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('alumni_id');
            $table->uuid('student_id');
            $table->string('status', 20)->default('pending');
            $table->text('focus_area')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->text('notes')->nullable();
            $table->datetimes();
            $table->foreign('alumni_id')->references('id')->on('alumni_profiles')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
        });

        // Alumni donation tracking table
        Schema::create('alumni_donations', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('alumni_id');
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->string('donation_type', 50);
            $table->string('campaign', 255)->nullable();
            $table->boolean('anonymous')->default(false);
            $table->boolean('acknowledged')->default(false);
            $table->text('message')->nullable();
            $table->datetimes();
            $table->foreign('alumni_id')->references('id')->on('alumni_profiles')->onDelete('cascade');
        });

        // Alumni events table
        Schema::create('alumni_events', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('created_by')->nullable();
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->string('event_type', 50);
            $table->string('location', 500)->nullable();
            $table->datetime('event_date');
            $table->integer('max_attendees')->nullable();
            $table->integer('current_attendees')->default(0);
            $table->string('status', 20)->default('upcoming');
            $table->datetimes();
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade')->nullable();
        });

        // Alumni event registrations table
        Schema::create('alumni_event_registrations', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('event_id');
            $table->uuid('alumni_id');
            $table->enum('attendance_status', ['registered', 'attended', 'cancelled', 'no_show'])->default('registered');
            $table->timestamp('registration_time')->nullable();
            $table->text('notes')->nullable();
            $table->datetimes();
            $table->foreign('event_id')->references('id')->on('alumni_events')->onDelete('cascade');
            $table->foreign('alumni_id')->references('id')->on('alumni_profiles')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alumni_event_registrations');
        Schema::dropIfExists('alumni_events');
        Schema::dropIfExists('alumni_donations');
        Schema::dropIfExists('alumni_mentorships');
        Schema::dropIfExists('alumni_achievements');
        Schema::dropIfExists('alumni_careers');
        Schema::dropIfExists('alumni_profiles');
    }
};
