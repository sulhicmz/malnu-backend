<?php

declare(strict_types=1);

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SchoolManagement\StudentController;
use App\Http\Controllers\Api\SchoolManagement\TeacherController;
use App\Http\Controllers\Attendance\LeaveRequestController;
use App\Http\Controllers\Attendance\LeaveTypeController;
use App\Http\Controllers\Attendance\StaffAttendanceController;
use App\Http\Controllers\Calendar\CalendarController;
use App\Http\Controllers\IndexController;
use Hyperf\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::any('/', [IndexController::class, 'index']);

    Route::group(['middleware' => ['input.sanitization', 'rate.limit']], function () {
        Route::prefix('auth')->group(function () {
            Route::post('/register', [AuthController::class, 'register']);
            Route::post('/login', [AuthController::class, 'login']);
            Route::post('/password/forgot', [AuthController::class, 'requestPasswordReset']);
            Route::post('/password/reset', [AuthController::class, 'resetPassword']);
        });
    });

    Route::group(['middleware' => ['jwt', 'rate.limit']], function () {
        Route::prefix('auth')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::post('/refresh', [AuthController::class, 'refresh']);
            Route::get('/me', [AuthController::class, 'me']);
            Route::post('/password/change', [AuthController::class, 'changePassword']);
        });

        Route::prefix('attendance')->group(function () {
            Route::apiResource('staff-attendances', StaffAttendanceController::class);
            Route::post('staff-attendances/mark-attendance', [StaffAttendanceController::class, 'markAttendance']);
            Route::apiResource('leave-types', LeaveTypeController::class);
            Route::apiResource('leave-requests', LeaveRequestController::class);
            Route::post('leave-requests/{id}/approve', [LeaveRequestController::class, 'approve']);
            Route::post('leave-requests/{id}/reject', [LeaveRequestController::class, 'reject']);
        });

        Route::prefix('school')->group(function () {
            Route::apiResource('students', StudentController::class);
            Route::apiResource('teachers', TeacherController::class);
        });

        Route::prefix('calendar')->group(function () {
            Route::post('calendars', [CalendarController::class, 'createCalendar']);
            Route::get('calendars/{id}', [CalendarController::class, 'getCalendar']);
            Route::put('calendars/{id}', [CalendarController::class, 'updateCalendar']);
            Route::delete('calendars/{id}', [CalendarController::class, 'deleteCalendar']);
            Route::post('events', [CalendarController::class, 'createEvent']);
            Route::get('events/{id}', [CalendarController::class, 'getEvent']);
            Route::put('events/{id}', [CalendarController::class, 'updateEvent']);
            Route::delete('events/{id}', [CalendarController::class, 'deleteEvent']);
            Route::get('calendars/{calendarId}/events', [CalendarController::class, 'getEventsByDateRange']);
            Route::post('events/{eventId}/register', [CalendarController::class, 'registerForEvent']);
            Route::post('calendars/{calendarId}/share', [CalendarController::class, 'shareCalendar']);
            Route::post('resources/book', [CalendarController::class, 'bookResource']);
        });
    });
});
