<?php

use Illuminate\Support\Facades\Route;
use Modules\ELearning\app\Http\Controllers\ELearningController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('elearnings', ELearningController::class)->names('elearning');
});
