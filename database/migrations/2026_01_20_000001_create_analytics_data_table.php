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
        Schema::create('analytics_data', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('user_id')->nullable();
            $table->string('data_type', 50)->comment('Type of analytics data (performance, attendance, grades, etc.)');
            $table->string('metric_name', 100)->comment('Name of the metric');
            $table->decimal('metric_value', 10, 2)->nullable()->comment('Value of the metric');
            $table->json('metadata')->nullable()->comment('Additional metadata as JSON');
            $table->string('period', 50)->nullable()->comment('Period for this metric (daily, weekly, monthly, yearly)');
            $table->timestamp('recorded_at')->nullable()->comment('When the metric was recorded');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->index(['data_type', 'period', 'recorded_at']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_data');
    }
};
