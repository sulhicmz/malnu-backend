<?php

declare(strict_types=1);

use App\Http\Controllers\IndexController;
use App\Http\Controllers\Attendance\LeaveRequestController;
use App\Http\Controllers\Attendance\LeaveTypeController;
use App\Http\Controllers\Attendance\StaffAttendanceController;
use Hyperf\Support\Facades\Route;

Route::any('/', [IndexController::class, 'index']);

// Attendance and Leave Management Routes
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
