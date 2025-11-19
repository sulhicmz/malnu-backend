<?php

declare(strict_types=1);

use App\Http\Controllers\IndexController;
use Hypervel\Support\Facades\Route;

// Basic API route
Route::any('/', [IndexController::class, 'index']);

// Mobile API routes with versioning
Route::prefix('v1')->middleware(['throttle:60,1', 'api.version'])->group(function () {
    // Mobile authentication routes
    Route::post('/auth/login', [App\Http\Controllers\Mobile\AuthController::class, 'login']);
    Route::post('/auth/logout', [App\Http\Controllers\Mobile\AuthController::class, 'logout']);
    Route::post('/auth/refresh', [App\Http\Controllers\Mobile\AuthController::class, 'refresh']);
    Route::post('/auth/me', [App\Http\Controllers\Mobile\AuthController::class, 'me']);

    // Mobile API routes that require authentication
    Route::middleware('auth:jwt')->group(function () {
        // Student mobile API
        Route::prefix('student')->group(function () {
            Route::get('/dashboard', [App\Http\Controllers\Mobile\StudentController::class, 'dashboard']);
            Route::get('/grades', [App\Http\Controllers\Mobile\StudentController::class, 'grades']);
            Route::get('/assignments', [App\Http\Controllers\Mobile\StudentController::class, 'assignments']);
            Route::get('/schedule', [App\Http\Controllers\Mobile\StudentController::class, 'schedule']);
            Route::get('/attendance', [App\Http\Controllers\Mobile\StudentController::class, 'attendance']);
        });

        // Parent mobile API
        Route::prefix('parent')->group(function () {
            Route::get('/dashboard', [App\Http\Controllers\Mobile\ParentController::class, 'dashboard']);
            Route::get('/student/{id}/progress', [App\Http\Controllers\Mobile\ParentController::class, 'studentProgress']);
            Route::get('/student/{id}/attendance', [App\Http\Controllers\Mobile\ParentController::class, 'studentAttendance']);
            Route::get('/student/{id}/fees', [App\Http\Controllers\Mobile\ParentController::class, 'studentFees']);
            Route::get('/student/{id}/grades', [App\Http\Controllers\Mobile\ParentController::class, 'studentGrades']);
        });

        // Teacher mobile API
        Route::prefix('teacher')->group(function () {
            Route::get('/dashboard', [App\Http\Controllers\Mobile\TeacherController::class, 'dashboard']);
            Route::get('/classes', [App\Http\Controllers\Mobile\TeacherController::class, 'classes']);
            Route::get('/students/{classId}', [App\Http\Controllers\Mobile\TeacherController::class, 'students']);
            Route::post('/attendance/mark', [App\Http\Controllers\Mobile\TeacherController::class, 'markAttendance']);
            Route::get('/assignments', [App\Http\Controllers\Mobile\TeacherController::class, 'assignments']);
            Route::post('/assignments/create', [App\Http\Controllers\Mobile\TeacherController::class, 'createAssignment']);
        });

        // Admin mobile API
        Route::prefix('admin')->group(function () {
            Route::get('/dashboard', [App\Http\Controllers\Mobile\AdminController::class, 'dashboard']);
            Route::get('/users', [App\Http\Controllers\Mobile\AdminController::class, 'users']);
            Route::get('/reports', [App\Http\Controllers\Mobile\AdminController::class, 'reports']);
        });
    });
});
