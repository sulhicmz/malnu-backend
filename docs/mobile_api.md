# Mobile API Documentation

This document describes the mobile API endpoints for the school management system.

## Authentication

The mobile API uses JWT (JSON Web Token) for authentication. All authenticated endpoints require a valid JWT token in the Authorization header.

### Login
- **POST** `/api/mobile/login`
- **Description**: Authenticate user and return JWT token
- **Request Body**:
  ```json
  {
    "email": "user@example.com",
    "password": "password"
  }
  ```
- **Response**:
  ```json
  {
    "success": true,
    "data": {
      "user": {
        "id": "user-id",
        "name": "User Name",
        "email": "user@example.com",
        "role": "student|parent|teacher|staff"
      },
      "token": "jwt-token",
      "token_type": "bearer",
      "expires_in": 7200
    }
  }
  ```

### Logout
- **POST** `/api/mobile/logout`
- **Description**: Invalidate the current JWT token
- **Headers**: 
  - `Authorization: Bearer {token}`
- **Response**:
  ```json
  {
    "success": true,
    "message": "Successfully logged out"
  }
  ```

### Refresh Token
- **POST** `/api/mobile/refresh`
- **Description**: Refresh the JWT token
- **Headers**: 
  - `Authorization: Bearer {token}`
- **Response**:
  ```json
  {
    "success": true,
    "data": {
      "token": "new-jwt-token",
      "token_type": "bearer",
      "expires_in": 7200
    }
  }
  ```

### Get User Profile
- **POST** `/api/mobile/me`
- **Description**: Get authenticated user information
- **Headers**: 
  - `Authorization: Bearer {token}`
- **Response**:
  ```json
  {
    "success": true,
    "data": {
      "id": "user-id",
      "name": "User Name",
      "email": "user@example.com",
      "role": "student|parent|teacher|staff",
      "avatar_url": "avatar-url",
      "is_active": true
    }
  }
  ```

## Student Endpoints

All student endpoints require authentication.

### Student Dashboard
- **GET** `/api/mobile/student/dashboard`
- **Description**: Get student dashboard data including recent grades, assignments, and schedule
- **Headers**: 
  - `Authorization: Bearer {token}`
- **Response**:
  ```json
  {
    "success": true,
    "data": {
      "student": {
        "id": "student-id",
        "nisn": "student-nisn",
        "class": "class-name",
        "status": "active"
      },
      "recent_grades": [...],
      "upcoming_assignments": [...],
      "weekly_schedule": [...]
    }
  }
  ```

### Student Grades
- **GET** `/api/mobile/student/grades`
- **Description**: Get all grades for the student
- **Headers**: 
  - `Authorization: Bearer {token}`
- **Response**:
  ```json
  {
    "success": true,
    "data": [...]
  }
  ```

### Student Assignments
- **GET** `/api/mobile/student/assignments`
- **Description**: Get all assignments for the student
- **Headers**: 
  - `Authorization: Bearer {token}`
- **Response**:
  ```json
  {
    "success": true,
    "data": [...]
  }
  ```

### Student Schedule
- **GET** `/api/mobile/student/schedule`
- **Description**: Get class schedule for the student
- **Headers**: 
  - `Authorization: Bearer {token}`
- **Response**:
  ```json
  {
    "success": true,
    "data": [...]
  }
  ```

### Student Profile
- **GET** `/api/mobile/student/profile`
- **Description**: Get student profile information
- **Headers**: 
  - `Authorization: Bearer {token}`
- **Response**:
  ```json
  {
    "success": true,
    "data": {...}
  }
  ```

## Parent Endpoints

All parent endpoints require authentication.

### Parent Dashboard
- **GET** `/api/mobile/parent/dashboard`
- **Description**: Get dashboard data for all students associated with the parent
- **Headers**: 
  - `Authorization: Bearer {token}`
- **Response**:
  ```json
  {
    "success": true,
    "data": [...]
  }
  ```

### Student Grades (for parent)
- **GET** `/api/mobile/parent/student/{studentId}/grades`
- **Description**: Get grades for a specific student
- **Headers**: 
  - `Authorization: Bearer {token}`
- **Response**:
  ```json
  {
    "success": true,
    "data": {...}
  }
  ```

### Student Assignments (for parent)
- **GET** `/api/mobile/parent/student/{studentId}/assignments`
- **Description**: Get assignments for a specific student
- **Headers**: 
  - `Authorization: Bearer {token}`
- **Response**:
  ```json
  {
    "success": true,
    "data": {...}
  }
  ```

### Student Attendance (for parent)
- **GET** `/api/mobile/parent/student/{studentId}/attendance`
- **Description**: Get attendance data for a specific student
- **Headers**: 
  - `Authorization: Bearer {token}`
- **Response**:
  ```json
  {
    "success": true,
    "data": {...}
  }
  ```

### Student Schedule (for parent)
- **GET** `/api/mobile/parent/student/{studentId}/schedule`
- **Description**: Get schedule for a specific student
- **Headers**: 
  - `Authorization: Bearer {token}`
- **Response**:
  ```json
  {
    "success": true,
    "data": {...}
  }
  ```

## Teacher Endpoints

All teacher endpoints require authentication.

### Teacher Dashboard
- **GET** `/api/mobile/teacher/dashboard`
- **Description**: Get teacher dashboard data
- **Headers**: 
  - `Authorization: Bearer {token}`
- **Response**:
  ```json
  {
    "success": true,
    "data": {...}
  }
  ```

### Teacher Classes
- **GET** `/api/mobile/teacher/classes`
- **Description**: Get classes taught by the teacher
- **Headers**: 
  - `Authorization: Bearer {token}`
- **Response**:
  ```json
  {
    "success": true,
    "data": [...]
  }
  ```

### Students in Class
- **GET** `/api/mobile/teacher/students/{classId}`
- **Description**: Get students in a specific class
- **Headers**: 
  - `Authorization: Bearer {token}`
- **Response**:
  ```json
  {
    "success": true,
    "data": [...]
  }
  ```

### Teacher Assignments
- **GET** `/api/mobile/teacher/assignments`
- **Description**: Get assignments created by the teacher
- **Headers**: 
  - `Authorization: Bearer {token}`
- **Response**:
  ```json
  {
    "success": true,
    "data": [...]
  }
  ```

### Teacher Grades
- **GET** `/api/mobile/teacher/grades`
- **Description**: Get grades entered by the teacher
- **Headers**: 
  - `Authorization: Bearer {token}`
- **Response**:
  ```json
  {
    "success": true,
    "data": [...]
  }
  ```

## Rate Limiting

The API implements rate limiting to prevent abuse:
- Login attempts: 10 per minute
- Logout attempts: 30 per minute
- Token refresh: 10 per minute
- User profile requests: 60 per minute
- Authenticated user requests: 120 per minute