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
        Schema::create('behavior_categories', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->string('name', 100);
            $table->string('description', 255)->nullable();
            $table->enum('type', ['positive', 'negative', 'neutral'])->default('neutral');
            $table->integer('severity_level')->default(0)->comment('1-5 scale for negative behaviors, 1-5 for positive behaviors');
            $table->boolean('is_active')->default(true);
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            $table->index('type');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('behavior_categories');
    }
};
