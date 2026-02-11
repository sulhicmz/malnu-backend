<?php

declare(strict_types=1);

use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('student_attendances', function (Blueprint $table) {
            $table->index(['student_id', 'attendance_date', 'status'], 'idx_student_attendances_date_status');
        });
    }

    public function down(): void
    {
        Schema::table('student_attendances', function (Blueprint $table) {
            $table->dropIndex('idx_student_attendances_date_status');
        });
    }
};
