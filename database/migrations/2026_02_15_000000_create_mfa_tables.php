<?php

declare(strict_types=1);

use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Schema\Schema;

class CreateMfaTables extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // User MFA settings table
        Schema::create('user_mfa_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->unique();
            $table->boolean('mfa_enabled')->default(false);
            $table->string('mfa_secret')->nullable();
            $table->string('mfa_type')->default('totp'); // totp, email, sms
            $table->timestamp('mfa_enabled_at')->nullable();
            $table->timestamp('mfa_verified_at')->nullable();
            $table->integer('backup_codes_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->index('mfa_enabled');
        });

        // MFA backup codes table
        Schema::create('mfa_backup_codes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->string('code_hash'); // Store hashed version of code
            $table->boolean('used')->default(false);
            $table->timestamp('used_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->index(['user_id', 'used']);
        });

        // MFA verification attempts log (for security monitoring)
        Schema::create('mfa_verification_attempts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->boolean('success');
            $table->string('method'); // totp, backup_code
            $table->timestamp('attempted_at');
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->index(['user_id', 'attempted_at']);
            $table->index(['ip_address', 'attempted_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mfa_verification_attempts');
        Schema::dropIfExists('mfa_backup_codes');
        Schema::dropIfExists('user_mfa_settings');
    }
}
