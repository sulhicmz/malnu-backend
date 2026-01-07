<?php

declare(strict_types=1);

use App\Http\Controllers\IndexController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Attendance\LeaveRequestController;
use App\Http\Controllers\Attendance\LeaveTypeController;
use App\Http\Controllers\Attendance\StaffAttendanceController;
use App\Http\Controllers\Api\SchoolManagement\StudentController;
use App\Http\Controllers\Api\SchoolManagement\TeacherController;
use App\Http\Controllers\Api\HealthManagementController;
use App\Http\Controllers\Calendar\CalendarController;
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

// Health Management Routes (protected)
Route::group(['middleware' => ['jwt']], function () {
    Route::prefix('health')->group(function () {
        // Health Records
        Route::get('students/{studentId}/health-record', [HealthManagementController::class, 'getHealthRecord']);
        Route::post('health-records', [HealthManagementController::class, 'createHealthRecord']);

        // Medications
        Route::get('students/{studentId}/medications', [HealthManagementController::class, 'getMedications']);
        Route::post('medications', [HealthManagementController::class, 'createMedication']);
        Route::put('medications/{id}', [HealthManagementController::class, 'updateMedication']);
        Route::delete('medications/{id}', [HealthManagementController::class, 'deleteMedication']);

        // Immunizations
        Route::get('students/{studentId}/immunizations', [HealthManagementController::class, 'getImmunizations']);
        Route::get('students/{studentId}/immunization-compliance', [HealthManagementController::class, 'getImmunizationCompliance']);
        Route::post('immunizations', [HealthManagementController::class, 'createImmunization']);

        // Allergies
        Route::get('students/{studentId}/allergies', [HealthManagementController::class, 'getAllergies']);
        Route::get('students/{studentId}/severe-allergies-alert', [HealthManagementController::class, 'getSevereAllergiesAlert']);
        Route::post('allergies', [HealthManagementController::class, 'createAllergy']);

        // Emergency Contacts
        Route::get('students/{studentId}/emergency-contacts', [HealthManagementController::class, 'getEmergencyContacts']);
        Route::post('emergency-contacts', [HealthManagementController::class, 'createEmergencyContact']);

        // Medical Incidents
        Route::get('medical-incidents', [HealthManagementController::class, 'getMedicalIncidents']);
        Route::post('medical-incidents', [HealthManagementController::class, 'createMedicalIncident']);

        // Reports and Analytics
        Route::get('students/{studentId}/health-report', [HealthManagementController::class, 'getHealthReport']);
        Route::get('health-summary', [HealthManagementController::class, 'getHealthSummary']);
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
