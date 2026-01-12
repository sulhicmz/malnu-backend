# Report Card and Transcript Generation System

## Overview

The Report Card and Transcript Generation System provides comprehensive academic reporting capabilities for the school management platform. This system enables:

- **Report Card Generation**: Generate professional report cards for students with grades, attendance, and behavior assessments
- **Transcript Generation**: Create official academic transcripts with cumulative GPA and historical data
- **Progress Reports**: Generate interim progress reports for parent communication
- **Batch Generation**: Generate reports for entire classes or grade levels
- **Template Customization**: Administrators can create and manage custom report templates
- **Digital Signatures**: Support for official signatures on reports

## Architecture

### Components

#### 1. Database Schema

**Existing Tables:**
- `reports` - Stores generated reports with metadata (average grade, rank, notes)
- `grades` - Individual grade records for students
- `competencies` - Student competency and skill assessments
- `student_portfolios` - Student portfolio work samples

**New Tables Added:**
- `report_templates` - Customizable HTML templates for different report types
- `report_signatures` - Digital signature management for official documents

#### 2. Models

- `App\Models\Grading\Report` - Generated report records
- `App\Models\Grading\ReportTemplate` - Template management
- `App\Models\Grading\ReportSignature` - Signature management
- `App\Models\Grading\Grade` - Grade records
- `App\Models\Grading\Competency` - Competency assessments
- `App\Models\SchoolManagement\Student` - Student information

#### 3. Service Layer

**ReportGenerationService** - Core service for report generation:
- `generateReportCard()` - Generate student report card
- `generateTranscript()` - Generate academic transcript with cumulative GPA
- `generateProgressReport()` - Generate interim progress report
- `generateBatchReportCards()` - Generate reports for entire class
- `calculateClassRank()` - Calculate student's rank in class
- `calculateCumulativeGpa()` - Calculate cumulative GPA across all grades
- `renderReportCardHtml()` - Generate HTML for report card
- `renderTranscriptHtml()` - Generate HTML for transcript
- `renderProgressReportHtml()` - Generate HTML for progress report

#### 4. API Controller

**ReportController** - API endpoints for report management:
- `GET /api/reports` - List all reports with filtering
- `GET /api/reports/{id}` - Get specific report
- `POST /api/reports/report-cards` - Generate report card
- `POST /api/reports/transcripts` - Generate transcript
- `POST /api/reports/progress-reports` - Generate progress report
- `POST /api/reports/batch-report-cards` - Generate batch report cards
- `POST /api/reports/{id}/publish` - Publish report

## API Usage

### Generate Report Card

```bash
POST /api/reports/report-cards
Authorization: Bearer {jwt_token}
Content-Type: application/json

{
    "student_id": "uuid-of-student",
    "class_id": "uuid-of-class",
    "semester": 1,
    "academic_year": "2024-2025",
    "template_id": "uuid-of-template-optional"
}

Response (200 OK):
{
    "success": true,
    "data": {
        "report_id": "uuid",
        "file_url": "/storage/reports/report_card_{student_id}_{semester}.pdf",
        "data": {
            "student": { ... },
            "class": { ... },
            "grades": [ ... ],
            "average_grade": 85.50,
            "rank_in_class": 5
        }
    },
    "message": "Report card generated successfully",
    "timestamp": "2026-01-12T10:00:00Z"
}
```

### Generate Transcript

```bash
POST /api/reports/transcripts
Authorization: Bearer {jwt_token}
Content-Type: application/json

{
    "student_id": "uuid-of-student"
}

Response (200 OK):
{
    "success": true,
    "data": {
        "report_id": "uuid",
        "file_url": "/storage/reports/transcript_{student_id}.pdf",
        "data": {
            "student": { ... },
            "academic_years": [ ... ],
            "cumulative_gpa": 87.25,
            "total_credits": 45
        }
    },
    "message": "Transcript generated successfully",
    "timestamp": "2026-01-12T10:00:00Z"
}
```

### Generate Progress Report

```bash
POST /api/reports/progress-reports
Authorization: Bearer {jwt_token}
Content-Type: application/json

{
    "student_id": "uuid-of-student",
    "class_id": "uuid-of-class",
    "semester": 1,
    "academic_year": "2024-2025"
}

Response (200 OK):
{
    "success": true,
    "data": {
        "report_id": "uuid",
        "file_url": "/storage/reports/progress_report_{student_id}_{semester}.pdf",
        "data": { ... }
    },
    "message": "Progress report generated successfully",
    "timestamp": "2026-01-12T10:00:00Z"
}
```

### Generate Batch Report Cards

```bash
POST /api/reports/batch-report-cards
Authorization: Bearer {jwt_token}
Content-Type: application/json

{
    "class_id": "uuid-of-class",
    "semester": 1,
    "academic_year": "2024-2025"
}

Response (200 OK):
{
    "success": true,
    "data": [
        {
            "report_id": "uuid",
            "file_url": "/storage/reports/report_card_{student_id}_{semester}.pdf",
            "data": { ... }
        },
        ...
    ],
    "message": "Batch report cards generated successfully",
    "timestamp": "2026-01-12T10:00:00Z"
}
```

### List Reports

```bash
GET /api/reports?student_id={uuid}&class_id={uuid}&semester={int}&academic_year={string}&is_published={boolean}
Authorization: Bearer {jwt_token}

Response (200 OK):
{
    "success": true,
    "data": [
        {
            "id": "uuid",
            "student_id": "uuid",
            "class_id": "uuid",
            "semester": 1,
            "academic_year": "2024-2025",
            "average_grade": 85.50,
            "rank_in_class": 5,
            "is_published": true,
            "published_at": "2026-01-12T10:00:00Z",
            "created_at": "2026-01-12T09:00:00Z",
            "updated_at": "2026-01-12T09:00:00Z"
        }
    ],
    "timestamp": "2026-01-12T10:00:00Z"
}
```

### Publish Report

```bash
POST /api/reports/{report_id}/publish
Authorization: Bearer {jwt_token}

Response (200 OK):
{
    "success": true,
    "data": { ...report... },
    "message": "Report published successfully",
    "timestamp": "2026-01-12T10:00:00Z"
}
```

## Template Customization

### Create Custom Template

```php
// In database or via API (when implemented)
$template = App\Models\Grading\ReportTemplate::create([
    'name' => 'Elementary School Report Card',
    'type' => 'report_card',
    'grade_level' => 'elementary',
    'content' => '<html>...your custom HTML...</html>',
    'css_styles' => '.header { color: blue; }',
    'is_default' => false,
    'is_active' => true,
]);

// Use template when generating report
$reportData = $reportService->generateReportCard(
    $studentId,
    $classId,
    $semester,
    $academicYear,
    $template->id
);
```

### Template Placeholders

Templates can use the following placeholders:

- `{{student}}` - Student data object
- `{{class}}` - Class data object
- `{{grades}}` - Array of grades
- `{{average_grade}}` - Average grade
- `{{rank_in_class}}` - Rank in class
- `{{semester}}` - Semester number
- `{{academic_year}}` - Academic year
- `{{cumulative_gpa}}` - Cumulative GPA (for transcripts)
- `{{total_credits}}` - Total credits (for transcripts)

## Performance Considerations

### Report Generation Performance

- **Individual Reports**: Target under 30 seconds
- **Batch Reports**: Target under 10 minutes for entire class
- **Caching**: Implement caching for frequently accessed reports
- **Background Jobs**: Use queue processing for batch operations

### Database Optimization

- Ensure indexes on:
  - `reports.student_id`
  - `reports.class_id`
  - `reports.semester`
  - `reports.academic_year`
  - `grades.student_id`
  - `grades.class_id`
  - `grades.semester`

### PDF Generation

- Consider using specialized PDF libraries (DomPDF, TCPDF)
- Implement template caching
- Use queue processing for large batches

## Security Considerations

### Access Control

- **Report Generation**: Requires role `Super Admin|Kepala Sekolah|Staf TU|Guru`
- **Report Viewing**: Students and parents can view their own reports
- **Report Publishing**: Requires admin role
- **Template Management**: Requires admin role

### Data Privacy

- Reports contain sensitive academic information
- Implement proper access logging
- Use JWT authentication for all endpoints
- Sanitize all student data in templates

## Integration Points

### Student Information System

Reports are linked to students via `student_id`. The system uses:
- Student basic information (name, NISN, class)
- Grade records from `grades` table
- Competency assessments from `competencies` table

### Grading System

Reports aggregate data from:
- Individual grades by subject and assignment
- Competency assessments
- Portfolio work samples
- Attendance records (to be integrated)

### Notification System

When publishing reports, the system can trigger notifications:
- Email reports to parents
- Send notifications to students
- Notify teachers of new reports

## Testing

### Run Tests

```bash
# Run report generation tests
vendor/bin/co-phpunit tests/Feature/ReportGenerationTest.php

# Run full test suite
vendor/bin/co-phpunit
```

### Test Coverage

Tests cover:
- Report card generation with valid data
- Report card generation with invalid student
- Transcript generation with valid student
- Transcript generation with invalid student
- Progress report generation
- Class rank calculation
- Cumulative GPA calculation
- HTML template generation
- Report archival and publishing
- Error handling for edge cases

## Future Enhancements

### Phase 2 Features (Not in Scope)

1. **Word/Excel Export**: Support for additional export formats beyond PDF
2. **Advanced Templates**: WYSIWYG template editor
3. **Digital Signature Verification**: Cryptographic verification of signatures
4. **Report Distribution**: Email, SMS, and push notification delivery
5. **Multi-language Reports**: Support for reports in multiple languages
6. **Blockchain Verification**: Option for blockchain-based transcript verification

### Phase 3 Features (Future)

1. **AI-Powered Insights**: Automatic performance analysis and recommendations
2. **Predictive Analytics**: Grade prediction and early warning systems
3. **Advanced Visualization**: Interactive charts and graphs in reports
4. **Parent Portal Integration**: Direct parent access to reports

## Troubleshooting

### Common Issues

**Issue**: Reports not generating for student
- Check if student ID is valid
- Verify student has grades for the specified semester
- Ensure class exists and student is enrolled

**Issue**: PDF generation fails
- Check storage permissions
- Verify `storage/app/reports` directory exists and is writable
- Check PHP memory limit for large reports

**Issue**: Class rank calculation is incorrect
- Ensure all grades for class are present
- Check if grades table has complete data for all students
- Verify calculation logic handles edge cases (ties)

**Issue**: Templates not rendering correctly
- Validate HTML template syntax
- Check placeholder format matches `{{key}}` pattern
- Verify CSS is properly included
- Test template with sample data before using in production

## Migration Notes

### Database Migration

After deploying this feature, run:

```bash
php artisan migrate
```

This will create:
- `report_templates` table
- `report_signatures` table

### Configuration

No additional configuration required. Reports are stored in `storage/app/reports/` directory by default.

### Storage Setup

Ensure storage directory is configured:

```bash
# Create storage directory if it doesn't exist
mkdir -p storage/app/reports

# Set appropriate permissions
chmod 755 storage/app/reports
```

## Breaking Changes

None. This implementation adds new functionality without modifying existing behavior.

## Notes

- Report generation uses HTML templates that can be customized
- PDF files are saved to storage and URLs are returned
- Reports can be published to make them available to parents/students
- Digital signatures can be added to reports for official documents
- Batch processing supports generating reports for entire classes at once
