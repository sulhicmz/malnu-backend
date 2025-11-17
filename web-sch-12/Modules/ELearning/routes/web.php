<?php

use Illuminate\Support\Facades\Route;
use Modules\ELearning\app\Http\Controllers\ELearningController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('elearnings', ELearningController::class)->names('elearning');
});
