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
use App\Http\Controllers\Alumni\AlumniManagementController;
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

// School Management Routes (protected)
Route::group(['middleware' => ['jwt']], function () {
    Route::prefix('school')->group(function () {
        // Student Management Routes
        Route::apiResource('students', StudentController::class);
        
        // Teacher Management Routes
        Route::apiResource('teachers', TeacherController::class);
    });
});

// Calendar and Event Management Routes (protected)
Route::group(['middleware' => ['jwt']], function () {
    Route::prefix('calendar')->group(function () {
        // Calendar Management
        Route::post('calendars', [CalendarController::class, 'createCalendar']);
        Route::get('calendars/{id}', [CalendarController::class, 'getCalendar']);
        Route::put('calendars/{id}', [CalendarController::class, 'updateCalendar']);
        Route::delete('calendars/{id}', [CalendarController::class, 'deleteCalendar']);

        // Event Management
        Route::post('events', [CalendarController::class, 'createEvent']);
        Route::get('events/{id}', [CalendarController::class, 'getEvent']);
        Route::put('events/{id}', [CalendarController::class, 'updateEvent']);
        Route::delete('events/{id}', [CalendarController::class, 'deleteEvent']);
        Route::get('calendars/{calendarId}/events', [CalendarController::class, 'getEventsByDateRange']);

        // Event Registration
        Route::post('events/{eventId}/register', [CalendarController::class, 'registerForEvent']);

        // Calendar Sharing
        Route::post('calendars/{calendarId}/share', [CalendarController::class, 'shareCalendar']);

        // Resource Booking
        Route::post('resources/book', [CalendarController::class, 'bookResource']);
    });
});

// Alumni Management Routes (protected)
Route::group(['middleware' => ['jwt']], function () {
    Route::prefix('alumni')->group(function () {
        // Alumni Profiles
        Route::post('profiles', [AlumniManagementController::class, 'createAlumni']);
        Route::get('profiles', [AlumniManagementController::class, 'getAllAlumni']);
        Route::get('profiles/{id}', [AlumniManagementController::class, 'getAlumni']);
        Route::put('profiles/{id}', [AlumniManagementController::class, 'updateAlumni']);
        Route::delete('profiles/{id}', [AlumniManagementController::class, 'deleteAlumni']);
        Route::post('profiles/{id}/verify', [AlumniManagementController::class, 'verifyAlumni']);
        Route::put('profiles/{id}/privacy', [AlumniManagementController::class, 'updatePrivacySettings']);

        // Career Management
        Route::post('careers', [AlumniManagementController::class, 'createCareer']);
        Route::put('careers/{id}', [AlumniManagementController::class, 'updateCareer']);
        Route::delete('careers/{id}', [AlumniManagementController::class, 'deleteCareer']);

        // Donation Management
        Route::post('donations', [AlumniManagementController::class, 'createDonation']);
        Route::get('donations', [AlumniManagementController::class, 'getDonations']);

        // Event Management
        Route::post('events', [AlumniManagementController::class, 'createEvent']);
        Route::get('events', [AlumniManagementController::class, 'getEvents']);
        Route::put('events/{id}', [AlumniManagementController::class, 'updateEvent']);
        Route::delete('events/{id}', [AlumniManagementController::class, 'deleteEvent']);

        // Event Registration
        Route::post('event-registrations', [AlumniManagementController::class, 'registerForEvent']);
        Route::delete('event-registrations/{id}', [AlumniManagementController::class, 'cancelRegistration']);
        Route::post('event-registrations/{id}/check-in', [AlumniManagementController::class, 'checkInAttendee']);

        // Mentorship Management
        Route::post('mentorships', [AlumniManagementController::class, 'createMentorship']);
        Route::get('mentorships', [AlumniManagementController::class, 'getMentorships']);
        Route::put('mentorships/{id}', [AlumniManagementController::class, 'updateMentorship']);
        Route::post('mentorships/{id}/activate', [AlumniManagementController::class, 'activateMentorship']);
        Route::post('mentorships/{id}/complete', [AlumniManagementController::class, 'completeMentorship']);
        Route::get('available-mentors', [AlumniManagementController::class, 'findAvailableMentors']);

        // Engagement Tracking
        Route::post('engagements', [AlumniManagementController::class, 'createEngagement']);
        Route::get('engagements', [AlumniManagementController::class, 'getEngagements']);

        // Directory and Reports
        Route::get('directory', [AlumniManagementController::class, 'getAlumniDirectory']);
        Route::get('reports/engagement', [AlumniManagementController::class, 'getEngagementReport']);
        Route::get('reports/donation', [AlumniManagementController::class, 'getDonationReport']);
    });
});
