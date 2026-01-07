<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateNurseVisitsTable extends Migration
{
    public function up(): void
    {
        Schema::create('nurse_visits', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('student_id');
            $table->dateTime('visit_date');
            $table->string('visit_reason');
            $table->text('complaint')->nullable();
            $table->text('symptoms')->nullable();
            $table->text('examination')->nullable();
            $table->text('treatment')->nullable();
            $table->text('medication_given')->nullable();
            $table->string('disposition')->default('returned_to_class');
            $table->dateTime('return_time')->nullable();
            $table->boolean('parent_notified')->default(false);
            $table->dateTime('parent_notification_time')->nullable();
            $table->boolean('referral')->default(false);
            $table->text('referral_details')->nullable();
            $table->uuid('nurse_id');
            $table->text('notes')->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('nurse_id')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');

            $table->index('student_id');
            $table->index('visit_date');
            $table->index('visit_reason');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nurse_visits');
    }
}
