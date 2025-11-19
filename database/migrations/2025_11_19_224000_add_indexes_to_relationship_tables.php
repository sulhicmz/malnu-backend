<?php

declare(strict_types=1);

use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hypervel\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add indexes to model_has_roles table
        Schema::table('model_has_roles', function (Blueprint $table) {
            $table->index('model_id', 'idx_model_has_roles_model_id');
            $table->index('role_id', 'idx_model_has_roles_role_id');
        });

        // Add indexes to model_has_permissions table
        Schema::table('model_has_permissions', function (Blueprint $table) {
            $table->index('model_id', 'idx_model_has_permissions_model_id');
            $table->index('permission_id', 'idx_model_has_permissions_permission_id');
        });

        // Add indexes to role_has_permissions table
        Schema::table('role_has_permissions', function (Blueprint $table) {
            $table->index('role_id', 'idx_role_has_permissions_role_id');
            $table->index('permission_id', 'idx_role_has_permissions_permission_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('model_has_roles', function (Blueprint $table) {
            $table->dropIndex('idx_model_has_roles_model_id');
            $table->dropIndex('idx_model_has_roles_role_id');
        });

        Schema::table('model_has_permissions', function (Blueprint $table) {
            $table->dropIndex('idx_model_has_permissions_model_id');
            $table->dropIndex('idx_model_has_permissions_permission_id');
        });

        Schema::table('role_has_permissions', function (Blueprint $table) {
            $table->dropIndex('idx_role_has_permissions_role_id');
            $table->dropIndex('idx_role_has_permissions_permission_id');
        });
    }
};