<?php

declare(strict_types=1);

use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\DbConnection\Db;
use Hyperf\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('template_id')->nullable();
            $table->string('title', 255);
            $table->text('message');
            $table->string('type', 50)->default('general'); // attendance, grade, event, emergency, etc.
            $table->string('priority', 20)->default('medium'); // low, medium, high, critical
            $table->json('channels')->nullable(); // ['email', 'sms', 'push', 'in_app']
            $table->json('metadata')->nullable(); // additional notification data
            $table->uuid('sent_by')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();

            $table->datetimes();

            $table->foreign('template_id')->references('id')->on('notification_templates')->onDelete('set null');
            $table->foreign('sent_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['type', 'priority']);
            $table->index(['scheduled_at', 'sent_at']);
        });

        Schema::create('notification_templates', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->string('type', 50); // attendance, grade, event, emergency, etc.
            $table->string('subject', 255)->nullable();
            $table->text('body');
            $table->json('variables')->nullable(); // ['student_name', 'date', ...]
            $table->boolean('is_active')->default(true);
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();

            $table->datetimes();

            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['type', 'is_active']);
        });

        Schema::create('notification_user_preferences', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('user_id');
            $table->boolean('email_enabled')->default(true);
            $table->boolean('sms_enabled')->default(true);
            $table->boolean('push_enabled')->default(true);
            $table->boolean('in_app_enabled')->default(true);
            $table->json('type_preferences')->nullable(); // {'attendance': true, 'grade': false, ...}
            $table->json('quiet_hours')->nullable(); // {'start': '22:00', 'end': '08:00'}

            $table->datetimes();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique('user_id');
        });

        Schema::create('notification_recipients', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('notification_id');
            $table->uuid('user_id');
            $table->boolean('read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->json('delivery_channels')->nullable(); // which channels were attempted
            $table->json('delivery_status')->nullable(); // {'email': 'sent', 'sms': 'pending', ...}

            $table->datetimes();

            $table->foreign('notification_id')->references('id')->on('notifications')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['notification_id', 'user_id'], 'unique_notification_user');
            $table->index(['notification_id', 'read']);
            $table->index(['user_id', 'read']);
        });

        Schema::create('notification_delivery_logs', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('notification_id');
            $table->uuid('recipient_id');
            $table->string('channel', 20); // email, sms, push, in_app
            $table->string('status', 20)->default('pending'); // pending, sent, delivered, failed
            $table->text('error_message')->nullable();
            $table->integer('retry_count')->default(0);
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->json('metadata')->nullable(); // additional delivery data

            $table->datetimes();

            $table->foreign('notification_id')->references('id')->on('notifications')->onDelete('cascade');
            $table->foreign('recipient_id')->references('id')->on('notification_recipients')->onDelete('cascade');
            $table->index(['notification_id', 'channel', 'status']);
            $table->index(['recipient_id', 'channel', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_delivery_logs');
        Schema::dropIfExists('notification_recipients');
        Schema::dropIfExists('notification_user_preferences');
        Schema::dropIfExists('notification_templates');
        Schema::dropIfExists('notifications');
    }
};
