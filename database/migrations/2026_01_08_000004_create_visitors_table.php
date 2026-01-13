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
        Schema::create('visitors', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('hostel_id');
            $table->uuid('visitor_student_id')->nullable();
            $table->string('visitor_name', 100);
            $table->string('visitor_phone', 20);
            $table->string('relationship', 50);
            $table->string('id_proof_type', 50)->nullable();
            $table->string('id_proof_number', 50)->nullable();
            $table->string('purpose', 200);
            $table->dateTime('visit_date');
            $table->dateTime('check_in_time');
            $table->dateTime('check_out_time')->nullable();
            $table->string('status', 20)->default('pending');
            $table->uuid('approved_by')->nullable();
            $table->text('notes')->nullable();
            $table->datetimes();
            $table->foreign('hostel_id')->references('id')->on('hostels')->onDelete('cascade');
            $table->foreign('visitor_student_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['hostel_id', 'status']);
            $table->index(['visitor_student_id', 'visit_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visitors');
    }
};
