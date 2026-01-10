<?php

declare(strict_types=1);

use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\DbConnection\Db;
use Hyperf\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('asset_maintenance', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('asset_id');
            $table->date('maintenance_date');
            $table->string('maintenance_type', 50);
            $table->text('description')->nullable();
            $table->decimal('cost', 10, 2)->nullable();
            $table->string('performed_by', 100)->nullable();
            $table->text('notes')->nullable();
            $table->datetimes();

            $table->foreign('asset_id')->references('id')->on('school_inventory')->onDelete('cascade');
            $table->index(['asset_id', 'maintenance_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_maintenance');
    }
};
