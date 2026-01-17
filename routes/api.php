<?php

declare(strict_types=1);

use App\Http\Controllers\IndexController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Attendance\LeaveRequestController;
use App\Http\Controllers\Attendance\LeaveTypeController;
use App\Http\Controllers\Attendance\StaffAttendanceController;
use App\Http\Controllers\Api\SchoolManagement\StudentController;
use App\Http\Controllers\Api\SchoolManagement\TeacherController;
use App\Http\Controllers\Api\SchoolManagement\InventoryController;
use App\Http\Controllers\Api\SchoolManagement\AcademicRecordsController;
use App\Http\Controllers\Api\SchoolManagement\StudentController;
use App\Http\Controllers\Api\SchoolManagement\TeacherController;
use App\Http\Controllers\Api\SchoolManagement\InventoryController;
use App\Http\Controllers\Api\SchoolManagement\AcademicRecordsController;
use App\Http\Controllers\Calendar\CalendarController;
use App\Http\Controllers\Api\Notification\NotificationController;
use App\Http\Controllers\Api\TransportationController;
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

// Attendance and Leave Management Routes (protected with role check)
Route::group(['middleware' => ['jwt', 'rate.limit', 'role:Super Admin|Kepala Sekolah|Staf TU|Guru']], function () {
    Route::any('/', [IndexController::class, 'index']);

    Route::prefix('attendance')->group(function () {
        // Staff Attendance Routes
        Route::apiResource('staff-attendances', StaffAttendanceController::class);
        Route::post('staff-attendances/mark-attendance', [StaffAttendanceController::class, 'markAttendance']);

        // Student Attendance Routes
        Route::post('student/mark', [AttendanceController::class, 'markAttendance']);
        Route::post('student/bulk-mark', [AttendanceController::class, 'markBulkAttendance']);
        Route::get('student/{id}', [AttendanceController::class, 'getStudentAttendance']);
        Route::get('student/{id}/statistics', [AttendanceController::class, 'getAttendanceStatistics']);
        Route::get('class/{id}', [AttendanceController::class, 'getClassAttendance']);
        Route::get('class/{id}/report', [AttendanceController::class, 'getAttendanceReport']);
        Route::get('chronic-absentees', [AttendanceController::class, 'getChronicAbsentees']);

        // Leave Management Routes
        Route::apiResource('leave-types', LeaveTypeController::class);
        Route::apiResource('leave-requests', LeaveRequestController::class);
        Route::post('leave-requests/{id}/approve', [LeaveRequestController::class, 'approve']);
        Route::post('leave-requests/{id}/reject', [LeaveRequestController::class, 'reject']);
    });
});

// School Management Routes (protected with role check)
Route::group(['middleware' => ['jwt', 'rate.limit', 'role:Super Admin|Kepala Sekolah|Staf TU']], function () {
    Route::prefix('school')->group(function () {
        // Student Management Routes
        Route::apiResource('students', StudentController::class);

        // Teacher Management Routes
        Route::apiResource('teachers', TeacherController::class);

        // Inventory Management Routes
        Route::apiResource('inventory', InventoryController::class);
        Route::post('inventory/{id}/assign', [InventoryController::class, 'assign']);
        Route::post('inventory/{id}/return', [InventoryController::class, 'returnItem']);
        Route::post('inventory/{id}/maintenance', [InventoryController::class, 'maintenance']);
        Route::get('inventory/{id}/assignments', [InventoryController::class, 'getAssignments']);
        Route::get('inventory/{id}/maintenance', [InventoryController::class, 'getMaintenanceRecords']);

         // Academic Records Routes
        Route::prefix('students/{studentId}')->group(function () {
            Route::get('gpa', [AcademicRecordsController::class, 'calculateGPA']);
            Route::get('academic-performance', [AcademicRecordsController::class, 'getAcademicPerformance']);
            Route::get('class-rank/{classId}', [AcademicRecordsController::class, 'getClassRank']);
            Route::get('transcript', [AcademicRecordsController::class, 'generateTranscript']);
            Route::get('report-card/{semester}/{academicYear}', [AcademicRecordsController::class, 'generateReportCard']);
            Route::get('subject-grades/{subjectId}', [AcademicRecordsController::class, 'getSubjectGrades']);
            Route::get('grades-history', [AcademicRecordsController::class, 'getGradesHistory']);
        });

        // Transportation Management Routes (protected with JWT authentication)
        Route::group(['middleware' => ['jwt', 'rate.limit']], function () {
            Route::prefix('transportation')->group(function () {
                Route::get('routes', [TransportationController::class, 'index']);
                Route::post('routes', [TransportationController::class, 'store']);
                Route::get('routes/{id}', [TransportationController::class, 'show']);
                Route::put('routes/{id}', [TransportationController::class, 'update']);
                Route::delete('routes/{id}', [TransportationController::class, 'destroy']);
                Route::post('routes/{id}/statistics', [TransportationController::class, 'getRouteStatistics']);

                Route::post('registrations', [TransportationController::class, 'registerStudent']);
                Route::get('registrations/{studentId}', [TransportationController::class, 'getStudentRegistrations']);
                Route::post('registrations/{registrationId}/assign', [TransportationController::class, 'assignDriver']);

                Route::get('vehicles', [TransportationController::class, 'vehicles']);
                Route::post('vehicles', [TransportationController::class, 'storeVehicle']);

                Route::get('drivers', [TransportationController::class, 'drivers']);
                Route::post('drivers', [TransportationController::class, 'storeDriver']);

                Route::post('fees', [TransportationController::class, 'createFee']);
                Route::put('fees/{feeId}', [TransportationController::class, 'markFeePaid']);

                Route::get('incidents', [TransportationController::class, 'incidents']);
                Route::post('incidents', [TransportationController::class, 'storeIncident']);
                Route::put('incidents/{id}', [TransportationController::class, 'updateIncident']);
            });
        });
    });
});

// Calendar and Event Management Routes (protected with role check)
Route::group(['middleware' => ['jwt', 'rate.limit']], function () {
    Route::prefix('calendar')->group(function () {
        // Calendar Management - Write operations require specific roles
        Route::post('calendars', [CalendarController::class, 'createCalendar'])->middleware(['role:Super Admin|Kepala Sekolah|Staf TU|Guru']);
        Route::get('calendars/{id}', [CalendarController::class, 'getCalendar']);
        Route::put('calendars/{id}', [CalendarController::class, 'updateCalendar'])->middleware(['role:Super Admin|Kepala Sekolah|Staf TU|Guru']);
        Route::delete('calendars/{id}', [CalendarController::class, 'deleteCalendar'])->middleware(['role:Super Admin|Kepala Sekolah|Staf TU|Guru']);

        // Event Management - Write operations require specific roles
        Route::post('events', [CalendarController::class, 'createEvent'])->middleware(['role:Super Admin|Kepala Sekolah|Staf TU|Guru']);
        Route::get('events/{id}', [CalendarController::class, 'getEvent']);
        Route::put('events/{id}', [CalendarController::class, 'updateEvent'])->middleware(['role:Super Admin|Kepala Sekolah|Staf TU|Guru']);
        Route::delete('events/{id}', [CalendarController::class, 'deleteEvent'])->middleware(['role:Super Admin|Kepala Sekolah|Staf TU|Guru']);
        Route::get('calendars/{calendarId}/events', [CalendarController::class, 'getEventsByDateRange']);

        // Event Registration - All authenticated users can register
        Route::post('events/{eventId}/register', [CalendarController::class, 'registerForEvent']);

        // Calendar Sharing - Write operation requires specific roles
        Route::post('calendars/{calendarId}/share', [CalendarController::class, 'shareCalendar'])->middleware(['role:Super Admin|Kepala Sekolah|Staf TU|Guru']);

        // Resource Booking - Write operation requires specific roles
        Route::post('resources/book', [CalendarController::class, 'bookResource'])->middleware(['role:Super Admin|Kepala Sekolah|Staf TU|Guru']);
    });
});

// Notification Routes (protected with authentication)
Route::group(['middleware' => ['jwt', 'rate.limit']], function () {
    Route::prefix('notifications')->group(function () {
        Route::post('/', [NotificationController::class, 'create']);
        Route::post('/send', [NotificationController::class, 'send']);
        Route::post('/emergency', [NotificationController::class, 'sendEmergency']);
        Route::get('/my', [NotificationController::class, 'index']);
        Route::get('/{id}', [NotificationController::class, 'show']);
        Route::put('/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::put('/read-all', [NotificationController::class, 'markAllAsRead']);
        Route::get('/{id}/stats', [NotificationController::class, 'getDeliveryStats']);
        Route::post('/templates', [NotificationController::class, 'createTemplate']);
        Route::get('/templates', [NotificationController::class, 'getTemplates']);
        Route::put('/preferences', [NotificationController::class, 'updatePreferences']);
        Route::get('/preferences', [NotificationController::class, 'getPreferences']);
    });
});
