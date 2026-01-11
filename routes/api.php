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
use App\Http\Controllers\Api\ParentPortal\ParentPortalController;
use App\Http\Controllers\Api\ParentPortal\ParentCommunicationController;
use App\Http\Controllers\Api\ParentPortal\ParentEngagementController;
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

// Attendance and Leave Management Routes (protected)
Route::group(['middleware' => ['jwt', 'rate.limit']], function () {
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
Route::group(['middleware' => ['jwt', 'rate.limit']], function () {
    Route::prefix('school')->group(function () {
        // Student Management Routes
        Route::apiResource('students', StudentController::class);
        
        // Teacher Management Routes
        Route::apiResource('teachers', TeacherController::class);
    });
});

// Calendar and Event Management Routes (protected)
Route::group(['middleware' => ['jwt', 'rate.limit']], function () {
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

// Parent Portal Routes (protected with JWT and role check)
Route::group(['middleware' => ['jwt', 'rate.limit']], function () {
    Route::prefix('parent')->group(function () {
        // Parent Portal - Student Info & Dashboard
        Route::get('children', [ParentPortalController::class, 'getChildren']);
        Route::get('children/{studentId}/dashboard', [ParentPortalController::class, 'getStudentDashboard']);
        Route::get('children/{studentId}/progress', [ParentPortalController::class, 'getStudentProgress']);
        Route::get('children/{studentId}/transcript', [ParentPortalController::class, 'getStudentTranscript']);
        Route::get('children/{studentId}/attendance', [ParentPortalController::class, 'getStudentAttendance']);
        Route::get('children/{studentId}/assignments', [ParentPortalController::class, 'getStudentAssignments']);
        Route::get('children/{studentId}/behavior', [ParentPortalController::class, 'getStudentBehavior']);

        // Communication - Messages
        Route::post('messages', [ParentCommunicationController::class, 'sendMessage']);
        Route::get('messages', [ParentCommunicationController::class, 'getMessages']);
        Route::get('messages/threads/{threadId}', [ParentCommunicationController::class, 'getMessageThread']);
        Route::put('messages/{messageId}/read', [ParentCommunicationController::class, 'markMessageAsRead']);
        Route::put('messages/read-all', [ParentCommunicationController::class, 'markAllAsRead']);

        // Communication - Conferences
        Route::post('conferences', [ParentCommunicationController::class, 'scheduleConference']);
        Route::put('conferences/{conferenceId}/status', [ParentCommunicationController::class, 'updateConferenceStatus']);
        Route::get('conferences', [ParentCommunicationController::class, 'getConferences']);
        Route::get('conferences/upcoming', [ParentCommunicationController::class, 'getUpcomingConferences']);

        // Engagement - Metrics
        Route::get('engagement/metrics', [ParentEngagementController::class, 'getEngagementMetrics']);

        // Engagement - Events
        Route::post('events/registrations', [ParentEngagementController::class, 'registerForEvent']);
        Route::delete('events/registrations/{eventId}', [ParentEngagementController::class, 'cancelEventRegistration']);
        Route::get('events/registrations', [ParentEngagementController::class, 'getParentRegistrations']);

        // Engagement - Volunteer
        Route::get('volunteer/opportunities', [ParentEngagementController::class, 'getVolunteerOpportunities']);
        Route::post('volunteer/signups', [ParentEngagementController::class, 'signupForVolunteerOpportunity']);
        Route::delete('volunteer/signups/{opportunityId}', [ParentEngagementController::class, 'cancelVolunteerSignup']);
        Route::get('volunteer/signups', [ParentEngagementController::class, 'getParentVolunteerSignups']);

        // Engagement - Notifications Preferences
        Route::put('notifications/preferences', [ParentEngagementController::class, 'updateNotificationPreferences']);
        Route::get('notifications/preferences', [ParentEngagementController::class, 'getNotificationPreferences']);
    });
});
