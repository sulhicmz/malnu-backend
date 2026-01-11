<?php

declare(strict_types=1);

use App\Http\Controllers\IndexController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Attendance\LeaveRequestController;
use App\Http\Controllers\Attendance\LeaveTypeController;
use App\Http\Controllers\Attendance\StaffAttendanceController;
use App\Http\Controllers\Api\SchoolManagement\StudentController;
use App\Http\Controllers\Api\SchoolManagement\TeacherController;
use App\Http\Controllers\Api\SchoolManagement\InventoryController;
use App\Http\Controllers\Calendar\CalendarController;
use App\Http\Controllers\Api\LibraryManagementController;
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

// Library Management Routes (protected)
Route::group(['middleware' => ['jwt', 'rate.limit']], function () {
    Route::prefix('library')->group(function () {
        // Patron Management Routes
        Route::post('patrons', [LibraryManagementController::class, 'createPatron']);
        Route::get('patrons', [LibraryManagementController::class, 'getPatrons']);
        Route::get('patrons/{id}', [LibraryManagementController::class, 'getPatron']);
        Route::put('patrons/{id}', [LibraryManagementController::class, 'updatePatron']);
        Route::delete('patrons/{id}', [LibraryManagementController::class, 'deletePatron']);

        // Circulation Routes
        Route::post('circulation/checkout', [LibraryManagementController::class, 'checkoutBook']);
        Route::post('circulation/return/{loanId}', [LibraryManagementController::class, 'returnBook']);
        Route::post('circulation/renew/{loanId}', [LibraryManagementController::class, 'renewBook']);

        // Hold Management Routes
        Route::post('holds', [LibraryManagementController::class, 'placeHold']);
        Route::post('holds/{holdId}/cancel', [LibraryManagementController::class, 'cancelHold']);
        Route::post('holds/{holdId}/fulfill', [LibraryManagementController::class, 'fulfillHold']);

        // Fine Management Routes
        Route::post('fines', [LibraryManagementController::class, 'createFine']);
        Route::post('fines/{fineId}/pay', [LibraryManagementController::class, 'payFine']);
        Route::post('fines/{fineId}/waive', [LibraryManagementController::class, 'waiveFine']);

        // Inventory Management Routes
        Route::post('inventory', [LibraryManagementController::class, 'createInventoryRecord']);

        // Acquisition Management Routes
        Route::post('acquisitions', [LibraryManagementController::class, 'createAcquisition']);
        Route::post('acquisitions/{id}/receive', [LibraryManagementController::class, 'markAcquisitionReceived']);

        // Reading Program Routes
        Route::post('reading-programs', [LibraryManagementController::class, 'createReadingProgram']);
        Route::post('reading-programs/enroll', [LibraryManagementController::class, 'enrollInProgram']);
        Route::post('reading-programs/participants/{participantId}/books-read', [LibraryManagementController::class, 'recordBooksRead']);

        // Space Management Routes
        Route::post('spaces', [LibraryManagementController::class, 'createSpace']);
        Route::post('spaces/book', [LibraryManagementController::class, 'bookSpace']);
        Route::post('spaces/bookings/{bookingId}/cancel', [LibraryManagementController::class, 'cancelSpaceBooking']);

        // MARC Record Routes
        Route::post('marc-records', [LibraryManagementController::class, 'createMarcRecord']);
        Route::post('marc-records/{recordId}/fields', [LibraryManagementController::class, 'addMarcField']);

        // Analytics Routes
        Route::post('analytics', [LibraryManagementController::class, 'recordAnalytics']);
        Route::get('analytics/popular-books', [LibraryManagementController::class, 'getPopularBooks']);
        Route::get('patrons/{patronId}/reading-history', [LibraryManagementController::class, 'getPatronReadingHistory']);
        Route::post('fines/generate-overdue', [LibraryManagementController::class, 'generateOverdueFines']);
    });
});
