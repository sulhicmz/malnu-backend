# Mobile API Documentation

This document describes the mobile API endpoints for the school management system.

## Base URL
```
https://your-domain.com/api
```

## Authentication
All protected endpoints require a JWT token in the Authorization header:
```
Authorization: Bearer {token}
```

## API Versioning
All mobile API endpoints use versioning: `/api/v1/`

## Rate Limiting
- General API: 120 requests per minute
- Grade recording: 100 requests per minute
- Attendance recording: 60 requests per minute
- Assignment creation: 30 requests per minute
- Notification marking: 60 requests per minute

## Endpoints

### Authentication

#### POST /api/v1/login
Authenticate user and get JWT token.

**Request:**
```json
{
  "email": "user@example.com",
  "password": "password"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "access_token": "jwt_token_here",
    "token_type": "bearer",
    "expires_in": 7200,
    "user": {
      "id": "uuid",
      "name": "John Doe",
      "email": "user@example.com",
      "role": "student|teacher|parent|admin",
      "profile_type": "student|teacher|parent|staff"
    }
  }
}
```

#### GET /api/v1/me
Get authenticated user information.

**Response:**
```json
{
  "success": true,
  "message": "User retrieved successfully",
  "data": {
    "user": {
      "id": "uuid",
      "name": "John Doe",
      "email": "user@example.com",
      "role": "student",
      "profile_type": "student"
    }
  }
}
```

#### POST /api/v1/logout
Logout user and invalidate token.

**Response:**
```json
{
  "success": true,
  "message": "Successfully logged out"
}
```

#### POST /api/v1/refresh
Refresh JWT token.

**Response:**
```json
{
  "success": true,
  "message": "Token refreshed successfully",
  "data": {
    "access_token": "new_jwt_token",
    "token_type": "bearer",
    "expires_in": 7200
  }
}
```

### Student Endpoints

#### GET /api/v1/student/profile
Get student profile information.

#### GET /api/v1/student/grades
Get student's grades.

#### GET /api/v1/student/assignments
Get student's assignments.

#### GET /api/v1/student/schedule
Get student's class schedule.

#### GET /api/v1/student/attendance
Get student's attendance records.

#### GET /api/v1/student/learning-materials
Get learning materials for student's class.

#### GET /api/v1/student/exam-results
Get student's exam results.

### Parent Endpoints

#### GET /api/v1/parent/profile
Get parent profile information.

#### GET /api/v1/parent/student-info/{studentId?}
Get student information (if studentId not provided, gets first associated student).

#### GET /api/v1/parent/student-grades/{studentId?}
Get student's grades.

#### GET /api/v1/parent/student-attendance/{studentId?}
Get student's attendance records.

#### GET /api/v1/parent/student-assignments/{studentId?}
Get student's assignments.

#### GET /api/v1/parent/student-learning-materials/{studentId?}
Get learning materials for student's class.

#### GET /api/v1/parent/student-exam-results/{studentId?}
Get student's exam results.

#### GET /api/v1/parent/student-fees/{studentId?}
Get student's fee/transaction history.

### Teacher Endpoints

#### GET /api/v1/teacher/profile
Get teacher profile information.

#### GET /api/v1/teacher/classes
Get classes assigned to the teacher.

#### GET /api/v1/teacher/class-students/{classId}
Get students in a specific class.

#### GET /api/v1/teacher/schedule
Get teacher's schedule.

#### POST /api/v1/teacher/record-attendance/{classId}
Record attendance for a class.

**Request:**
```json
{
  "date": "2023-12-01",
  "attendance": [
    {
      "student_id": "uuid",
      "status": "present|absent|late|excused"
    }
  ]
}
```

#### GET /api/v1/teacher/class-attendance/{classId}/{date?}
Get attendance for a class on a specific date (defaults to today).

#### POST /api/v1/teacher/create-assignment/{classId}
Create an assignment for a class.

**Request:**
```json
{
  "title": "Assignment Title",
  "description": "Assignment Description",
  "subject_id": "uuid",
  "due_date": "2023-12-15"
}
```

#### GET /api/v1/teacher/class-assignments/{classId}
Get assignments for a class.

#### POST /api/v1/teacher/record-grades/{classId}/{subjectId}
Record grades for students.

**Request:**
```json
{
  "grades": [
    {
      "student_id": "uuid",
      "score": 85,
      "competency_id": "uuid",
      "semester": 1,
      "year": 2023
    }
  ]
}
```

### Notification Endpoints

#### GET /api/v1/notifications
Get user's notifications.

#### GET /api/v1/notifications/unread
Get count of unread notifications.

#### POST /api/v1/notifications/{id}/read
Mark a notification as read.

## Error Responses

All error responses follow this format:
```json
{
  "success": false,
  "message": "Error message here"
}
```

Common HTTP status codes:
- 200: Success
- 400: Bad Request
- 401: Unauthorized
- 403: Forbidden
- 404: Not Found
- 429: Too Many Requests
- 500: Server Error