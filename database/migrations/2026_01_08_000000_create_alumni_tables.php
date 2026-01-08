<?php

declare(strict_types=1);

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateAlumniTables extends Migration
{
    public function up(): void
    {
        Schema::create('alumni', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('student_id');
            $table->string('user_id');
            $table->string('graduation_year');
            $table->string('graduation_class')->nullable();
            $table->string('degree')->nullable();
            $table->string('field_of_study')->nullable();
            $table->text('current_company')->nullable();
            $table->string('current_position')->nullable();
            $table->string('industry')->nullable();
            $table->text('linkedin_url')->nullable();
            $table->text('bio')->nullable();
            $table->text('achievements')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_public')->default(true);
            $table->boolean('allow_contact')->default(true);
            $table->boolean('newsletter_subscription')->default(true);
            $table->boolean('mentor_availability')->default(false);
            $table->text('privacy_settings')->nullable();
            $table->text('consent_data')->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->datetimes();
            $table->softDeletes();

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index('graduation_year');
            $table->index('industry');
        });

        Schema::create('alumni_careers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('alumni_id');
            $table->string('company_name');
            $table->string('position');
            $table->string('industry')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->boolean('is_current')->default(false);
            $table->text('description')->nullable();
            $table->text('achievements')->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->datetimes();
            $table->softDeletes();

            $table->foreign('alumni_id')->references('id')->on('alumni')->onDelete('cascade');
            $table->index('alumni_id');
            $table->index('industry');
        });

        Schema::create('alumni_donations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('alumni_id')->nullable();
            $table->string('donor_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('currency')->default('USD');
            $table->string('donation_type');
            $table->string('campaign')->nullable();
            $table->boolean('is_recurring')->default(false);
            $table->string('recurring_frequency')->nullable();
            $table->date('donation_date');
            $table->text('payment_method')->nullable();
            $table->string('transaction_id')->nullable();
            $table->boolean('is_anonymous')->default(false);
            $table->text('message')->nullable();
            $table->string('status')->default('completed');
            $table->text('receipt_details')->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->datetimes();
            $table->softDeletes();

            $table->foreign('alumni_id')->references('id')->on('alumni')->onDelete('set null');
            $table->index('alumni_id');
            $table->index('donation_date');
            $table->index('status');
        });

        Schema::create('alumni_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('event_type');
            $table->datetime('event_date');
            $table->datetime('end_date')->nullable();
            $table->string('location')->nullable();
            $table->string('virtual_link')->nullable();
            $table->boolean('is_virtual')->default(false);
            $table->integer('max_capacity')->nullable();
            $table->integer('current_attendees')->default(0);
            $table->string('status')->default('upcoming');
            $table->text('image_url')->nullable();
            $table->text('organizer_name')->nullable();
            $table->text('contact_email')->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->datetimes();
            $table->softDeletes();

            $table->index('event_date');
            $table->index('status');
            $table->index('event_type');
        });

        Schema::create('alumni_event_registrations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('event_id');
            $table->string('alumni_id')->nullable();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->integer('guests')->default(0);
            $table->boolean('is_attending')->default(true);
            $table->text('dietary_requirements')->nullable();
            $table->text('special_requests')->nullable();
            $table->datetime('registration_date');
            $table->boolean('check_in_status')->default(false);
            $table->datetime('check_in_time')->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->datetimes();
            $table->softDeletes();

            $table->foreign('event_id')->references('id')->on('alumni_events')->onDelete('cascade');
            $table->foreign('alumni_id')->references('id')->on('alumni')->onDelete('set null');
            $table->index('event_id');
            $table->index('alumni_id');
            $table->index('registration_date');
        });

        Schema::create('alumni_engagements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('alumni_id');
            $table->string('engagement_type');
            $table->text('description')->nullable();
            $table->datetime('engagement_date');
            $table->string('category')->nullable();
            $table->text('details')->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->datetimes();
            $table->softDeletes();

            $table->foreign('alumni_id')->references('id')->on('alumni')->onDelete('cascade');
            $table->index('alumni_id');
            $table->index('engagement_type');
            $table->index('engagement_date');
        });

        Schema::create('alumni_mentorships', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('mentor_id');
            $table->string('student_id')->nullable();
            $table->string('mentee_name')->nullable();
            $table->string('mentee_email')->nullable();
            $table->string('status')->default('pending');
            $table->string('focus_area')->nullable();
            $table->text('goals')->nullable();
            $table->datetime('start_date')->nullable();
            $table->datetime('end_date')->nullable();
            $table->integer('sessions_count')->default(0);
            $table->text('notes')->nullable();
            $table->text('feedback')->nullable();
            $table->text('match_criteria')->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->datetimes();
            $table->softDeletes();

            $table->foreign('mentor_id')->references('id')->on('alumni')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('set null');
            $table->index('mentor_id');
            $table->index('student_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alumni_mentorships');
        Schema::dropIfExists('alumni_engagements');
        Schema::dropIfExists('alumni_event_registrations');
        Schema::dropIfExists('alumni_events');
        Schema::dropIfExists('alumni_donations');
        Schema::dropIfExists('alumni_careers');
        Schema::dropIfExists('alumni');
    }
}
