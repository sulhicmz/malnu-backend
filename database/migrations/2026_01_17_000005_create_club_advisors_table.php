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
        Schema::create('club_advisors', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->uuid('club_id');
            $table->uuid('teacher_id');
            $table->date('assigned_date');
            $table->text('notes')->nullable();
            
            $table->datetimes();
            
            $table->foreign('club_id')->references('id')->on('clubs')->onDelete('cascade');
            $table->foreign('teacher_id')->references('id')->on('teachers')->onDelete('cascade');
            
            $table->unique(['club_id', 'teacher_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('club_advisors');
    }
}
