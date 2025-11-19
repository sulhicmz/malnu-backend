<?php

declare(strict_types=1);

use App\Http\Controllers\IndexController;
use Hypervel\Support\Facades\Route;

// Basic API route
Route::any('/', [IndexController::class, 'index']);

// API version 1 routes
Route::prefix('v1')->group(function () {
    // Public API routes
    Route::post('/auth/login', [App\Http\Controllers\Api\v1\Auth\LoginController::class, 'login']);
    Route::post('/auth/logout', [App\Http\Controllers\Api\v1\Auth\LoginController::class, 'logout']);
    Route::post('/auth/refresh', [App\Http\Controllers\Api\v1\Auth\LoginController::class, 'refresh']);
    Route::post('/auth/me', [App\Http\Controllers\Api\v1\Auth\LoginController::class, 'me']);

    // Protected API routes (require authentication)
    Route::middleware(['auth:jwt', 'mobile.throttle'])->group(function () {
        // Student API routes
        Route::prefix('student')->group(function () {
            Route::get('/dashboard', [App\Http\Controllers\Api\v1\Student\DashboardController::class, 'index']);
            Route::get('/profile', [App\Http\Controllers\Api\v1\Student\ProfileController::class, 'show']);
            Route::put('/profile', [App\Http\Controllers\Api\v1\Student\ProfileController::class, 'update']);
            Route::get('/grades', [App\Http\Controllers\Api\v1\Student\GradeController::class, 'index']);
            Route::get('/assignments', [App\Http\Controllers\Api\v1\Student\AssignmentController::class, 'index']);
            Route::get('/schedule', [App\Http\Controllers\Api\v1\Student\ScheduleController::class, 'index']);
            Route::get('/attendance', [App\Http\Controllers\Api\v1\Student\AttendanceController::class, 'index']);
        });

        // Parent API routes
        Route::prefix('parent')->group(function () {
            Route::get('/dashboard', [App\Http\Controllers\Api\v1\Parent\DashboardController::class, 'index']);
            Route::get('/student/{studentId}/grades', [App\Http\Controllers\Api\v1\Parent\GradeController::class, 'show']);
            Route::get('/student/{studentId}/assignments', [App\Http\Controllers\Api\v1\Parent\AssignmentController::class, 'index']);
            Route::get('/student/{studentId}/attendance', [App\Http\Controllers\Api\v1\Parent\AttendanceController::class, 'index']);
            Route::get('/student/{studentId}/progress', [App\Http\Controllers\Api\v1\Parent\ProgressController::class, 'show']);
            Route::get('/fees', [App\Http\Controllers\Api\v1\Parent\FeeController::class, 'index']);
        });

        // Teacher API routes
        Route::prefix('teacher')->group(function () {
            Route::get('/dashboard', [App\Http\Controllers\Api\v1\Teacher\DashboardController::class, 'index']);
            Route::get('/classes', [App\Http\Controllers\Api\v1\Teacher\ClassController::class, 'index']);
            Route::get('/classes/{classId}/students', [App\Http\Controllers\Api\v1\Teacher\ClassController::class, 'students']);
            Route::post('/attendance', [App\Http\Controllers\Api\v1\Teacher\AttendanceController::class, 'store']);
            Route::post('/grades', [App\Http\Controllers\Api\v1\Teacher\GradeController::class, 'store']);
            Route::get('/assignments', [App\Http\Controllers\Api\v1\Teacher\AssignmentController::class, 'index']);
            Route::post('/assignments', [App\Http\Controllers\Api\v1\Teacher\AssignmentController::class, 'store']);
        });

        // Admin API routes
        Route::prefix('admin')->group(function () {
            Route::get('/dashboard', [App\Http\Controllers\Api\v1\Admin\DashboardController::class, 'index']);
            Route::get('/users', [App\Http\Controllers\Api\v1\Admin\UserController::class, 'index']);
            Route::get('/students', [App\Http\Controllers\Api\v1\Admin\StudentController::class, 'index']);
            Route::get('/teachers', [App\Http\Controllers\Api\v1\Admin\TeacherController::class, 'index']);
            Route::get('/classes', [App\Http\Controllers\Api\v1\Admin\ClassController::class, 'index']);
        });
    });
});