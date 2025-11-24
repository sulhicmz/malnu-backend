<?php

declare(strict_types=1);

use App\Http\Controllers\IndexController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Attendance\LeaveRequestController;
use App\Http\Controllers\Attendance\LeaveTypeController;
use App\Http\Controllers\Attendance\StaffAttendanceController;
use App\Http\Controllers\Api\Communication\MessagesController;
use App\Http\Controllers\Api\Communication\AnnouncementsController;
use Hyperf\Support\Facades\Route;

// Public routes (no authentication required)
Route::group(['middleware' => ['input.sanitization']], function () {
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/password/forgot', [AuthController::class, 'requestPasswordReset']);
    Route::post('/auth/password/reset', [AuthController::class, 'resetPassword']);
});

// Protected routes (JWT authentication required)
Route::group(['middleware' => ['jwt']], function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/password/change', [AuthController::class, 'changePassword']);
});

// Attendance and Leave Management Routes (protected)
Route::group(['middleware' => ['jwt']], function () {
    Route::any('/', [IndexController::class, 'index']);

    Route::prefix('attendance')->group(function () {
        // Staff Attendance Routes
        Route::apiResource('staff-attendances', StaffAttendanceController::class);
        Route::post('staff-attendances/mark-attendance', [StaffAttendanceController::class, 'markAttendance']);

        // Leave Management Routes
        Route::apiResource('leave-types', LeaveTypeController::class);
        Route::apiResource('leave-requests', LeaveRequestController::class);
        Route::post('leave-requests/{id}/approve', [LeaveRequestController::class, 'approve']);
        Route::post('leave-requests/{id}/reject', [LeaveRequestController::class, 'reject']);
    });
});

// Communication System Routes (protected)
Route::group(['middleware' => ['jwt']], function () {
    // Messaging Routes
    Route::prefix('communication')->group(function () {
        Route::get('/messages', [MessagesController::class, 'index']);
        Route::post('/messages', [MessagesController::class, 'store']);
        Route::get('/messages/{id}', [MessagesController::class, 'show']);
        Route::get('/messages/threads/{threadId}', [MessagesController::class, 'getThread']);

        // Announcement Routes
        Route::get('/announcements', [AnnouncementsController::class, 'index']);
        Route::post('/announcements', [AnnouncementsController::class, 'store']);
        Route::get('/announcements/{id}', [AnnouncementsController::class, 'show']);
    });
});
