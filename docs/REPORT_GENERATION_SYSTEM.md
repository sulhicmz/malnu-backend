# Report Generation System

## Overview

The Report Generation System is a comprehensive module for generating, managing, and distributing academic reports including report cards, transcripts, and progress reports. It provides customizable templates, digital signatures, and batch processing capabilities.

## Features

- **Report Card Generation**: Create detailed report cards with grades, competencies, and class rankings
- **Academic Transcripts**: Generate official transcripts with cumulative GPA and academic history
- **Progress Reports**: Produce interim progress reports with improvement trends
- **Template System**: Customizable HTML templates for different report types and grade levels
- **Digital Signatures**: Support for digital signatures from administrators and teachers
- **Batch Processing**: Generate reports for entire classes at once
- **Multi-format Export**: HTML output (PDF-ready structure)
- **Parent Portal Ready**: Report publishing workflow for parent access

## Architecture

### Components

1. **ReportGenerationService** (`app/Services/ReportGenerationService.php`)
   - Core business logic for report generation
   - Handles GPA calculations, class rankings, and academic history
   - Template processing and HTML generation

2. **PdfService** (`app/Services/PdfService.php`)
   - HTML template processing
   - File storage and retrieval
   - Placeholder replacement system

3. **ReportController** (`app/Http/Controllers/Api/Grading/ReportController.php`)
   - API endpoints for report operations
   - Request validation and response formatting

4. **Models**
   - `Report` - Stores generated report metadata
   - `ReportTemplate` - Customizable report templates
   - `ReportSignature` - Digital signature records

### Database Schema

**report_templates**
- `id` (UUID, PK)
- `name` (string)
- `type` (enum: report_card, transcript, progress_report)
- `grade_level` (string, nullable)
- `header_template` (longText)
- `content_template` (longText)
- `footer_template` (longText)
- `css_styles` (longText, nullable)
- `is_default` (boolean)
- `is_active` (boolean)

**report_signatures**
- `id` (UUID, PK)
- `report_id` (UUID, FK)
- `signer_name` (string)
- `signer_title` (string)
- `signature_image_url` (string, nullable)
- `signed_at` (timestamp)

## API Endpoints

### Report Generation

#### Generate Report Card
```
POST /api/reports/report-cards
Authorization: Bearer {token}
Content-Type: application/json

{
    "student_id": "uuid",
    "class_id": "uuid",
    "semester": 1,
    "academic_year": "2024-2025",
    "template_id": "uuid" (optional)
}
```

#### Generate Transcript
```
POST /api/reports/transcripts
Authorization: Bearer {token}
Content-Type: application/json

{
    "student_id": "uuid"
}
```

#### Generate Progress Report
```
POST /api/reports/progress-reports
Authorization: Bearer {token}
Content-Type: application/json

{
    "student_id": "uuid",
    "class_id": "uuid",
    "semester": 1,
    "academic_year": "2024-2025"
}
```

#### Batch Generate Report Cards
```
POST /api/reports/batch-report-cards
Authorization: Bearer {token}
Content-Type: application/json

{
    "class_id": "uuid",
    "semester": 1,
    "academic_year": "2024-2025",
    "template_id": "uuid" (optional)
}
```

### Report Management

#### Get Student Reports
```
GET /api/reports/student/{studentId}?semester=1&academic_year=2024-2025&is_published=true
Authorization: Bearer {token}
```

#### Get Class Reports
```
GET /api/reports/class/{classId}?semester=1&academic_year=2024-2025
Authorization: Bearer {token}
```

#### Get Report by ID
```
GET /api/reports/{id}
Authorization: Bearer {token}
```

#### Publish Report
```
POST /api/reports/{id}/publish
Authorization: Bearer {token}
```

#### Add Signature
```
POST /api/reports/{id}/signatures
Authorization: Bearer {token}
Content-Type: application/json

{
    "signer_name": "Principal Name",
    "signer_title": "Principal",
    "signature_image_url": "https://example.com/signature.png",
    "notes": "Official signature"
}
```

### Template Management

#### Create Template
```
POST /api/report-templates
Authorization: Bearer {token}
Content-Type: application/json

{
    "name": "Custom Report Card",
    "type": "report_card",
    "grade_level": "high_school",
    "header_template": "<div class='header'>...</div>",
    "content_template": "<div class='content'>...</div>",
    "footer_template": "<div class='footer'>...</div>",
    "css_styles": "body { font-family: Arial; }"
}
```

#### Get Templates
```
GET /api/report-templates?type=report_card&grade_level=high_school
Authorization: Bearer {token}
```

## Template System

### Available Placeholders

**Student Information**
- `{{student_name}}` - Student full name
- `{{student_nisn}}` - NISN number
- `{{student_nis}}` - NIS number
- `{{date_of_birth}}` - Date of birth
- `{{place_of_birth}}` - Place of birth
- `{{enrollment_date}}` - Enrollment date
- `{{graduation_date}}` - Graduation date

**Class Information**
- `{{class_name}}` - Class name
- `{{class_grade_level}}` - Grade level

**Academic Information**
- `{{semester}}` - Semester number
- `{{academic_year}}` - Academic year
- `{{average_grade}}` - Average grade
- `{{rank_in_class}}` - Class rank
- `{{cumulative_gpa}}` - Cumulative GPA
- `{{total_credits}}` - Total credits

**Dynamic Tables**
- `{{grades_table}}` - Subject grades table
- `{{competencies_table}}` - Competencies table
- `{{academic_history}}` - Academic history
- `{{improvement_trends}}` - Improvement trends

**Notes**
- `{{homeroom_notes}}` - Teacher notes
- `{{principal_notes}}` - Principal notes

**Meta**
- `{{generation_date}}` - Generation date

### Default Template Example

```html
<!-- Header -->
<div class="report-header">
    <h1>School Report Card</h1>
    <p>Academic Year {{academic_year}} - Semester {{semester}}</p>
</div>

<!-- Content -->
<div class="student-info">
    <h2>{{student_name}}</h2>
    <p>NISN: {{student_nisn}} | Class: {{class_name}}</p>
</div>

<div class="grades-section">
    <h3>Subject Grades</h3>
    {{grades_table}}
</div>

<div class="summary-section">
    <p><strong>Average Grade:</strong> {{average_grade}}</p>
    <p><strong>Class Rank:</strong> {{rank_in_class}}</p>
</div>

<!-- Footer -->
<div class="report-footer">
    <p>Generated on {{generation_date}}</p>
</div>
```

## Calculations

### Average Grade
Calculated as the mean of all grades for the specified semester:
```
Average = Sum of all grades / Number of grades
```

### Class Rank
Determined by comparing the student's average to classmates:
```
Rank = Number of students with higher average + 1
```

### Cumulative GPA
Weighted average across all academic periods:
```
GPA = Sum(semester average × credits) / Total credits
```

### Improvement Trends
Compares current grades to previous period:
```
Improvement = Current average - Previous average
Trend = improving | declining | stable
```

## Security

### Access Control

| Endpoint | Required Role |
|----------|--------------|
| Generate Reports | Super Admin, Kepala Sekolah, Staf TU, Guru |
| View Student Reports | Super Admin, Kepala Sekolah, Staf TU, Guru, Wali Kelas |
| Publish Reports | Super Admin, Kepala Sekolah, Staf TU |
| Manage Templates | Super Admin, Kepala Sekolah, Staf TU |

### Data Protection
- All endpoints require JWT authentication
- Students can only access their own published reports
- Teachers can access reports for their assigned classes
- Report files stored securely in `storage/app/reports/`

## Usage Examples

### Generate a Report Card

```bash
curl -X POST http://localhost:9501/api/reports/report-cards \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "student_id": "123e4567-e89b-12d3-a456-426614174000",
    "class_id": "123e4567-e89b-12d3-a456-426614174001",
    "semester": 1,
    "academic_year": "2024-2025"
  }'
```

### Generate Batch Reports

```bash
curl -X POST http://localhost:9501/api/reports/batch-report-cards \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "class_id": "123e4567-e89b-12d3-a456-426614174001",
    "semester": 1,
    "academic_year": "2024-2025"
  }'
```

### Add Digital Signature

```bash
curl -X POST http://localhost:9501/api/reports/REPORT_ID/signatures \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "signer_name": "Dr. Principal Name",
    "signer_title": "Principal",
    "signature_image_url": "https://school.edu/signatures/principal.png"
  }'
```

## Testing

Run the test suite:
```bash
vendor/bin/co-phpunit tests/Feature/ReportGenerationTest.php
```

Test coverage includes:
- Report card generation with valid/invalid data
- Transcript generation with academic history
- Progress report generation with trends
- Batch report generation
- Class rank calculations
- Cumulative GPA calculations
- Template management
- Digital signatures

## Migration

Run the database migration:
```bash
php artisan migrate
```

This will create:
- `report_templates` table
- `report_signatures` table
- Add columns to existing `reports` table

## Future Enhancements

- PDF generation with libraries like TCPDF or DomPDF
- Word/Excel export formats
- Advanced template designer UI
- Blockchain-based transcript verification
- Integration with external credential verification services
- Multi-language report generation
- Automated report scheduling
- Email notification for published reports

## Troubleshooting

### Reports not generating
- Check that student has grades for the specified semester
- Verify class and student IDs are correct
- Ensure user has appropriate role permissions

### Templates not applying
- Verify template is active (`is_active = true`)
- Check template type matches report type
- Confirm grade level compatibility

### Storage issues
- Ensure `storage/app/reports/` directory exists and is writable
- Check disk space availability
- Verify file permissions (755 for directories, 644 for files)

## Support

For issues or questions:
1. Check the test suite for usage examples
2. Review the API documentation above
3. Consult the codebase comments
4. Create an issue in the GitHub repository
