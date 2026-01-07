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
use App\Http\Controllers\Transportation\TransportationController;
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

// Transportation Management Routes (protected)
Route::group(['middleware' => ['jwt']], function () {
    Route::prefix('transportation')->group(function () {
        // Vehicle Management
        Route::post('vehicles', [TransportationController::class, 'createVehicle']);
        Route::get('vehicles', [TransportationController::class, 'getAllVehicles']);
        Route::get('vehicles/{id}', [TransportationController::class, 'getVehicle']);
        Route::put('vehicles/{id}', [TransportationController::class, 'updateVehicle']);
        Route::delete('vehicles/{id}', [TransportationController::class, 'deleteVehicle']);
        
        // Stop Management
        Route::post('stops', [TransportationController::class, 'createStop']);
        Route::get('stops', [TransportationController::class, 'getAllStops']);
        Route::get('stops/{id}', [TransportationController::class, 'getStop']);
        Route::put('stops/{id}', [TransportationController::class, 'updateStop']);
        Route::delete('stops/{id}', [TransportationController::class, 'deleteStop']);
        
        // Route Management
        Route::post('routes', [TransportationController::class, 'createRoute']);
        Route::get('routes', [TransportationController::class, 'getAllRoutes']);
        Route::get('routes/{id}', [TransportationController::class, 'getRoute']);
        Route::put('routes/{id}', [TransportationController::class, 'updateRoute']);
        Route::delete('routes/{id}', [TransportationController::class, 'deleteRoute']);
        Route::post('routes/{routeId}/stops', [TransportationController::class, 'addStopToRoute']);
        Route::delete('routes/{routeId}/stops/{stopId}', [TransportationController::class, 'removeStopFromRoute']);
        
        // Driver Management
        Route::post('drivers', [TransportationController::class, 'createDriver']);
        Route::get('drivers', [TransportationController::class, 'getAllDrivers']);
        Route::get('drivers/{id}', [TransportationController::class, 'getDriver']);
        Route::put('drivers/{id}', [TransportationController::class, 'updateDriver']);
        Route::delete('drivers/{id}', [TransportationController::class, 'deleteDriver']);
        
        // Schedule Management
        Route::post('schedules', [TransportationController::class, 'createSchedule']);
        Route::get('schedules', [TransportationController::class, 'getAllSchedules']);
        Route::get('schedules/{id}', [TransportationController::class, 'getSchedule']);
        Route::put('schedules/{id}', [TransportationController::class, 'updateSchedule']);
        Route::delete('schedules/{id}', [TransportationController::class, 'deleteSchedule']);
        
        // Assignment Management
        Route::post('assignments', [TransportationController::class, 'createAssignment']);
        Route::get('assignments/{id}', [TransportationController::class, 'getAssignment']);
        Route::put('assignments/{id}', [TransportationController::class, 'updateAssignment']);
        Route::delete('assignments/{id}', [TransportationController::class, 'deleteAssignment']);
        Route::get('students/{studentId}/assignments', [TransportationController::class, 'getStudentAssignments']);
        Route::get('routes/{routeId}/assignments', [TransportationController::class, 'getRouteAssignments']);
        
        // Attendance Management
        Route::post('attendance', [TransportationController::class, 'recordAttendance']);
        Route::get('attendance/{id}', [TransportationController::class, 'getAttendance']);
        Route::get('students/{studentId}/attendance', [TransportationController::class, 'getStudentAttendance']);
        Route::get('routes/{routeId}/attendance', [TransportationController::class, 'getRouteAttendance']);
        
        // Vehicle Tracking
        Route::post('tracking', [TransportationController::class, 'recordVehicleLocation']);
        Route::get('vehicles/{vehicleId}/location', [TransportationController::class, 'getVehicleLocation']);
        
        // Fee Management
        Route::post('fees', [TransportationController::class, 'createFee']);
        Route::get('fees/{id}', [TransportationController::class, 'getFee']);
        Route::post('fees/{id}/pay', [TransportationController::class, 'markFeePaid']);
        Route::get('students/{studentId}/fees', [TransportationController::class, 'getStudentFees']);
        Route::get('fees/pending', [TransportationController::class, 'getPendingFees']);
        
        // Notification Management
        Route::post('notifications', [TransportationController::class, 'createNotification']);
        Route::post('notifications/delay', [TransportationController::class, 'createBusDelayNotification']);
        Route::post('notifications/emergency', [TransportationController::class, 'createEmergencyNotification']);
        Route::post('notifications/{id}/send', [TransportationController::class, 'sendNotification']);
        
        // Reports and Analytics
        Route::get('reports/occupancy/{routeId}', [TransportationController::class, 'getVehicleOccupancy']);
        Route::get('reports/analytics/{routeId}', [TransportationController::class, 'getRouteAnalytics']);
        Route::get('reports/summary', [TransportationController::class, 'getTransportationReport']);
    });
});
