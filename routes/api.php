<?php

declare(strict_types=1);

use App\Http\Controllers\IndexController;
use App\Http\Controllers\Attendance\LeaveRequestController;
use App\Http\Controllers\Attendance\LeaveTypeController;
use App\Http\Controllers\Attendance\StaffAttendanceController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\TeacherController;
use App\Http\Controllers\Api\ClassController;
use App\Http\Controllers\Api\SubjectController;
use App\Http\Controllers\Api\ScheduleController;
use App\Http\Controllers\Api\GradeController;
use App\Http\Controllers\Api\ParentController;
use App\Http\Controllers\Api\DigitalLibraryController;
use App\Http\Controllers\Api\ELearningController;
use App\Http\Controllers\Api\OnlineExamController;
use App\Http\Controllers\Api\PpdbController;
use App\Http\Controllers\Api\CareerDevelopmentController;
use App\Http\Controllers\Api\MonetizationController;
use Hyperf\Support\Facades\Route;

Route::any('/', [IndexController::class, 'index']);

// Attendance and Leave Management Routes
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

// Student Management Routes
Route::apiResource('students', StudentController::class);

// Teacher Management Routes
Route::apiResource('teachers', TeacherController::class);

// Class Management Routes
Route::apiResource('classes', ClassController::class);

// Subject Management Routes
Route::apiResource('subjects', SubjectController::class);

// Schedule Management Routes
Route::apiResource('schedules', ScheduleController::class);

// Grade Management Routes
Route::apiResource('grades', GradeController::class);

// Parent Portal Routes
Route::apiResource('parents', ParentController::class);

// Digital Library Routes
Route::apiResource('books', DigitalLibraryController::class);

// E-Learning Routes
Route::apiResource('assignments', ELearningController::class);

// Online Exam Routes
Route::apiResource('exams', OnlineExamController::class);

// PPDB Routes
Route::apiResource('ppdb-registrations', PpdbController::class);

// Career Development Routes
Route::apiResource('career-assessments', CareerDevelopmentController::class);

// Monetization Routes
Route::apiResource('transactions', MonetizationController::class);