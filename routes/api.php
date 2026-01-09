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
use App\Http\Controllers\Api\AIAssistantController;
use App\Http\Controllers\Api\ParentPortalController;
use App\Http\Controllers\Api\DigitalLibraryController;
use App\Http\Controllers\Api\PPDBController;
use App\Http\Controllers\Api\MonetizationController;
use App\Http\Controllers\Api\CareerDevelopmentController;
use App\Http\Controllers\Api\OnlineExamController;
use App\Http\Controllers\Api\GradingController;
use App\Http\Controllers\Api\ELearningController;
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

// AI Assistant Routes (protected)
Route::group(['middleware' => ['jwt', 'rate.limit']], function () {
    Route::prefix('ai-assistant')->group(function () {
        Route::get('sessions', [AIAssistantController::class, 'index']);
        Route::post('sessions', [AIAssistantController::class, 'store']);
        Route::get('sessions/{id}', [AIAssistantController::class, 'show']);
        Route::put('sessions/{id}', [AIAssistantController::class, 'update']);
        Route::delete('sessions/{id}', [AIAssistantController::class, 'destroy']);
    });
});

// Parent Portal Routes (protected)
Route::group(['middleware' => ['jwt', 'rate.limit']], function () {
    Route::prefix('parent-portal')->group(function () {
        Route::apiResource('parents', ParentPortalController::class);
    });
});

// Digital Library Routes (protected)
Route::group(['middleware' => ['jwt', 'rate.limit']], function () {
    Route::prefix('digital-library')->group(function () {
        Route::get('books', [DigitalLibraryController::class, 'indexBooks']);
        Route::post('books', [DigitalLibraryController::class, 'storeBook']);
        Route::get('books/{id}', [DigitalLibraryController::class, 'showBook']);
        Route::put('books/{id}', [DigitalLibraryController::class, 'updateBook']);
        Route::delete('books/{id}', [DigitalLibraryController::class, 'destroyBook']);
        
        Route::get('book-loans', [DigitalLibraryController::class, 'indexBookLoans']);
        Route::post('book-loans', [DigitalLibraryController::class, 'storeBookLoan']);
        Route::get('book-loans/{id}', [DigitalLibraryController::class, 'showBookLoan']);
        Route::put('book-loans/{id}', [DigitalLibraryController::class, 'updateBookLoan']);
        Route::delete('book-loans/{id}', [DigitalLibraryController::class, 'destroyBookLoan']);
        
        Route::get('book-reviews', [DigitalLibraryController::class, 'indexBookReviews']);
        Route::post('book-reviews', [DigitalLibraryController::class, 'storeBookReview']);
        Route::get('book-reviews/{id}', [DigitalLibraryController::class, 'showBookReview']);
        Route::put('book-reviews/{id}', [DigitalLibraryController::class, 'updateBookReview']);
        Route::delete('book-reviews/{id}', [DigitalLibraryController::class, 'destroyBookReview']);
        
        Route::get('ebook-formats', [DigitalLibraryController::class, 'indexEbookFormats']);
        Route::post('ebook-formats', [DigitalLibraryController::class, 'storeEbookFormat']);
        Route::get('ebook-formats/{id}', [DigitalLibraryController::class, 'showEbookFormat']);
        Route::put('ebook-formats/{id}', [DigitalLibraryController::class, 'updateEbookFormat']);
        Route::delete('ebook-formats/{id}', [DigitalLibraryController::class, 'destroyEbookFormat']);
    });
});

// PPDB Routes (protected)
Route::group(['middleware' => ['jwt', 'rate.limit']], function () {
    Route::prefix('ppdb')->group(function () {
        Route::get('registrations', [PPDBController::class, 'indexRegistrations']);
        Route::post('registrations', [PPDBController::class, 'storeRegistration']);
        Route::get('registrations/{id}', [PPDBController::class, 'showRegistration']);
        Route::put('registrations/{id}', [PPDBController::class, 'updateRegistration']);
        Route::delete('registrations/{id}', [PPDBController::class, 'destroyRegistration']);
        
        Route::get('tests', [PPDBController::class, 'indexTests']);
        Route::post('tests', [PPDBController::class, 'storeTest']);
        Route::get('tests/{id}', [PPDBController::class, 'showTest']);
        Route::put('tests/{id}', [PPDBController::class, 'updateTest']);
        Route::delete('tests/{id}', [PPDBController::class, 'destroyTest']);
        
        Route::get('documents', [PPDBController::class, 'indexDocuments']);
        Route::post('documents', [PPDBController::class, 'storeDocument']);
        Route::get('documents/{id}', [PPDBController::class, 'showDocument']);
        Route::put('documents/{id}', [PPDBController::class, 'updateDocument']);
        Route::delete('documents/{id}', [PPDBController::class, 'destroyDocument']);
        
        Route::get('announcements', [PPDBController::class, 'indexAnnouncements']);
        Route::post('announcements', [PPDBController::class, 'storeAnnouncement']);
        Route::get('announcements/{id}', [PPDBController::class, 'showAnnouncement']);
        Route::put('announcements/{id}', [PPDBController::class, 'updateAnnouncement']);
        Route::delete('announcements/{id}', [PPDBController::class, 'destroyAnnouncement']);
    });
});

// Monetization Routes (protected)
Route::group(['middleware' => ['jwt', 'rate.limit']], function () {
    Route::prefix('monetization')->group(function () {
        Route::get('products', [MonetizationController::class, 'indexProducts']);
        Route::post('products', [MonetizationController::class, 'storeProduct']);
        Route::get('products/{id}', [MonetizationController::class, 'showProduct']);
        Route::put('products/{id}', [MonetizationController::class, 'updateProduct']);
        Route::delete('products/{id}', [MonetizationController::class, 'destroyProduct']);
        
        Route::get('transactions', [MonetizationController::class, 'indexTransactions']);
        Route::post('transactions', [MonetizationController::class, 'storeTransaction']);
        Route::get('transactions/{id}', [MonetizationController::class, 'showTransaction']);
        Route::put('transactions/{id}', [MonetizationController::class, 'updateTransaction']);
        Route::delete('transactions/{id}', [MonetizationController::class, 'destroyTransaction']);
        
        Route::get('transaction-items', [MonetizationController::class, 'indexTransactionItems']);
        Route::post('transaction-items', [MonetizationController::class, 'storeTransactionItem']);
        Route::get('transaction-items/{id}', [MonetizationController::class, 'showTransactionItem']);
        Route::put('transaction-items/{id}', [MonetizationController::class, 'updateTransactionItem']);
        Route::delete('transaction-items/{id}', [MonetizationController::class, 'destroyTransactionItem']);
    });
});

// Career Development Routes (protected)
Route::group(['middleware' => ['jwt', 'rate.limit']], function () {
    Route::prefix('career-development')->group(function () {
        Route::get('assessments', [CareerDevelopmentController::class, 'indexAssessments']);
        Route::post('assessments', [CareerDevelopmentController::class, 'storeAssessment']);
        Route::get('assessments/{id}', [CareerDevelopmentController::class, 'showAssessment']);
        Route::put('assessments/{id}', [CareerDevelopmentController::class, 'updateAssessment']);
        Route::delete('assessments/{id}', [CareerDevelopmentController::class, 'destroyAssessment']);
        
        Route::get('sessions', [CareerDevelopmentController::class, 'indexSessions']);
        Route::post('sessions', [CareerDevelopmentController::class, 'storeSession']);
        Route::get('sessions/{id}', [CareerDevelopmentController::class, 'showSession']);
        Route::put('sessions/{id}', [CareerDevelopmentController::class, 'updateSession']);
        Route::delete('sessions/{id}', [CareerDevelopmentController::class, 'destroySession']);
        
        Route::get('partners', [CareerDevelopmentController::class, 'indexPartners']);
        Route::post('partners', [CareerDevelopmentController::class, 'storePartner']);
        Route::get('partners/{id}', [CareerDevelopmentController::class, 'showPartner']);
        Route::put('partners/{id}', [CareerDevelopmentController::class, 'updatePartner']);
        Route::delete('partners/{id}', [CareerDevelopmentController::class, 'destroyPartner']);
    });
});

// Online Exam Routes (protected)
Route::group(['middleware' => ['jwt', 'rate.limit']], function () {
    Route::prefix('online-exam')->group(function () {
        Route::get('exams', [OnlineExamController::class, 'indexExams']);
        Route::post('exams', [OnlineExamController::class, 'storeExam']);
        Route::get('exams/{id}', [OnlineExamController::class, 'showExam']);
        Route::put('exams/{id}', [OnlineExamController::class, 'updateExam']);
        Route::delete('exams/{id}', [OnlineExamController::class, 'destroyExam']);
        
        Route::get('questions', [OnlineExamController::class, 'indexQuestions']);
        Route::post('questions', [OnlineExamController::class, 'storeQuestion']);
        
        Route::get('exam-questions/{examId}', [OnlineExamController::class, 'indexExamQuestions']);
        Route::post('exam-questions', [OnlineExamController::class, 'storeExamQuestion']);
        
        Route::get('results', [OnlineExamController::class, 'indexResults']);
        Route::post('results', [OnlineExamController::class, 'storeResult']);
        
        Route::get('answers/{resultId}', [OnlineExamController::class, 'indexAnswers']);
        Route::post('answers', [OnlineExamController::class, 'storeAnswer']);
    });
});

// Grading Routes (protected)
Route::group(['middleware' => ['jwt', 'rate.limit']], function () {
    Route::prefix('grading')->group(function () {
        Route::get('grades', [GradingController::class, 'indexGrades']);
        Route::post('grades', [GradingController::class, 'storeGrade']);
        Route::get('grades/{id}', [GradingController::class, 'showGrade']);
        Route::put('grades/{id}', [GradingController::class, 'updateGrade']);
        Route::delete('grades/{id}', [GradingController::class, 'destroyGrade']);
        
        Route::get('competencies', [GradingController::class, 'indexCompetencies']);
        Route::post('competencies', [GradingController::class, 'storeCompetency']);
        
        Route::get('reports', [GradingController::class, 'indexReports']);
        Route::post('reports', [GradingController::class, 'storeReport']);
        
        Route::get('portfolios', [GradingController::class, 'indexPortfolios']);
        Route::post('portfolios', [GradingController::class, 'storePortfolio']);
    });
});

// E-Learning Routes (protected)
Route::group(['middleware' => ['jwt', 'rate.limit']], function () {
    Route::prefix('elearning')->group(function () {
        Route::get('virtual-classes', [ELearningController::class, 'indexVirtualClasses']);
        Route::post('virtual-classes', [ELearningController::class, 'storeVirtualClass']);
        Route::get('virtual-classes/{id}', [ELearningController::class, 'showVirtualClass']);
        Route::put('virtual-classes/{id}', [ELearningController::class, 'updateVirtualClass']);
        Route::delete('virtual-classes/{id}', [ELearningController::class, 'destroyVirtualClass']);
        
        Route::get('learning-materials', [ELearningController::class, 'indexLearningMaterials']);
        Route::post('learning-materials', [ELearningController::class, 'storeLearningMaterial']);
        Route::get('learning-materials/{id}', [ELearningController::class, 'showLearningMaterial']);
        Route::put('learning-materials/{id}', [ELearningController::class, 'updateLearningMaterial']);
        Route::delete('learning-materials/{id}', [ELearningController::class, 'destroyLearningMaterial']);
        
        Route::get('assignments', [ELearningController::class, 'indexAssignments']);
        Route::post('assignments', [ELearningController::class, 'storeAssignment']);
        Route::get('assignments/{id}', [ELearningController::class, 'showAssignment']);
        Route::put('assignments/{id}', [ELearningController::class, 'updateAssignment']);
        Route::delete('assignments/{id}', [ELearningController::class, 'destroyAssignment']);
        
        Route::get('quizzes', [ELearningController::class, 'indexQuizzes']);
        Route::post('quizzes', [ELearningController::class, 'storeQuiz']);
        Route::get('quizzes/{id}', [ELearningController::class, 'showQuiz']);
        Route::put('quizzes/{id}', [ELearningController::class, 'updateQuiz']);
        Route::delete('quizzes/{id}', [ELearningController::class, 'destroyQuiz']);
        
        Route::get('discussions', [ELearningController::class, 'indexDiscussions']);
        Route::post('discussions', [ELearningController::class, 'storeDiscussion']);
        Route::get('discussions/{id}', [ELearningController::class, 'showDiscussion']);
        Route::put('discussions/{id}', [ELearningController::class, 'updateDiscussion']);
        Route::delete('discussions/{id}', [ELearningController::class, 'destroyDiscussion']);
        
        Route::get('discussion-replies/{discussionId}', [ELearningController::class, 'indexDiscussionReplies']);
        Route::post('discussion-replies', [ELearningController::class, 'storeDiscussionReply']);
        
        Route::get('video-conferences', [ELearningController::class, 'indexVideoConferences']);
        Route::post('video-conferences', [ELearningController::class, 'storeVideoConference']);
        Route::get('video-conferences/{id}', [ELearningController::class, 'showVideoConference']);
        Route::put('video-conferences/{id}', [ELearningController::class, 'updateVideoConference']);
        Route::delete('video-conferences/{id}', [ELearningController::class, 'destroyVideoConference']);
    });
});
