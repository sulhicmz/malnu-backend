<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parent_student_relationships', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('parent_id');
            $table->string('student_id');
            $table->enum('relationship_type', ['father', 'mother', 'guardian', 'other']);
            $table->boolean('is_primary_contact')->default(false);
            $table->boolean('has_custody')->default(true);
            $table->json('contact_preferences')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('parent_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->unique(['parent_id', 'student_id']);
        });

        Schema::create('parent_messages', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('sender_id');
            $table->string('recipient_id');
            $table->string('subject');
            $table->text('message');
            $table->enum('type', ['individual', 'group', 'announcement']);
            $table->string('thread_id')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->string('attachment_url')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('recipient_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['recipient_id', 'is_read']);
            $table->index('thread_id');
        });

        Schema::create('parent_conferences', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('parent_id');
            $table->string('teacher_id');
            $table->string('student_id');
            $table->dateTime('scheduled_date');
            $table->integer('duration_minutes')->default(30);
            $table->enum('status', ['scheduled', 'confirmed', 'completed', 'cancelled'])->default('scheduled');
            $table->text('notes')->nullable();
            $table->text('teacher_notes')->nullable();
            $table->text('parent_notes')->nullable();
            $table->json('reminders_sent')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('parent_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('teacher_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->index(['scheduled_date', 'status']);
        });

        Schema::create('parent_notification_preferences', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('parent_id');
            $table->enum('notification_type', ['grade', 'attendance', 'assignment', 'event', 'message', 'emergency', 'announcement']);
            $table->boolean('email_enabled')->default(true);
            $table->boolean('sms_enabled')->default(false);
            $table->boolean('push_enabled')->default(true);
            $table->boolean('in_app_enabled')->default(true);
            $table->boolean('digest_mode')->default(false);
            $table->string('digest_frequency')->default('daily');
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['parent_id', 'notification_type']);
        });

        Schema::create('parent_engagement_logs', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('parent_id');
            $table->string('action_type');
            $table->text('action_details')->nullable();
            $table->json('metadata')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['parent_id', 'created_at']);
        });

        Schema::create('parent_event_registrations', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('parent_id');
            $table->string('student_id')->nullable();
            $table->string('event_id');
            $table->enum('status', ['registered', 'confirmed', 'attended', 'cancelled'])->default('registered');
            $table->integer('number_of_attendees')->default(1);
            $table->json('additional_info')->nullable();
            $table->timestamp('registered_at');
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('event_id')->references('id')->on('calendar_events')->onDelete('cascade');
            $table->unique(['parent_id', 'event_id']);
        });

        Schema::create('parent_volunteer_opportunities', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('title');
            $table->text('description');
            $table->date('event_date')->nullable();
            $table->string('location')->nullable();
            $table->integer('slots_available')->nullable();
            $table->integer('slots_filled')->default(0);
            $table->enum('status', ['open', 'closed', 'completed'])->default('open');
            $table->json('requirements')->nullable();
            $table->timestamps();
        });

        Schema::create('parent_volunteer_signups', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('parent_id');
            $table->string('opportunity_id');
            $table->enum('status', ['signed_up', 'confirmed', 'completed', 'cancelled'])->default('signed_up');
            $table->text('notes')->nullable();
            $table->timestamp('signed_up_at');
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('opportunity_id')->references('id')->on('parent_volunteer_opportunities')->onDelete('cascade');
            $table->unique(['parent_id', 'opportunity_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parent_volunteer_signups');
        Schema::dropIfExists('parent_volunteer_opportunities');
        Schema::dropIfExists('parent_event_registrations');
        Schema::dropIfExists('parent_engagement_logs');
        Schema::dropIfExists('parent_notification_preferences');
        Schema::dropIfExists('parent_conferences');
        Schema::dropIfExists('parent_messages');
        Schema::dropIfExists('parent_student_relationships');
    }
};
