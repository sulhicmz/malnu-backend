<?php

declare(strict_types=1);

use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\DbConnection\Db;
use Hyperf\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('allergies', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('student_id');
            $table->uuid('health_record_id');
            $table->string('allergen');
            $table->string('severity')->default('mild'); // mild, moderate, severe, life_threatening
            $table->text('symptoms')->nullable();
            $table->text('emergency_protocol')->nullable();
            $table->boolean('epipen_required')->default(false);
            $table->date('diagnosed_date')->nullable();
            $table->text('notes')->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('health_record_id')->references('id')->on('health_records')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');

            $table->index('student_id');
            $table->index('severity');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('allergies');
    }
};
