<?php

declare(strict_types=1);

use App\Http\Controllers\IndexController;
use App\Http\Controllers\Mobile\AuthController;
use App\Http\Controllers\Mobile\StudentController;
use App\Http\Controllers\Mobile\ParentController;
use App\Http\Controllers\Mobile\TeacherController;
use App\Http\Controllers\Mobile\NotificationController;
use Hypervel\Support\Facades\Route;

// Public routes
Route::group(['prefix' => 'v1'], function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']); // If registration is needed
    Route::post('refresh', [AuthController::class, 'refresh'])->middleware('auth:jwt');
});

// Protected routes
Route::group(['prefix' => 'v1', 'middleware' => ['auth:jwt', 'throttle:120,1']], function () {
    // Authentication routes
    Route::get('me', [AuthController::class, 'me']);
    Route::post('logout', [AuthController::class, 'logout']);
    
    // Student routes
    Route::group(['prefix' => 'student'], function () {
        Route::get('profile', [StudentController::class, 'profile']);
        Route::get('grades', [StudentController::class, 'grades']);
        Route::get('assignments', [StudentController::class, 'assignments']);
        Route::get('schedule', [StudentController::class, 'schedule']);
        Route::get('attendance', [StudentController::class, 'attendance']);
        Route::get('learning-materials', [StudentController::class, 'learningMaterials']);
        Route::get('exam-results', [StudentController::class, 'examResults']);
    });
    
    // Parent routes
    Route::group(['prefix' => 'parent'], function () {
        Route::get('profile', [ParentController::class, 'profile']);
        Route::get('student-info/{studentId?}', [ParentController::class, 'studentInfo']);
        Route::get('student-grades/{studentId?}', [ParentController::class, 'studentGrades']);
        Route::get('student-attendance/{studentId?}', [ParentController::class, 'studentAttendance']);
        Route::get('student-assignments/{studentId?}', [ParentController::class, 'studentAssignments']);
        Route::get('student-learning-materials/{studentId?}', [ParentController::class, 'studentLearningMaterials']);
        Route::get('student-exam-results/{studentId?}', [ParentController::class, 'studentExamResults']);
        Route::get('student-fees/{studentId?}', [ParentController::class, 'studentFees']);
    });
    
    // Teacher routes
    Route::group(['prefix' => 'teacher'], function () {
        Route::get('profile', [TeacherController::class, 'profile']);
        Route::get('classes', [TeacherController::class, 'classes']);
        Route::get('class-students/{classId}', [TeacherController::class, 'classStudents']);
        Route::get('schedule', [TeacherController::class, 'schedule']);
        Route::post('record-attendance/{classId}', [TeacherController::class, 'recordAttendance'])->middleware('throttle:60,1');
        Route::get('class-attendance/{classId}/{date?}', [TeacherController::class, 'classAttendance']);
        Route::post('create-assignment/{classId}', [TeacherController::class, 'createAssignment'])->middleware('throttle:30,1');
        Route::get('class-assignments/{classId}', [TeacherController::class, 'classAssignments']);
        Route::post('record-grades/{classId}/{subjectId}', [TeacherController::class, 'recordGrades'])->middleware('throttle:100,1');
    });
    
    // Notification routes
    Route::group(['prefix' => 'notifications'], function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('unread', [NotificationController::class, 'unreadCount']);
        Route::post('{id}/read', [NotificationController::class, 'markAsRead'])->middleware('throttle:60,1');
    });
});

// Public index route
Route::any('/', [IndexController::class, 'index']);
