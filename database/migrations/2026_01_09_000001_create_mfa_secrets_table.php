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
        Schema::create('mfa_secrets', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('user_id')->unique();
            $table->string('secret', 64);
            $table->boolean('is_enabled')->default(false);
            $table->json('backup_codes')->nullable();
            $table->integer('backup_codes_count')->default(10);
            
            $table->datetimes();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            $table->index('user_id');
            $table->index('is_enabled');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mfa_secrets');
    }
};
