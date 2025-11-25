<?php

declare(strict_types=1);

use App\Http\Controllers\IndexController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Attendance\LeaveRequestController;
use App\Http\Controllers\Attendance\LeaveTypeController;
use App\Http\Controllers\Attendance\StaffAttendanceController;
use App\Http\Controllers\Api\SchoolManagement\StudentController;
use App\Http\Controllers\Api\SchoolManagement\TeacherController;
use App\Http\Controllers\Api\SchoolManagement\AcademicRecordController;
use App\Http\Controllers\Api\SchoolManagement\EnrollmentController;
use App\Http\Controllers\Api\SchoolManagement\StudentAnalyticsController;
use App\Http\Controllers\Api\SchoolManagement\StudentDocumentController;
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
    
    // Student Information System (SIS) Routes
    Route::prefix('sis')->group(function () {
        // Academic Records Management
        Route::get('students/{studentId}/academic-record', [AcademicRecordController::class, 'getAcademicRecord']);
        Route::get('students/{studentId}/transcript', [AcademicRecordController::class, 'generateTranscript']);
        Route::get('classes/{classId}/academic-records', [AcademicRecordController::class, 'getClassAcademicRecords']);
        Route::put('grades/{gradeId}', [AcademicRecordController::class, 'updateGrade']);
        
        // Enrollment Management
        Route::get('students/{studentId}/enrollment', [EnrollmentController::class, 'getEnrollmentDetails']);
        Route::put('students/{studentId}/enrollment-status', [EnrollmentController::class, 'updateEnrollmentStatus']);
        Route::post('students/{studentId}/assign-class', [EnrollmentController::class, 'assignToClass']);
        Route::get('students/academic-year/{academicYear}', [EnrollmentController::class, 'getStudentsByAcademicYear']);
        Route::get('enrollment/stats', [EnrollmentController::class, 'getEnrollmentStats']);
        
        // Student Analytics
        Route::get('students/{studentId}/analytics', [StudentAnalyticsController::class, 'getStudentPerformanceAnalytics']);
        Route::get('classes/{classId}/analytics', [StudentAnalyticsController::class, 'getClassPerformanceAnalytics']);
        
        // Student Documents & Portfolios
        Route::get('students/{studentId}/documents', [StudentDocumentController::class, 'getStudentDocuments']);
        Route::post('students/{studentId}/documents', [StudentDocumentController::class, 'addStudentDocument']);
        Route::put('documents/{documentId}', [StudentDocumentController::class, 'updateStudentDocument']);
        Route::delete('documents/{documentId}', [StudentDocumentController::class, 'deleteStudentDocument']);
        Route::get('students/{studentId}/documents/type/{type}', [StudentDocumentController::class, 'getDocumentsByType']);
        Route::post('students/{studentId}/certificates/generate', [StudentDocumentController::class, 'generateCertificate']);
    });
});
