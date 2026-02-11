<?php

declare(strict_types=1);

use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Notification\NotificationController;
use App\Http\Controllers\Api\PasswordChangeController;
use App\Http\Controllers\Api\PasswordResetController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\SchoolManagement\AcademicRecordsController;
use App\Http\Controllers\Api\SchoolManagement\InventoryController;
use App\Http\Controllers\Api\SchoolManagement\StudentController;
use App\Http\Controllers\Api\SchoolManagement\TeacherController;
use App\Http\Controllers\Api\SchoolManagement\TimetableController;
use App\Http\Controllers\Attendance\LeaveRequestController;
use App\Http\Controllers\Attendance\LeaveTypeController;
use App\Http\Controllers\Attendance\StaffAttendanceController;
use App\Http\Controllers\Api\SchoolManagement\ClassController;
use App\Http\Controllers\Api\SchoolManagement\SubjectController;
use App\Http\Controllers\Api\Grading\GradeController;
use App\Http\Controllers\Api\SchoolManagement\AssetCategoryController;
use App\Http\Controllers\Api\SchoolManagement\AssetAssignmentController;
use App\Http\Controllers\Api\SchoolManagement\AssetMaintenanceController;
use App\Http\Controllers\Calendar\CalendarController;
use App\Http\Controllers\IndexController;
use App\Http\Controllers\Api\LMSController;
use App\Http\Controllers\Api\Mobile\StudentMobileController;
use App\Http\Controllers\Api\Mobile\ParentMobileController;
use App\Http\Controllers\Api\Mobile\TeacherMobileController;
use App\Http\Controllers\Api\Mobile\AdminMobileController;
use App\Http\Controllers\Api\Mobile\PushNotificationController;
use App\Http\Controllers\Api\ParentPortal\ParentPortalController;
use App\Http\Controllers\Api\FinancialManagement\FeeTypeController;
use App\Http\Controllers\Api\FinancialManagement\FeeStructureController;
use App\Http\Controllers\Api\FinancialManagement\InvoiceController;
use App\Http\Controllers\Api\FinancialManagement\PaymentController;
use App\Http\Controllers\BehavioralTrackingController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\Alumni\AlumniController;
use App\Http\Controllers\Api\ComplianceController;
use Hyperf\Support\Facades\Route;

// Public routes (no authentication required)
Route::group(['middleware' => ['input.sanitization', 'rate.limit']], function () {
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/password/forgot', [PasswordResetController::class, 'requestPasswordReset']);
    Route::post('/auth/password/reset', [PasswordResetController::class, 'resetPassword']);
});

// Protected routes (JWT authentication required)
Route::group(['middleware' => ['jwt', 'rate.limit']], function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);
    Route::get('/auth/me', [ProfileController::class, 'me']);
    Route::post('/auth/password/change', [PasswordChangeController::class, 'changePassword']);
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

        // Class Management Routes
        Route::apiResource('classes', ClassController::class);

        // Subject Management Routes
        Route::apiResource('subjects', SubjectController::class);

        // Timetable Management Routes
        Route::prefix('timetable')->group(function () {
            Route::post('generate', [TimetableController::class, 'generate']);
            Route::post('validate', [TimetableController::class, 'validate']);
            Route::post('conflicts', [TimetableController::class, 'detectConflicts']);
            Route::get('available-slots', [TimetableController::class, 'getAvailableSlots']);
            Route::get('class/{classId}/schedule', [TimetableController::class, 'getClassSchedule']);
            Route::get('teacher/{teacherId}/schedule', [TimetableController::class, 'getTeacherSchedule']);
            Route::apiResource('schedules', TimetableController::class, ['only' => ['store', 'update', 'destroy']]);
        });

        // Inventory Management Routes
        Route::apiResource('inventory', InventoryController::class);
        Route::post('inventory/{id}/assign', [InventoryController::class, 'assign']);
        Route::post('inventory/{id}/return', [InventoryController::class, 'returnItem']);
        Route::post('inventory/{id}/maintenance', [InventoryController::class, 'maintenance']);
        Route::get('inventory/{id}/assignments', [InventoryController::class, 'getAssignments']);
        Route::get('inventory/{id}/maintenance', [InventoryController::class, 'getMaintenanceRecords']);
        Route::get('inventory/valuation', [InventoryController::class, 'getValuation']);
        Route::get('inventory/depreciation', [InventoryController::class, 'getDepreciation']);
        Route::get('inventory/usage', [InventoryController::class, 'getUsageStatistics']);
        Route::apiResource('asset-categories', AssetCategoryController::class);
        Route::apiResource('asset-assignments', AssetAssignmentController::class);
        Route::apiResource('asset-maintenance', AssetMaintenanceController::class);

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
    });
});

// Alumni Management Routes (protected with role check)
Route::group(['middleware' => ['jwt', 'rate.limit', 'role:Super Admin|Kepala Sekolah|Staf TU|Guru']], function () {
    Route::prefix('alumni')->group(function () {
        // Alumni Profile Routes
        Route::post('profiles', [AlumniController::class, 'createProfile'])->middleware(['role:Super Admin|Kepala Sekolah|Staf TU|Guru']);
        Route::get('profiles/{id}', [AlumniController::class, 'getProfile']);
        Route::put('profiles/{id}', [AlumniController::class, 'updateProfile'])->middleware(['role:Super Admin|Kepala Sekolah|Staf TU|Guru']);
        Route::delete('profiles/{id}', [AlumniController::class, 'deleteProfile'])->middleware(['role:Super Admin|Kepala Sekolah|Staf TU|Guru']);
        Route::get('directory', [AlumniController::class, 'getDirectory']);

        // Career Routes
        Route::post('profiles/{id}/careers', [AlumniController::class, 'addCareer']);
        Route::put('careers/{id}', [AlumniController::class, 'updateCareer']);
        Route::delete('careers/{id}', [AlumniController::class, 'deleteCareer']);

        // Achievement Routes
        Route::post('profiles/{id}/achievements', [AlumniController::class, 'addAchievement']);
        Route::put('achievements/{id}', [AlumniController::class, 'updateAchievement']);
        Route::delete('achievements/{id}', [AlumniController::class, 'deleteAchievement']);

        // Mentorship Routes
        Route::post('mentorships', [AlumniController::class, 'createMentorship']);
        Route::put('mentorships/{id}', [AlumniController::class, 'updateMentorship']);
        Route::get('profiles/{id}/mentorships', [AlumniController::class, 'getAlumniMentorships']);
        Route::get('mentorships/student/{id}', [AlumniController::class, 'getStudentMentorships']);
    });
});
