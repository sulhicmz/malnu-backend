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

// School Administration and Governance Routes (protected)
Route::group(['middleware' => ['jwt', 'rate.limit']], function () {
    Route::prefix('administration')->group(function () {
        // Index route
        Route::get('/', [App\Http\Controllers\Api\SchoolAdministrationController::class, 'index']);

        // Compliance and Accreditation Routes
        Route::get('compliance', [App\Http\Controllers\Api\SchoolAdministrationController::class, 'index']);
        Route::post('compliance', [App\Http\Controllers\Api\SchoolAdministrationController::class, 'createComplianceRequirement']);
        Route::put('compliance/{id}', [App\Http\Controllers\Api\SchoolAdministrationController::class, 'updateComplianceRequirement']);
        Route::delete('compliance/{id}', [App\Http\Controllers\Api\SchoolAdministrationController::class, 'deleteComplianceRequirement']);

        Route::get('accreditation', [App\Http\Controllers\Api\SchoolAdministrationController::class, 'index']);
        Route::post('accreditation', [App\Http\Controllers\Api\SchoolAdministrationController::class, 'createAccreditationStandard']);

        // Policy Management Routes
        Route::get('policies', [App\Http\Controllers\Api\SchoolAdministrationController::class, 'index']);
        Route::post('policies', [App\Http\Controllers\Api\SchoolAdministrationController::class, 'createPolicy']);
        Route::put('policies/{id}', [App\Http\Controllers\Api\SchoolAdministrationController::class, 'updatePolicy']);

        // Staff Evaluation Routes
        Route::get('evaluations', [App\Http\Controllers\Api\SchoolAdministrationController::class, 'index']);
        Route::post('evaluations', [App\Http\Controllers\Api\SchoolAdministrationController::class, 'createEvaluation']);
        Route::put('evaluations/{id}', [App\Http\Controllers\Api\SchoolAdministrationController::class, 'updateEvaluation']);

        // Professional Development Routes
        Route::get('professional-development', [App\Http\Controllers\Api\SchoolAdministrationController::class, 'index']);
        Route::post('professional-development', [App\Http\Controllers\Api\SchoolAdministrationController::class, 'createProfessionalDevelopment']);
        Route::put('professional-development/{id}', [App\Http\Controllers\Api\SchoolAdministrationController::class, 'updateProfessionalDevelopment']);

        // Budget Management Routes
        Route::get('budget', [App\Http\Controllers\Api\SchoolAdministrationController::class, 'index']);
        Route::post('budget', [App\Http\Controllers\Api\SchoolAdministrationController::class, 'createBudgetAllocation']);
        Route::put('budget/{id}', [App\Http\Controllers\Api\SchoolAdministrationController::class, 'updateBudgetAllocation']);

        // Expense Management Routes
        Route::get('expenses', [App\Http\Controllers\Api\SchoolAdministrationController::class, 'index']);
        Route::post('expenses', [App\Http\Controllers\Api\SchoolAdministrationController::class, 'createExpense']);
        Route::post('expenses/{id}/approve', [App\Http\Controllers\Api\SchoolAdministrationController::class, 'approveExpense']);
        Route::post('expenses/{id}/reject', [App\Http\Controllers\Api\SchoolAdministrationController::class, 'rejectExpense']);

        // Inventory Management Routes
        Route::get('inventory', [App\Http\Controllers\Api\SchoolAdministrationController::class, 'index']);
        Route::post('inventory', [App\Http\Controllers\Api\SchoolAdministrationController::class, 'createInventoryItem']);
        Route::put('inventory/{id}', [App\Http\Controllers\Api\SchoolAdministrationController::class, 'updateInventoryItem']);

        // Vendor Contract Routes
        Route::get('vendors', [App\Http\Controllers\Api\SchoolAdministrationController::class, 'index']);
        Route::post('vendors', [App\Http\Controllers\Api\SchoolAdministrationController::class, 'createVendorContract']);
        Route::put('vendors/{id}', [App\Http\Controllers\Api\SchoolAdministrationController::class, 'updateVendorContract']);

        // Institutional Metrics Routes
        Route::get('metrics', [App\Http\Controllers\Api\SchoolAdministrationController::class, 'index']);
        Route::post('metrics', [App\Http\Controllers\Api\SchoolAdministrationController::class, 'createMetric']);
        Route::put('metrics/{id}', [App\Http\Controllers\Api\SchoolAdministrationController::class, 'updateMetric']);

        // Reports Routes
        Route::get('reports', [App\Http\Controllers\Api\SchoolAdministrationController::class, 'getReports']);
    });
});

