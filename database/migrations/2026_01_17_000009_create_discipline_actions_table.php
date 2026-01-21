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
        Schema::create('discipline_actions', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('incident_id')->comment('Reference to behavior incident');
            $table->uuid('assigned_to')->comment('User assigned to implement the action');
            $table->enum('action_type', ['warning', 'detention', 'suspension', 'expulsion', 'counseling', 'community_service', 'parent_conference', 'other'])->default('warning');
            $table->string('action_type_other', 255)->nullable()->comment('Other action type description');
            $table->integer('duration_days')->nullable()->comment('Duration in days for suspension/detention');
            $table->date('start_date')->nullable()->comment('Start date for suspension/detention');
            $table->date('end_date')->nullable()->comment('End date for suspension/detention');
            $table->text('description')->nullable()->comment('Description of the disciplinary action');
            $table->text('conditions')->nullable()->comment('Conditions for reinstatement');
            $table->boolean('is_completed')->default(false)->comment('Whether the action has been completed');
            $table->timestamp('completed_at')->nullable()->comment('When the action was completed');
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();

            $table->foreign('incident_id')->references('id')->on('behavior_incidents')->onDelete('cascade');
            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            $table->index('incident_id');
            $table->index('action_type');
            $table->index('is_completed');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discipline_actions');
    }
};
