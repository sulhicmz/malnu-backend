<?php

declare(strict_types=1);

use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->index('day_of_week');
            $table->index('start_time');
            $table->index('end_time');
            $table->index('room');
            $table->index(['day_of_week', 'start_time']);
        });

        Schema::table('class_subjects', function (Blueprint $table) {
            $table->index('class_id');
            $table->index('subject_id');
            $table->index('teacher_id');
            $table->index(['class_id', 'teacher_id']);
        });
    }

    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropIndex(['day_of_week', 'start_time']);
            $table->dropIndex('room');
            $table->dropIndex('end_time');
            $table->dropIndex('start_time');
            $table->dropIndex('day_of_week');
        });

        Schema::table('class_subjects', function (Blueprint $table) {
            $table->dropIndex(['class_id', 'teacher_id']);
            $table->dropIndex('teacher_id');
            $table->dropIndex('subject_id');
            $table->dropIndex('class_id');
        });
    }
};
