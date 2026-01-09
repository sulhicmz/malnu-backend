<?php

declare (strict_types = 1);

use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Support\Facades\Schema;
use Hyperf\DbConnection\Db;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_devices', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('user_id');
            $table->string('device_name', 100)->nullable();
            $table->string('user_agent', 500);
            $table->string('ip_address', 45);
            $table->boolean('is_trusted')->default(false);
            $table->timestamp('last_used_at')->nullable();
            
            $table->datetimes();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            $table->index('user_id');
            $table->index('is_trusted');
            $table->index('last_used_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_devices');
    }
};
