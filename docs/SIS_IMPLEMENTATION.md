# Student Information System (SIS) Implementation

This document describes the comprehensive Student Information System (SIS) implementation that adds academic records management, enrollment workflows, and performance analytics to the school management platform.

## Overview

The SIS implementation extends the existing student management system with advanced features for:

- **Academic Records Management**: GPA calculation, transcript generation, and grade tracking
- **Enrollment & Registration**: Student enrollment workflows with status tracking
- **Performance Analytics**: Student and class performance analysis with comparative insights
- **Document Management**: Student portfolios and academic history tracking

## API Endpoints

### Academic Records

#### Calculate GPA
Calculate GPA for a student with optional semester filter.

**Endpoint**: `GET /api/school/students/{id}/gpa`

**Parameters**:
- `semester` (optional): Filter by semester number

**Response**:
```json
{
  "success": true,
  "data": {
    "gpa": 3.85,
    "total_grades": 10,
    "total_grade_points": 38.5,
    "grades": [
      {
        "subject": "Mathematics",
        "grade": 90,
        "grade_point": 4.0,
        "semester": 1,
        "grade_type": "exam"
      }
    ],
    "semester": "all"
  },
  "message": "GPA calculated successfully"
}
```

#### Generate Transcript
Generate a complete academic transcript for a student.

**Endpoint**: `GET /api/school/students/{id}/transcript`

**Response**:
```json
{
  "success": true,
  "data": {
    "student": {
      "id": "uuid",
      "name": "John Doe",
      "nisn": "1234567890",
      "class": "Class 10A",
      "enrollment_date": "2023-01-01",
      "status": "active"
    },
    "overall_gpa": 3.85,
    "semesters": [
      {
        "semester": 1,
        "gpa": 3.9,
        "subjects": [...],
        "total_subjects": 5
      }
    ],
    "generated_at": "2026-01-07 10:30:00"
  },
  "message": "Transcript generated successfully"
}
```

#### Get Student Progress
Get detailed progress tracking across subjects.

**Endpoint**: `GET /api/school/students/{id}/progress`

**Response**:
```json
{
  "success": true,
  "data": {
    "average_grade": 87.5,
    "total_subjects": 5,
    "total_grades": 20,
    "progress_by_subject": {
      "Mathematics": {
        "average": 90,
        "grade_point": 4.0,
        "total_grades": 4,
        "sum_grades": 360,
        "grade_types": {
          "exam": { "average": 92, "count": 2 },
          "assignment": { "average": 88, "count": 2 }
        }
      }
    }
  },
  "message": "Student progress retrieved successfully"
}
```

### Enrollment Management

#### Update Enrollment Status
Update a student's enrollment status.

**Endpoint**: `PUT /api/school/students/{id}/enrollment-status`

**Request Body**:
```json
{
  "status": "graduated"
}
```

**Valid Statuses**: `active`, `inactive`, `graduated`, `transferred`, `suspended`

#### Assign to Class
Assign a student to a class.

**Endpoint**: `PUT /api/school/students/{id}/class-assignment`

**Request Body**:
```json
{
  "class_id": "class-uuid"
}
```

#### Get Enrollment History
Get complete enrollment history for a student.

**Endpoint**: `GET /api/school/students/{id}/enrollment-history`

**Response**:
```json
{
  "success": true,
  "data": {
    "student_id": "uuid",
    "student_name": "John Doe",
    "nisn": "1234567890",
    "current_class": "Class 10A",
    "enrollment_date": "2023-01-01",
    "current_status": "active",
    "enrollment_years": 3
  },
  "message": "Enrollment history retrieved successfully"
}
```

#### Get Enrollment Statistics
Get school-wide enrollment statistics.

**Endpoint**: `GET /api/school/enrollment/stats`

**Response**:
```json
{
  "success": true,
  "data": {
    "total_students": 500,
    "active_students": 450,
    "inactive_students": 30,
    "graduated_students": 15,
    "transferred_students": 3,
    "suspended_students": 2,
    "new_students_this_year": 50,
    "enrollment_rate": 90.0
  },
  "message": "Enrollment statistics retrieved successfully"
}
```

#### Get Class Enrollment
Get enrollment details for a specific class.

**Endpoint**: `GET /api/school/classes/{classId}/enrollment`

**Response**:
```json
{
  "success": true,
  "data": {
    "class_id": "class-uuid",
    "total_students": 30,
    "active_students": 28,
    "students": [
      {
        "id": "uuid",
        "name": "John Doe",
        "nisn": "1234567890",
        "status": "active",
        "enrollment_date": "2023-01-01"
      }
    ]
  },
  "message": "Class enrollment retrieved successfully"
}
```

### Performance Analytics

#### Get Student Performance
Get detailed performance analysis for a student.

**Endpoint**: `GET /api/school/students/{id}/performance`

**Parameters**:
- `semester` (optional): Filter by semester number

**Response**:
```json
{
  "success": true,
  "data": {
    "student": {
      "id": "uuid",
      "name": "John Doe",
      "nisn": "1234567890",
      "class": "Class 10A",
      "status": "active"
    },
    "has_data": true,
    "performance": {
      "overall_average": 87.5,
      "highest_grade": 95,
      "lowest_grade": 75,
      "total_assessments": 20
    },
    "subject_performance": [...],
    "semester": "all"
  },
  "message": "Student performance retrieved successfully"
}
```

#### Get Class Performance
Get performance analysis for an entire class.

**Endpoint**: `GET /api/school/classes/{classId}/performance`

**Parameters**:
- `semester` (optional): Filter by semester number

**Response**:
```json
{
  "success": true,
  "data": {
    "class": {
      "id": "class-uuid",
      "name": "Class 10A"
    },
    "has_data": true,
    "class_average": 85.2,
    "total_students": 30,
    "students_with_grades": 30,
    "top_performers": [...],
    "needs_attention": [...],
    "semester": "all"
  },
  "message": "Class performance retrieved successfully"
}
```

#### Get Comparative Analysis
Get comparative analysis of a student against their class.

**Endpoint**: `GET /api/school/students/{id}/comparative-analysis`

**Parameters**:
- `semester` (optional): Filter by semester number

**Response**:
```json
{
  "success": true,
  "data": {
    "student": {
      "id": "uuid",
      "name": "John Doe",
      "nisn": "1234567890",
      "class": "Class 10A",
      "status": "active"
    },
    "has_data": true,
    "student_performance": {
      "overall_average": 90,
      "highest_grade": 95,
      "lowest_grade": 85,
      "total_assessments": 20
    },
    "class_comparison": {
      "class_average": 85,
      "difference": 5,
      "above_class_average": true,
      "percentile_rank": 75
    },
    "recommendation": "Good performance. Student is above class average."
  },
  "message": "Comparative analysis retrieved successfully"
}
```

## GPA Calculation

The GPA is calculated using a standard 4.0 scale:

| Grade Range | Grade Point |
|------------|-------------|
| 90-100     | 4.0         |
| 85-89      | 3.7         |
| 80-84      | 3.3         |
| 75-79      | 3.0         |
| 70-74      | 2.7         |
| 65-69      | 2.3         |
| 60-64      | 2.0         |
| 55-59      | 1.7         |
| 50-54      | 1.3         |
| Below 50   | 1.0         |

## Services

### AcademicRecordService

Handles all academic record operations including GPA calculation and transcript generation.

**Methods**:
- `calculateGPA(string $studentId, ?int $semester = null): array`
- `generateTranscript(string $studentId): array`
- `getStudentProgress(string $studentId): array`

### EnrollmentService

Manages student enrollment workflows and status tracking.

**Methods**:
- `updateEnrollmentStatus(string $studentId, string $status): Student`
- `assignToClass(string $studentId, string $classId): Student`
- `getEnrollmentHistory(string $studentId): array`
- `getEnrollmentStatistics(): array`
- `getClassEnrollment(string $classId): array`

### PerformanceAnalyticsService

Provides performance analytics for students and classes.

**Methods**:
- `getStudentPerformance(string $studentId, ?int $semester = null): array`
- `getClassPerformance(string $classId, ?int $semester = null): array`
- `getComparativeAnalysis(string $studentId, ?int $semester = null): array`

## Testing

The SIS implementation includes comprehensive test coverage in `tests/Feature/StudentInformationSystemTest.php`.

Run tests with:
```bash
php artisan test --filter StudentInformationSystemTest
```

## Integration

The SIS integrates seamlessly with:
- **Existing Student Model**: Uses the existing Student model and relationships
- **Grading System**: Leverages existing Grade, Report, and StudentPortfolio models
- **Authentication**: All endpoints are protected with JWT authentication
- **API Patterns**: Follows the existing API response format and conventions

## Future Enhancements

Potential future improvements:
- Certificate generation (completion, merit, attendance)
- Advanced analytics with AI-powered insights
- Parent-teacher conference scheduling
- Learning outcome assessment tracking
- Digital signature integration for official documents

## Support

For issues or questions related to the SIS implementation, please refer to the main project documentation or create an issue in the repository.
