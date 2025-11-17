<?php

declare(strict_types=1);

use App\Http\Controllers\IndexController;
use App\Http\Controllers\SchoolManagement\StudentController;
use App\Http\Controllers\SchoolManagement\TeacherController;
use App\Http\Controllers\SchoolManagement\ClassController;
use Hypervel\Support\Facades\Route;

Route::any('/', [IndexController::class, 'index']);

// School Management Routes
Route::prefix('school-management')->group(function () {
    // Student routes
    Route::apiResource('students', StudentController::class);
    
    // Teacher routes
    Route::apiResource('teachers', TeacherController::class);
    
    // Class routes
    Route::apiResource('classes', ClassController::class);
});
