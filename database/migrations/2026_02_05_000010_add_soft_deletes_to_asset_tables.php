<?php

declare(strict_types=1);

use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asset_categories', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable();
        });

        Schema::table('asset_assignments', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable();
        });

        Schema::table('asset_maintenance', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('asset_categories', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('asset_assignments', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('asset_maintenance', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });
    }
};
