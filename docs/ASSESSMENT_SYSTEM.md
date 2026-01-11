# Assessment and Examination Management System

## Overview

The Assessment and Examination Management System provides comprehensive functionality for creating, managing, and grading various types of assessments including quizzes, tests, exams, assignments, and presentations. It supports automated grading for objective questions and rubric-based evaluation for subjective assessments.

## Architecture

### Database Schema

The system uses the following database tables:

#### assessments
Unified assessment records supporting multiple assessment types
- Formative assessments (quizzes, quick checks, polls)
- Summative assessments (exams, final projects, presentations)
- Performance-based assessments (portfolios, practical demonstrations)

#### rubrics & rubric_criteria
Rubric definitions for subjective question evaluation
- Multi-criteria rubrics with weighted scoring
- Flexible criteria definition
- Reusable rubric templates

#### assessment_submissions
Student submission tracking
- Answer storage for all question types
- Time tracking and attempt management
- Status management (in_progress, submitted, graded)

#### assessment_analytics
Performance analytics and insights
- Aggregate statistics (average, highest, lowest scores)
- Pass rate calculation
- Question-level performance analysis
- Learning outcome mapping

### Models

#### Assessment
```php
namespace App\Models\Assessment;

class Assessment extends Model
{
    // Relationships
    public function subject()
    public function class()
    public function rubric()
    public function creator()
    public function submissions()
    public function analytics()
    
    // Scopes
    public function scopeActive($query)
    public function scopeUpcoming($query)
    public function scopeOngoing($query)
    public function scopeEnded($query)
    
    // Methods
    public function isAccessibleBy(User $user): bool
}
```

#### Submission
```php
namespace App\Models\Assessment;

class Submission extends Model
{
    // Relationships
    public function assessment()
    public function student()
    public function grades()
    
    // Scopes
    public function scopeInProgress($query)
    public function scopeSubmitted($query)
    public function scopeGraded($query)
    
    // Methods
    public function markAsGraded(float $score, string $feedback = null): void
    public function isLate(): bool
}
```

#### Rubric & RubricCriterion
Rubric models for subjective question evaluation
- Flexible criteria definition
- Weighted scoring
- Reusable templates

#### Analytics
Performance tracking and insights
- Aggregate statistics
- Question performance
- Learning outcome mapping

## API Endpoints

### Assessment Management

#### GET /api/assessments
List all assessments with filtering

**Query Parameters:**
- `class_id`: Filter by class
- `subject_id`: Filter by subject
- `assessment_type`: Filter by type (quiz, test, exam, assignment, presentation)
- `page`: Page number for pagination
- `per_page`: Items per page

**Response:**
```json
{
    "success": true,
    "data": {
        "current_page": 1,
        "data": [...],
        "total": 100
    },
    "message": "Assessments retrieved successfully"
}
```

#### POST /api/assessments
Create a new assessment

**Request Body:**
```json
{
    "title": "Math Quiz 1",
    "assessment_type": "quiz",
    "description": "Chapter 1 quiz",
    "subject_id": "uuid",
    "class_id": "uuid",
    "start_time": "2026-01-10 09:00:00",
    "end_time": "2026-01-10 11:00:00",
    "duration_minutes": 60,
    "total_points": 100,
    "passing_grade": 60,
    "allow_retakes": false,
    "max_attempts": 1,
    "shuffle_questions": false,
    "show_results_immediately": true,
    "proctoring_enabled": false,
    "rubric_id": "uuid"
}
```

**Response:**
```json
{
    "success": true,
    "data": {...},
    "message": "Assessment created successfully"
}
```

#### GET /api/assessments/{id}
Get assessment details with rubric and questions

#### PUT /api/assessments/{id}
Update assessment details

#### DELETE /api/assessments/{id}
Delete an assessment

#### POST /api/assessments/{id}/publish
Publish assessment to make it available to students

### Student Operations

#### GET /api/assessments/my
Get assessments available to current student

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": "uuid",
            "title": "Math Quiz 1",
            "assessment_type": "quiz",
            "start_time": "2026-01-10 09:00:00",
            "end_time": "2026-01-10 11:00:00",
            "total_points": 100,
            "passing_grade": 60,
            "is_published": true
        }
    ],
    "message": "Student assessments retrieved successfully"
}
```

#### POST /api/assessments/{id}/start
Start an assessment (create submission)

**Response:**
```json
{
    "success": true,
    "data": {
        "id": "submission-uuid",
        "assessment_id": "uuid",
        "student_id": "uuid",
        "started_at": "2026-01-10 09:05:00",
        "attempt_number": 1,
        "status": "in_progress"
    },
    "message": "Assessment started successfully"
}
```

#### POST /api/assessments/submissions/{id}/submit
Submit assessment answers

**Request Body:**
```json
{
    "answers": {
        "question-1": "A",
        "question-2": "Paris",
        "question-3": "true"
    },
    "time_spent_minutes": 45
}
```

#### POST /api/assessments/submissions/{id}/grade
Grade a submission (auto-grades objective questions)

**Response:**
```json
{
    "success": true,
    "data": {
        "score": 85,
        "percentage": 85.00,
        "passed": true,
        "questions_correct": 17,
        "total_questions": 20
    },
    "message": "Assessment graded successfully"
}
```

### Analytics & Reporting

#### GET /api/assessments/{id}/analytics
Get assessment analytics

**Response:**
```json
{
    "success": true,
    "data": {
        "total_participants": 30,
        "completed_count": 25,
        "average_score": 78.5,
        "highest_score": 98,
        "lowest_score": 45,
        "pass_rate": 88,
        "average_time_minutes": 52.3
    },
    "message": "Assessment analytics retrieved successfully"
}
```

#### GET /api/assessments/performance/me
Get student performance summary

**Query Parameters:**
- `subject_id`: Filter by subject (optional)

**Response:**
```json
{
    "success": true,
    "data": {
        "average_score": 82.5,
        "total_assessments": 15,
        "pass_rate": 93.3,
        "recent_performance": [...]
    },
    "message": "Student performance retrieved successfully"
}
```

## Assessment Types

### 1. Formative Assessments
- **Quizzes**: Short, frequent assessments for checking understanding
- **Quick Checks**: Brief in-class checks
- **Polls**: Class-wide questions for immediate feedback

### 2. Summative Assessments
- **Tests**: Mid-term and end-of-term evaluations
- **Exams**: Comprehensive final assessments
- **Assignments**: Take-home projects and work
- **Presentations**: Oral or visual demonstrations

### 3. Performance-based Assessments
- **Portfolios**: Collection of student work over time
- **Practical Demonstrations**: Hands-on skill assessments

## Question Types

### Supported Question Types

#### Multiple Choice (multiple_choice)
- Single correct answer from multiple options
- Auto-graded
- Configurable point values

#### True/False (true_false)
- Binary choice questions
- Auto-graded
- Quick assessment format

#### Fill in the Blank (fill_in_blank)
- Text completion questions
- Case-insensitive matching
- Auto-graded

#### Short Answer (short_answer)
- Brief text responses
- Manual grading required
- Rubric-based evaluation

#### Essay (essay)
- Extended text responses
- Manual grading required
- Rubric-based evaluation

#### Matching (matching)
- Pair items from two lists
- Auto-graded or manual
- For association testing

## Automated Grading

### Objective Question Grading

The system automatically grades:
- Multiple choice questions
- True/false questions
- Fill-in-the-blank questions (case-insensitive)

**Example:**
```php
$score = $this->assessmentService->gradeSubmission($submission);
// Returns: score, percentage, passed, questions_correct, total_questions
```

### Subjective Question Grading

For questions requiring manual evaluation:
1. Use rubrics to define criteria
2. Assign scores per criterion
3. Calculate total score from rubric

**Rubric-based Grading:**
```php
$criteriaScores = [
    'content' => 8,      // 8/10 points
    'organization' => 9,   // 9/10 points
    'language' => 7,       // 7/10 points
];

$totalScore = array_sum($criteriaScores);
$percentage = ($totalScore / $rubric->max_score) * 100;
```

## Assessment Workflow

### 1. Creation (Teacher/Admin)
1. Create assessment with details
2. Define questions and/or attach rubric
3. Set schedule (optional)
4. Configure retake policy
5. Publish assessment

### 2. Taking (Student)
1. View available assessments
2. Start assessment (creates submission)
3. Complete and submit answers
4. System records time spent

### 3. Grading (Teacher)
1. Review submissions
2. Auto-grade objective questions
3. Manually grade subjective questions
4. Provide feedback
5. Submission marked as "graded"

### 4. Analytics
1. View assessment-level analytics
2. Review student performance
3. Identify learning gaps
4. Adjust teaching strategies

## Security & Access Control

### Role-Based Access

**Admin/Teacher:**
- Create, update, delete assessments
- Publish assessments
- View all submissions
- Grade submissions
- View analytics

**Student:**
- View available assessments
- Start and submit assessments
- View own submissions and grades
- View own performance

### Access Control

```php
public function isAccessibleBy(User $user): bool
{
    // Admins and teachers can access all assessments
    if ($user->hasRole('admin') || $user->hasRole('teacher')) {
        return true;
    }
    
    // Students can only access their class's published assessments
    return $student && $this->class_id === $student->class_id && $this->is_published;
}
```

## Performance Optimization

### Database Indexing
- Class ID and subject ID indexes for filtering
- Assessment type index for type-based queries
- Status indexes for submission queries
- Composite indexes on (assessment_id, student_id)

### Caching Strategy
- Cache assessment definitions
- Cache rubric structures
- Invalidate on updates

### Query Optimization
- Eager loading relationships (with())
- Selective field selection
- Pagination for large datasets

## Integration Points

### Student Information System
- Links via `class_id` and `student_id`
- Retrieves student class information
- Student profile access

### Grading System
- Creates Grade records automatically
- Links assessment results to gradebook
- Supports multiple grade types

### Online Exam Module
- Extends existing ExamQuestion model
- Reuses QuestionBank functionality
- Consistent question management

### Notification System (Future)
- Assessment availability alerts
- Due date reminders
- Grade notifications
- Placeholder implementation ready

## Best Practices

### For Teachers
1. Use clear assessment titles and descriptions
2. Set appropriate passing grades
3. Provide rubrics for subjective questions
4. Review analytics to improve assessments
5. Allow reasonable time for completion

### For Students
1. Start assessments promptly
2. Manage time effectively
3. Review results and feedback
4. Track performance over time
5. Request help for unclear questions

### For Developers
1. Use appropriate assessment types
2. Implement proper time tracking
3. Validate all inputs
4. Handle edge cases (network issues, timeout)
5. Log all assessment activities

## Testing

Run assessment tests:
```bash
php bin/hyperf.php test tests/Feature/AssessmentTest.php
```

## Troubleshooting

### Common Issues

**Issue: Students can't see assessments**
- Check assessment is published
- Verify student is in correct class
- Check assessment start/end times

**Issue: Grading not working**
- Ensure questions have correct answer fields
- Verify rubric is attached for subjective questions
- Check question types are supported

**Issue: Analytics showing no data**
- Ensure submissions are marked as "graded"
- Run analytics calculation manually if needed
- Check database indexes for performance

## Future Enhancements

1. **AI-Assisted Grading**: Natural language processing for essay grading
2. **Advanced Proctoring**: Webcam monitoring, browser lockdown
3. **Adaptive Assessments**: Question difficulty adjustment based on performance
4. **Peer Assessment**: Student-to-student evaluation workflows
5. **Question Bank Integration**: Drag-and-drop question selection
6. **Multi-language Support**: Translations for international schools

## References

- Issue #231: https://github.com/sulhicmz/malnu-backend/issues/231
- UNESCO Assessment Guidelines
- EdTech Hub Research on Digital Assessment
- Existing OnlineExam and Grading modules
