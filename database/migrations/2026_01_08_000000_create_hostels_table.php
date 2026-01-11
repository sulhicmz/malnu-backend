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
        Schema::create('hostels', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->string('name', 100);
            $table->string('code', 20)->unique();
            $table->string('type', 50)->default('boarding');
            $table->string('gender', 10)->nullable();
            $table->integer('capacity')->default(0);
            $table->integer('current_occupancy')->default(0);
            $table->string('warden_name', 100)->nullable();
            $table->string('warden_contact', 20)->nullable();
            $table->text('address')->nullable();
            $table->text('facilities')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->datetimes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hostels');
    }
};
