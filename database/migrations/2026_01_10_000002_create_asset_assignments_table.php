<?php

declare(strict_types=1);

use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\DbConnection\Db;
use Hyperf\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('asset_assignments', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('asset_id');
            $table->uuid('assigned_to');
            $table->string('assigned_to_type', 20);
            $table->date('assigned_date');
            $table->date('returned_date')->nullable();
            $table->text('notes')->nullable();
            $table->string('status', 20)->default('active');
            $table->datetimes();

            $table->foreign('asset_id')->references('id')->on('school_inventory')->onDelete('cascade');
            $table->index(['asset_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_assignments');
    }
};
