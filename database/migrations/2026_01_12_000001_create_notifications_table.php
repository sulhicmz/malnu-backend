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
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('template_id')->nullable();
            $table->string('title');
            $table->text('message');
            $table->string('type', 50)->default('info');
            $table->string('priority', 20)->default('medium');
            $table->json('data')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->datetimes();
            $table->foreign('template_id')->references('id')->on('notification_templates')->onDelete('set null');

            $table->index('type');
            $table->index('priority');
            $table->index('scheduled_at');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
