<?php

declare(strict_types=1);

use App\Http\Controllers\IndexController;
use Hypervel\Support\Facades\Route;

// Basic API route
Route::any('/', [IndexController::class, 'index']);

// Mobile API routes
Route::prefix('mobile')->group(function () {
    // Mobile authentication routes with rate limiting
    Route::post('/login', [App\Http\Controllers\Mobile\MobileAuthController::class, 'login'])->middleware('throttle:10,1'); // 10 attempts per minute
    Route::post('/logout', [App\Http\Controllers\Mobile\MobileAuthController::class, 'logout'])->middleware('throttle:30,1'); // 30 attempts per minute
    Route::post('/refresh', [App\Http\Controllers\Mobile\MobileAuthController::class, 'refresh'])->middleware('throttle:10,1'); // 10 attempts per minute
    Route::post('/me', [App\Http\Controllers\Mobile\MobileAuthController::class, 'me'])->middleware('throttle:60,1'); // 60 attempts per minute

    // Protected mobile routes
    Route::middleware(['jwt.auth', 'throttle:120,1'])->group(function () { // 120 requests per minute for authenticated users
        // Student routes
        Route::prefix('student')->group(function () {
            Route::get('/dashboard', [App\Http\Controllers\Mobile\MobileStudentController::class, 'dashboard']);
            Route::get('/grades', [App\Http\Controllers\MobileStudentController::class, 'grades']);
            Route::get('/assignments', [App\Http\Controllers\MobileStudentController::class, 'assignments']);
            Route::get('/schedule', [App\Http\Controllers\MobileStudentController::class, 'schedule']);
            Route::get('/profile', [App\Http\Controllers\MobileStudentController::class, 'profile']);
        });

        // Parent routes
        Route::prefix('parent')->group(function () {
            Route::get('/dashboard', [App\Http\Controllers\Mobile\MobileParentController::class, 'dashboard']);
            Route::get('/student/{studentId}/grades', [App\Http\Controllers\Mobile\MobileParentController::class, 'studentGrades']);
            Route::get('/student/{studentId}/assignments', [App\Http\Controllers\Mobile\MobileParentController::class, 'studentAssignments']);
            Route::get('/student/{studentId}/attendance', [App\Http\Controllers\Mobile\MobileParentController::class, 'studentAttendance']);
            Route::get('/student/{studentId}/schedule', [App\Http\Controllers\Mobile\MobileParentController::class, 'studentSchedule']);
        });

        // Teacher routes
        Route::prefix('teacher')->group(function () {
            Route::get('/dashboard', [App\Http\Controllers\Mobile\MobileTeacherController::class, 'dashboard']);
            Route::get('/classes', [App\Http\Controllers\MobileTeacherController::class, 'classes']);
            Route::get('/students/{classId}', [App\Http\Controllers\MobileTeacherController::class, 'students']);
            Route::get('/assignments', [App\Http\Controllers\MobileTeacherController::class, 'assignments']);
            Route::get('/grades', [App\Http\Controllers\MobileTeacherController::class, 'grades']);
        });
    });
});
