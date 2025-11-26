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
        // Notification templates table
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->string('name', 255);
            $table->string('slug', 255)->unique();
            $table->text('subject')->nullable();
            $table->text('body');
            $table->json('placeholders')->nullable(); // JSON array of placeholder variables
            $table->string('type', 50)->default('general'); // email, sms, in_app, push
            $table->boolean('is_active')->default(true);
            $table->json('channels')->default(['email']); // JSON array of delivery channels
            $table->timestamps();
        });

        // User notification preferences table
        Schema::create('user_notification_preferences', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('user_id');
            $table->json('preferences')->default('{}'); // JSON object with notification preferences
            $table->boolean('email_enabled')->default(true);
            $table->boolean('sms_enabled')->default(true);
            $table->boolean('push_enabled')->default(true);
            $table->boolean('in_app_enabled')->default(true);
            $table->string('timezone', 50)->default('UTC');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // Notifications table
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('template_id')->nullable();
            $table->uuid('sender_id')->nullable(); // User ID of sender (nullable for system notifications)
            $table->string('title', 255);
            $table->text('content');
            $table->string('type', 100)->default('general'); // emergency, alert, reminder, info
            $table->string('priority', 20)->default('medium'); // low, medium, high, critical
            $table->json('data')->nullable(); // Additional data for the notification
            $table->json('channels')->default(['email']); // JSON array of delivery channels
            $table->timestamp('scheduled_at')->nullable(); // For scheduled notifications
            $table->timestamp('sent_at')->nullable(); // When notification was sent
            $table->timestamp('expires_at')->nullable(); // When notification expires
            $table->boolean('is_broadcast')->default(false); // For system-wide notifications
            $table->boolean('is_read')->default(false); // For in-app notifications
            $table->timestamps();

            $table->foreign('template_id')->references('id')->on('notification_templates')->onDelete('set null');
            $table->foreign('sender_id')->references('id')->on('users')->onDelete('set null');
        });

        // Notification recipients table
        Schema::create('notification_recipients', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('notification_id');
            $table->uuid('user_id');
            $table->json('delivery_status')->default(['email' => 'pending']); // JSON object with status per channel
            $table->timestamp('read_at')->nullable(); // When recipient read the notification
            $table->timestamps();

            $table->foreign('notification_id')->references('id')->on('notifications')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['notification_id', 'user_id']);
        });

        // Notification delivery logs table
        Schema::create('notification_delivery_logs', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('notification_id');
            $table->uuid('recipient_id');
            $table->string('channel', 50); // email, sms, push, in_app
            $table->string('status', 50); // pending, sent, failed, delivered
            $table->text('response')->nullable(); // Response from the delivery service
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();

            $table->foreign('notification_id')->references('id')->on('notifications')->onDelete('cascade');
            $table->foreign('recipient_id')->references('id')->on('notification_recipients')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_delivery_logs');
        Schema::dropIfExists('notification_recipients');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('user_notification_preferences');
        Schema::dropIfExists('notification_templates');
    }
};