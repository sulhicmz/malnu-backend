# API Documentation

## 📑 Table of Contents

- [Overview](#-overview)
- [Authentication](#-authentication)
  - [Register](#-register-)
  - [Login](#-login-)
  - [Logout](#-logout-)
  - [Refresh Token](#-refresh-token-)
  - [Get Current User](#-get-current-user-)
  - [Request Password Reset](#-request-password-reset-)
  - [Reset Password](#-reset-password-)
  - [Change Password](#-change-password-)
- [School Management](#-school-management)
  - [Get Students](#-get-students-)
  - [Get Student](#-get-student-)
  - [Create Student](#-create-student-)
  - [Update Student](#-update-student-)
  - [Delete Student](#-delete-student-)
  - [Get Teachers](#-get-teachers-)
  - [Get Teacher](#-get-teacher-)
  - [Create Teacher](#-create-teacher-)
  - [Update Teacher](#-update-teacher-)
  - [Delete Teacher](#-delete-teacher-)
  - [Get Schedules](#-get-schedules-)
  - [Get Schedule](#-get-schedule-)
  - [Create Schedule](#-create-schedule-)
  - [Update Schedule](#-update-schedule-)
  - [Delete Schedule](#-delete-schedule-)
- [Student Attendance](#-student-attendance)
  - [Mark Student Attendance](#-mark-student-attendance-)
  - [Mark Bulk Student Attendance](#-mark-bulk-student-attendance-)
  - [Get Student Attendance](#-get-student-attendance-)
  - [Get Student Attendance Statistics](#-get-student-attendance-statistics-)
  - [Get Class Attendance](#-get-class-attendance-)
  - [Get Class Attendance Report](#-get-class-attendance-report-)
  - [Get Chronic Absentees](#-get-chronic-absentees-)
- [Staff Attendance](#-attendance-management)
  - [Get Staff Attendances](#-get-staff-attendances-)
  - [Get Staff Attendance](#-get-staff-attendance-)
  - [Create Staff Attendance](#-create-staff-attendance-)
  - [Update Staff Attendance](#-update-staff-attendance-)
  - [Delete Staff Attendance](#-delete-staff-attendance-)
  - [Mark Staff Attendance](#-mark-staff-attendance-)
- [Leave Management](#-leave-management)
  - [Get Leave Types](#-get-leave-types-)
  - [Get Leave Type](#-get-leave-type-)
  - [Create Leave Type](#-create-leave-type-)
  - [Update Leave Type](#-update-leave-type-)
  - [Delete Leave Type](#-delete-leave-type-)
  - [Get Leave Requests](#-get-leave-requests-)
  - [Get Leave Request](#-get-leave-request-)
  - [Create Leave Request](#-create-leave-request-)
  - [Update Leave Request](#-update-leave-request-)
  - [Delete Leave Request](#-delete-leave-request-)
  - [Approve Leave Request](#-approve-leave-request-)
  - [Reject Leave Request](#-reject-leave-request-)
- [Inventory Management](#-inventory-management)
  - [Get Inventory Items](#-get-inventory-items-)
  - [Get Inventory Item](#-get-inventory-item-)
  - [Create Inventory Item](#-create-inventory-item-)
  - [Update Inventory Item](#-update-inventory-item-)
  - [Delete Inventory Item](#-delete-inventory-item-)
  - [Assign Inventory Item](#-assign-inventory-item-)
  - [Return Inventory Item](#-return-inventory-item-)
  - [Record Maintenance](#-record-maintenance-)
  - [Get Item Assignments](#-get-item-assignments-)
  - [Get Maintenance Records](#-get-maintenance-records-)
- [Academic Records](#-academic-records)
  - [Calculate GPA](#-calculate-gpa-)
  - [Get Academic Performance](#-get-academic-performance-)
  - [Get Class Rank](#-get-class-rank-)
  - [Generate Transcript](#-generate-transcript-)
  - [Generate Report Card](#-generate-report-card-)
  - [Get Subject Grades](#-get-subject-grades-)
  - [Get Grades History](#-get-grades-history-)
- [Calendar Management](#-calendar-management)
  - [Create Calendar](#-create-calendar-)
  - [Get Calendar](#-get-calendar-)
  - [Update Calendar](#-update-calendar-)
  - [Delete Calendar](#-delete-calendar-)
  - [Get Calendar Events](#-get-calendar-events-)
  - [Share Calendar](#-share-calendar-)
  - [Create Event](#-create-event-)
  - [Get Event](#-get-event-)
  - [Update Event](#-update-event-)
  - [Delete Event](#-delete-event-)
  - [Register for Event](#-register-for-event-)
  - [Book Resource](#-book-resource-)
- [Notification Management](#-notification-management)
  - [Create Notification](#-create-notification-)
  - [Send Notification](#-send-notification-)
  - [Send Emergency Notification](#-send-emergency-notification-)
  - [Get My Notifications](#-get-my-notifications-)
  - [Get Notification](#-get-notification-)
  - [Mark Notification as Read](#-mark-notification-as-read-)
  - [Mark All Notifications as Read](#-mark-all-notifications-as-read-)
  - [Get Notification Delivery Statistics](#-get-notification-delivery-statistics-)
  - [Create Notification Template](#-create-notification-template-)
  - [Get Notification Templates](#-get-notification-templates-)
  - [Update Notification Preferences](#-update-notification-preferences-)
  - [Get Notification Preferences](#-get-notification-preferences-)
- [Error Responses](#-error-responses)
  - [Common Error Codes](#-common-error-codes)
  - [Response Format](#-response-format)
- [Rate Limiting](#-rate-limiting)
- [Implementation Status](#-implementation-status)

---

## 📡 Overview

This document describes RESTful API endpoints for Malnu Backend School Management System. The API follows REST conventions and returns JSON responses.

**Implementation Status:** 87 of 87 endpoints implemented (100%)

## 📖 Interactive API Documentation

For interactive API documentation with Swagger UI, see **[OpenAPI/Swagger Documentation](OPENAPI_SWAGGER.md)**.

The OpenAPI specification allows you to:
- Explore all available endpoints
- Test API requests directly from the browser
- View request/response examples
- Understand authentication requirements

To generate the OpenAPI specification:
```bash
# After installing zircote/swagger-php
vendor/bin/openapi -o public/swagger.json app
```

Then open `public/swagger.json` in the [Swagger UI Editor](https://editor.swagger.io/).

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

### Get Schedules ✅
```http
GET /school/schedules
Authorization: Bearer <jwt_token>
```

**Query Parameters:**
- `class_id` (optional): Filter by class
- `day_of_week` (optional): Filter by day of week (1-7)
- `teacher_id` (optional): Filter by teacher
- `room` (optional): Filter by room
- `page` (optional, default: 1): Page number for pagination
- `limit` (optional, default: 15): Items per page

**Response:**
```json
{
  "success": true,
  "data": {
    "schedules": [
      {
        "id": "uuid-string",
        "class_subject_id": "uuid-string",
        "day_of_week": 1,
        "start_time": "08:00",
        "end_time": "09:00",
        "room": "Room 101",
        "class_subject": {
          "id": "uuid-string",
          "class": {
            "id": "uuid-string",
            "name": "Class 10A"
          },
          "subject": {
            "id": "uuid-string",
            "name": "Mathematics",
            "code": "MATH101"
          },
          "teacher": {
            "id": "uuid-string",
            "user": {
              "name": "John Smith"
            }
          }
        }
      }
    ]
  },
  "timestamp": "2026-01-13T00:00:00Z"
}
```

**Implementation Status:** ✅ Implemented

---

### Get Schedule ✅
```http
GET /school/schedules/{id}
Authorization: Bearer <jwt_token>
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": "uuid-string",
    "class_subject_id": "uuid-string",
    "day_of_week": 1,
    "start_time": "08:00",
    "end_time": "09:00",
    "room": "Room 101",
    "class_subject": {
      "id": "uuid-string",
      "class": {
        "id": "uuid-string",
        "name": "Class 10A"
      },
      "subject": {
        "id": "uuid-string",
        "name": "Mathematics",
        "code": "MATH101"
      },
      "teacher": {
        "id": "uuid-string",
        "user": {
          "name": "John Smith"
        }
      }
    }
  },
  "timestamp": "2026-01-13T00:00:00Z"
}
```

**Implementation Status:** ✅ Implemented

---

### Create Schedule ✅
```http
POST /school/schedules
Authorization: Bearer <jwt_token>
Content-Type: application/json

{
  "class_subject_id": "uuid-string",
  "day_of_week": 1,
  "start_time": "08:00",
  "end_time": "09:00",
  "room": "Room 101"
}
```

**Error Response (Conflict):**
```json
{
  "success": false,
  "message": "Schedule conflicts detected",
  "error_code": "SCHEDULE_CONFLICT",
  "details": {
    "conflicts": [
      {
        "type": "teacher_conflict",
        "message": "Teacher John Smith is already scheduled during this time slot on day 1",
        "conflicting_schedule_id": "uuid-string"
      }
    ]
  },
  "timestamp": "2026-01-13T00:00:00Z"
}
```

**Implementation Status:** ✅ Implemented

---

### Update Schedule ✅
```http
PUT /school/schedules/{id}
Authorization: Bearer <jwt_token>
Content-Type: application/json

{
  "start_time": "10:00",
  "end_time": "11:00",
  "room": "Room 201"
}
```

**Error Response (Conflict):**
```json
{
  "success": false,
  "message": "Schedule conflicts detected",
  "error_code": "SCHEDULE_CONFLICT",
  "details": {
    "conflicts": [
      {
        "type": "room_conflict",
        "message": "Room Room 201 is already booked during this time slot on day 1",
        "conflicting_schedule_id": "uuid-string"
      }
    ]
  },
  "timestamp": "2026-01-13T00:00:00Z"
}
```

**Implementation Status:** ✅ Implemented

---

### Delete Schedule ✅
```http
DELETE /school/schedules/{id}
Authorization: Bearer <jwt_token>
```

**Response:**
```json
{
  "success": true,
  "message": "Schedule deleted successfully",
  "data": null,
  "timestamp": "2026-01-13T00:00:00Z"
}
```

**Implementation Status:** ✅ Implemented

---

## 📦 Inventory Management

All endpoints in this section require JWT authentication and role `Super Admin|Kepala Sekolah|Staf TU`.

### Get Inventory Items ✅
```http
GET /school/inventory
Authorization: Bearer <jwt_token>
```

**Query Parameters:**
- `category_id` (optional): Filter by category
- `status` (optional): Filter by status (available, assigned, maintenance, retired)
- `search` (optional): Search by name, asset code, or serial number
- `page` (optional, default: 1): Page number for pagination
- `limit` (optional, default: 15): Items per page

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
        "name": "Laptop Dell XPS 15",
        "asset_code": "INV-001",
        "serial_number": "SN123456789",
        "category_id": "uuid-string",
        "category": {
          "id": "uuid-string",
          "name": "Computers",
          "description": "Desktop computers and laptops"
        },
        "quantity": 10,
        "status": "available",
        "assigned_to": null,
        "assigned_date": null,
        "last_maintenance": "2025-01-01",
        "created_at": "2025-01-01T00:00:00Z"
      }
    ],
    "per_page": 15,
    "total": 50
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
    "name": "Laptop Dell XPS 15",
    "asset_code": "INV-001",
    "serial_number": "SN123456789",
    "category_id": "uuid-string",
    "category": {
      "id": "uuid-string",
      "name": "Computers"
    },
    "quantity": 10,
    "status": "available",
    "assigned_to": null,
    "assigned_date": null,
    "last_maintenance": "2025-01-01",
    "assignedTo": {
      "user_id": "uuid-string",
      "user_name": "John Doe",
      "assignment_date": "2025-01-10"
    },
    "maintenanceRecords": [
      {
        "id": "uuid-string",
        "maintenance_date": "2025-01-01",
        "maintenance_type": "routine",
        "description": "Regular maintenance check",
        "cost": 50000
      }
    ],
    "assignments": [
      {
        "id": "uuid-string",
        "assigned_to": "Jane Smith",
        "assigned_date": "2024-12-01",
        "returned_date": "2025-01-01",
        "status": "returned"
      }
    ]
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
  "name": "Laptop Dell XPS 15",
  "category_id": "uuid-string",
  "asset_code": "INV-001",
  "serial_number": "SN123456789",
  "quantity": 10,
  "purchase_date": "2025-01-01",
  "purchase_price": 15000000,
  "status": "available"
}
```

**Validation Rules:**
- `name` (required): Item name
- `category` (required): Category name (for validation, use category_id for reference)
- `quantity` (required): Quantity of items

**Response:**
```json
{
  "success": true,
  "message": "Inventory item created successfully",
  "data": {
    "id": "uuid-string",
    "name": "Laptop Dell XPS 15",
    "asset_code": "INV-001",
    "serial_number": "SN123456789",
    "category_id": "uuid-string",
    "quantity": 10,
    "status": "available",
    "created_at": "2025-01-15T10:30:00Z"
  }
}
```

**Error Response (Category Not Found):**
```json
{
  "success": false,
  "message": "The given data was invalid.",
  "error_code": "VALIDATION_ERROR",
  "data": {
    "category_id": ["Category not found."]
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
  "name": "Updated Laptop Name",
  "quantity": 8,
  "status": "available"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Inventory item updated successfully",
  "data": {
    "id": "uuid-string",
    "name": "Updated Laptop Name",
    "quantity": 8,
    "status": "available",
    "updated_at": "2025-01-15T11:00:00Z"
  }
}
```

**Error Response (Status Conflict):**
```json
{
  "success": false,
  "message": "The given data was invalid.",
  "error_code": "VALIDATION_ERROR",
  "data": {
    "assigned_to": ["assigned_to is required when status is assigned."]
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

**Response:**
```json
{
  "success": true,
  "message": "Inventory item deleted successfully",
  "data": null
}
```

**Error Response (Assigned Item):**
```json
{
  "success": false,
  "message": "Cannot delete an assigned item",
  "error_code": "ASSIGNED_ITEM_DELETION_ERROR",
  "data": null
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
  "assigned_to": "uuid-string",
  "assigned_to_type": "user",
  "notes": "Assigned to teacher for classroom use"
}
```

**Validation Rules:**
- `assigned_to` (required): User ID to assign to
- `assigned_to_type` (optional): Type of assigned entity (default: "user")
- `notes` (optional): Assignment notes

**Response:**
```json
{
  "success": true,
  "message": "Item assigned successfully",
  "data": {
    "id": "uuid-string",
    "asset_id": "uuid-string",
    "assigned_to": "uuid-string",
    "assigned_to_type": "user",
    "assigned_date": "2025-01-15",
    "status": "active",
    "notes": "Assigned to teacher for classroom use"
  }
}
```

**Error Response (Not Available):**
```json
{
  "success": false,
  "message": "Item is not available for assignment",
  "error_code": "ITEM_NOT_AVAILABLE",
  "data": null
}
```

**Implementation Status:** ✅ Implemented

---

### Return Inventory Item ✅
```http
POST /school/inventory/{id}/return
Authorization: Bearer <jwt_token>
```

**Response:**
```json
{
  "success": true,
  "message": "Item returned successfully",
  "data": null
}
```

**Error Response (Not Assigned):**
```json
{
  "success": false,
  "message": "Item is not assigned",
  "error_code": "ITEM_NOT_ASSIGNED",
  "data": null
}
```

**Implementation Status:** ✅ Implemented

---

### Record Maintenance ✅
```http
POST /school/inventory/{id}/maintenance
Authorization: Bearer <jwt_token>
Content-Type: application/json

{
  "maintenance_date": "2025-01-20",
  "maintenance_type": "routine",
  "description": "Monthly maintenance check",
  "cost": 75000,
  "performed_by": "Technician John",
  "notes": "Replaced battery and checked all ports"
}
```

**Validation Rules:**
- `maintenance_date` (required): Date of maintenance (Y-m-d format)
- `maintenance_type` (required): Type of maintenance
- `description` (optional): Description of maintenance
- `cost` (optional): Cost of maintenance
- `performed_by` (optional): Who performed maintenance
- `notes` (optional): Additional notes

**Response:**
```json
{
  "success": true,
  "message": "Maintenance record created successfully",
  "data": {
    "id": "uuid-string",
    "asset_id": "uuid-string",
    "maintenance_date": "2025-01-20",
    "maintenance_type": "routine",
    "description": "Monthly maintenance check",
    "cost": 75000,
    "performed_by": "Technician John",
    "notes": "Replaced battery and checked all ports",
    "created_at": "2025-01-20T00:00:00Z"
  }
}
```

**Implementation Status:** ✅ Implemented

---

### Get Item Assignments ✅
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
      "asset_id": "uuid-string",
      "assigned_to": "uuid-string",
      "assigned_to_type": "user",
      "assigned_date": "2024-12-01",
      "returned_date": "2025-01-01",
      "status": "returned",
      "notes": "Assigned for classroom use",
      "assignedTo": {
        "user_id": "uuid-string",
        "user_name": "John Doe",
        "email": "john@example.com"
      }
    },
    {
      "id": "uuid-string",
      "asset_id": "uuid-string",
      "assigned_to": "uuid-string",
      "assigned_to_type": "user",
      "assigned_date": "2025-01-15",
      "returned_date": null,
      "status": "active",
      "notes": "Current assignment"
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
      "asset_id": "uuid-string",
      "maintenance_date": "2025-01-20",
      "maintenance_type": "routine",
      "description": "Monthly maintenance check",
      "cost": 75000,
      "performed_by": "Technician John",
      "notes": "Replaced battery and checked all ports",
      "created_at": "2025-01-20T00:00:00Z"
    },
    {
      "id": "uuid-string",
      "asset_id": "uuid-string",
      "maintenance_date": "2025-02-20",
      "maintenance_type": "repair",
      "description": "Fixed screen issue",
      "cost": 150000,
      "performed_by": "Technician Jane",
      "notes": "Replaced LCD panel",
      "created_at": "2025-02-20T00:00:00Z"
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

## 📊 Student Attendance

All endpoints in this section require JWT authentication.

### Mark Student Attendance ✅
```http
POST /attendance/student/mark
Authorization: Bearer <jwt_token>
Content-Type: application/json

{
  "student_id": "uuid-string",
  "class_id": "uuid-string",
  "status": "present",
  "marked_by": "uuid-string",
  "attendance_date": "2025-01-15",
  "notes": "Student present on time",
  "check_in_time": "08:00:00",
  "check_out_time": "14:00:00"
}
```

**Validation Rules:**
- `student_id` (required): Student UUID
- `class_id` (required): Class UUID
- `status` (required): One of `present`, `absent`, `late`, `excused`
- `marked_by` (required): Teacher/user ID who marked attendance
- `attendance_date` (optional): Date in Y-m-d format
- `notes` (optional): Attendance notes (max 500 characters)
- `check_in_time` (optional): Check-in time in H:i:s format
- `check_out_time` (optional): Check-out time in H:i:s format

**Response:**
```json
{
  "success": true,
  "message": "Attendance marked successfully",
  "data": {
    "id": "uuid-string",
    "student_id": "uuid-string",
    "class_id": "uuid-string",
    "status": "present",
    "attendance_date": "2025-01-15",
    "notes": "Student present on time",
    "created_at": "2025-01-15T00:00:00Z"
  }
}
```

**Implementation Status:** ✅ Implemented

---

### Mark Bulk Student Attendance ✅
```http
POST /attendance/student/bulk-mark
Authorization: Bearer <jwt_token>
Content-Type: application/json

{
  "class_id": "uuid-string",
  "teacher_id": "uuid-string",
  "marked_by": "uuid-string",
  "attendance_date": "2025-01-15",
  "attendances": [
    {
      "student_id": "uuid-string-1",
      "status": "present",
      "notes": "Present on time",
      "check_in_time": "08:00:00",
      "check_out_time": "14:00:00"
    },
    {
      "student_id": "uuid-string-2",
      "status": "late",
      "notes": "Arrived 10 minutes late",
      "check_in_time": "08:10:00",
      "check_out_time": "14:00:00"
    }
  ]
}
```

**Response:**
```json
{
  "success": true,
  "message": "Bulk attendance marked successfully",
  "data": {
    "count": 2,
    "attendances": [
      {
        "id": "uuid-string-1",
        "student_id": "uuid-string-1",
        "class_id": "uuid-string",
        "status": "present"
      },
      {
        "id": "uuid-string-2",
        "student_id": "uuid-string-2",
        "class_id": "uuid-string",
        "status": "late"
      }
    ]
  }
}
```

**Implementation Status:** ✅ Implemented

---

### Get Student Attendance ✅
```http
GET /attendance/student/{id}
Authorization: Bearer <jwt_token>
```

**Query Parameters:**
- `start_date` (optional): Filter attendance from this date (Y-m-d format)
- `end_date` (optional): Filter attendance until this date (Y-m-d format)

**Response:**
```json
{
  "success": true,
  "message": "Student attendance retrieved successfully",
  "data": {
    "student_id": "uuid-string",
    "student_name": "John Doe",
    "attendance_records": [
      {
        "id": "uuid-string",
        "date": "2025-01-15",
        "status": "present",
        "notes": "Present on time",
        "check_in_time": "08:00:00",
        "check_out_time": "14:00:00"
      }
    ]
  }
}
```

**Implementation Status:** ✅ Implemented

---

### Get Student Attendance Statistics ✅
```http
GET /attendance/student/{id}/statistics
Authorization: Bearer <jwt_token>
```

**Query Parameters:**
- `start_date` (optional): Calculate statistics from this date (Y-m-d format)
- `end_date` (optional): Calculate statistics until this date (Y-m-d format)

**Response:**
```json
{
  "success": true,
  "message": "Attendance statistics retrieved successfully",
  "data": {
    "student_id": "uuid-string",
    "student_name": "John Doe",
    "total_days": 20,
    "present_days": 18,
    "absent_days": 1,
    "late_days": 1,
    "excused_days": 0,
    "attendance_percentage": 90.0,
    "absence_percentage": 5.0,
    "late_percentage": 5.0,
    "period": {
      "start_date": "2025-01-01",
      "end_date": "2025-01-20"
    }
  }
}
```

**Implementation Status:** ✅ Implemented

---

### Get Class Attendance ✅
```http
GET /attendance/class/{id}
Authorization: Bearer <jwt_token>
```

**Query Parameters:**
- `date` (optional): Get attendance for specific date (Y-m-d format)

**Response:**
```json
{
  "success": true,
  "message": "Class attendance retrieved successfully",
  "data": {
    "class_id": "uuid-string",
    "class_name": "Class 10A",
    "date": "2025-01-15",
    "total_students": 30,
    "attendance": [
      {
        "student_id": "uuid-string",
        "student_name": "John Doe",
        "status": "present",
        "check_in_time": "08:00:00",
        "check_out_time": "14:00:00"
      }
    ],
    "summary": {
      "present": 28,
      "absent": 1,
      "late": 1
    }
  }
}
```

**Implementation Status:** ✅ Implemented

---

### Get Class Attendance Report ✅
```http
GET /attendance/class/{id}/report
Authorization: Bearer <jwt_token>
```

**Query Parameters:**
- `start_date` (required): Report start date (Y-m-d format)
- `end_date` (required): Report end date (Y-m-d format)

**Response:**
```json
{
  "success": true,
  "message": "Attendance report generated successfully",
  "data": {
    "class_id": "uuid-string",
    "class_name": "Class 10A",
    "period": {
      "start_date": "2025-01-01",
      "end_date": "2025-01-31"
    },
    "daily_attendance": [
      {
        "date": "2025-01-15",
        "total": 30,
        "present": 28,
        "absent": 1,
        "late": 1
      }
    ],
    "summary": {
      "total_days": 20,
      "average_attendance": 28.5,
      "attendance_rate": 95.0
    }
  }
}
```

**Implementation Status:** ✅ Implemented

---

### Get Chronic Absentees ✅
```http
GET /attendance/chronic-absentees
Authorization: Bearer <jwt_token>
```

**Query Parameters:**
- `days` (optional): Number of consecutive absences to consider chronic (default: 30, range: 1-365)

**Response:**
```json
{
  "success": true,
  "message": "Chronic absentees retrieved successfully",
  "data": {
    "threshold_days": 30,
    "chronic_absentees": [
      {
        "student_id": "uuid-string",
        "student_name": "Jane Smith",
        "class_name": "Class 10A",
        "consecutive_absences": 35,
        "last_attendance_date": "2024-12-10",
        "total_absences": 42
      }
    ],
    "total_count": 1
  }
}
```

**Implementation Status:** ✅ Implemented

---

## 🎓 Academic Records

All endpoints in this section require JWT authentication.

### Calculate GPA ✅
```http
GET /school/students/{studentId}/gpa
Authorization: Bearer <jwt_token>
```

**Query Parameters:**
- `academic_year` (optional): Filter by academic year (e.g., "2024-2025")
- `semester` (optional): Filter by semester (1 or 2)

**Response:**
```json
{
  "success": true,
  "message": "GPA calculated successfully",
  "data": {
    "student_id": "uuid-string",
    "gpa": 3.75,
    "gpa_scale": "4.0",
    "academic_year": "2024-2025",
    "semester": "1",
    "letter_grade": "A-"
  }
}
```

**Implementation Status:** ✅ Implemented

---

### Get Academic Performance ✅
```http
GET /school/students/{studentId}/academic-performance
Authorization: Bearer <jwt_token>
```

**Response:**
```json
{
  "success": true,
  "message": "Academic performance retrieved successfully",
  "data": {
    "student_id": "uuid-string",
    "student_name": "John Doe",
    "overall_gpa": 3.65,
    "class_rank": 5,
    "total_students": 30,
    "subjects": [
      {
        "subject_id": "uuid-string",
        "subject_name": "Mathematics",
        "subject_code": "MATH101",
        "average_grade": 3.8,
        "letter_grade": "A",
        "credits": 4
      },
      {
        "subject_id": "uuid-string",
        "subject_name": "Physics",
        "subject_code": "PHY101",
        "average_grade": 3.5,
        "letter_grade": "B+",
        "credits": 4
      }
    ],
    "academic_years": [
      {
        "year": "2024-2025",
        "gpa": 3.65,
        "rank": 5
      }
    ]
  }
}
```

**Implementation Status:** ✅ Implemented

---

### Get Class Rank ✅
```http
GET /school/students/{studentId}/class-rank/{classId}
Authorization: Bearer <jwt_token>
```

**Query Parameters:**
- `semester` (optional): Filter by semester (1 or 2)
- `academic_year` (optional): Filter by academic year (e.g., "2024-2025")

**Response:**
```json
{
  "success": true,
  "message": "Class rank retrieved successfully",
  "data": {
    "student_id": "uuid-string",
    "class_id": "uuid-string",
    "rank": 5,
    "class_name": "Class 10A",
    "total_students": 30,
    "semester": "1",
    "academic_year": "2024-2025"
  }
}
```

**Implementation Status:** ✅ Implemented

---

### Generate Transcript ✅
```http
GET /school/students/{studentId}/transcript
Authorization: Bearer <jwt_token>
```

**Query Parameters:**
- `academic_year` (optional): Filter by academic year (e.g., "2024-2025")

**Response:**
```json
{
  "success": true,
  "message": "Transcript generated successfully",
  "data": {
    "student_id": "uuid-string",
    "student_name": "John Doe",
    "student_dob": "2008-05-15",
    "enrollment_date": "2023-08-01",
    "academic_years": [
      {
        "year": "2023-2024",
        "semesters": [
          {
            "semester": 1,
            "gpa": 3.8,
            "total_credits": 20,
            "subjects": [
              {
                "subject_name": "Mathematics",
                "subject_code": "MATH101",
                "grade": "A",
                "grade_point": 4.0,
                "credits": 4
              }
            ]
          }
        ]
      },
      {
        "year": "2024-2025",
        "semesters": [
          {
            "semester": 1,
            "gpa": 3.65,
            "total_credits": 20,
            "subjects": [
              {
                "subject_name": "Physics",
                "subject_code": "PHY101",
                "grade": "B+",
                "grade_point": 3.5,
                "credits": 4
              }
            ]
          }
        ]
      }
    ],
    "cumulative_gpa": 3.72,
    "total_credits_earned": 80,
    "generation_date": "2025-01-15"
  }
}
```

**Implementation Status:** ✅ Implemented

---

### Generate Report Card ✅
```http
GET /school/students/{studentId}/report-card/{semester}/{academicYear}
Authorization: Bearer <jwt_token>
```

**Path Parameters:**
- `studentId`: Student UUID
- `semester`: Semester number (1 or 2)
- `academicYear`: Academic year (e.g., "2024-2025")

**Response:**
```json
{
  "success": true,
  "message": "Report card generated successfully",
  "data": {
    "student_id": "uuid-string",
    "student_name": "John Doe",
    "class_name": "Class 10A",
    "semester": 1,
    "academic_year": "2024-2025",
    "gpa": 3.65,
    "class_rank": 5,
    "total_students": 30,
    "subjects": [
      {
        "subject_id": "uuid-string",
        "subject_name": "Mathematics",
        "subject_code": "MATH101",
        "teacher_name": "Mr. Smith",
        "midterm_grade": "A",
        "final_grade": "A",
        "average_grade": "A",
        "grade_point": 4.0,
        "credits": 4,
        "remarks": "Excellent performance"
      },
      {
        "subject_id": "uuid-string",
        "subject_name": "Physics",
        "subject_code": "PHY101",
        "teacher_name": "Ms. Johnson",
        "midterm_grade": "B+",
        "final_grade": "B+",
        "average_grade": "B+",
        "grade_point": 3.5,
        "credits": 4,
        "remarks": "Good performance, can improve"
      }
    ],
    "attendance_summary": {
      "total_days": 60,
      "present_days": 56,
      "absent_days": 3,
      "late_days": 1,
      "attendance_percentage": 93.3
    },
    "behavioral_comments": [
      {
        "comment": "John is a dedicated student who participates actively in class discussions.",
        "comment_type": "positive",
        "commented_by": "Homeroom Teacher",
        "comment_date": "2025-01-15"
      }
    ],
    "generation_date": "2025-01-15"
  }
}
```

**Implementation Status:** ✅ Implemented

---

### Get Subject Grades ✅
```http
GET /school/students/{studentId}/subject-grades/{subjectId}
Authorization: Bearer <jwt_token>
```

**Query Parameters:**
- `academic_year` (optional): Filter by academic year (e.g., "2024-2025")

**Response:**
```json
{
  "success": true,
  "message": "Subject GPA retrieved successfully",
  "data": {
    "student_id": "uuid-string",
    "subject_id": "uuid-string",
    "subject_name": "Mathematics",
    "subject_code": "MATH101",
    "subject_gpa": 3.8,
    "letter_grade": "A-",
    "academic_year": "2024-2025",
    "grades": [
      {
        "grade_id": "uuid-string",
        "assessment_type": "assignment",
        "assessment_name": "Homework 1",
        "score": 95,
        "max_score": 100,
        "percentage": 95.0,
        "grade": "A",
        "graded_date": "2024-09-15"
      },
      {
        "grade_id": "uuid-string",
        "assessment_type": "exam",
        "assessment_name": "Midterm Exam",
        "score": 92,
        "max_score": 100,
        "percentage": 92.0,
        "grade": "A-",
        "graded_date": "2024-10-20"
      }
    ],
    "average_score": 93.5,
    "total_assessments": 2
  }
}
```

**Implementation Status:** ✅ Implemented

---

### Get Grades History ✅
```http
GET /school/students/{studentId}/grades-history
Authorization: Bearer <jwt_token>
```

**Query Parameters:**
- `page` (optional, default: 1): Page number for pagination
- `limit` (optional, default: 20): Grades per page

**Response:**
```json
{
  "success": true,
  "message": "Grades history retrieved successfully",
  "data": {
    "current_page": 1,
    "data": [
      {
        "grade_id": "uuid-string",
        "subject_id": "uuid-string",
        "subject_name": "Mathematics",
        "subject_code": "MATH101",
        "class_id": "uuid-string",
        "class_name": "Class 10A",
        "assessment_type": "exam",
        "assessment_name": "Final Exam",
        "score": 92,
        "max_score": 100,
        "percentage": 92.0,
        "grade": "A-",
        "graded_date": "2025-01-10",
        "graded_by": "Mr. Smith"
      }
    ],
    "per_page": 20,
    "total": 50
  }
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

## 🔔 Notification Management

All endpoints in this section require JWT authentication.

### Create Notification ✅
```http
POST /notifications
Authorization: Bearer <jwt_token>
Content-Type: application/json

{
  "title": "School Event Reminder",
  "message": "Don't forget the parent-teacher conference tomorrow at 2 PM",
  "type": "high",
  "recipient_type": "user",
  "recipient_id": "uuid-string",
  "scheduled_at": "2025-01-16T14:00:00Z"
}
```

**Validation Rules:**
- `title` (required): Notification title
- `message` (required): Notification message body
- `type` (required): One of `info`, `high`, `medium`, `low`, `critical`
- `recipient_type` (optional): Type of recipient (user, role, class)
- `recipient_id` (optional): Recipient ID
- `scheduled_at` (optional): When to send the notification

**Response:**
```json
{
  "success": true,
  "message": "Notification created successfully",
  "data": {
    "id": "uuid-string",
    "title": "School Event Reminder",
    "message": "Don't forget the parent-teacher conference tomorrow at 2 PM",
    "type": "high",
    "created_at": "2025-01-15T10:30:00Z",
    "created_by": "uuid-string"
  }
}
```

**Implementation Status:** ✅ Implemented

---

### Send Notification ✅
```http
POST /notifications/send
Authorization: Bearer <jwt_token>
Content-Type: application/json

{
  "notification_id": "uuid-string",
  "user_ids": ["uuid-string-1", "uuid-string-2", "uuid-string-3"],
  "send_immediately": true
}
```

**Validation Rules:**
- `notification_id` (required): UUID of notification to send
- `user_ids` (required): Array of user UUIDs to send to
- `send_immediately` (optional): Send immediately (default: true)

**Response:**
```json
{
  "success": true,
  "message": "Notification sent successfully",
  "data": null
}
```

**Implementation Status:** ✅ Implemented

---

### Send Emergency Notification ✅
```http
POST /notifications/emergency
Authorization: Bearer <jwt_token>
Content-Type: application/json

{
  "title": "SCHOOL CLOSURE - EMERGENCY",
  "message": "School will be closed tomorrow due to severe weather conditions. All students and staff should stay home.",
  "channels": ["email", "sms", "push"]
}
```

**Validation Rules:**
- `title` (required): Emergency notification title
- `message` (required): Emergency notification message
- `channels` (optional): Notification channels (default: all channels)

**Response:**
```json
{
  "success": true,
  "message": "Emergency notification sent successfully",
  "data": {
    "id": "uuid-string",
    "title": "SCHOOL CLOSURE - EMERGENCY",
    "message": "School will be closed tomorrow due to severe weather conditions.",
    "type": "critical",
    "priority": "critical",
    "sent_at": "2025-01-15T11:00:00Z",
    "channels": ["email", "sms", "push"]
  }
}
```

**Implementation Status:** ✅ Implemented

---

### Get My Notifications ✅
```http
GET /notifications/my
Authorization: Bearer <jwt_token>
```

**Query Parameters:**
- `limit` (optional, default: 20): Number of notifications per page
- `offset` (optional, default: 0): Number of notifications to skip
- `type` (optional): Filter by notification type (info, high, medium, low, critical)
- `read` (optional): Filter by read status (true or false)

**Response:**
```json
{
  "success": true,
  "message": "Notifications retrieved successfully",
  "data": {
    "total": 15,
    "unread": 5,
    "notifications": [
      {
        "id": "uuid-string",
        "title": "School Event Reminder",
        "message": "Parent-teacher conference tomorrow at 2 PM",
        "type": "high",
        "read": false,
        "read_at": null,
        "created_at": "2025-01-15T10:30:00Z"
      },
      {
        "id": "uuid-string",
        "title": "Grade Update",
        "message": "Your math grade has been updated",
        "type": "info",
        "read": true,
        "read_at": "2025-01-15T11:00:00Z",
        "created_at": "2025-01-15T10:00:00Z"
      }
    ]
  }
}
```

**Implementation Status:** ✅ Implemented

---

### Get Notification ✅
```http
GET /notifications/{id}
Authorization: Bearer <jwt_token>
```

**Response:**
```json
{
  "success": true,
  "message": "Notification retrieved successfully",
  "data": {
    "id": "uuid-string",
    "title": "School Event Reminder",
    "message": "Parent-teacher conference tomorrow at 2 PM",
    "type": "high",
    "read": false,
    "read_at": null,
    "created_at": "2025-01-15T10:30:00Z",
    "notification": {
      "title": "School Event Reminder",
      "message": "Parent-teacher conference tomorrow at 2 PM",
      "created_by": "uuid-string",
      "created_at": "2025-01-15T10:00:00Z"
    }
  }
}
```

**Implementation Status:** ✅ Implemented

---

### Mark Notification as Read ✅
```http
PUT /notifications/{id}/read
Authorization: Bearer <jwt_token>
```

**Response:**
```json
{
  "success": true,
  "message": "Notification marked as read",
  "data": null
}
```

**Error Response (Not Found):**
```json
{
  "success": false,
  "message": "Notification not found",
  "error_code": "NOT_FOUND",
  "data": null
}
```

**Implementation Status:** ✅ Implemented

---

### Mark All Notifications as Read ✅
```http
PUT /notifications/read-all
Authorization: Bearer <jwt_token>
```

**Response:**
```json
{
  "success": true,
  "message": "All notifications marked as read",
  "data": null
}
```

**Implementation Status:** ✅ Implemented

---

### Get Notification Delivery Statistics ✅
```http
GET /notifications/{id}/stats
Authorization: Bearer <jwt_token>
```

**Response:**
```json
{
  "success": true,
  "message": "Delivery statistics retrieved",
  "data": {
    "notification_id": "uuid-string",
    "title": "School Event Reminder",
    "total_recipients": 50,
    "sent_count": 48,
    "delivered_count": 45,
    "read_count": 30,
    "failed_count": 3,
    "delivery_rate": 95.8,
    "read_rate": 66.7,
    "channels": {
      "email": {
        "sent": 50,
        "delivered": 48,
        "read": 25,
        "failed": 2
      },
      "sms": {
        "sent": 50,
        "delivered": 47,
        "read": null,
        "failed": 3
      },
      "push": {
        "sent": 50,
        "delivered": 45,
        "read": 30,
        "failed": 5
      }
    }
  }
}
```

**Implementation Status:** ✅ Implemented

---

### Create Notification Template ✅
```http
POST /notifications/templates
Authorization: Bearer <jwt_token>
Content-Type: application/json

{
  "name": "Grade Update Template",
  "type": "academic",
  "subject": "Grade Update Notification",
  "body": "Dear Parent,\n\nYour child's grade has been updated.\n\nSubject: {{subject_name}}\nGrade: {{grade}}\n\nBest regards,\n{{school_name}}"
}
```

**Validation Rules:**
- `name` (required): Template name
- `type` (required): Template type (e.g., academic, emergency, general)
- `subject` (required): Template subject line
- `body` (required): Template body (supports variables like {{student_name}}, {{grade}}, etc.)

**Response:**
```json
{
  "success": true,
  "message": "Template created successfully",
  "data": {
    "id": "uuid-string",
    "name": "Grade Update Template",
    "type": "academic",
    "subject": "Grade Update Notification",
    "body": "Dear Parent,\n\nYour child's grade has been updated.\n\nSubject: {{subject_name}}\nGrade: {{grade}}\n\nBest regards,\n{{school_name}}",
    "created_at": "2025-01-15T12:00:00Z"
  }
}
```

**Implementation Status:** ✅ Implemented

---

### Get Notification Templates ✅
```http
GET /notifications/templates
Authorization: Bearer <jwt_token>
```

**Query Parameters:**
- `type` (optional): Filter templates by type

**Response:**
```json
{
  "success": true,
  "message": "Templates retrieved successfully",
  "data": [
    {
      "id": "uuid-string",
      "name": "Grade Update Template",
      "type": "academic",
      "subject": "Grade Update Notification",
      "body": "Dear Parent,\n\nYour child's grade has been updated.\n\nSubject: {{subject_name}}\nGrade: {{grade}}\n\nBest regards,\n{{school_name}}",
      "created_at": "2025-01-15T12:00:00Z"
    },
    {
      "id": "uuid-string",
      "name": "Emergency Closure Template",
      "type": "emergency",
      "subject": "School Closure - Emergency",
      "body": "Dear Parents and Students,\n\nDue to {{reason}}, the school will be closed from {{start_date}} to {{end_date}}.\n\nPlease stay safe.\n\nBest regards,\n{{school_name}}",
      "created_at": "2025-01-10T09:00:00Z"
    }
  ]
}
```

**Implementation Status:** ✅ Implemented

---

### Update Notification Preferences ✅
```http
PUT /notifications/preferences
Authorization: Bearer <jwt_token>
Content-Type: application/json

{
  "type": "academic",
  "email_enabled": true,
  "sms_enabled": false,
  "push_enabled": true,
  "in_app_enabled": true,
  "quiet_hours_start": "22:00",
  "quiet_hours_end": "07:00"
}
```

**Validation Rules:**
- `type` (required): Preference type (academic, emergency, general)
- `email_enabled` (optional): Enable email notifications (default: true)
- `sms_enabled` (optional): Enable SMS notifications (default: true)
- `push_enabled` (optional): Enable push notifications (default: true)
- `in_app_enabled` (optional): Enable in-app notifications (default: true)
- `quiet_hours_start` (optional): Start of quiet hours (H:i format)
- `quiet_hours_end` (optional): End of quiet hours (H:i format)

**Response:**
```json
{
  "success": true,
  "message": "Preferences updated successfully",
  "data": null
}
```

**Implementation Status:** ✅ Implemented

---

### Get Notification Preferences ✅
```http
GET /notifications/preferences
Authorization: Bearer <jwt_token>
```

**Query Parameters:**
- `type` (optional): Get preferences for specific type

**Response:**
```json
{
  "success": true,
  "message": "Preferences retrieved successfully",
  "data": {
    "user_id": "uuid-string",
    "preferences": [
      {
        "type": "academic",
        "email_enabled": true,
        "sms_enabled": false,
        "push_enabled": true,
        "in_app_enabled": true,
        "quiet_hours_start": "22:00",
        "quiet_hours_end": "07:00"
      },
      {
        "type": "emergency",
        "email_enabled": true,
        "sms_enabled": true,
        "push_enabled": true,
        "in_app_enabled": true,
        "quiet_hours_start": null,
        "quiet_hours_end": null
      }
    ]
  }
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
- `SERVICE_UNAVAILABLE` (503): External service unavailable (circuit breaker open)
- `TIMEOUT_ERROR` (504): External service timeout
- `CONNECTION_ERROR` (502): Failed to connect to external service
- `MAX_RETRIES_EXCEEDED` (429): Maximum retry attempts exceeded

---

## 🔌 Integration & Resilience

The API includes built-in resilience patterns to handle external service failures gracefully.

### Circuit Breaker Open

When an external service fails repeatedly, circuit breaker opens to prevent cascading failures:

```json
{
  "success": false,
  "error": {
    "message": "Service unavailable due to circuit breaker",
    "code": "SERVICE_UNAVAILABLE",
    "details": {
      "service": "email",
      "circuit_breaker_status": "OPEN",
      "message": "Email service is temporarily unavailable. Please try again later."
    }
  },
  "timestamp": "2025-01-14T10:30:00Z"
}
```

### Timeout Error

When an external service request exceeds configured timeout:

```json
{
  "success": false,
  "error": {
    "message": "Request to external service timed out",
    "code": "TIMEOUT_ERROR",
    "details": {
      "service": "http",
      "timeout": "30s",
      "message": "The request to https://api.example.com timed out after 30 seconds"
    }
  },
  "timestamp": "2025-01-14T10:30:00Z"
}
```

### Connection Error

When unable to establish connection to external service:

```json
{
  "success": false,
  "error": {
    "message": "Failed to connect to external service",
    "code": "CONNECTION_ERROR",
    "details": {
      "service": "email",
      "message": "Unable to connect to SMTP server smtp.mailtrap.io:2525"
    }
  },
  "timestamp": "2025-01-14T10:30:00Z"
}
```

### Max Retries Exceeded

When operation fails after maximum retry attempts:

```json
{
  "success": false,
  "error": {
    "message": "Maximum retry attempts exceeded",
    "code": "MAX_RETRIES_EXCEEDED",
    "details": {
      "service": "email",
      "attempts": 3,
      "message": "Operation failed after 3 retry attempts. Please try again later."
    }
  },
  "timestamp": "2025-01-14T10:30:00Z"
}
```

### Circuit Breaker States

- **CLOSED**: Normal operation, all requests pass through
- **OPEN**: Service is failing, requests blocked until recovery timeout
- **HALF_OPEN**: Testing recovery after timeout, allowing limited requests

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
| Student Attendance | 7 | 7 | ✅ 100% |
| Staff Attendance | 6 | 6 | ✅ 100% |
| Leave Management | 11 | 11 | ✅ 100% |
| Student Management | 5 | 5 | ✅ 100% |
| Teacher Management | 5 | 5 | ✅ 100% |
| Schedule Management | 5 | 5 | ✅ 100% |
| Inventory Management | 10 | 10 | ✅ 100% |
| Academic Records | 7 | 7 | ✅ 100% |
| Calendar Management | 11 | 11 | ✅ 100% |
| Notification Management | 12 | 12 | ✅ 100% |
| **Total** | **87** | **87** | **100%** |

---

*This API documentation is continuously updated as new endpoints are implemented.*

**Last Updated:** 2025-01-08
