<?php

declare (strict_types = 1);

use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Support\Facades\Schema;
use Hyperf\DbConnection\Db;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('clubs', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->string('category', 50)->default('academic');
            $table->integer('max_members')->nullable();
            $table->uuid('advisor_id')->nullable();
            $table->string('status', 20)->default('active');
            $table->datetimes();

            $table->foreign('advisor_id')->references('id')->on('teachers')->onDelete('set null');
        });

        Schema::create('club_memberships', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('club_id');
            $table->uuid('student_id');
            $table->string('role', 50)->default('member');
            $table->date('joined_date');
            $table->datetimes();

            $table->foreign('club_id')->references('id')->on('clubs')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
        });

        Schema::create('activities', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('club_id');
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->string('location', 100)->nullable();
            $table->integer('max_attendees')->nullable();
            $table->datetimes();

            $table->foreign('club_id')->references('id')->on('clubs')->onDelete('cascade');
        });

        Schema::create('activity_attendances', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('activity_id');
            $table->uuid('student_id');
            $table->string('status', 50)->default('present');
            $table->text('notes')->nullable();
            $table->datetimes();

            $table->foreign('activity_id')->references('id')->on('activities')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
        });

        Schema::create('club_advisors', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('club_id');
            $table->uuid('teacher_id');
            $table->date('assigned_date');
            $table->datetimes();

            $table->foreign('club_id')->references('id')->on('clubs')->onDelete('cascade');
            $table->foreign('teacher_id')->references('id')->on('teachers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('club_advisors');
        Schema::dropIfExists('activity_attendances');
        Schema::dropIfExists('activities');
        Schema::dropIfExists('club_memberships');
        Schema::dropIfExists('clubs');
    }
}
