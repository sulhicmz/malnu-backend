# Learning Management System (LMS) Integration

## Overview

This document describes the foundational Learning Management System (LMS) implementation for the Malnu Backend system. The LMS provides core course management, enrollment tracking, and learning progress monitoring capabilities.

## Architecture

### Database Schema

#### courses
Central course management table with subject and teacher associations.

**Columns:**
- `id` (UUID, primary key)
- `subject_id` (UUID, foreign key to subjects)
- `teacher_id` (UUID, foreign key to teachers)
- `code` (string, unique course identifier)
- `name` (string, course title)
- `description` (text, course description)
- `credits` (integer, credit hours)
- `duration_weeks` (integer, course duration in weeks)
- `level` (enum: beginner, intermediate, advanced)
- `status` (enum: draft, published, archived)
- `start_date` (date, course start date)
- `end_date` (date, course end date)
- `max_students` (integer, enrollment capacity limit)
- `allow_enrollment` (boolean, enrollment open flag)
- `is_active` (boolean, active course flag)
- `created_at`, `updated_at`, `deleted_at` (timestamps)

**Indexes:**
- `subject_id`, `teacher_id` composite index
- `status` index
- `is_active` index

#### course_enrollments
Student enrollment tracking with progress monitoring.

**Columns:**
- `id` (UUID, primary key)
- `course_id` (UUID, foreign key to courses)
- `student_id` (UUID, foreign key to students)
- `enrollment_status` (enum: pending, active, completed, dropped, suspended)
- `progress_percentage` (decimal, overall completion percentage)
- `lessons_completed` (integer, completed lessons count)
- `total_lessons` (integer, total lessons in course)
- `enrolled_at` (date, enrollment date)
- `completed_at` (date, completion date)
- `final_grade` (decimal, final course grade)
- `completion_notes` (text, completion notes)
- `created_at`, `updated_at`, `deleted_at` (timestamps)

**Indexes:**
- `course_id`, `student_id` unique constraint
- `course_id`, `enrollment_status` composite index
- `enrollment_status` index

#### learning_progress
Detailed progress tracking for course content and assessments.

**Columns:**
- `id` (UUID, primary key)
- `course_enrollment_id` (UUID, foreign key to course_enrollments)
- `learning_material_id` (UUID, foreign key to learning_materials)
- `assignment_id` (UUID, foreign key to assignments)
- `quiz_id` (UUID, foreign key to quizzes)
- `status` (enum: not_started, in_progress, completed)
- `score` (decimal, assessment score)
- `time_spent_minutes` (integer, time spent in minutes)
- `attempts` (integer, number of attempts)
- `started_at` (date, start timestamp)
- `completed_at` (date, completion timestamp)
- `last_accessed_at` (date, last access timestamp)
- `notes` (text, progress notes)
- `created_at`, `updated_at` (timestamps)

**Indexes:**
- `course_enrollment_id`, `learning_material_id` unique constraint
- `course_enrollment_id`, `assignment_id` unique constraint
- `course_enrollment_id`, `quiz_id` unique constraint
- `course_enrollment_id`, `status` composite index

### Models

#### Course
Core course model with relationships and helper methods.

**Relationships:**
- `subject()` - BelongsTo Subject
- `teacher()` - BelongsTo Teacher
- `enrollments()` - HasMany CourseEnrollment
- `learningProgress()` - HasManyThrough LearningProgress (via CourseEnrollment)
- `activeEnrollments()` - HasMany CourseEnrollment (active only)

**Query Scopes:**
- `active()` - Filter active courses
- `published()` - Filter published courses
- `bySubject($subjectId)` - Filter by subject
- `byTeacher($teacherId)` - Filter by teacher

**Computed Attributes:**
- `enrolled_count` - Count of active enrollments
- `available_slots` - Available enrollment slots
- `is_full` - Whether course is at capacity

#### CourseEnrollment
Student enrollment management with progress tracking.

**Relationships:**
- `course()` - BelongsTo Course
- `student()` - BelongsTo Student
- `learningProgress()` - HasMany LearningProgress

**Query Scopes:**
- `active()` - Filter active enrollments
- `completed()` - Filter completed enrollments
- `byStudent($studentId)` - Filter by student
- `byCourse($courseId)` - Filter by course
- `inProgress()` - Filter in-progress enrollments

**Helper Methods:**
- `markAsActive()` - Activate pending enrollment
- `updateProgress($percentage, $lessonsCompleted)` - Update enrollment progress
- `getIsActiveAttribute()` - Check if enrollment is active
- `getIsCompletedAttribute()` - Check if enrollment is completed

#### LearningProgress
Detailed learning progress tracking for course content.

**Relationships:**
- `enrollment()` - BelongsTo CourseEnrollment
- `learningMaterial()` - BelongsTo LearningMaterial
- `assignment()` - BelongsTo Assignment
- `quiz()` - BelongsTo Quiz

**Query Scopes:**
- `inProgress()` - Filter in-progress items
- `completed()` - Filter completed items
- `byEnrollment($enrollmentId)` - Filter by enrollment
- `byType($type)` - Filter by content type

**Helper Methods:**
- `markAsStarted()` - Mark progress as started
- `markAsCompleted($score)` - Mark progress as completed
- `recordAccess($minutes)` - Record learning access time
- `getIsStartedAttribute()` - Check if started
- `getIsCompletedAttribute()` - Check if completed
- `getTypeAttribute()` - Get content type (learning_material, assignment, quiz)

### Service Layer

#### LmsService
Core LMS business logic service.

**Course Management:**
- `createCourse($data)` - Create new course
- `updateCourse($id, $data)` - Update course details
- `publishCourse($id)` - Publish course for enrollment
- `archiveCourse($id)` - Archive completed course

**Enrollment Management:**
- `enrollStudent($courseId, $studentId)` - Enroll student in course
- `activateEnrollment($enrollmentId)` - Activate pending enrollment
- `dropCourse($enrollmentId)` - Drop active enrollment
- `completeCourse($enrollmentId, $finalGrade)` - Complete enrollment with grade

**Progress Tracking:**
- `recordLearningProgress($enrollmentId, $type, $itemId, $data)` - Record content progress
- `updateEnrollmentProgress($enrollment)` - Update enrollment progress calculation

**Analytics & Reporting:**
- `getCourses($filters)` - Get paginated courses with filters
- `getCourseDetails($courseId)` - Get detailed course information
- `getStudentEnrollments($studentId, $filters)` - Get student enrollments
- `getStudentProgress($enrollmentId)` - Get detailed student progress
- `getCourseAnalytics($courseId)` - Get course analytics and statistics

### API Controllers

#### LmsController
RESTful API controller for LMS operations.

**Course Endpoints:**
- `GET /api/lms/courses` - List courses with pagination and filters
- `POST /api/lms/courses` - Create new course (Admin/Teacher)
- `GET /api/lms/courses/{id}` - Get course details
- `PUT /api/lms/courses/{id}` - Update course (Admin/Teacher)
- `POST /api/lms/courses/{id}/publish` - Publish course (Admin/Teacher)
- `POST /api/lms/courses/{id}/archive` - Archive course (Admin/Teacher)

**Enrollment Endpoints:**
- `POST /api/lms/enroll` - Enroll student in course
- `POST /api/lms/enrollments/{id}/activate` - Activate enrollment (Admin/Teacher)
- `POST /api/lms/enrollments/{id}/drop` - Drop enrollment (Student/Admin)
- `POST /api/lms/enrollments/{id}/complete` - Complete course with grade (Teacher)

**Progress Endpoints:**
- `POST /api/lms/enrollments/{id}/progress` - Record learning progress
- `GET /api/lms/enrollments/{id}/progress` - Get student progress details

**Analytics Endpoints:**
- `GET /api/lms/students/{studentId}/enrollments` - Get student's enrollments
- `GET /api/lms/courses/{id}/analytics` - Get course analytics

### API Routes

All LMS routes are under `/api/lms` prefix with required JWT authentication and rate limiting.

**Role Requirements:**
- Course creation/update/publish/archive: Super Admin, Kepala Sekolah, Staf TU, Guru
- Enrollment activation/completion: Super Admin, Kepala Sekolah, Staf TU, Guru
- Course enrollment: All authenticated users
- Progress recording: All authenticated users
- Analytics viewing: Super Admin, Kepala Sekolah, Staf TU, Guru

### Testing

#### LmsTest
Comprehensive test suite covering:
- Course creation and publishing
- Student enrollment and activation
- Learning progress recording
- Course completion
- Course analytics
- Student enrollment listing
- Course details retrieval
- Error handling (not found, capacity limits, duplicate codes)

## Migration

After merging this PR, run:

```bash
php artisan migrate
```

This will create:
1. `courses` table
2. `course_enrollments` table
3. `learning_progress` table

## Integration with Existing Systems

### E-Learning Integration
The LMS integrates with existing e-learning models:
- `LearningMaterial` - Progress tracking via learning_progress table
- `Assignment` - Progress tracking via learning_progress table
- `Quiz` - Progress tracking via learning_progress table
- `VirtualClass` - Can be linked to Course model for future enhancement

### Student Management
Uses existing `Student` model for enrollment tracking.

### Subject Management
Uses existing `Subject` model for course organization.

### Teacher Management
Uses existing `Teacher` model for instructor assignment.

## Future Enhancements

Out of scope for this initial implementation but planned for future PRs:

### Advanced Features
- Learning paths and adaptive learning
- Gamification (points, badges, leaderboards)
- Certificate generation and digital credentials
- Collaboration features (group projects, peer review, discussion forums)
- Multimedia content management and streaming
- SCORM/xAPI compliance

### Analytics
- Predictive analytics for at-risk students
- Content effectiveness analytics
- Learning outcome mapping
- Skill development tracking

### Integration
- Parent portal for progress viewing
- Mobile app support
- External learning tool integration

## Security Considerations

- All LMS endpoints require JWT authentication
- Role-based access control for sensitive operations
- Rate limiting on all endpoints
- Student privacy protection for progress data
- Audit logging for enrollment and grade changes

## Performance Optimizations

- Database indexes on frequently queried columns
- Composite indexes for common filter combinations
- Eager loading to prevent N+1 queries
- Efficient enrollment progress calculation
- Pagination for large datasets

## Troubleshooting

### Common Issues

**Issue: Course enrollment failing**
- Check course `allow_enrollment` flag is true
- Verify student is not already enrolled
- Check course capacity limit

**Issue: Progress not updating**
- Ensure enrollment is in `active` status
- Verify learning_progress record has correct type
- Check foreign key relationships

**Issue: Analytics not showing accurate data**
- Ensure enrollments have correct status
- Verify learning_progress records exist for enrollment
- Check for deleted_at on records

## API Usage Examples

### Create and Publish Course
```bash
# Create course
curl -X POST http://localhost:9501/api/lms/courses \
  -H "Authorization: Bearer {jwt_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "subject_id": "{subject_id}",
    "teacher_id": "{teacher_id}",
    "code": "CS101",
    "name": "Introduction to Programming",
    "description": "Basic programming concepts",
    "credits": 3,
    "duration_weeks": 12,
    "level": "beginner"
  }'

# Publish course
curl -X POST http://localhost:9501/api/lms/courses/{course_id}/publish \
  -H "Authorization: Bearer {jwt_token}"
```

### Enroll Student in Course
```bash
curl -X POST http://localhost:9501/api/lms/enroll \
  -H "Authorization: Bearer {jwt_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "course_id": "{course_id}",
    "student_id": "{student_id}"
  }'
```

### Record Learning Progress
```bash
curl -X POST http://localhost:9501/api/lms/enrollments/{enrollment_id}/progress \
  -H "Authorization: Bearer {jwt_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "type": "learning_material",
    "item_id": "{learning_material_id}",
    "status": "in_progress",
    "time_spent_minutes": 45
  }'
```

### Get Course Analytics
```bash
curl -X GET http://localhost:9501/api/lms/courses/{course_id}/analytics \
  -H "Authorization: Bearer {jwt_token}"
```

## Conclusion

This LMS implementation provides a solid foundation for course management, enrollment tracking, and progress monitoring. It integrates seamlessly with existing e-learning, student, teacher, and subject management systems.

The implementation focuses on core functionality and is designed for future enhancements including advanced analytics, gamification, collaboration features, and mobile app support.
