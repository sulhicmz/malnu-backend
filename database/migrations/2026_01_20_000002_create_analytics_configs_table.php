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
        Schema::create('analytics_configs', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('user_id')->nullable();
            $table->string('dashboard_name', 100)->comment('Name of the dashboard configuration');
            $table->json('config_data')->comment('Dashboard configuration as JSON');
            $table->boolean('is_public')->default(false)->comment('Whether dashboard is publicly visible');
            $table->boolean('is_default')->default(false)->comment('Whether this is default dashboard');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_configs');
    }
};
