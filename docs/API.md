# API Documentation

## 📡 Overview

This document describes the RESTful API endpoints for the Malnu Backend School Management System. The API follows REST conventions and returns JSON responses.

**Table of Contents:**
- [Authentication](#-authentication)
- [School Management](#-school-management)
  - [Students](#-students)
  - [Teachers](#-teachers)
  - [Inventory](#-inventory)
- [Attendance Management](#-attendance-management)
- [Calendar Management](#-calendar-management)
- [Error Responses](#-error-responses)
- [Response Format](#-response-format)
- [Rate Limiting](#-rate-limiting)
- [Implementation Status](#-implementation-status)

**Implementation Status:** 47 of 47 documented endpoints (100%)

## 🔐 Authentication

All API endpoints (except authentication endpoints) require JWT authentication.

### Authentication Endpoints (Public)
The following endpoints do NOT require authentication:
- `POST /auth/register` - Rate limit: 3 requests/minute
- `POST /auth/login` - Rate limit: 5 requests/minute
- `POST /auth/password/forgot` - Rate limit: 3 requests/minute
- `POST /auth/password/reset` - Rate limit: 3 requests/minute

### Protected Endpoints
All other endpoints require a valid JWT token in the Authorization header.

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

### Get Inventory Items ✅
```http
GET /school/inventory
Authorization: Bearer <jwt_token>
```

**Query Parameters:**
- `category_id` (optional): Filter by category UUID
- `status` (optional): Filter by status (available, assigned, maintenance, retired)
- `search` (optional): Search by name, asset code, or serial number
- `page` (optional): Page number (default: 1)
- `limit` (optional): Items per page (default: 15)

**Response:**
```json
{
  "success": true,
  "message": "Inventory items retrieved successfully",
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": "uuid-string",
        "name": "Desktop Computer",
        "asset_code": "COMP-001",
        "serial_number": "SN123456",
        "category_id": "uuid-string",
        "quantity": 1,
        "status": "available",
        "assigned_to": null,
        "assigned_date": null,
        "last_maintenance": null,
        "created_at": "2025-01-01T00:00:00Z"
      }
    ],
    "total": 50,
    "per_page": 15
  }
}
```

**Implementation Status:** ✅ Implemented

---

### Get Inventory Item ✅
```http
GET /school/inventory/{id}
Authorization: Bearer <jwt_token>
```

**Response:**
```json
{
  "success": true,
  "message": "Inventory item retrieved successfully",
  "data": {
    "id": "uuid-string",
    "name": "Desktop Computer",
    "asset_code": "COMP-001",
    "serial_number": "SN123456",
    "category_id": "uuid-string",
    "quantity": 1,
    "status": "available",
    "assigned_to": null,
    "assigned_date": null,
    "last_maintenance": "2025-01-15",
    "category": {
      "id": "uuid-string",
      "name": "Computers"
    },
    "assignedTo": null,
    "maintenanceRecords": [],
    "assignments": []
  }
}
```

**Implementation Status:** ✅ Implemented

---

### Create Inventory Item ✅
```http
POST /school/inventory
Authorization: Bearer <jwt_token>
Content-Type: application/json

{
  "name": "Desktop Computer",
  "asset_code": "COMP-001",
  "serial_number": "SN123456",
  "category_id": "uuid-string",
  "quantity": 1,
  "status": "available"
}
```

**Request Parameters:**
- `name` (required): Item name
- `category` (required): Category name or `category_id` (UUID)
- `quantity` (required): Number of items
- `asset_code` (optional): Asset identification code
- `serial_number` (optional): Serial number
- `status` (optional): Status - default: available
- `description` (optional): Item description
- `purchase_date` (optional): Purchase date
- `purchase_cost` (optional): Purchase cost

**Response:**
```json
{
  "success": true,
  "message": "Inventory item created successfully",
  "data": {
    "id": "uuid-string",
    "name": "Desktop Computer",
    "asset_code": "COMP-001",
    "status": "available",
    "created_at": "2025-01-01T00:00:00Z"
  }
}
```

**Implementation Status:** ✅ Implemented

---

### Update Inventory Item ✅
```http
PUT /school/inventory/{id}
Authorization: Bearer <jwt_token>
Content-Type: application/json

{
  "name": "Updated Computer Name",
  "status": "available",
  "quantity": 2
}
```

**Request Parameters:**
- All fields from create, optional
- When setting `status` to `assigned`, `assigned_to` is required

**Response:**
```json
{
  "success": true,
  "message": "Inventory item updated successfully",
  "data": {
    "id": "uuid-string",
    "name": "Updated Computer Name",
    "status": "available"
  }
}
```

**Implementation Status:** ✅ Implemented

---

### Delete Inventory Item ✅
```http
DELETE /school/inventory/{id}
Authorization: Bearer <jwt_token>
```

**Constraints:**
- Cannot delete items with status `assigned`

**Error Response (assigned item):**
```json
{
  "success": false,
  "message": "Cannot delete an assigned item",
  "error_code": "ASSIGNED_ITEM_DELETION_ERROR"
}
```

**Implementation Status:** ✅ Implemented

---

### Assign Inventory Item ✅
```http
POST /school/inventory/{id}/assign
Authorization: Bearer <jwt_token>
Content-Type: application/json

{
  "assigned_to": "user-uuid",
  "assigned_to_type": "user",
  "notes": "Assigned to IT department"
}
```

**Request Parameters:**
- `assigned_to` (required): User UUID to assign item to
- `assigned_to_type` (optional): Type (default: user)
- `notes` (optional): Assignment notes

**Error Response (item not available):**
```json
{
  "success": false,
  "message": "Item is not available for assignment",
  "error_code": "ITEM_NOT_AVAILABLE"
}
```

**Implementation Status:** ✅ Implemented

---

### Return Inventory Item ✅
```http
POST /school/inventory/{id}/return
Authorization: Bearer <jwt_token>
```

**Error Response (item not assigned):**
```json
{
  "success": false,
  "message": "Item is not assigned",
  "error_code": "ITEM_NOT_ASSIGNED"
}
```

**Implementation Status:** ✅ Implemented

---

### Create Maintenance Record ✅
```http
POST /school/inventory/{id}/maintenance
Authorization: Bearer <jwt_token>
Content-Type: application/json

{
  "maintenance_date": "2025-01-20",
  "maintenance_type": "preventive",
  "description": "Regular maintenance check",
  "cost": 150.00,
  "performed_by": "Tech Services",
  "notes": "Replaced thermal paste"
}
```

**Request Parameters:**
- `maintenance_date` (required): Date of maintenance
- `maintenance_type` (required): Type (preventive, corrective, emergency)
- `description` (optional): Description of work done
- `cost` (optional): Cost of maintenance
- `performed_by` (optional): Who performed maintenance
- `notes` (optional): Additional notes

**Implementation Status:** ✅ Implemented

---

### Get Inventory Assignments ✅
```http
GET /school/inventory/{id}/assignments
Authorization: Bearer <jwt_token>
```

**Response:**
```json
{
  "success": true,
  "message": "Assignment history retrieved successfully",
  "data": [
    {
      "id": "uuid-string",
      "assigned_to": "user-uuid",
      "assigned_date": "2025-01-10",
      "status": "active",
      "returned_date": null,
      "assignedTo": {
        "id": "user-uuid",
        "name": "John Doe"
      }
    }
  ]
}
```

**Implementation Status:** ✅ Implemented

---

### Get Maintenance Records ✅
```http
GET /school/inventory/{id}/maintenance
Authorization: Bearer <jwt_token>
```

**Response:**
```json
{
  "success": true,
  "message": "Maintenance records retrieved successfully",
  "data": [
    {
      "id": "uuid-string",
      "maintenance_date": "2025-01-15",
      "maintenance_type": "preventive",
      "description": "Regular check",
      "cost": 150.00,
      "performed_by": "Tech Services",
      "notes": null
    }
  ]
}
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

### Common Error Codes

#### UNAUTHORIZED (401)
Invalid or missing JWT token.
```json
{
  "success": false,
  "message": "Unauthorized",
  "data": null
}
```

#### FORBIDDEN (403)
User does not have required role or permission.
```json
{
  "success": false,
  "message": "You do not have permission to access this resource",
  "data": null
}
```

#### NOT_FOUND (404)
Resource not found.
```json
{
  "success": false,
  "message": "Inventory item not found",
  "data": null
}
```

#### VALIDATION_ERROR (422)
Input validation failed.
```json
{
  "success": false,
  "message": "The given data was invalid.",
  "data": {
    "email": ["The email must be a valid email address."],
    "password": ["The password must be at least 8 characters."],
    "assigned_to": ["assigned_to is required when status is assigned."]
  }
}
```

#### SERVER_ERROR (500)
Internal server error.
```json
{
  "success": false,
  "message": "Internal server error",
  "data": null
}
```

#### REGISTRATION_ERROR (400)
Registration failed (user already exists, validation failed).
```json
{
  "success": false,
  "message": "Registration failed",
  "data": {
    "email": ["The email has already been taken."]
  }
}
```

#### Business Logic Errors
Custom error codes for business rule violations:

**ASSIGNED_ITEM_DELETION_ERROR** (400)
```json
{
  "success": false,
  "message": "Cannot delete an assigned item",
  "error_code": "ASSIGNED_ITEM_DELETION_ERROR",
  "data": null
}
```

**ITEM_NOT_AVAILABLE** (400)
```json
{
  "success": false,
  "message": "Item is not available for assignment",
  "error_code": "ITEM_NOT_AVAILABLE",
  "data": null
}
```

**ITEM_NOT_ASSIGNED** (400)
```json
{
  "success": false,
  "message": "Item is not assigned",
  "error_code": "ITEM_NOT_ASSIGNED",
  "data": null
}
```

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
Standard success response format:
```json
{
  "success": true,
  "message": "Operation successful",
  "data": {
    // Response data (object or array)
  }
}
```

#### Create Success (201 Status Code)
```json
{
  "success": true,
  "message": "Inventory item created successfully",
  "data": {
    "id": "uuid-string",
    "name": "Desktop Computer",
    "status": "available",
    "created_at": "2025-01-12T10:30:00Z"
  }
}
```

#### List Success with Pagination
```json
{
  "success": true,
  "message": "Students retrieved successfully",
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": "uuid-string",
        "name": "John Doe",
        "email": "john@example.com",
        "status": "active"
      }
    ],
    "total": 50,
    "per_page": 15,
    "from": 1,
    "to": 15
  }
}
```

#### Delete Success
```json
{
  "success": true,
  "message": "Inventory item deleted successfully",
  "data": null
}
```

### Validation Error Response
Field validation errors with specific error messages:
```json
{
  "success": false,
  "message": "The given data was invalid.",
  "data": {
    "email": ["The email must be a valid email address."],
    "password": ["The password must be at least 8 characters."],
    "category_id": ["Category not found."],
    "assigned_to": ["assigned_to is required when status is assigned."]
  }
}
```

### Authentication Error Response
Missing or invalid JWT token:
```json
{
  "success": false,
  "message": "Unauthorized",
  "data": null
}
```

### Authorization Error Response
User lacks required role or permission:
```json
{
  "success": false,
  "message": "You do not have permission to access this resource",
  "data": null
}
```

---

## 🔒 Rate Limiting

API endpoints are rate-limited to prevent abuse:

### Rate Limits by Endpoint

- **POST /auth/login**: 5 requests per minute
- **POST /auth/register**: 3 requests per minute
- **POST /auth/password/forgot**: 3 requests per minute
- **POST /auth/password/reset**: 3 requests per minute
- **POST /auth/password/change**: 10 requests per minute
- **Public API endpoints**: 60 requests per minute
- **Protected API endpoints**: 300 requests per minute

### Rate Limit Headers
Rate limit headers are included in all responses:
```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59
X-RateLimit-Reset: 1640995200
Retry-After: 30
```

### Rate Limit Exceeded Response
When rate limit is exceeded, a `429 Too Many Requests` response is returned:
```json
{
  "success": false,
  "message": "Too many requests",
  "data": null
}
```

The `Retry-After` header indicates seconds to wait before retrying.

---

## 📄 Pagination & Filtering

### Pagination
Many list endpoints support pagination to handle large datasets efficiently.

**Query Parameters:**
- `page` (optional): Page number (default: 1)
- `limit` (optional): Items per page (default: 15, max: 100)

**Example Request:**
```http
GET /school/students?page=2&limit=20
Authorization: Bearer <jwt_token>
```

**Paginated Response Structure:**
```json
{
  "success": true,
  "data": {
    "current_page": 2,
    "data": [...],
    "total": 150,
    "per_page": 20,
    "from": 21,
    "to": 40,
    "last_page": 8
  }
}
```

### Common Query Filters

#### Status Filtering
Filter by status (active, inactive, available, assigned, etc.):
```http
GET /school/students?status=active
GET /school/inventory?status=available
```

#### Search
Full-text search across relevant fields:
```http
GET /school/students?search=John
GET /school/inventory?search=computer
GET /school/teachers?search=math
```

#### Category Filtering
Filter by category or related entity:
```http
GET /school/inventory?category_id=uuid-string
```

#### Date Range Filtering
Filter records by date range (for calendar, attendance, etc.):
```http
GET /calendar/calendars/{calendarId}/events?start_date=2025-01-01&end_date=2025-01-31
```

### Filtering Notes

- All filters are optional - use only what you need
- Multiple filters are combined with AND logic
- Use URL encoding for special characters in search terms
- Empty string values are ignored

---

## 📊 Implementation Status

| Section | Implemented | Documented | Status |
|---------|-------------|------------|--------|
| Authentication | 8 | 8 | ✅ 100% |
| School Management | 17 | 17 | ✅ 100% |
| Attendance Management | 10 | 10 | ✅ 100% |
| Calendar Management | 11 | 11 | ✅ 100% |
| User Management | 0 | 0 | ❌ Not Implemented |
| Class Management | 0 | 0 | ❌ Not Implemented |
| Subject Management | 0 | 0 | ❌ Not Implemented |
| Schedule Management | 0 | 0 | ❌ Not Implemented |
| Grade Management | 0 | 0 | ❌ Not Implemented |
| Digital Library | 0 | 0 | ❌ Not Implemented |
| E-Learning | 0 | 0 | ❌ Not Implemented |
| Reports & Analytics | 0 | 0 | ❌ Not Implemented |
| **Total** | **46** | **46** | **100%** |

### School Management Breakdown
- Students: 5 endpoints (index, store, show, update, destroy)
- Teachers: 5 endpoints (index, store, show, update, destroy)
- Inventory: 10 endpoints (index, store, show, update, destroy, assign, return, maintenance, getAssignments, getMaintenanceRecords)

---

*This API documentation is continuously updated as new endpoints are implemented.*

**Last Updated:** 2025-01-12

---

## 🎭 Role-Based Access Control

Different endpoints require different roles for access:

### Admin & Staff Roles (Super Admin | Kepala Sekolah | Staf TU)
Can access:
- ✅ All School Management endpoints (Students, Teachers, Inventory)
- ✅ All Attendance Management endpoints
- ✅ Calendar write operations (create, update, delete)
- ✅ Event write operations (create, update, delete)
- ✅ Calendar sharing
- ✅ Resource booking

### Teacher Role (Guru)
Can access:
- ✅ All Attendance Management endpoints
- ✅ Calendar write operations (create, update, delete)
- ✅ Event write operations (create, update, delete)
- ✅ Calendar sharing
- ✅ Resource booking
- ❌ School Management write operations (Students, Teachers, Inventory)
- ✅ Read-only access to School Management data

### All Authenticated Users
Can access:
- ✅ Calendar read operations
- ✅ Event read operations
- ✅ Event registration
- ✅ Own user profile (`GET /auth/me`)

### Role Checking
The API automatically checks user roles based on JWT token claims. Unauthorized access returns a 403 Forbidden response:

```json
{
  "success": false,
  "message": "You do not have permission to access this resource",
  "data": null
}
```
