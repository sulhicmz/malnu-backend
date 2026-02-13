<?php

declare(strict_types=1);

use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\DbConnection\Db;
use Hyperf\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('teacher_workloads', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('teacher_id');
            $table->string('academic_year', 9);
            $table->string('semester', 10);
            $table->decimal('total_hours_per_week', 5, 2)->default(0);
            $table->decimal('max_hours_per_week', 5, 2)->default(40);
            $table->decimal('teaching_hours', 5, 2)->default(0);
            $table->decimal('administrative_hours', 5, 2)->default(0);
            $table->decimal('extracurricular_hours', 5, 2)->default(0);
            $table->decimal('preparation_hours', 5, 2)->default(0);
            $table->decimal('grading_hours', 5, 2)->default(0);
            $table->decimal('other_duties_hours', 5, 2)->default(0);
            $table->string('workload_status', 20)->default('normal');
            $table->text('notes')->nullable();

            $table->datetimes();

            $table->foreign('teacher_id')->references('id')->on('teachers')->onDelete('cascade');
            $table->unique(['teacher_id', 'academic_year', 'semester']);
            $table->index(['academic_year', 'semester']);
            $table->index('workload_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_workloads');
    }
};
