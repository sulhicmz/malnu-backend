<?php

declare(strict_types=1);

use App\Http\Controllers\IndexController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Attendance\LeaveRequestController;
use App\Http\Controllers\Attendance\LeaveTypeController;
use App\Http\Controllers\Attendance\StaffAttendanceController;
use App\Http\Controllers\Api\SchoolManagement\StudentController;
use App\Http\Controllers\Api\SchoolManagement\TeacherController;
use App\Http\Controllers\Calendar\CalendarController;
use Hyperf\Support\Facades\Route;

// Public routes (no authentication required)
Route::group(['middleware' => ['input.sanitization', 'rate.limit']], function () {
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/password/forgot', [AuthController::class, 'requestPasswordReset']);
    Route::post('/auth/password/reset', [AuthController::class, 'resetPassword']);
});

// Protected routes (JWT authentication required)
Route::group(['middleware' => ['jwt', 'rate.limit']], function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/password/change', [AuthController::class, 'changePassword']);
});

// Attendance and Leave Management Routes (protected - requires specific roles)
Route::group(['middleware' => ['jwt', 'rate.limit']], function () {
    Route::any('/', [IndexController::class, 'index']);

    Route::prefix('attendance')->group(function () {
        // Staff Attendance Routes - Admin, Staff, Teacher, Principal, Super Admin
        Route::middleware(['role:Super Admin|Kepala Sekolah|Staf TU|Guru'])->group(function () {
            Route::apiResource('staff-attendances', StaffAttendanceController::class);
            Route::post('staff-attendances/mark-attendance', [StaffAttendanceController::class, 'markAttendance']);

            // Leave Management Routes
            Route::apiResource('leave-types', LeaveTypeController::class);
            Route::apiResource('leave-requests', LeaveRequestController::class);
            Route::post('leave-requests/{id}/approve', [LeaveRequestController::class, 'approve']);
            Route::post('leave-requests/{id}/reject', [LeaveRequestController::class, 'reject']);
        });
    });
});

// School Management Routes (protected - requires specific roles)
Route::group(['middleware' => ['jwt', 'rate.limit']], function () {
    Route::prefix('school')->group(function () {
        // Student and Teacher Management - Admin, Staff, Principal, Super Admin only
        Route::middleware(['role:Super Admin|Kepala Sekolah|Staf TU'])->group(function () {
            // Student Management Routes
            Route::apiResource('students', StudentController::class);

            // Teacher Management Routes
            Route::apiResource('teachers', TeacherController::class);
        });
    });
});

// Calendar and Event Management Routes (protected - requires specific roles)
Route::group(['middleware' => ['jwt', 'rate.limit']], function () {
    Route::prefix('calendar')->group(function () {
        // Calendar Management - Admin, Staff, Teacher, Principal, Super Admin can create/update/delete
        Route::middleware(['role:Super Admin|Kepala Sekolah|Staf TU|Guru'])->group(function () {
            Route::post('calendars', [CalendarController::class, 'createCalendar']);
            Route::put('calendars/{id}', [CalendarController::class, 'updateCalendar']);
            Route::delete('calendars/{id}', [CalendarController::class, 'deleteCalendar']);

            // Event Management - create/update/delete
            Route::post('events', [CalendarController::class, 'createEvent']);
            Route::put('events/{id}', [CalendarController::class, 'updateEvent']);
            Route::delete('events/{id}', [CalendarController::class, 'deleteEvent']);
            Route::post('events/{eventId}/register', [CalendarController::class, 'registerForEvent']);
            Route::post('calendars/{calendarId}/share', [CalendarController::class, 'shareCalendar']);
            Route::post('resources/book', [CalendarController::class, 'bookResource']);
        });

        // View access - All authenticated users
        Route::get('calendars/{id}', [CalendarController::class, 'getCalendar']);
        Route::get('events/{id}', [CalendarController::class, 'getEvent']);
        Route::get('calendars/{calendarId}/events', [CalendarController::class, 'getEventsByDateRange']);
    });
});
