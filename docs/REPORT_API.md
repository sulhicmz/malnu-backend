# Report Card and Transcript Generation API

## Overview
This document describes the API endpoints for generating report cards and academic transcripts in the school management system.

## Base URL
`/api/grading/`

## Authentication
All endpoints require JWT authentication in the Authorization header:
```
Authorization: Bearer {jwt_token}
```

## Endpoints

### Generate Report Card
Generate a report card for a specific student.

- **URL:** `POST /reports/generate-card/{studentId}`
- **Headers:**
  - `Authorization: Bearer {jwt_token}`
  - `Content-Type: application/json`
- **Parameters (in request body):**
  - `class_id` (string, required): The ID of the class
  - `semester` (integer, required): The semester number (1 or 2)
  - `academic_year` (string, required): The academic year (e.g., "2023/2024")
- **Response:**
```json
{
  "success": true,
  "data": {
    "student": { ... },
    "class": { ... },
    "semester": 1,
    "academic_year": "2023/2024",
    "grades": [ ... ],
    "competencies": [ ... ],
    "average_grade": "85.50",
    "rank_in_class": 5,
    "generated_at": "2023-12-01 10:30:00"
  }
}
```

### Generate Academic Transcript
Generate an academic transcript for a student.

- **URL:** `POST /reports/generate-transcript/{studentId}`
- **Headers:**
  - `Authorization: Bearer {jwt_token}`
- **Response:**
```json
{
  "success": true,
  "data": {
    "student": { ... },
    "grades_by_year": { ... },
    "cumulative_gpa": "3.50",
    "total_credits": 30,
    "generated_at": "2023-12-01 10:30:00"
  }
}
```

### Get Student Reports
Retrieve all reports for a specific student.

- **URL:** `GET /reports/student/{studentId}`
- **Headers:**
  - `Authorization: Bearer {jwt_token}`
- **Response:**
```json
{
  "success": true,
  "data": [ ... ],
  "message": "Reports functionality not fully implemented due to framework dependencies"
}
```

### Get Class Reports
Retrieve reports for all students in a specific class.

- **URL:** `GET /reports/class/{classId}`
- **Headers:**
  - `Authorization: Bearer {jwt_token}`
- **Parameters (query string):**
  - `semester` (integer, required): The semester number
  - `academic_year` (string, required): The academic year
- **Response:**
```json
{
  "success": true,
  "data": [ ... ],
  "message": "Reports functionality not fully implemented due to framework dependencies"
}
```

## Error Responses
Common error responses:

- **400 Bad Request:**
```json
{
  "success": false,
  "message": "Missing required parameters: class_id, semester, academic_year"
}
```

- **401 Unauthorized:**
```json
{
  "success": false,
  "message": "Unauthenticated"
}
```

- **500 Internal Server Error:**
```json
{
  "success": false,
  "message": "Error message details"
}
```

## Data Structures

### Student Object
```json
{
  "id": "uuid-string",
  "name": "Student Name",
  "email": "student@example.com",
  "created_at": "2023-01-01 00:00:00"
}
```

### Grade Object
```json
{
  "id": "uuid-string",
  "student_id": "uuid-string",
  "subject_id": "uuid-string",
  "class_id": "uuid-string",
  "grade": 85.50,
  "semester": 1,
  "grade_type": "exam",
  "notes": "Good performance",
  "subject": { ... }
}
```

### Competency Object
```json
{
  "id": "uuid-string",
  "student_id": "uuid-string",
  "subject_id": "uuid-string",
  "competency_code": "COMP-001",
  "competency_name": "Mathematical Reasoning",
  "achievement_level": "Proficient",
  "semester": 1,
  "notes": "Shows strong understanding",
  "subject": { ... }
}
```