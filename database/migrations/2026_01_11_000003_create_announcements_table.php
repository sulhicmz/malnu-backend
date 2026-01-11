<?php

declare(strict_types=1);

use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Support\Facades\Schema;
use Hyperf\DbConnection\Db;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('created_by');
            $table->string('title');
            $table->text('content');
            $table->string('type')->default('general'); // general, emergency, class, subject
            $table->string('target_type')->default('all'); // all, role, class, specific
            $table->text('target_roles')->nullable(); // JSON array of roles
            $table->text('target_classes')->nullable(); // JSON array of class IDs
            $table->json('target_users')->nullable(); // JSON array of user IDs
            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('attachment_url')->nullable();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->index('created_by');
            $table->index('type');
            $table->index('is_active');
            $table->index('published_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
