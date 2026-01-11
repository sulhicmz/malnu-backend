<?php

declare(strict_types=1);

use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Support\Facades\Schema;
use Hyperf\DbConnection\Db;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('message_participants', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('thread_id');
            $table->uuid('user_id');
            $table->boolean('is_admin')->default(false); // Can add/remove participants
            $table->boolean('has_left')->default(false); // User left the conversation
            $table->timestamp('last_read_at')->nullable();
            $table->timestamps();

            $table->foreign('thread_id')->references('id')->on('message_threads')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['thread_id', 'user_id']);
            $table->index('thread_id');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('message_participants');
    }
};
