<?php

declare(strict_types = 1);

use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Support\Facades\Schema;
use Hyperf\DbConnection\Db;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rubrics', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('max_score', 5, 2)->default(100.00);
            $table->boolean('is_default')->default(false);
            $table->uuid('created_by');
            $table->datetimes();
            
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            
            $table->index('is_default');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rubrics');
    }
};
