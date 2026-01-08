# Report Card and Transcript Generation System

## Overview

The Report Card and Transcript Generation System provides comprehensive reporting capabilities for the school management platform, enabling generation of professional academic documents including report cards, transcripts, and progress reports.

## Features

- **Report Card Generation**: Generate detailed report cards with grades, competencies, and class rankings
- **Academic Transcripts**: Create official transcripts with cumulative GPA and academic history
- **Progress Reports**: Generate interim progress reports showing student improvement trends
- **Batch Generation**: Generate reports for entire classes or grade levels
- **Template Customization**: Create and manage customizable report templates
- **Digital Signatures**: Support for digital signature management
- **Multi-format Export**: Generate HTML and PDF format reports
- **Report Publishing**: Publish reports for parent and student access
- **Historical Tracking**: Maintain complete record of all generated reports

## Database Schema

### Report Templates
- `id`: Primary key (UUID)
- `name`: Template name
- `type`: Template type (report_card, transcript, progress_report)
- `html_template`: HTML template with variable placeholders
- `variables`: JSON array of template variables
- `grade_level`: Optional grade level specificity
- `is_active`: Active status
- `created_by`: Creator user ID
- Timestamps

### Generated Reports
- `id`: Primary key (UUID)
- `student_id`: Student reference
- `report_type`: Type of report (report_card, transcript, progress_report)
- `semester`: Optional semester filter
- `academic_year`: Optional academic year filter
- `template_id`: Template reference
- `file_path`: Storage path to generated file
- `file_format`: File format (pdf, html)
- `file_size`: File size in bytes
- `status`: Generation status (generated, published, archived)
- `generation_data`: JSON of generation data
- `is_published`: Published status
- `published_at`: Publication timestamp
- `created_by`: Creator user ID
- Timestamps

### Report Signatures
- `id`: Primary key (UUID)
- `name`: Signatory name
- `title`: Optional title/position
- `signature_type`: Type (principal, homeroom_teacher, etc.)
- `signature_image`: Binary signature data
- `signature_image_path`: Path to stored signature image
- `is_default`: Default signature flag
- `metadata`: Additional metadata (JSON)
- `created_by`: Creator user ID
- Timestamps

## API Endpoints

### Report Generation

#### Generate Report Card
```
POST /api/reports/report-cards
```

**Request Body:**
```json
{
  "student_id": "uuid",
  "semester": "1",
  "academic_year": "2024"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "report": {
      "id": "uuid",
      "report_type": "report_card",
      "file_path": "/storage/reports/report_card_...",
      "download_url": "/storage/reports/report_card_...",
      ...
    },
    "data": {
      "student_name": "John Doe",
      "average_grade": "85.50",
      "rank_in_class": 5,
      "grades": [...],
      "competencies": [...]
    }
  },
  "message": "Report card generated successfully"
}
```

#### Generate Transcript
```
POST /api/reports/transcripts
```

**Request Body:**
```json
{
  "student_id": "uuid",
  "academic_year": "2024"
}
```

#### Generate Progress Report
```
POST /api/reports/progress-reports
```

**Request Body:**
```json
{
  "student_id": "uuid",
  "semester": "1",
  "academic_year": "2024"
}
```

#### Generate Class Reports (Batch)
```
POST /api/reports/class-reports
```

**Request Body:**
```json
{
  "class_id": "uuid",
  "semester": "1",
  "academic_year": "2024"
}
```

### Report Retrieval

#### Get Student Reports
```
GET /api/reports/students/{studentId}/reports?report_type=report_card&semester=1&academic_year=2024
```

#### Get Class Reports
```
GET /api/reports/classes/{classId}/reports?report_type=report_card&semester=1&academic_year=2024
```

#### Get Report Details
```
GET /api/reports/reports/{id}
```

#### Publish Report
```
POST /api/reports/reports/{id}/publish
```

### Template Management

#### Get Templates
```
GET /api/reports/templates?type=report_card&grade_level=10
```

#### Create Template
```
POST /api/reports/templates
```

**Request Body:**
```json
{
  "name": "Grade 10 Report Card",
  "type": "report_card",
  "html_template": "<html><body><h1>{student_name}</h1>...</body></html>",
  "variables": ["student_name", "average_grade", "rank_in_class"],
  "grade_level": "10",
  "is_active": true
}
```

#### Update Template
```
PUT /api/reports/templates/{id}
```

#### Delete Template
```
DELETE /api/reports/templates/{id}
```

### Signature Management

#### Get Signatures
```
GET /api/reports/signatures?type=principal
```

#### Create Signature
```
POST /api/reports/signatures
```

**Request Body:**
```json
{
  "name": "John Smith",
  "title": "Principal",
  "signature_type": "principal",
  "signature_image_path": "/storage/signatures/principal.png",
  "is_default": true
}
```

## Template Variables

### Report Card Variables
- `{student_name}`: Student full name
- `{student_nisn}`: Student NISN
- `{class_name}`: Class name
- `{semester}`: Semester number
- `{academic_year}`: Academic year
- `{average_grade}`: Average grade
- `{rank_in_class}`: Class rank
- `{grades}`: Array of grades (formatted as table rows)
- `{competencies}`: Array of competencies (formatted as list)
- `{generated_date}`: Generation date

### Transcript Variables
- `{student_name}`: Student full name
- `{student_nisn}`: Student NISN
- `{enrollment_date}`: Enrollment date
- `{cumulative_gpa}`: Cumulative GPA
- `{total_credits}`: Total credits earned
- `{academic_records}`: Academic records by year/semester
- `{generated_date}`: Generation date

### Progress Report Variables
- `{student_name}`: Student full name
- `{class_name}`: Class name
- `{semester}`: Semester number
- `{academic_year}`: Academic year
- `{current_average}`: Current semester average
- `{previous_average}`: Previous semester average
- `{improvement}`: Improvement amount
- `{subjects}`: Subject performance with trends
- `{competencies}`: Competency assessments
- `{generated_date}`: Generation date

## GPA Calculation

The system uses a standard 4.0 GPA scale:

| Percentage | GPA | Grade |
|------------|------|-------|
| 90-100     | 4.0  | A     |
| 80-89      | 3.0  | B     |
| 70-79      | 2.0  | C     |
| 60-69      | 1.0  | D     |
| Below 60   | 0.0  | F     |

## Class Ranking

Class rankings are calculated based on average grades within the same class and semester:
1. Calculate average grade for each student
2. Sort students by average grade (descending)
3. Assign rank based on position

## Security Considerations

- All endpoints require JWT authentication
- Role-based access control recommended for sensitive reports
- Audit logging for all report generation and access
- File storage in secure directory
- Published reports accessible only to authorized users

## Performance Optimization

- Caching of frequently accessed reports
- Efficient database queries with proper indexing
- Batch processing support for class-wide generation
- Lazy loading of relationships
- Background job processing recommended for large batches

## Integration Points

### Student Information System (Issue #229)
- Student profile data
- Class assignments
- Academic records

### Grading Module
- Grade records
- Competency assessments
- Assignment and exam results

### Parent Portal (Issue #232)
- Published report access
- Download functionality
- Historical report viewing

## Error Handling

The system provides consistent error responses:

```json
{
  "success": false,
  "error": {
    "message": "Student not found",
    "code": "STUDENT_NOT_FOUND",
    "details": {}
  },
  "timestamp": "2024-01-08T12:00:00+00:00"
}
```

## File Storage

Generated reports are stored in:
```
BASE_PATH/storage/reports/
```

File naming convention:
```
{report_type}_{student_nisn}_{date}{_semester}.html
```

Example:
```
report_card_12345_2024-01-08_sem1.html
transcript_12345_2024-01-08.html
progress_report_12345_2024-01-08_sem1.html
```

## Best Practices

1. **Template Management**
   - Create separate templates for different grade levels
   - Use descriptive variable names
   - Test templates thoroughly before use
   - Keep backups of working templates

2. **Report Generation**
   - Generate reports after grade finalization
   - Use academic year and semester filters for accuracy
   - Review generated reports before publishing
   - Maintain consistent naming conventions

3. **Performance**
   - Use batch generation for class reports
   - Schedule report generation during off-peak hours
   - Implement caching for frequently accessed reports
   - Monitor generation times for optimization

## Troubleshooting

### Template Variables Not Replacing
- Verify variable names match template placeholders
- Check that variables are defined in template metadata
- Ensure curly braces are used: `{variable_name}`

### Reports Not Generating
- Check student has grade data for the requested period
- Verify template is active and valid
- Review error logs for specific failure reasons
- Ensure storage directory has write permissions

### File Access Issues
- Verify storage directory permissions
- Check public symlink is configured
- Confirm file path in database matches actual location
- Review web server configuration

## Future Enhancements

- Advanced PDF generation with TCPDF/DomPDF
- Real-time preview of reports
- Bulk operations for multiple students
- Integration with notification system for report availability
- Multi-language template support
- Custom branding and school logos
- Electronic signature integration with external services
- Report versioning and revision history
