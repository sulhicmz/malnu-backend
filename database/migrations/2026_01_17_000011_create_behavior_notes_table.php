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
        Schema::create('behavior_notes', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('student_id')->comment('Reference to student');
            $table->uuid('recorded_by')->comment('User who recorded the note');
            $table->date('note_date')->comment('Date of the observation');
            $table->text('note')->comment('Behavioral observation or note');
            $table->enum('type', ['positive', 'negative', 'neutral'])->default('neutral');
            $table->boolean('is_private')->default(false)->comment('Whether note is private (only for staff)');
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('recorded_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            $table->index('student_id');
            $table->index('note_date');
            $table->index('type');
            $table->index('is_private');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('behavior_notes');
    }
};
