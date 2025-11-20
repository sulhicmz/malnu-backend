# API Documentation

## 📡 Overview

This document describes the RESTful API endpoints for the Malnu Backend School Management System. The API follows REST conventions and returns JSON responses.

## 🔐 Authentication

### JWT Authentication
All API endpoints (except authentication endpoints) require JWT authentication.

#### Headers
```
Authorization: Bearer <jwt_token>
Content-Type: application/json
```

#### Login
```http
POST /api/auth/login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "user": {
      "id": "uuid-string",
      "name": "John Doe",
      "email": "user@example.com",
      "role": "student"
    },
    "expires_in": 3600
  }
}
```

#### Logout
```http
POST /api/auth/logout
Authorization: Bearer <jwt_token>
```

#### Refresh Token
```http
POST /api/auth/refresh
Authorization: Bearer <jwt_token>
```

## 👥 User Management

### Get Current User
```http
GET /api/user
Authorization: Bearer <jwt_token>
```

### Update Profile
```http
PUT /api/user
Authorization: Bearer <jwt_token>
Content-Type: application/json

{
  "name": "Updated Name",
  "email": "updated@example.com"
}
```

### Change Password
```http
PUT /api/user/password
Authorization: Bearer <jwt_token>
Content-Type: application/json

{
  "current_password": "old_password",
  "password": "new_password",
  "password_confirmation": "new_password"
}
```

## 🎓 Student Management

### Get Students
```http
GET /api/students
Authorization: Bearer <jwt_token>
```

**Query Parameters:**
- `page`: Page number (default: 1)
- `limit`: Items per page (default: 15)
- `search`: Search by name or NISN
- `class_id`: Filter by class
- `status`: Filter by status (active, inactive, graduated)

**Response:**
```json
{
  "success": true,
  "data": {
    "students": [
      {
        "id": "uuid-string",
        "nisn": "1234567890",
        "name": "Student Name",
        "email": "student@example.com",
        "class": {
          "id": "uuid-string",
          "name": "Class 10A"
        },
        "status": "active",
        "created_at": "2025-01-01T00:00:00Z"
      }
    ],
    "pagination": {
      "current_page": 1,
      "total_pages": 10,
      "total_items": 150,
      "per_page": 15
    }
  }
}
```

### Get Student
```http
GET /api/students/{id}
Authorization: Bearer <jwt_token>
```

### Create Student
```http
POST /api/students
Authorization: Bearer <jwt_token>
Content-Type: application/json

{
  "user_id": "uuid-string",
  "nisn": "1234567890",
  "class_id": "uuid-string",
  "birth_date": "2005-01-01",
  "birth_place": "City",
  "address": "Student Address",
  "parent_id": "uuid-string",
  "enrollment_date": "2025-01-01"
}
```

### Update Student
```http
PUT /api/students/{id}
Authorization: Bearer <jwt_token>
Content-Type: application/json

{
  "class_id": "uuid-string",
  "address": "Updated Address"
}
```

### Delete Student
```http
DELETE /api/students/{id}
Authorization: Bearer <jwt_token>
```

## 👨‍🏫 Teacher Management

### Get Teachers
```http
GET /api/teachers
Authorization: Bearer <jwt_token>
```

### Get Teacher
```http
GET /api/teachers/{id}
Authorization: Bearer <jwt_token>
```

### Create Teacher
```http
POST /api/teachers
Authorization: Bearer <jwt_token>
Content-Type: application/json

{
  "user_id": "uuid-string",
  "nip": "123456789012345678",
  "specialization": "Mathematics",
  "phone": "+6281234567890",
  "address": "Teacher Address"
}
```

### Update Teacher
```http
PUT /api/teachers/{id}
Authorization: Bearer <jwt_token>
Content-Type: application/json

{
  "specialization": "Physics",
  "phone": "+6281234567890"
}
```

### Delete Teacher
```http
DELETE /api/teachers/{id}
Authorization: Bearer <jwt_token>
```

## 📚 Class Management

### Get Classes
```http
GET /api/classes
Authorization: Bearer <jwt_token>
```

**Response:**
```json
{
  "success": true,
  "data": {
    "classes": [
      {
        "id": "uuid-string",
        "name": "Class 10A",
        "grade_level": 10,
        "academic_year": "2025/2026",
        "teacher": {
          "id": "uuid-string",
          "name": "Teacher Name"
        },
        "student_count": 30,
        "created_at": "2025-01-01T00:00:00Z"
      }
    ]
  }
}
```

### Get Class
```http
GET /api/classes/{id}
Authorization: Bearer <jwt_token>
```

### Create Class
```http
POST /api/classes
Authorization: Bearer <jwt_token>
Content-Type: application/json

{
  "name": "Class 10A",
  "grade_level": 10,
  "academic_year": "2025/2026",
  "teacher_id": "uuid-string"
}
```

### Update Class
```http
PUT /api/classes/{id}
Authorization: Bearer <jwt_token>
Content-Type: application/json

{
  "name": "Class 10B",
  "teacher_id": "uuid-string"
}
```

### Delete Class
```http
DELETE /api/classes/{id}
Authorization: Bearer <jwt_token>
```

### Get Class Students
```http
GET /api/classes/{id}/students
Authorization: Bearer <jwt_token>
```

## 📖 Subject Management

### Get Subjects
```http
GET /api/subjects
Authorization: Bearer <jwt_token>
```

### Get Subject
```http
GET /api/subjects/{id}
Authorization: Bearer <jwt_token>
```

### Create Subject
```http
POST /api/subjects
Authorization: Bearer <jwt_token>
Content-Type: application/json

{
  "name": "Mathematics",
  "code": "MATH",
  "description": "Mathematics subject",
  "credits": 4
}
```

### Update Subject
```http
PUT /api/subjects/{id}
Authorization: Bearer <jwt_token>
Content-Type: application/json

{
  "name": "Advanced Mathematics",
  "credits": 5
}
```

### Delete Subject
```http
DELETE /api/subjects/{id}
Authorization: Bearer <jwt_token>
```

## 📅 Schedule Management

### Get Schedules
```http
GET /api/schedules
Authorization: Bearer <jwt_token>
```

**Query Parameters:**
- `class_id`: Filter by class
- `teacher_id`: Filter by teacher
- `subject_id`: Filter by subject
- `day`: Filter by day (monday, tuesday, etc.)
- `start_time`: Filter by start time

### Get Schedule
```http
GET /api/schedules/{id}
Authorization: Bearer <jwt_token>
```

### Create Schedule
```http
POST /api/schedules
Authorization: Bearer <jwt_token>
Content-Type: application/json

{
  "class_id": "uuid-string",
  "teacher_id": "uuid-string",
  "subject_id": "uuid-string",
  "day": "monday",
  "start_time": "08:00",
  "end_time": "09:30",
  "room": "Room 101"
}
```

### Update Schedule
```http
PUT /api/schedules/{id}
Authorization: Bearer <jwt_token>
Content-Type: application/json

{
  "start_time": "09:00",
  "end_time": "10:30",
  "room": "Room 102"
}
```

### Delete Schedule
```http
DELETE /api/schedules/{id}
Authorization: Bearer <jwt_token>
```

## 📊 Grade Management

### Get Grades
```http
GET /api/grades
Authorization: Bearer <jwt_token>
```

**Query Parameters:**
- `student_id`: Filter by student
- `subject_id`: Filter by subject
- `semester`: Filter by semester
- `academic_year`: Filter by academic year

### Create Grade
```http
POST /api/grades
Authorization: Bearer <jwt_token>
Content-Type: application/json

{
  "student_id": "uuid-string",
  "subject_id": "uuid-string",
  "assignment_type": "midterm",
  "score": 85,
  "max_score": 100,
  "semester": 1,
  "academic_year": "2025/2026"
}
```

### Update Grade
```http
PUT /api/grades/{id}
Authorization: Bearer <jwt_token>
Content-Type: application/json

{
  "score": 90
}
```

### Delete Grade
```http
DELETE /api/grades/{id}
Authorization: Bearer <jwt_token>
```

## 📝 Attendance Management

### Get Attendance Records
```http
GET /api/attendance
Authorization: Bearer <jwt_token>
```

### Mark Attendance
```http
POST /api/attendance
Authorization: Bearer <jwt_token>
Content-Type: application/json

{
  "student_id": "uuid-string",
  "schedule_id": "uuid-string",
  "status": "present",
  "notes": "On time"
}
```

### Update Attendance
```http
PUT /api/attendance/{id}
Authorization: Bearer <jwt_token>
Content-Type: application/json

{
  "status": "late",
  "notes": "Arrived 10 minutes late"
}
```

## 📚 Digital Library

### Get Books
```http
GET /api/library/books
Authorization: Bearer <jwt_token>
```

**Query Parameters:**
- `search`: Search by title or author
- `category`: Filter by category
- `available`: Filter by availability (true/false)

### Get Book
```http
GET /api/library/books/{id}
Authorization: Bearer <jwt_token>
```

### Borrow Book
```http
POST /api/library/books/{id}/borrow
Authorization: Bearer <jwt_token>
Content-Type: application/json

{
  "due_date": "2025-02-01"
}
```

### Return Book
```http
POST /api/library/books/{id}/return
Authorization: Bearer <jwt_token>
```

## 🎓 E-Learning

### Get Courses
```http
GET /api/elearning/courses
Authorization: Bearer <jwt_token>
```

### Get Course Materials
```http
GET /api/elearning/courses/{id}/materials
Authorization: Bearer <jwt_token>
```

### Get Assignments
```http
GET /api/elearning/assignments
Authorization: Bearer <jwt_token>
```

### Submit Assignment
```http
POST /api/elearning/assignments/{id}/submit
Authorization: Bearer <jwt_token>
Content-Type: multipart/form-data

{
  "file": <file>,
  "text": "Assignment text content"
}
```

## 📊 Reports & Analytics

### Get Student Reports
```http
GET /api/reports/students/{id}
Authorization: Bearer <jwt_token>
```

### Get Class Reports
```http
GET /api/reports/classes/{id}
Authorization: Bearer <jwt_token>
```

### Get Attendance Reports
```http
GET /api/reports/attendance
Authorization: Bearer <jwt_token>
```

**Query Parameters:**
- `start_date`: Report start date
- `end_date`: Report end date
- `class_id`: Filter by class
- `format`: Export format (pdf, excel)

## 🚨 Error Responses

All error responses follow this format:

```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "The given data was invalid.",
    "details": {
      "email": ["The email field is required."],
      "password": ["The password must be at least 8 characters."]
    }
  }
}
```

### Common Error Codes
- `UNAUTHORIZED` (401): Invalid or missing authentication
- `FORBIDDEN` (403): Insufficient permissions
- `NOT_FOUND` (404): Resource not found
- `VALIDATION_ERROR` (422): Input validation failed
- `SERVER_ERROR` (500): Internal server error

## 📝 Response Format

### Success Response
```json
{
  "success": true,
  "data": {
    // Response data
  },
  "meta": {
    "timestamp": "2025-01-01T00:00:00Z",
    "version": "v1"
  }
}
```

### Paginated Response
```json
{
  "success": true,
  "data": {
    "items": [...],
    "pagination": {
      "current_page": 1,
      "total_pages": 10,
      "total_items": 150,
      "per_page": 15,
      "has_next": true,
      "has_prev": false
    }
  }
}
```

## 🔒 Rate Limiting

API endpoints are rate-limited to prevent abuse:

- **Authentication endpoints**: 5 requests per minute
- **Standard endpoints**: 60 requests per minute
- **File upload endpoints**: 10 requests per minute

Rate limit headers are included in responses:
```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59
X-RateLimit-Reset: 1640995200
```

---

*This API documentation is continuously updated as new endpoints are implemented.*