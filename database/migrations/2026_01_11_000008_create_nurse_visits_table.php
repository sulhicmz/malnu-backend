<?php

declare(strict_types=1);

use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\DbConnection\Db;
use Hyperf\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('nurse_visits', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('student_id');
            $table->uuid('health_record_id');
            $table->string('visit_reason');
            $table->text('symptoms')->nullable();
            $table->timestamp('visit_time');
            $table->text('examination')->nullable();
            $table->text('treatment')->nullable();
            $table->string('disposition')->nullable(); // returned_to_class, sent_home, referred_to_hospital, admitted
            $table->timestamp('return_time')->nullable();
            $table->text('referral_details')->nullable();
            $table->uuid('attended_by')->nullable();
            $table->text('notes')->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('health_record_id')->references('id')->on('health_records')->onDelete('cascade');
            $table->foreign('attended_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');

            $table->index('student_id');
            $table->index('visit_time');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nurse_visits');
    }
};
