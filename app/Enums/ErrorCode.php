<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Centralized error codes for API responses.
 *
 * This ensures consistency across all controllers and services.
 * Use these constants when returning error responses.
 */
final class ErrorCode
{
    // General Errors (1xxx)
    public const GENERAL_ERROR = 'GENERAL_ERROR';

    public const VALIDATION_ERROR = 'VALIDATION_ERROR';

    public const NOT_FOUND = 'NOT_FOUND';

    public const UNAUTHORIZED = 'UNAUTHORIZED';

    public const FORBIDDEN = 'FORBIDDEN';

    public const SERVER_ERROR = 'SERVER_ERROR';

    public const METHOD_NOT_ALLOWED = 'METHOD_NOT_ALLOWED';

    public const TOO_MANY_REQUESTS = 'TOO_MANY_REQUESTS';

    public const SERVICE_UNAVAILABLE = 'SERVICE_UNAVAILABLE';

    // Authentication Errors (2xxx)
    public const AUTH_INVALID_CREDENTIALS = 'AUTH_INVALID_CREDENTIALS';

    public const AUTH_TOKEN_EXPIRED = 'AUTH_TOKEN_EXPIRED';

    public const AUTH_TOKEN_INVALID = 'AUTH_TOKEN_INVALID';

    public const AUTH_TOKEN_BLACKLISTED = 'AUTH_TOKEN_BLACKLISTED';

    public const AUTH_NOT_AUTHENTICATED = 'AUTH_NOT_AUTHENTICATED';

    // Registration Errors (3xxx)
    public const REGISTRATION_FAILED = 'REGISTRATION_FAILED';

    public const REGISTRATION_EMAIL_EXISTS = 'REGISTRATION_EMAIL_EXISTS';

    public const REGISTRATION_WEAK_PASSWORD = 'REGISTRATION_WEAK_PASSWORD';

    // Student Errors (4xxx)
    public const STUDENT_NOT_FOUND = 'STUDENT_NOT_FOUND';

    public const STUDENT_CREATION_ERROR = 'STUDENT_CREATION_ERROR';

    public const STUDENT_UPDATE_ERROR = 'STUDENT_UPDATE_ERROR';

    public const STUDENT_DELETION_ERROR = 'STUDENT_DELETION_ERROR';

    public const STUDENT_NISN_EXISTS = 'STUDENT_NISN_EXISTS';

    public const STUDENT_EMAIL_EXISTS = 'STUDENT_EMAIL_EXISTS';

    // Teacher Errors (5xxx)
    public const TEACHER_NOT_FOUND = 'TEACHER_NOT_FOUND';

    public const TEACHER_CREATION_ERROR = 'TEACHER_CREATION_ERROR';

    public const TEACHER_UPDATE_ERROR = 'TEACHER_UPDATE_ERROR';

    public const TEACHER_DELETION_ERROR = 'TEACHER_DELETION_ERROR';

    public const TEACHER_EMAIL_EXISTS = 'TEACHER_EMAIL_EXISTS';

    // Class Errors (6xxx)
    public const CLASS_NOT_FOUND = 'CLASS_NOT_FOUND';

    public const CLASS_HAS_STUDENTS = 'CLASS_HAS_STUDENTS';

    public const CLASS_CREATION_ERROR = 'CLASS_CREATION_ERROR';

    // Subject Errors (7xxx)
    public const SUBJECT_NOT_FOUND = 'SUBJECT_NOT_FOUND';

    public const SUBJECT_CREATION_ERROR = 'SUBJECT_CREATION_ERROR';

    // Attendance Errors (8xxx)
    public const ATTENDANCE_ERROR = 'ATTENDANCE_ERROR';

    public const ATTENDANCE_ALREADY_MARKED = 'ATTENDANCE_ALREADY_MARKED';

    // Leave Request Errors (9xxx)
    public const LEAVE_REQUEST_NOT_FOUND = 'LEAVE_REQUEST_NOT_FOUND';

    public const LEAVE_REQUEST_CREATION_ERROR = 'LEAVE_REQUEST_CREATION_ERROR';

    public const LEAVE_REQUEST_APPROVAL_ERROR = 'LEAVE_REQUEST_APPROVAL_ERROR';

    public const LEAVE_REQUEST_REJECTION_ERROR = 'LEAVE_REQUEST_REJECTION_ERROR';

    public const LEAVE_REQUEST_INVALID_STATUS = 'LEAVE_REQUEST_INVALID_STATUS';

    public const LEAVE_INSUFFICIENT_BALANCE = 'LEAVE_INSUFFICIENT_BALANCE';

    public const LEAVE_TYPE_NOT_FOUND = 'LEAVE_TYPE_NOT_FOUND';

    // Calendar Errors (10xxx)
    public const CALENDAR_NOT_FOUND = 'CALENDAR_NOT_FOUND';

    public const CALENDAR_CREATION_ERROR = 'CALENDAR_CREATION_ERROR';

    public const CALENDAR_UPDATE_ERROR = 'CALENDAR_UPDATE_ERROR';

    public const CALENDAR_DELETION_ERROR = 'CALENDAR_DELETION_ERROR';

    public const EVENT_NOT_FOUND = 'EVENT_NOT_FOUND';

    public const EVENT_CREATION_ERROR = 'EVENT_CREATION_ERROR';

    public const EVENT_UPDATE_ERROR = 'EVENT_UPDATE_ERROR';

    public const EVENT_DELETION_ERROR = 'EVENT_DELETION_ERROR';

    public const EVENT_REGISTRATION_ERROR = 'EVENT_REGISTRATION_ERROR';

    public const RESOURCE_BOOKING_ERROR = 'RESOURCE_BOOKING_ERROR';

    public const RESOURCE_ALREADY_BOOKED = 'RESOURCE_ALREADY_BOOKED';

    // File Upload Errors (11xxx)
    public const FILE_UPLOAD_ERROR = 'FILE_UPLOAD_ERROR';

    public const FILE_UPLOAD_INVALID_TYPE = 'FILE_UPLOAD_INVALID_TYPE';

    public const FILE_UPLOAD_SIZE_EXCEEDED = 'FILE_UPLOAD_SIZE_EXCEEDED';

    public const FILE_UPLOAD_MALICIOUS = 'FILE_UPLOAD_MALICIOUS';

    // Database Errors (12xxx)
    public const DATABASE_CONNECTION_ERROR = 'DATABASE_CONNECTION_ERROR';

    public const DATABASE_QUERY_ERROR = 'DATABASE_QUERY_ERROR';

    public const DATABASE_TRANSACTION_ERROR = 'DATABASE_TRANSACTION_ERROR';

    // Cache Errors (13xxx)
    public const CACHE_ERROR = 'CACHE_ERROR';

    public const CACHE_CONNECTION_ERROR = 'CACHE_CONNECTION_ERROR';

    // External Service Errors (14xxx)
    public const EXTERNAL_SERVICE_ERROR = 'EXTERNAL_SERVICE_ERROR';

    public const EXTERNAL_SERVICE_TIMEOUT = 'EXTERNAL_SERVICE_TIMEOUT';

    public const EXTERNAL_SERVICE_UNAVAILABLE = 'EXTERNAL_SERVICE_UNAVAILABLE';

    // Payment/Monetization Errors (15xxx)
    public const PAYMENT_ERROR = 'PAYMENT_ERROR';

    public const PAYMENT_FAILED = 'PAYMENT_FAILED';

    public const TRANSACTION_NOT_FOUND = 'TRANSACTION_NOT_FOUND';

    public const TRANSACTION_ERROR = 'TRANSACTION_ERROR';

    // Exam Errors (16xxx)
    public const EXAM_NOT_FOUND = 'EXAM_NOT_FOUND';

    public const EXAM_CREATION_ERROR = 'EXAM_CREATION_ERROR';

    public const EXAM_ALREADY_SUBMITTED = 'EXAM_ALREADY_SUBMITTED';

    // Digital Library Errors (17xxx)
    public const BOOK_NOT_FOUND = 'BOOK_NOT_FOUND';

    public const BOOK_LOAN_ERROR = 'BOOK_LOAN_ERROR';

    public const BOOK_NOT_AVAILABLE = 'BOOK_NOT_AVAILABLE';

    // Rate Limiting Errors (18xxx)
    public const RATE_LIMIT_EXCEEDED = 'RATE_LIMIT_EXCEEDED';

    /**
     * Get HTTP status code for error code.
     */
    public static function getStatusCode(string $errorCode): int
    {
        $statusCodes = [
            self::GENERAL_ERROR => 500,
            self::VALIDATION_ERROR => 422,
            self::NOT_FOUND => 404,
            self::UNAUTHORIZED => 401,
            self::FORBIDDEN => 403,
            self::SERVER_ERROR => 500,
            self::METHOD_NOT_ALLOWED => 405,
            self::TOO_MANY_REQUESTS => 429,
            self::SERVICE_UNAVAILABLE => 503,

            // Authentication
            self::AUTH_INVALID_CREDENTIALS => 401,
            self::AUTH_TOKEN_EXPIRED => 401,
            self::AUTH_TOKEN_INVALID => 401,
            self::AUTH_TOKEN_BLACKLISTED => 401,
            self::AUTH_NOT_AUTHENTICATED => 401,

            // Registration
            self::REGISTRATION_FAILED => 400,
            self::REGISTRATION_EMAIL_EXISTS => 409,
            self::REGISTRATION_WEAK_PASSWORD => 422,

            // Student/Teacher/Class/Subject
            self::STUDENT_NOT_FOUND => 404,
            self::STUDENT_CREATION_ERROR => 400,
            self::STUDENT_UPDATE_ERROR => 400,
            self::STUDENT_DELETION_ERROR => 400,
            self::STUDENT_NISN_EXISTS => 409,
            self::STUDENT_EMAIL_EXISTS => 409,
            self::TEACHER_NOT_FOUND => 404,
            self::TEACHER_CREATION_ERROR => 400,
            self::TEACHER_UPDATE_ERROR => 400,
            self::TEACHER_DELETION_ERROR => 400,
            self::TEACHER_EMAIL_EXISTS => 409,
            self::CLASS_NOT_FOUND => 404,
            self::CLASS_HAS_STUDENTS => 409,
            self::CLASS_CREATION_ERROR => 400,
            self::SUBJECT_NOT_FOUND => 404,
            self::SUBJECT_CREATION_ERROR => 400,

            // Attendance
            self::ATTENDANCE_ERROR => 400,
            self::ATTENDANCE_ALREADY_MARKED => 409,

            // Leave Requests
            self::LEAVE_REQUEST_NOT_FOUND => 404,
            self::LEAVE_REQUEST_CREATION_ERROR => 400,
            self::LEAVE_REQUEST_APPROVAL_ERROR => 400,
            self::LEAVE_REQUEST_REJECTION_ERROR => 400,
            self::LEAVE_REQUEST_INVALID_STATUS => 400,
            self::LEAVE_INSUFFICIENT_BALANCE => 400,
            self::LEAVE_TYPE_NOT_FOUND => 404,

            // Calendar
            self::CALENDAR_NOT_FOUND => 404,
            self::CALENDAR_CREATION_ERROR => 400,
            self::CALENDAR_UPDATE_ERROR => 400,
            self::CALENDAR_DELETION_ERROR => 400,
            self::EVENT_NOT_FOUND => 404,
            self::EVENT_CREATION_ERROR => 400,
            self::EVENT_UPDATE_ERROR => 400,
            self::EVENT_DELETION_ERROR => 400,
            self::EVENT_REGISTRATION_ERROR => 400,
            self::RESOURCE_BOOKING_ERROR => 400,
            self::RESOURCE_ALREADY_BOOKED => 409,

            // File Upload
            self::FILE_UPLOAD_ERROR => 400,
            self::FILE_UPLOAD_INVALID_TYPE => 415,
            self::FILE_UPLOAD_SIZE_EXCEEDED => 413,
            self::FILE_UPLOAD_MALICIOUS => 400,

            // Database
            self::DATABASE_CONNECTION_ERROR => 503,
            self::DATABASE_QUERY_ERROR => 500,
            self::DATABASE_TRANSACTION_ERROR => 500,

            // Cache
            self::CACHE_ERROR => 500,
            self::CACHE_CONNECTION_ERROR => 503,

            // External Services
            self::EXTERNAL_SERVICE_ERROR => 502,
            self::EXTERNAL_SERVICE_TIMEOUT => 504,
            self::EXTERNAL_SERVICE_UNAVAILABLE => 503,

            // Payment
            self::PAYMENT_ERROR => 400,
            self::PAYMENT_FAILED => 402,
            self::TRANSACTION_NOT_FOUND => 404,
            self::TRANSACTION_ERROR => 400,

            // Exam
            self::EXAM_NOT_FOUND => 404,
            self::EXAM_CREATION_ERROR => 400,
            self::EXAM_ALREADY_SUBMITTED => 409,

            // Digital Library
            self::BOOK_NOT_FOUND => 404,
            self::BOOK_LOAN_ERROR => 400,
            self::BOOK_NOT_AVAILABLE => 409,

            // Rate Limiting
            self::RATE_LIMIT_EXCEEDED => 429,
        ];

        return $statusCodes[$errorCode] ?? 500;
    }
}
