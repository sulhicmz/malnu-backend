<?php

use Illuminate\Support\Facades\Route;
use Modules\AiLearning\app\Http\Controllers\AiLearningController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('ailearnings', AiLearningController::class)->names('ailearning');
});
