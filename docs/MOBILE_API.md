# Mobile API Documentation

## Overview

The Malnu School Management System provides comprehensive mobile API endpoints optimized for mobile app consumption. The mobile API uses RESTful design principles with role-based access control.

## Base URL

```
https://api.malnu-school.com/api/mobile/v1
```

## Authentication

All mobile API endpoints require JWT authentication. Include the token in the Authorization header:

```
Authorization: Bearer {jwt_token}
```

## API Versioning

The mobile API is versioned to ensure backward compatibility. All endpoints are prefixed with `/v1`. Future versions will use `/v2`, `/v3`, etc.

---

## Student API Endpoints

### Get Student Dashboard

Retrieves the student's dashboard with overview information.

```
GET /api/mobile/v1/student/dashboard
```

**Response:**
```json
{
  "success": true,
  "data": {
    "student": {
      "id": "uuid",
      "name": "John Doe",
      "nisn": "1234567890",
      "class": "10-A",
      "status": "active"
    },
    "gpa": 3.5,
    "attendance_summary": {
      "present": 180,
      "absent": 5,
      "late": 3
    },
    "upcoming_assignments": [],
    "recent_grades": [],
    "notifications": []
  },
  "message": "Dashboard retrieved successfully",
  "timestamp": "2026-01-30T12:00:00+00:00"
}
```

### Get Student Grades

Retrieves the student's grades for all subjects.

```
GET /api/mobile/v1/student/grades
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "subject": "Mathematics",
      "grade": 85,
      "credits": 4,
      "semester": "1"
    }
  ],
  "message": "Grades retrieved successfully",
  "timestamp": "2026-01-30T12:00:00+00:00"
}
```

### Get Student Assignments

Retrieves the student's assignments including upcoming and completed.

```
GET /api/mobile/v1/student/assignments
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": "uuid",
      "subject": "Mathematics",
      "title": "Algebra Homework",
      "due_date": "2026-02-15",
      "status": "pending",
      "description": "Complete exercises 1-10"
    }
  ],
  "message": "Assignments retrieved successfully",
  "timestamp": "2026-01-30T12:00:00+00:00"
}
```

### Get Student Schedule

Retrieves the student's class schedule.

```
GET /api/mobile/v1/student/schedule
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "day": "Monday",
      "periods": [
        {
          "period": 1,
          "subject": "Mathematics",
          "teacher": "Mr. Smith",
          "room": "101",
          "time": "08:00-08:45"
        }
      ]
    }
  ],
  "message": "Schedule retrieved successfully",
  "timestamp": "2026-01-30T12:00:00+00:00"
}
```

### Get Student Attendance

Retrieves the student's attendance records.

```
GET /api/mobile/v1/student/attendance
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "date": "2026-01-30",
      "status": "present",
      "subject": "Mathematics",
      "teacher": "Mr. Smith"
    }
  ],
  "message": "Attendance retrieved successfully",
  "timestamp": "2026-01-30T12:00:00+00:00"
}
```

---

## Parent API Endpoints

### Get Children

Retrieves a list of children associated with the parent account.

```
GET /api/mobile/v1/parent/children
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": "uuid",
      "name": "John Doe",
      "nisn": "1234567890",
      "class": "10-A",
      "status": "active"
    }
  ],
  "message": "Children retrieved successfully",
  "timestamp": "2026-01-30T12:00:00+00:00"
}
```

### Get Child Progress

Retrieves detailed progress information for a specific child.

```
GET /api/mobile/v1/parent/children/{childId}/progress
```

**Response:**
```json
{
  "success": true,
  "data": {
    "child": {
      "id": "uuid",
      "name": "John Doe",
      "nisn": "1234567890",
      "class": "10-A"
    },
    "academic_performance": {},
    "attendance_summary": {},
    "recent_activities": [],
    "upcoming_events": []
  },
  "message": "Child progress retrieved successfully",
  "timestamp": "2026-01-30T12:00:00+00:00"
}
```

### Get Child Attendance

Retrieves attendance records for a specific child.

```
GET /api/mobile/v1/parent/children/{childId}/attendance
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "date": "2026-01-30",
      "status": "present",
      "subject": "Mathematics"
    }
  ],
  "message": "Child attendance retrieved successfully",
  "timestamp": "2026-01-30T12:00:00+00:00"
}
```

### Get Child Grades

Retrieves grades for a specific child.

```
GET /api/mobile/v1/parent/children/{childId}/grades
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "subject": "Mathematics",
      "grade": 85,
      "credits": 4,
      "semester": "1"
    }
  ],
  "message": "Child grades retrieved successfully",
  "timestamp": "2026-01-30T12:00:00+00:00"
}
```

### Get Child Fees

Retrieves fee status for a specific child.

```
GET /api/mobile/v1/parent/children/{childId}/fees
```

**Response:**
```json
{
  "success": true,
  "data": {
    "total_due": 5000000,
    "total_paid": 3000000,
    "balance": 2000000,
    "transactions": []
  },
  "message": "Child fee status retrieved successfully",
  "timestamp": "2026-01-30T12:00:00+00:00"
}
```

---

## Teacher API Endpoints

### Get Teacher Dashboard

Retrieves the teacher's dashboard with overview information.

```
GET /api/mobile/v1/teacher/dashboard
```

**Response:**
```json
{
  "success": true,
  "data": {
    "teacher": {
      "id": "uuid",
      "name": "Mr. Smith"
    },
    "classes": [],
    "today_schedule": [],
    "pending_attendance": [],
    "recent_activities": [],
    "notifications": []
  },
  "message": "Dashboard retrieved successfully",
  "timestamp": "2026-01-30T12:00:00+00:00"
}
```

### Get Classes

Retrieves a list of classes taught by the teacher.

```
GET /api/mobile/v1/teacher/classes
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": "uuid",
      "name": "10-A",
      "subject": "Mathematics",
      "students_count": 30
    }
  ],
  "message": "Classes retrieved successfully",
  "timestamp": "2026-01-30T12:00:00+00:00"
}
```

### Get Class Students

Retrieves a list of students in a specific class.

```
GET /api/mobile/v1/teacher/classes/{classId}/students
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": "uuid",
      "name": "John Doe",
      "nisn": "1234567890",
      "status": "active"
    }
  ],
  "message": "Class students retrieved successfully",
  "timestamp": "2026-01-30T12:00:00+00:00"
}
```

### Mark Attendance

Marks attendance for students in a class.

```
POST /api/mobile/v1/teacher/attendance/mark
```

**Request Body:**
```json
{
  "class_id": "uuid",
  "date": "2026-01-30",
  "attendance": [
    {
      "student_id": "uuid",
      "status": "present"
    }
  ]
}
```

**Response:**
```json
{
  "success": true,
  "message": "Attendance marked successfully",
  "timestamp": "2026-01-30T12:00:00+00:00"
}
```

### Get Teacher Schedule

Retrieves the teacher's schedule.

```
GET /api/mobile/v1/teacher/schedule
```

**Response:**
```json
{
  "success": true,
  "data": [],
  "message": "Schedule retrieved successfully",
  "timestamp": "2026-01-30T12:00:00+00:00"
}
```

---

## Admin API Endpoints

### Get Admin Dashboard

Retrieves the admin dashboard with overview statistics.

```
GET /api/mobile/v1/admin/dashboard
```

**Response:**
```json
{
  "success": true,
  "data": {
    "admin": {
      "id": "uuid",
      "name": "Administrator"
    },
    "statistics": {
      "total_students": 500,
      "total_teachers": 50,
      "total_classes": 20,
      "today_attendance_rate": 95.5
    },
    "recent_activities": [],
    "pending_approvals": [],
    "upcoming_events": [],
    "alerts": []
  },
  "message": "Dashboard retrieved successfully",
  "timestamp": "2026-01-30T12:00:00+00:00"
}
```

### Get School Information

Retrieves school information.

```
GET /api/mobile/v1/admin/school-info
```

**Response:**
```json
{
  "success": true,
  "data": {
    "name": "Malnu Kananga School",
    "address": "",
    "phone": "",
    "email": "",
    "academic_year": "2025-2026"
  },
  "message": "School info retrieved successfully",
  "timestamp": "2026-01-30T12:00:00+00:00"
}
```

### Get Statistics

Retrieves detailed school statistics.

```
GET /api/mobile/v1/admin/statistics
```

**Response:**
```json
{
  "success": true,
  "data": {
    "enrollment": [],
    "attendance": [],
    "academic": [],
    "financial": []
  },
  "message": "Statistics retrieved successfully",
  "timestamp": "2026-01-30T12:00:00+00:00"
}
```

### Get Recent Activities

Retrieves recent activities in the school.

```
GET /api/mobile/v1/admin/recent-activities
```

**Response:**
```json
{
  "success": true,
  "data": [],
  "message": "Recent activities retrieved successfully",
  "timestamp": "2026-01-30T12:00:00+00:00"
}
```

---

## Push Notification Endpoints

### Register Device

Registers a mobile device for push notifications.

```
POST /api/mobile/v1/push/register
```

**Request Body:**
```json
{
  "device_token": "string",
  "platform": "ios|android"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "user_id": "uuid",
    "device_token": "string",
    "platform": "ios",
    "device_info": {},
    "registered_at": "2026-01-30T12:00:00+00:00"
  },
  "message": "Device registered successfully",
  "timestamp": "2026-01-30T12:00:00+00:00"
}
```

### Unregister Device

Unregisters a mobile device from push notifications.

```
POST /api/mobile/v1/push/unregister
```

**Request Body:**
```json
{
  "device_token": "string"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Device unregistered successfully",
  "timestamp": "2026-01-30T12:00:00+00:00"
}
```

### Update Notification Preferences

Updates notification preferences for the user.

```
PUT /api/mobile/v1/push/preferences
```

**Request Body:**
```json
{
  "enabled": true,
  "notifications": {
    "grades": true,
    "attendance": true,
    "assignments": true,
    "fees": true,
    "announcements": true,
    "emergency": true
  }
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "user_id": "uuid",
    "enabled": true,
    "notifications": {}
  },
  "message": "Notification preferences updated successfully",
  "timestamp": "2026-01-30T12:00:00+00:00"
}
```

### Get Notification Preferences

Retrieves notification preferences for the user.

```
GET /api/mobile/v1/push/preferences
```

**Response:**
```json
{
  "success": true,
  "data": {
    "user_id": "uuid",
    "enabled": true,
    "notifications": {
      "grades": true,
      "attendance": true,
      "assignments": true,
      "fees": true,
      "announcements": true,
      "emergency": true
    }
  },
  "message": "Notification preferences retrieved successfully",
  "timestamp": "2026-01-30T12:00:00+00:00"
}
```

### Test Push Notification

Sends a test push notification to the user's registered devices.

```
POST /api/mobile/v1/push/test
```

**Response:**
```json
{
  "success": true,
  "data": {
    "title": "Test Notification",
    "message": "This is a test push notification from Malnu School Management System",
    "user_id": "uuid",
    "sent_at": "2026-01-30T12:00:00+00:00"
  },
  "message": "Test push notification sent successfully",
  "timestamp": "2026-01-30T12:00:00+00:00"
}
```

---

## Error Responses

All endpoints follow a standard error response format:

```json
{
  "success": false,
  "error": {
    "message": "Error description",
    "code": "ERROR_CODE",
    "details": {}
  },
  "timestamp": "2026-01-30T12:00:00+00:00"
}
```

### Common Error Codes

- `UNAUTHORIZED` (401): Invalid or expired token
- `FORBIDDEN` (403): Insufficient permissions
- `NOT_FOUND` (404): Resource not found
- `VALIDATION_ERROR` (422): Invalid input data
- `SERVER_ERROR` (500): Internal server error

---

## Rate Limiting

The mobile API implements rate limiting to prevent abuse. The default limit is 60 requests per minute per user.

If you exceed the limit, you will receive a 429 status code:

```json
{
  "success": false,
  "error": {
    "message": "Too many requests",
    "code": "RATE_LIMIT_EXCEEDED"
  },
  "timestamp": "2026-01-30T12:00:00+00:00"
}
```

---

## Mobile Middleware

The mobile API uses a custom middleware to identify mobile devices and extract device information. This information is used for:

- Device identification and tracking
- Optimized response formats for mobile
- Analytics and usage statistics
- Push notification targeting

The middleware automatically detects:
- Device type (mobile, tablet, desktop)
- Operating system (iOS, Android, Windows Phone)
- Browser information
