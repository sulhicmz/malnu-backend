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

        // Donation Routes
        Route::post('profiles/{id}/donations', [AlumniController::class, 'recordDonation']);
        Route::get('profiles/{id}/donations', [AlumniController::class, 'getDonations']);

        // Event Routes
        Route::post('events', [AlumniController::class, 'createEvent'])->middleware(['role:Super Admin|Kepala Sekolah|Staf TU|Guru']);
        Route::get('events/{id}', [AlumniController::class, 'getEvent']);
        Route::put('events/{id}', [AlumniController::class, 'updateEvent'])->middleware(['role:Super Admin|Kepala Sekolah|Staf TU|Guru']);
        Route::delete('events/{id}', [AlumniController::class, 'deleteEvent'])->middleware(['role:Super Admin|Kepala Sekolah|Staf TU|Guru']);
        Route::get('events/upcoming', [AlumniController::class, 'getUpcomingEvents']);

        // Event Registration Routes
        Route::post('events/{id}/register', [AlumniController::class, 'registerForEvent']);
        Route::put('registrations/{id}', [AlumniController::class, 'updateRegistration']);
        Route::post('registrations/{id}/cancel', [AlumniController::class, 'cancelRegistration']);

        // Statistics Routes
        Route::get('statistics', [AlumniController::class, 'getStatistics']);
    });
    });

// Mobile API Routes (protected with authentication and mobile middleware)
Route::group(['middleware' => ['jwt', 'rate.limit', 'mobile']], function () {
    Route::prefix('mobile/v1')->group(function () {
        // Student Mobile Routes
        Route::group(['middleware' => ['role:Siswa']], function () {
            Route::prefix('student')->group(function () {
                Route::get('/dashboard', [StudentMobileController::class, 'getDashboard']);
                Route::get('/grades', [StudentMobileController::class, 'getGrades']);
                Route::get('/assignments', [StudentMobileController::class, 'getAssignments']);
                Route::get('/schedule', [StudentMobileController::class, 'getSchedule']);
                Route::get('/attendance', [StudentMobileController::class, 'getAttendance']);
            });
        });

        // Parent Mobile Routes
        Route::group(['middleware' => ['role:Orang Tua']], function () {
            Route::prefix('parent')->group(function () {
                Route::get('/children', [ParentMobileController::class, 'getChildren']);
                Route::get('/children/{childId}/progress', [ParentMobileController::class, 'getChildProgress']);
                Route::get('/children/{childId}/attendance', [ParentMobileController::class, 'getChildAttendance']);
                Route::get('/children/{childId}/grades', [ParentMobileController::class, 'getChildGrades']);
                Route::get('/children/{childId}/fees', [ParentMobileController::class, 'getChildFees']);
            });
        });

        // Teacher Mobile Routes
        Route::group(['middleware' => ['role:Guru']], function () {
            Route::prefix('teacher')->group(function () {
                Route::get('/dashboard', [TeacherMobileController::class, 'getDashboard']);
                Route::get('/classes', [TeacherMobileController::class, 'getClasses']);
                Route::get('/classes/{classId}/students', [TeacherMobileController::class, 'getClassStudents']);
                Route::post('/attendance/mark', [TeacherMobileController::class, 'markAttendance']);
                Route::get('/schedule', [TeacherMobileController::class, 'getSchedule']);
            });
        });

        // Admin Mobile Routes
        Route::group(['middleware' => ['role:Super Admin|Kepala Sekolah|Staf TU']], function () {
            Route::prefix('admin')->group(function () {
                Route::get('/dashboard', [AdminMobileController::class, 'getDashboard']);
                Route::get('/school-info', [AdminMobileController::class, 'getSchoolInfo']);
                Route::get('/statistics', [AdminMobileController::class, 'getStatistics']);
                Route::get('/recent-activities', [AdminMobileController::class, 'getRecentActivities']);
            });
        });

        // Push Notification Routes (all authenticated users)
        Route::prefix('push')->group(function () {
            Route::post('/register', [PushNotificationController::class, 'registerDevice']);
            Route::post('/unregister', [PushNotificationController::class, 'unregisterDevice']);
            Route::put('/preferences', [PushNotificationController::class, 'updatePreferences']);
            Route::get('/preferences', [PushNotificationController::class, 'getPreferences']);
            Route::post('/test', [PushNotificationController::class, 'testPush']);
        });
    });
});

// Behavioral Tracking Routes (protected with role check and privacy controls)
Route::group(['middleware' => ['jwt', 'rate.limit', 'role:Super Admin|Kepala Sekolah|Staf TU|Guru|Wali Murid']], function () {
    Route::prefix('behavioral')->group(function () {
        // Incident Management - Teachers and Admin can log
        Route::post('incidents', [BehavioralTrackingController::class, 'logIncident'])->middleware(['role:Super Admin|Kepala Sekolah|Staf TU|Guru']);
        Route::get('incidents', [BehavioralTrackingController::class, 'getIncidents']);
        Route::post('incidents/{id}/resolve', [BehavioralTrackingController::class, 'resolveIncident'])->middleware(['role:Super Admin|Kepala Sekolah|Staf TU|Guru']);

        // Assessment Management - Counselors can create
        Route::post('assessments', [BehavioralTrackingController::class, 'createAssessment'])->middleware(['role:Super Admin|Kepala Sekolah|Staf TU']);
        Route::get('assessments', [BehavioralTrackingController::class, 'getAssessments']);

        // Session Management - Counselors can manage
        Route::post('sessions', [BehavioralTrackingController::class, 'scheduleSession'])->middleware(['role:Super Admin|Kepala Sekolah|Staf TU']);
        Route::get('sessions', [BehavioralTrackingController::class, 'getSessions']);

        // Intervention Management - Teachers, Counselors, and Admin can manage
        Route::post('interventions', [BehavioralTrackingController::class, 'recordIntervention'])->middleware(['role:Super Admin|Kepala Sekolah|Staf TU|Guru']);
        Route::get('interventions', [BehavioralTrackingController::class, 'getInterventions']);

        // Analytics - All roles can view trends
        Route::get('trends', [BehavioralTrackingController::class, 'getTrends']);
        Route::get('at-risk', [BehavioralTrackingController::class, 'getAtRiskStudents']);

        // Student History - Students can view own, Parents can view children, Counselors/Admin can view all
        Route::get('student/{studentId}/history', [BehavioralTrackingController::class, 'getStudentHistory']);
    });
});

// Club and Extracurricular Activity Management Routes (protected with role check)
Route::group(['middleware' => ['jwt', 'rate.limit', 'role:Super Admin|Kepala Sekolah|Staf TU|Guru']], function () {
    Route::prefix('clubs')->group(function () {
        Route::apiResource('clubs', \App\Http\Controllers\Api\ClubManagement\ClubController::class);
    });

    Route::prefix('activities')->group(function () {
        Route::apiResource('activities', \App\Http\Controllers\Api\ClubManagement\ActivityController::class);
    });

    Route::prefix('club-memberships')->group(function () {
        Route::apiResource('club-memberships', \App\Http\Controllers\Api\ClubManagement\MembershipController::class);
    });

    Route::prefix('club-advisors')->group(function () {
        Route::apiResource('club-advisors', \App\Http\Controllers\Api\ClubManagement\AdvisorController::class);
    });
});

// Grading Management Routes (protected with role check)
Route::group(['middleware' => ['jwt', 'rate.limit', 'role:Super Admin|Kepala Sekolah|Guru']], function () {
    Route::prefix('grades')->group(function () {
        // Grade Management Routes
        Route::apiResource('/', GradeController::class);
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

// Learning Management System Routes (protected with authentication)
Route::group(['middleware' => ['jwt', 'rate.limit']], function () {
    Route::prefix('lms')->group(function () {
        // Course Management Routes
        Route::post('courses', [LMSController::class, 'createCourse'])->middleware(['role:Super Admin|Kepala Sekolah|Staf TU|Guru']);
        Route::get('courses', [LMSController::class, 'getCourses']);
        Route::get('courses/{id}', [LMSController::class, 'getCourse']);
        Route::put('courses/{id}', [LMSController::class, 'updateCourse'])->middleware(['role:Super Admin|Kepala Sekolah|Staf TU|Guru']);

        // Learning Path Management Routes
        Route::post('learning-paths', [LMSController::class, 'createLearningPath'])->middleware(['role:Super Admin|Kepala Sekolah|Staf TU|Guru']);
        Route::get('learning-paths', [LMSController::class, 'getLearningPaths']);
        Route::get('learning-paths/{id}', [LMSController::class, 'getLearningPath']);

        // Enrollment Management Routes
        Route::post('courses/{courseId}/enroll', [LMSController::class, 'enrollStudent'])->middleware(['role:Super Admin|Kepala Sekolah|Staf TU|Guru']);
        Route::get('enrollments', [LMSController::class, 'getEnrollments']);

        // Progress Tracking Routes
        Route::get('courses/{courseId}/progress', [LMSController::class, 'getCourseProgress']);
        Route::put('progress/{enrollmentId}', [LMSController::class, 'updateProgress']);
        Route::post('progress/{enrollmentId}/complete', [LMSController::class, 'completeCourse']);

        // Certificate Management Routes
        Route::get('certificates', [LMSController::class, 'getCertificates']);
    });
});

// Parent Portal Routes (protected with authentication and parent role check)
Route::group(['middleware' => ['jwt', 'rate.limit', 'role:Parent|Ortu']], function () {
    Route::prefix('parent')->group(function () {
        Route::get('/dashboard', [ParentPortalController::class, 'dashboard']);
        Route::prefix('children/{id}')->group(function () {
            Route::get('/grades', [ParentPortalController::class, 'getChildGrades']);
            Route::get('/attendance', [ParentPortalController::class, 'getChildAttendance']);
            Route::get('/assignments', [ParentPortalController::class, 'getChildAssignments']);
        });
    });
});

// Financial Management Routes (protected with role check)
Route::group(['middleware' => ['jwt', 'rate.limit', 'role:Super Admin|Kepala Sekolah|Staf TU|Guru']], function () {
    Route::prefix('financial')->group(function () {
        // Fee Type Management Routes
        Route::apiResource('fee-types', FeeTypeController::class);

        // Fee Structure Management Routes
        Route::apiResource('fee-structures', FeeStructureController::class);

        // Invoice Management Routes
        Route::get('invoices', [InvoiceController::class, 'index']);
        Route::get('invoices/{id}', [InvoiceController::class, 'show']);
        Route::post('invoices', [InvoiceController::class, 'store']);
        Route::put('invoices/{id}', [InvoiceController::class, 'update']);
        Route::delete('invoices/{id}', [InvoiceController::class, 'destroy']);

        // Payment Management Routes
        Route::get('payments', [PaymentController::class, 'index']);
        Route::get('payments/{id}', [PaymentController::class, 'show']);
        Route::post('payments', [PaymentController::class, 'store']);
        Route::put('payments/{id}', [PaymentController::class, 'update']);
    });
});

// Analytics Routes (protected with authentication)
Route::group(['middleware' => ['jwt', 'rate.limit']], function () {
    Route::prefix('analytics')->group(function () {
        Route::get('/dashboard', [AnalyticsController::class, 'dashboard']);
        Route::get('/students/{id}/performance', [AnalyticsController::class, 'studentPerformance']);
        Route::get('/classes/{id}/metrics', [AnalyticsController::class, 'classMetrics']);
        Route::post('/reports/generate', [AnalyticsController::class, 'generateReport']);
        Route::post('/metrics', [AnalyticsController::class, 'recordMetric']);
    });
});
