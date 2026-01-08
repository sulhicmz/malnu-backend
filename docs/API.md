# API Documentation

## 📡 Overview

This document describes the RESTful API endpoints for the Malnu Backend School Management System. The API follows REST conventions and returns JSON responses.

**Implementation Status:** 27 of 54 endpoints implemented (50%)

## 🔐 Authentication

All API endpoints (except authentication endpoints) require JWT authentication.

### Headers
```
Authorization: Bearer <jwt_token>
Content-Type: application/json
```

### Register ✅
```http
POST /auth/register
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123"
}
```

**Response:**
```json
{
  "success": true,
  "message": "User registered successfully",
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "user": {
      "id": "uuid-string",
      "name": "John Doe",
      "email": "john@example.com",
      "role": "student"
    }
  }
}
```

**Implementation Status:** ✅ Implemented

---

### Login ✅
```http
POST /auth/login
Content-Type: application/json

{
  "email": "john@example.com",
  "password": "password123"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "user": {
      "id": "uuid-string",
      "name": "John Doe",
      "email": "john@example.com",
      "role": "student"
    },
    "expires_in": 3600
  }
}
```

**Implementation Status:** ✅ Implemented

---

### Logout ✅
```http
POST /auth/logout
Authorization: Bearer <jwt_token>
```

**Response:**
```json
{
  "success": true,
  "message": "Successfully logged out",
  "data": null
}
```

**Implementation Status:** ✅ Implemented

---

### Refresh Token ✅
```http
POST /auth/refresh
Authorization: Bearer <jwt_token>
```

**Response:**
```json
{
  "success": true,
  "message": "Token refreshed successfully",
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "expires_in": 3600
  }
}
```

**Implementation Status:** ✅ Implemented

---

### Get Current User ✅
```http
GET /auth/me
Authorization: Bearer <jwt_token>
```

**Response:**
```json
{
  "success": true,
  "message": "User retrieved successfully",
  "data": {
    "user": {
      "id": "uuid-string",
      "name": "John Doe",
      "email": "john@example.com",
      "role": "student"
    }
  }
}
```

**Implementation Status:** ✅ Implemented

---

### Request Password Reset ✅
```http
POST /auth/password/forgot
Content-Type: application/json

{
  "email": "john@example.com"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Password reset email sent",
  "data": {
    "message": "If the email exists, a reset link has been sent"
  }
}
```

**Implementation Status:** ✅ Implemented

---

### Reset Password ✅
```http
POST /auth/password/reset
Content-Type: application/json

{
  "token": "reset-token-here",
  "password": "newpassword123"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Password reset successful",
  "data": {
    "message": "Your password has been reset"
  }
}
```

**Implementation Status:** ✅ Implemented

---

### Change Password ✅
```http
POST /auth/password/change
Authorization: Bearer <jwt_token>
Content-Type: application/json

{
  "current_password": "oldpassword",
  "new_password": "newpassword123"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Password changed successfully",
  "data": {
    "message": "Your password has been changed"
  }
}
```

**Implementation Status:** ✅ Implemented

---

## 👥 School Management

All endpoints in this section require JWT authentication.

### Get Students ✅
```http
GET /school/students
Authorization: Bearer <jwt_token>
```

**Response:**
```json
{
  "success": true,
  "data": {
    "students": [
      {
        "id": "uuid-string",
        "user_id": "uuid-string",
        "name": "John Doe",
        "email": "john@example.com",
        "status": "active",
        "created_at": "2025-01-01T00:00:00Z"
      }
    ]
  }
}
```

**Implementation Status:** ✅ Implemented

---

### Get Student ✅
```http
GET /school/students/{id}
Authorization: Bearer <jwt_token>
```

**Implementation Status:** ✅ Implemented

---

### Create Student ✅
```http
POST /school/students
Authorization: Bearer <jwt_token>
Content-Type: application/json

{
  "user_id": "uuid-string",
  "name": "John Doe",
  "email": "john@example.com"
}
```

**Implementation Status:** ✅ Implemented

---

### Update Student ✅
```http
PUT /school/students/{id}
Authorization: Bearer <jwt_token>
Content-Type: application/json

{
  "name": "Updated Name",
  "email": "updated@example.com"
}
```

**Implementation Status:** ✅ Implemented

---

### Delete Student ✅
```http
DELETE /school/students/{id}
Authorization: Bearer <jwt_token>
```

**Implementation Status:** ✅ Implemented

---

### Get Teachers ✅
```http
GET /school/teachers
Authorization: Bearer <jwt_token>
```

**Response:**
```json
{
  "success": true,
  "data": {
    "teachers": [
      {
        "id": "uuid-string",
        "user_id": "uuid-string",
        "name": "Jane Smith",
        "email": "jane@example.com",
        "specialization": "Mathematics",
        "phone": "+6281234567890",
        "created_at": "2025-01-01T00:00:00Z"
      }
    ]
  }
}
```

**Implementation Status:** ✅ Implemented

---

### Get Teacher ✅
```http
GET /school/teachers/{id}
Authorization: Bearer <jwt_token>
```

**Implementation Status:** ✅ Implemented

---

### Create Teacher ✅
```http
POST /school/teachers
Authorization: Bearer <jwt_token>
Content-Type: application/json

{
  "user_id": "uuid-string",
  "name": "Jane Smith",
  "email": "jane@example.com",
  "specialization": "Mathematics",
  "phone": "+6281234567890"
}
```

**Implementation Status:** ✅ Implemented

---

### Update Teacher ✅
```http
PUT /school/teachers/{id}
Authorization: Bearer <jwt_token>
Content-Type: application/json

{
  "specialization": "Physics",
  "phone": "+6281234567890"
}
```

**Implementation Status:** ✅ Implemented

---

### Delete Teacher ✅
```http
DELETE /school/teachers/{id}
Authorization: Bearer <jwt_token>
```

**Implementation Status:** ✅ Implemented

---

## 📅 Attendance Management

All endpoints in this section require JWT authentication.

### Get Staff Attendances ✅
```http
GET /attendance/staff-attendances
Authorization: Bearer <jwt_token>
```

**Implementation Status:** ✅ Implemented

---

### Get Staff Attendance ✅
```http
GET /attendance/staff-attendances/{id}
Authorization: Bearer <jwt_token>
```

**Implementation Status:** ✅ Implemented

---

### Create Staff Attendance ✅
```http
POST /attendance/staff-attendances
Authorization: Bearer <jwt_token>
Content-Type: application/json

{
  "staff_id": "uuid-string",
  "date": "2025-01-01",
  "check_in": "08:00",
  "check_out": "17:00",
  "status": "present"
}
```

**Implementation Status:** ✅ Implemented

---

### Update Staff Attendance ✅
```http
PUT /attendance/staff-attendances/{id}
Authorization: Bearer <jwt_token>
Content-Type: application/json

{
  "status": "present",
  "check_out": "17:30"
}
```

**Implementation Status:** ✅ Implemented

---

### Delete Staff Attendance ✅
```http
DELETE /attendance/staff-attendances/{id}
Authorization: Bearer <jwt_token>
```

**Implementation Status:** ✅ Implemented

---

### Mark Staff Attendance ✅
```http
POST /attendance/staff-attendances/mark-attendance
Authorization: Bearer <jwt_token>
Content-Type: application/json

{
  "staff_id": "uuid-string",
  "status": "present",
  "check_in": "08:00"
}
```

**Implementation Status:** ✅ Implemented

---

### Get Leave Types ✅
```http
GET /attendance/leave-types
Authorization: Bearer <jwt_token>
```

**Implementation Status:** ✅ Implemented

---

### Get Leave Type ✅
```http
GET /attendance/leave-types/{id}
Authorization: Bearer <jwt_token>
```

**Implementation Status:** ✅ Implemented

---

### Create Leave Type ✅
```http
POST /attendance/leave-types
Authorization: Bearer <jwt_token>
Content-Type: application/json

{
  "name": "Annual Leave",
  "description": "Regular annual leave",
  "max_days": 14
}
```

**Implementation Status:** ✅ Implemented

---

### Update Leave Type ✅
```http
PUT /attendance/leave-types/{id}
Authorization: Bearer <jwt_token>
Content-Type: application/json

{
  "name": "Annual Leave",
  "max_days": 21
}
```

**Implementation Status:** ✅ Implemented

---

### Delete Leave Type ✅
```http
DELETE /attendance/leave-types/{id}
Authorization: Bearer <jwt_token>
```

**Implementation Status:** ✅ Implemented

---

### Get Leave Requests ✅
```http
GET /attendance/leave-requests
Authorization: Bearer <jwt_token>
```

**Implementation Status:** ✅ Implemented

---

### Get Leave Request ✅
```http
GET /attendance/leave-requests/{id}
Authorization: Bearer <jwt_token>
```

**Implementation Status:** ✅ Implemented

---

### Create Leave Request ✅
```http
POST /attendance/leave-requests
Authorization: Bearer <jwt_token>
Content-Type: application/json

{
  "staff_id": "uuid-string",
  "leave_type_id": "uuid-string",
  "start_date": "2025-01-15",
  "end_date": "2025-01-20",
  "reason": "Family vacation"
}
```

**Implementation Status:** ✅ Implemented

---

### Update Leave Request ✅
```http
PUT /attendance/leave-requests/{id}
Authorization: Bearer <jwt_token>
Content-Type: application/json

{
  "start_date": "2025-01-16",
  "end_date": "2025-01-21"
}
```

**Implementation Status:** ✅ Implemented

---

### Delete Leave Request ✅
```http
DELETE /attendance/leave-requests/{id}
Authorization: Bearer <jwt_token>
```

**Implementation Status:** ✅ Implemented

---

### Approve Leave Request ✅
```http
POST /attendance/leave-requests/{id}/approve
Authorization: Bearer <jwt_token>
```

**Implementation Status:** ✅ Implemented

---

### Reject Leave Request ✅
```http
POST /attendance/leave-requests/{id}/reject
Authorization: Bearer <jwt_token>
Content-Type: application/json

{
  "reason": "Insufficient staff coverage"
}
```

**Implementation Status:** ✅ Implemented

---

## 📅 Calendar Management

All endpoints in this section require JWT authentication.

### Create Calendar ✅
```http
POST /calendar/calendars
Authorization: Bearer <jwt_token>
Content-Type: application/json

{
  "name": "Academic Calendar 2025",
  "description": "Main academic calendar",
  "color": "#FF5733"
}
```

**Implementation Status:** ✅ Implemented

---

### Get Calendar ✅
```http
GET /calendar/calendars/{id}
Authorization: Bearer <jwt_token>
```

**Implementation Status:** ✅ Implemented

---

### Update Calendar ✅
```http
PUT /calendar/calendars/{id}
Authorization: Bearer <jwt_token>
Content-Type: application/json

{
  "name": "Updated Calendar Name",
  "description": "Updated description"
}
```

**Implementation Status:** ✅ Implemented

---

### Delete Calendar ✅
```http
DELETE /calendar/calendars/{id}
Authorization: Bearer <jwt_token>
```

**Implementation Status:** ✅ Implemented

---

### Get Calendar Events ✅
```http
GET /calendar/calendars/{calendarId}/events
Authorization: Bearer <jwt_token>
```

**Query Parameters:**
- `start_date`: Filter by start date
- `end_date`: Filter by end date

**Implementation Status:** ✅ Implemented

---

### Share Calendar ✅
```http
POST /calendar/calendars/{calendarId}/share
Authorization: Bearer <jwt_token>
Content-Type: application/json

{
  "user_id": "uuid-string",
  "permission": "read" or "write"
}
```

**Implementation Status:** ✅ Implemented

---

### Create Event ✅
```http
POST /calendar/events
Authorization: Bearer <jwt_token>
Content-Type: application/json

{
  "calendar_id": "uuid-string",
  "title": "Exam Day",
  "description": "Mid-term examination",
  "start_time": "2025-01-15T09:00:00Z",
  "end_time": "2025-01-15T11:00:00Z",
  "location": "Main Hall",
  "event_type": "exam"
}
```

**Implementation Status:** ✅ Implemented

---

### Get Event ✅
```http
GET /calendar/events/{id}
Authorization: Bearer <jwt_token>
```

**Implementation Status:** ✅ Implemented

---

### Update Event ✅
```http
PUT /calendar/events/{id}
Authorization: Bearer <jwt_token>
Content-Type: application/json

{
  "title": "Updated Event Title",
  "start_time": "2025-01-15T10:00:00Z"
}
```

**Implementation Status:** ✅ Implemented

---

### Delete Event ✅
```http
DELETE /calendar/events/{id}
Authorization: Bearer <jwt_token>
```

**Implementation Status:** ✅ Implemented

---

### Register for Event ✅
```http
POST /calendar/events/{eventId}/register
Authorization: Bearer <jwt_token>
Content-Type: application/json

{
  "user_id": "uuid-string"
}
```

**Implementation Status:** ✅ Implemented

---

### Book Resource ✅
```http
POST /calendar/resources/book
Authorization: Bearer <jwt_token>
Content-Type: application/json

{
  "resource_id": "uuid-string",
  "event_id": "uuid-string",
  "start_time": "2025-01-15T09:00:00Z",
  "end_time": "2025-01-15T11:00:00Z"
}
```

**Implementation Status:** ✅ Implemented

---

## 🚨 Error Responses

All error responses follow this format:

```json
{
  "success": false,
  "message": "Error message here",
  "data": null
}
```

### Common Error Codes
- `UNAUTHORIZED` (401): Invalid or missing authentication
- `FORBIDDEN` (403): Insufficient permissions
- `NOT_FOUND` (404): Resource not found
- `VALIDATION_ERROR` (422): Input validation failed
- `SERVER_ERROR` (500): Internal server error
- `REGISTRATION_ERROR` (400): Registration failed

---

## 📝 Response Format

### Success Response
```json
{
  "success": true,
  "message": "Operation successful",
  "data": {
    // Response data
  }
}
```

### Validation Error Response
```json
{
  "success": false,
  "message": "The given data was invalid.",
  "data": {
    "email": ["The email must be a valid email address."],
    "password": ["The password must be at least 6 characters."]
  }
}
```

---

## 🔒 Rate Limiting

API endpoints are rate-limited to prevent abuse:

- **POST /auth/login**: 5 requests per minute
- **POST /auth/register**: 3 requests per minute
- **POST /auth/password/forgot**: 3 requests per minute
- **POST /auth/password/reset**: 3 requests per minute
- **Public API endpoints**: 60 requests per minute
- **Protected API endpoints**: 300 requests per minute

Rate limit headers are included in responses:
```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59
X-RateLimit-Reset: 1640995200
Retry-After: 30
```

When rate limit is exceeded, a `429 Too Many Requests` response is returned:
```json
{
  "success": false,
  "message": "Too many requests",
  "data": null
}
```

---

## 📊 Implementation Status

| Section | Implemented | Total | Status |
|---------|-------------|-------|--------|
| Authentication | 8 | 8 | ✅ 100% |
| School Management | 8 | 8 | ✅ 100% |
| Attendance Management | 10 | 10 | ✅ 100% |
| Calendar Management | 11 | 11 | ✅ 100% |
| User Management | 0 | 3 | ❌ 0% |
| Class Management | 0 | 6 | ❌ 0% |
| Subject Management | 0 | 5 | ❌ 0% |
| Schedule Management | 0 | 5 | ❌ 0% |
| Grade Management | 0 | 4 | ❌ 0% |
| Digital Library | 0 | 5 | ❌ 0% |
| E-Learning | 0 | 4 | ❌ 0% |
| Reports & Analytics | 0 | 3 | ❌ 0% |
| **Total** | **37** | **73** | **51%** |

---

*This API documentation is continuously updated as new endpoints are implemented.*

**Last Updated:** 2025-01-08
