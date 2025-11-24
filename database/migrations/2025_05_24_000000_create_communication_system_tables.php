<?php

declare (strict_types = 1);

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
        // Message Categories/Types
        Schema::create('message_categories', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('(UUID())'));
            $table->string('name', 100);
            $table->string('description', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->datetimes();
        });

        // Message Threads (Conversations)
        Schema::create('message_threads', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('(UUID())'));
            $table->string('subject', 200)->nullable();
            $table->string('type', 50)->default('private'); // private, group, broadcast
            $table->boolean('is_active')->default(true);
            $table->uuid('created_by')->nullable();
            $table->datetimes();
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });

        // Thread Participants
        Schema::create('thread_participants', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('(UUID())'));
            $table->uuid('thread_id');
            $table->uuid('user_id');
            $table->boolean('is_admin')->default(false);
            $table->boolean('is_muted')->default(false);
            $table->timestamp('last_read_at')->nullable();
            $table->datetimes();
            $table->foreign('thread_id')->references('id')->on('message_threads')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['thread_id', 'user_id']);
        });

        // Messages
        Schema::create('messages', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('(UUID())'));
            $table->uuid('thread_id')->nullable();
            $table->uuid('sender_id');
            $table->uuid('recipient_id')->nullable(); // For direct messages
            $table->text('content');
            $table->string('message_type', 50)->default('text'); // text, file, image, etc.
            $table->string('file_url', 255)->nullable();
            $table->json('metadata')->nullable(); // For additional message data
            $table->boolean('is_read')->default(false);
            $table->boolean('is_delivered')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->uuid('reply_to_id')->nullable(); // For reply threads
            $table->uuid('message_category_id')->nullable(); // For categorizing messages
            $table->datetimes();
            $table->foreign('thread_id')->references('id')->on('message_threads')->onDelete('cascade');
            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('recipient_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('reply_to_id')->references('id')->on('messages')->onDelete('set null');
            $table->foreign('message_category_id')->references('id')->on('message_categories')->onDelete('set null');
        });

        // Message Read Status
        Schema::create('message_read_status', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('(UUID())'));
            $table->uuid('message_id');
            $table->uuid('user_id');
            $table->timestamp('read_at')->nullable();
            $table->datetimes();
            $table->foreign('message_id')->references('id')->on('messages')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['message_id', 'user_id']);
        });

        // Announcements
        Schema::create('announcements', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('(UUID())'));
            $table->string('title', 200);
            $table->text('content');
            $table->string('type', 50)->default('general'); // general, urgent, school, class, subject
            $table->uuid('created_by')->nullable();
            $table->uuid('target_audience_id')->nullable(); // Could be class, grade, etc.
            $table->string('target_audience_type', 50)->nullable(); // class, grade, role, etc.
            $table->timestamp('publish_date')->nullable();
            $table->timestamp('expiry_date')->nullable();
            $table->boolean('is_published')->default(false);
            $table->boolean('is_pinned')->default(false);
            $table->json('attachments')->nullable(); // For file attachments
            $table->datetimes();
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });

        // Announcement Read Status
        Schema::create('announcement_read_status', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('(UUID())'));
            $table->uuid('announcement_id');
            $table->uuid('user_id');
            $table->timestamp('read_at')->nullable();
            $table->datetimes();
            $table->foreign('announcement_id')->references('id')->on('announcements')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['announcement_id', 'user_id']);
        });

        // Notifications
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('(UUID())'));
            $table->uuid('user_id'); // Recipient of the notification
            $table->string('title', 200);
            $table->text('content');
            $table->string('type', 100); // message, announcement, reminder, etc.
            $table->string('priority', 20)->default('normal'); // low, normal, high, urgent
            $table->json('data')->nullable(); // Additional data for the notification
            $table->string('action_url', 255)->nullable(); // URL to redirect when notification is clicked
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->uuid('related_model_id')->nullable(); // ID of related model (message, announcement, etc.)
            $table->string('related_model_type', 100)->nullable(); // Type of related model
            $table->datetimes();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // Message Templates
        Schema::create('message_templates', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('(UUID())'));
            $table->string('name', 200);
            $table->string('slug', 100)->unique();
            $table->text('content');
            $table->string('subject', 200)->nullable();
            $table->string('type', 50)->default('general'); // general, announcement, reminder, etc.
            $table->json('placeholders')->nullable(); // Available placeholders in the template
            $table->boolean('is_active')->default(true);
            $table->uuid('created_by')->nullable();
            $table->datetimes();
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('message_read_status');
        Schema::dropIfExists('announcement_read_status');
        Schema::dropIfExists('thread_participants');
        Schema::dropIfExists('messages');
        Schema::dropIfExists('message_threads');
        Schema::dropIfExists('message_categories');
        Schema::dropIfExists('announcements');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('message_templates');
    }
};