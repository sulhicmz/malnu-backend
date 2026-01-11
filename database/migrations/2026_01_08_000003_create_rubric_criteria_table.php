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
        Schema::create('rubric_criteria', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('rubric_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('max_score', 5, 2);
            $table->integer('weight')->default(1);
            $table->datetimes();
            
            $table->foreign('rubric_id')->references('id')->on('rubrics')->onDelete('cascade');
            
            $table->index('rubric_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rubric_criteria');
    }
};
