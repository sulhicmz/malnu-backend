# Mobile API Documentation

This document describes the mobile API endpoints for the school management system.

## Base URL
```
https://your-domain.com/api/v1
```

## Authentication
All protected endpoints require a Bearer token in the Authorization header:
```
Authorization: Bearer {access_token}
```

## Common Response Format
```json
{
  "success": true,
  "message": "Success message",
  "data": {}
}
```

## Authentication Endpoints

### Login
```
POST /auth/login
```
Authenticate user and get access token.

**Request Body:**
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
  "message": "Success",
  "data": {
    "access_token": "token",
    "refresh_token": "refresh_token",
    "token_type": "bearer",
    "expires_in": 7200,
    "user": {
      "id": "user_id",
      "name": "User Name",
      "email": "user@example.com",
      "role": "student|parent|teacher|admin"
    }
  }
}
```

### Logout
```
POST /auth/logout
```
Invalidate the current token.

### Refresh Token
```
POST /auth/refresh
```
Refresh the access token.

### Get Current User
```
POST /auth/me
```
Get information about the authenticated user.

## Student Endpoints

### Get Dashboard
```
GET /student/dashboard
```
Get student dashboard data including recent assignments, grades, and schedule.

### Get Profile
```
GET /student/profile
```
Get student profile information.

### Update Profile
```
PUT /student/profile
```
Update student profile information.

**Request Body:**
```json
{
  "name": "New Name",
  "phone": "New Phone"
}
```

### Get Grades
```
GET /student/grades
```
Get all grades for the student.

### Get Assignments
```
GET /student/assignments
```
Get all assignments for the student's class.

### Get Schedule
```
GET /student/schedule
```
Get class schedule for the student.

### Get Attendance
```
GET /student/attendance
```
Get attendance records for the student.

## Parent Endpoints

### Get Dashboard
```
GET /parent/dashboard
```
Get parent dashboard with information about their students.

### Get Student Grades
```
GET /parent/student/{studentId}/grades
```
Get grades for a specific student.

### Get Student Assignments
```
GET /parent/student/{studentId}/assignments
```
Get assignments for a specific student.

### Get Student Attendance
```
GET /parent/student/{studentId}/attendance
```
Get attendance for a specific student.

### Get Student Progress
```
GET /parent/student/{studentId}/progress
```
Get progress report for a specific student.

### Get Fees
```
GET /parent/fees
```
Get fee information for all students under the parent.

## Teacher Endpoints

### Get Dashboard
```
GET /teacher/dashboard
```
Get teacher dashboard with classes and recent assignments.

### Get Classes
```
GET /teacher/classes
```
Get all classes the teacher is associated with.

### Get Class Students
```
GET /teacher/classes/{classId}/students
```
Get all students in a specific class.

### Record Attendance
```
POST /teacher/attendance
```
Record attendance for a student.

**Request Body:**
```json
{
  "class_id": "class_id",
  "student_id": "student_id",
  "date": "2023-01-01",
  "status": "present|absent|late|excused",
  "notes": "Optional notes"
}
```

### Record Grade
```
POST /teacher/grades
```
Record a grade for a student.

**Request Body:**
```json
{
  "student_id": "student_id",
  "subject_id": "subject_id",
  "assignment_id": "optional_assignment_id",
  "grade_value": 85,
  "grade_type": "test|quiz|homework",
  "description": "Optional description"
}
```

### Get Assignments
```
GET /teacher/assignments
```
Get all assignments created by the teacher.

### Create Assignment
```
POST /teacher/assignments
```
Create a new assignment.

**Request Body:**
```json
{
  "title": "Assignment Title",
  "description": "Assignment Description",
  "class_id": "class_id",
  "subject_id": "subject_id",
  "due_date": "2023-01-01",
  "max_score": 100
}
```

## Admin Endpoints

### Get Dashboard
```
GET /admin/dashboard
```
Get admin dashboard with system statistics.

### Get Users
```
GET /admin/users
```
Get paginated list of all users.

### Get Students
```
GET /admin/students
```
Get paginated list of all students.

### Get Teachers
```
GET /admin/teachers
```
Get paginated list of all teachers.

### Get Classes
```
GET /admin/classes
```
Get paginated list of all classes.

## Rate Limiting
The API implements rate limiting:
- Unauthenticated requests: 30 per minute
- Students: 100 per minute
- Parents: 80 per minute
- Teachers: 150 per minute
- Admins: 200 per minute