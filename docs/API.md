# API Documentation

## 📡 Overview

This document describes the RESTful API endpoints for the Malnu Backend School Management System. The API follows REST conventions and returns JSON responses.

**Implementation Status:** 38 of 74 endpoints implemented (51%)

## 🔐 Authentication

All API endpoints (except authentication endpoints) require JWT authentication.

### Headers
```
Authorization: Bearer <jwt_token>
Content-Type: application/json
```

### Register ✅
```http
POST /api/v1/auth/register
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
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "user": {
      "id": "uuid-string",
      "name": "John Doe",
      "email": "john@example.com",
      "role": "student"
    }
  },
  "message": "User registered successfully",
  "timestamp": "2026-01-08T12:00:00+00:00"
}
```

**Implementation Status:** ✅ Implemented

---

### Login ✅
```http
POST /api/v1/auth/login
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
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "user": {
      "id": "uuid-string",
      "name": "John Doe",
      "email": "john@example.com",
      "role": "student"
    },
    "expires_in": 3600
  },
  "message": "Login successful",
  "timestamp": "2026-01-08T12:00:00+00:00"
}
```

**Implementation Status:** ✅ Implemented

---

### Logout ✅
```http
POST /api/v1/auth/logout
Authorization: Bearer <jwt_token>
```

**Response:**
```json
{
  "success": true,
  "data": null,
  "message": "Successfully logged out",
  "timestamp": "2026-01-08T12:00:00+00:00"
}
```

**Implementation Status:** ✅ Implemented

---

### Refresh Token ✅
```http
POST /api/v1/auth/refresh
Authorization: Bearer <jwt_token>
```

**Response:**
```json
{
  "success": true,
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "expires_in": 3600
  },
  "message": "Token refreshed successfully",
  "timestamp": "2026-01-08T12:00:00+00:00"
}
```

**Implementation Status:** ✅ Implemented

---

### Get Current User ✅
```http
GET /api/v1/auth/me
Authorization: Bearer <jwt_token>
```

**Response:**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": "uuid-string",
      "name": "John Doe",
      "email": "john@example.com",
      "role": "student"
    }
  },
  "message": "User retrieved successfully",
  "timestamp": "2026-01-08T12:00:00+00:00"
}
```

**Implementation Status:** ✅ Implemented

---

### Request Password Reset ✅
```http
POST /api/v1/auth/password/forgot
Content-Type: application/json

{
  "email": "john@example.com"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "message": "If email exists, a reset link has been sent"
  },
  "message": "Password reset email sent",
  "timestamp": "2026-01-08T12:00:00+00:00"
}
```

**Implementation Status:** ✅ Implemented

---

### Reset Password ✅
```http
POST /api/v1/auth/password/reset
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
  "data": {
    "message": "Your password has been reset"
  },
  "message": "Password reset successful",
  "timestamp": "2026-01-08T12:00:00+00:00"
}
```

**Implementation Status:** ✅ Implemented

---

### Change Password ✅
```http
POST /api/v1/auth/password/change
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
  "data": {
    "message": "Your password has been changed"
  },
  "message": "Password changed successfully",
  "timestamp": "2026-01-08T12:00:00+00:00"
}
```

**Implementation Status:** ✅ Implemented

---

## 👥 School Management

All endpoints in this section require JWT authentication.

### Get Students ✅
```http
GET /api/v1/school/students
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
  },
  "message": "Students retrieved successfully",
  "timestamp": "2026-01-08T12:00:00+00:00"
}
```

**Implementation Status:** ✅ Implemented

---

### Get Student ✅
```http
GET /api/v1/school/students/{id}
Authorization: Bearer <jwt_token>
```

**Implementation Status:** ✅ Implemented

---

### Create Student ✅
```http
POST /api/v1/school/students
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
PUT /api/v1/school/students/{id}
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
DELETE /api/v1/school/students/{id}
Authorization: Bearer <jwt_token>
```

**Implementation Status:** ✅ Implemented

---

### Get Teachers ✅
```http
GET /api/v1/school/teachers
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
  },
  "message": "Teachers retrieved successfully",
  "timestamp": "2026-01-08T12:00:00+00:00"
}
```

**Implementation Status:** ✅ Implemented

---

### Get Teacher ✅
```http
GET /api/v1/school/teachers/{id}
Authorization: Bearer <jwt_token>
```

**Implementation Status:** ✅ Implemented

---

### Create Teacher ✅
```http
POST /api/v1/school/teachers
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
PUT /api/v1/school/teachers/{id}
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
DELETE /api/v1/school/teachers/{id}
Authorization: Bearer <jwt_token>
```

**Implementation Status:** ✅ Implemented

---

## 📅 Attendance Management

All endpoints in this section require JWT authentication.

### Get Staff Attendances ✅
```http
GET /api/v1/attendance/staff-attendances
Authorization: Bearer <jwt_token>
```

**Implementation Status:** ✅ Implemented

---

### Get Staff Attendance ✅
```http
GET /api/v1/attendance/staff-attendances/{id}
Authorization: Bearer <jwt_token>
```

**Implementation Status:** ✅ Implemented

---

### Create Staff Attendance ✅
```http
POST /api/v1/attendance/staff-attendances
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
PUT /api/v1/attendance/staff-attendances/{id}
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
DELETE /api/v1/attendance/staff-attendances/{id}
Authorization: Bearer <jwt_token>
```

**Implementation Status:** ✅ Implemented

---

### Mark Staff Attendance ✅
```http
POST /api/v1/attendance/staff-attendances/mark-attendance
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
GET /api/v1/attendance/leave-types
Authorization: Bearer <jwt_token>
```

**Implementation Status:** ✅ Implemented

---

### Get Leave Type ✅
```http
GET /api/v1/attendance/leave-types/{id}
Authorization: Bearer <jwt_token>
```

**Implementation Status:** ✅ Implemented

---

### Create Leave Type ✅
```http
POST /api/v1/attendance/leave-types
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
PUT /api/v1/attendance/leave-types/{id}
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
DELETE /api/v1/attendance/leave-types/{id}
Authorization: Bearer <jwt_token>
```

**Implementation Status:** ✅ Implemented

---

### Get Leave Requests ✅
```http
GET /api/v1/attendance/leave-requests
Authorization: Bearer <jwt_token>
```

**Implementation Status:** ✅ Implemented

---

### Get Leave Request ✅
```http
GET /api/v1/attendance/leave-requests/{id}
Authorization: Bearer <jwt_token>
```

**Implementation Status:** ✅ Implemented

---

### Create Leave Request ✅
```http
POST /api/v1/attendance/leave-requests
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
PUT /api/v1/attendance/leave-requests/{id}
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
DELETE /api/v1/attendance/leave-requests/{id}
Authorization: Bearer <jwt_token>
```

**Implementation Status:** ✅ Implemented

---

### Approve Leave Request ✅
```http
POST /api/v1/attendance/leave-requests/{id}/approve
Authorization: Bearer <jwt_token>
```

**Implementation Status:** ✅ Implemented

---

### Reject Leave Request ✅
```http
POST /api/v1/attendance/leave-requests/{id}/reject
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
POST /api/v1/calendar/calendars
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
GET /api/v1/calendar/calendars/{id}
Authorization: Bearer <jwt_token>
```

**Implementation Status:** ✅ Implemented

---

### Update Calendar ✅
```http
PUT /api/v1/calendar/calendars/{id}
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
DELETE /api/v1/calendar/calendars/{id}
Authorization: Bearer <jwt_token>
```

**Implementation Status:** ✅ Implemented

---

### Get Calendar Events ✅
```http
GET /api/v1/calendar/calendars/{calendarId}/events
Authorization: Bearer <jwt_token>
```

**Query Parameters:**
- `start_date`: Filter by start date
- `end_date`: Filter by end date

**Implementation Status:** ✅ Implemented

---

### Share Calendar ✅
```http
POST /api/v1/calendar/calendars/{calendarId}/share
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
POST /api/v1/calendar/events
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
GET /api/v1/calendar/events/{id}
Authorization: Bearer <jwt_token>
```

**Implementation Status:** ✅ Implemented

---

### Update Event ✅
```http
PUT /api/v1/calendar/events/{id}
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
DELETE /api/v1/calendar/events/{id}
Authorization: Bearer <jwt_token>
```

**Implementation Status:** ✅ Implemented

---

### Register for Event ✅
```http
POST /api/v1/calendar/events/{eventId}/register
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
POST /api/v1/calendar/resources/book
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

## 🔍 System Health

### Health Check ✅
```http
GET /api/v1/system/health
```

**Response:**
```json
{
  "success": true,
  "data": {
    "status": "healthy",
    "timestamp": "2026-01-10T12:00:00+00:00",
    "checks": {
      "cache": {
        "status": "healthy",
        "message": "Cache service is operational",
        "metrics": {
          "total_commands": 15234,
          "total_keys": 245,
          "keyspace_hits": 12345,
          "keyspace_misses": 2889,
          "hit_rate": 81.04
        }
      },
      "email": {
        "service": "email",
        "status": "healthy",
        "circuit_breaker": {
          "service": "email_service",
          "state": "closed",
          "failures": 0,
          "failure_threshold": 5,
          "timeout_seconds": 60
        },
        "configuration": {
          "timeout_seconds": 10,
          "max_retries": 3,
          "initial_delay_ms": 100
        }
      },
      "memory": {
        "status": "healthy",
        "usage_bytes": 83886080,
        "limit_bytes": 536870912,
        "usage_percent": 15.63
      },
      "disk": {
        "status": "healthy",
        "free_bytes": 107374182400,
        "total_bytes": 214748364800,
        "used_bytes": 107374182400,
        "usage_percent": 50
      }
    }
  },
  "message": "Health check completed",
  "timestamp": "2026-01-10T12:00:00+00:00"
}
```

**Implementation Status:** ✅ Implemented

**Health Status Values:**
- `healthy`: All checks passing
- `degraded`: Some checks passing but with warnings (e.g., high memory/disk usage)
- `unhealthy`: Critical failures (e.g., cache down, circuit breaker open)

---

## 🚨 Error Responses

All error responses follow standardized format with structured error codes.

For complete error code reference, see [API_ERROR_CODES.md](API_ERROR_CODES.md).

### Standard Error Response Format

```json
{
  "success": false,
  "error": {
    "message": "User-friendly error message",
    "code": "AUTH_001",
    "type": "authentication",
    "details": {
      "field": "Specific validation details (if applicable)"
    }
  },
  "timestamp": "2026-01-08T12:00:00+00:00"
}
```

### Common Error Codes

#### Authentication Errors (AUTH_XXX)
- `AUTH_001` (401): Invalid credentials
- `AUTH_002` (401): Invalid token
- `AUTH_003` (401): Token expired
- `AUTH_004` (401): Token blacklisted
- `AUTH_005` (401): Unauthorized (not authenticated)
- `AUTH_006` (403): Forbidden (insufficient permissions)
- `AUTH_007` (404): User not found
- `AUTH_008` (409): User already exists

#### Validation Errors (VAL_XXX)
- `VAL_001` (422): Validation failed
- `VAL_002` (422): Required field missing
- `VAL_003` (422): Invalid format
- `VAL_004` (422): Invalid type
- `VAL_005` (422): Invalid length
- `VAL_006` (422): Invalid range
- `VAL_007` (422): Invalid date
- `VAL_008` (409): Duplicate entry

#### Resource Errors (RES_XXX)
- `RES_001` (404): Resource not found
- `RES_002` (400): Creation failed
- `RES_003` (400): Update failed
- `RES_004` (400): Deletion failed
- `RES_005` (423): Resource locked
- `RES_006` (400): Insufficient balance
- `RES_007` (409): Already processed

#### Server Errors (SRV_XXX)
- `SRV_001` (500): Internal error
- `SRV_002` (500): Database error
- `SRV_003` (502): External service error
- `SRV_004` (504): Timeout
- `SRV_005` (503): Maintenance

#### Rate Limiting Errors (RTL_XXX)
- `RTL_001` (429): Rate limit exceeded

---

## 📝 Response Format

### Success Response
```json
{
  "success": true,
  "data": {
    // Response data
  },
  "message": "Operation successful",
  "timestamp": "2026-01-08T12:00:00+00:00"
}
```

### Error Response
```json
{
  "success": false,
  "error": {
    "message": "Error message here",
    "code": "AUTH_001",
    "type": "authentication",
    "details": {}
  },
  "timestamp": "2026-01-08T12:00:00+00:00"
}
```

### Validation Error Response
```json
{
  "success": false,
  "error": {
    "message": "Validation failed. Please check your input.",
    "code": "VAL_001",
    "type": "validation",
    "details": {
      "email": ["The email must be a valid email address."],
      "password": ["The password must be at least 6 characters."]
    }
  },
  "timestamp": "2026-01-08T12:00:00+00:00"
}
```

---

## 🔒 Rate Limiting

API endpoints are rate-limited to prevent abuse:

- **POST /api/v1/auth/login**: 5 requests per minute
- **POST /api/v1/auth/register**: 3 requests per minute
- **POST /api/v1/auth/password/forgot**: 3 requests per minute
- **POST /api/v1/auth/password/reset**: 3 requests per minute
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
  "error": {
    "message": "Rate limit exceeded",
    "code": "RTL_001",
    "type": "rate_limit",
    "details": {}
  },
  "timestamp": "2026-01-08T12:00:00+00:00"
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
| System Health | 1 | 1 | ✅ 100% |
| **Total** | **38** | **74** | **51%** |

---

*This API documentation is continuously updated as new endpoints are implemented.*

**Last Updated:** January 8, 2026
