<?php

declare(strict_types=1);

use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run migrations.
     */
    public function up(): void
    {
        // Add deleted_at to monetization tables
        Schema::table('marketplace_products', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable();
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable();
        });

        Schema::table('transaction_items', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable();
        });

        // Add deleted_at to career development tables
        Schema::table('career_assessments', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable();
        });

        Schema::table('counseling_sessions', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable();
        });

        Schema::table('industry_partners', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable();
        });

        // Add deleted_at to AI assistant tables
        Schema::table('ai_tutor_sessions', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable();
        });

        // Note: audit_logs should NOT have soft deletes
        // Audit logs must be permanent for compliance and security reasons
    }

    /**
     * Reverse migrations.
     */
    public function down(): void
    {
        Schema::table('ai_tutor_sessions', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('industry_partners', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('counseling_sessions', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('career_assessments', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('transaction_items', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('marketplace_products', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });
    }
};
