# Mobile API Documentation

This document provides information about the mobile API endpoints available in the School Management System.

## Base URL
```
https://your-domain.com/api/v1
```

## Authentication
All authenticated endpoints require a valid JWT token in the Authorization header:
```
Authorization: Bearer {token}
```

## API Endpoints

### Authentication

#### POST /auth/login
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
    "token": "jwt_token_here",
    "token_type": "bearer",
    "expires_in": 7200,
    "user": {
        "id": "user_id",
        "name": "User Name",
        "email": "user@example.com",
        "role": "student|parent|teacher|admin"
    }
}
```

#### POST /auth/logout
Logout user and invalidate token.

**Response:**
```json
{
    "message": "Successfully logged out"
}
```

#### POST /auth/refresh
Refresh JWT token.

**Response:**
```json
{
    "token": "new_jwt_token_here",
    "token_type": "bearer",
    "expires_in": 7200
}
```

#### POST /auth/me
Get authenticated user information.

**Response:**
```json
{
    "user": {
        "id": "user_id",
        "name": "User Name",
        "email": "user@example.com",
        "role": "student|parent|teacher|admin",
        "avatar_url": "avatar_url",
        "is_active": true
    }
}
```

### Student Endpoints

#### GET /student/dashboard
Get student dashboard data.

**Response:**
```json
{
    "student": {
        "id": "student_id",
        "name": "Student Name",
        "nis": "student_nis",
        "class": "Class Name"
    },
    "recent_assignments": [...],
    "latest_grades": [...],
    "todays_schedule": [...],
    "attendance_rate": 95.0
}
```

#### GET /student/grades
Get student grades.

#### GET /student/assignments
Get student assignments.

#### GET /student/schedule
Get student schedule.

#### GET /student/attendance
Get student attendance.

### Parent Endpoints

#### GET /parent/dashboard
Get parent dashboard data with all children information.

#### GET /parent/student/{id}/progress
Get specific student progress.

#### GET /parent/student/{id}/attendance
Get specific student attendance.

#### GET /parent/student/{id}/fees
Get specific student fees information.

#### GET /parent/student/{id}/grades
Get specific student grades.

### Teacher Endpoints

#### GET /teacher/dashboard
Get teacher dashboard data.

#### GET /teacher/classes
Get teacher classes.

#### GET /teacher/students/{classId}
Get students in a specific class.

#### POST /teacher/attendance/mark
Mark attendance for students.

**Request:**
```json
{
    "class_id": "class_id",
    "date": "2023-01-01",
    "attendance": [
        {
            "student_id": "student_id",
            "status": "present|absent|late|excused"
        }
    ]
}
```

#### GET /teacher/assignments
Get teacher assignments.

#### POST /teacher/assignments/create
Create a new assignment.

**Request:**
```json
{
    "title": "Assignment Title",
    "description": "Assignment Description",
    "class_id": "class_id",
    "subject_id": "subject_id",
    "due_date": "2023-01-01"
}
```

### Admin Endpoints

#### GET /admin/dashboard
Get admin dashboard data.

#### GET /admin/users
Get list of users.

#### GET /admin/reports
Get system reports.

## Rate Limiting
The API implements rate limiting with 60 requests per minute per IP address (100 for authenticated users).

## API Versioning
The API uses versioning in the URL path (e.g., `/api/v1/`).

## Error Handling
All error responses follow this format:
```json
{
    "message": "Error message here"
}
```

## Security
- All API endpoints use HTTPS
- JWT tokens are required for authenticated endpoints
- Rate limiting is enforced
- Input validation is performed on all requests