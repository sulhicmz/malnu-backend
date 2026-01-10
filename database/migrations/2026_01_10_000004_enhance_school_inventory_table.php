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
        Schema::table('school_inventory', function (Blueprint $table) {
            $table->string('serial_number', 100)->nullable()->after('name');
            $table->string('asset_code', 50)->nullable()->after('serial_number');
            $table->uuid('category_id')->nullable()->after('asset_code');
            $table->string('status', 20)->default('available')->after('condition');
            $table->decimal('purchase_cost', 10, 2)->nullable()->after('purchase_date');
            $table->uuid('assigned_to')->nullable()->after('status');
            $table->date('assigned_date')->nullable()->after('assigned_to');
            $table->text('notes')->nullable()->after('assigned_date');

            $table->foreign('category_id')->references('id')->on('asset_categories')->onDelete('set null');
            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');
            $table->index(['status', 'category_id']);
        });
    }

    public function down(): void
    {
        Schema::table('school_inventory', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropForeign(['assigned_to']);
            $table->dropColumn([
                'serial_number',
                'asset_code',
                'category_id',
                'status',
                'purchase_cost',
                'assigned_to',
                'assigned_date',
                'notes'
            ]);
        });
    }
};
