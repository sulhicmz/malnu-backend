# Report Card and Transcript Generation System

## Overview
This system provides comprehensive report card and academic transcript generation capabilities for the school management system. It includes functionality for generating individual report cards, academic transcripts, and managing report data.

## Features Implemented

### 1. Report Service
- `ReportService` class for generating report cards and academic transcripts
- Report card generation with grades, competencies, and student information
- Academic transcript generation with cumulative GPA and historical data
- Rank calculation within class functionality
- Data aggregation from multiple sources (grades, competencies, etc.)

### 2. PDF Generation
- `PdfService` class for generating PDF reports
- HTML templates for report cards and transcripts
- Structured data presentation with proper formatting
- Support for grades, competencies, and student information

### 3. API Endpoints
- `/api/grading/reports/generate-card/{studentId}` - Generate report card
- `/api/grading/reports/generate-transcript/{studentId}` - Generate transcript
- `/api/grading/reports/student/{studentId}` - Get student reports
- `/api/grading/reports/class/{classId}` - Get class reports

### 4. Controllers
- `ReportController` with methods for all report operations
- Proper error handling and response formatting
- Input validation for required parameters

### 5. Documentation
- Complete API documentation in REPORT_API.md
- Usage examples and response formats
- Error handling information

## Architecture

### Service Layer
- `ReportService`: Core business logic for report generation
- `PdfService`: PDF/HTML generation functionality

### Controller Layer
- `ReportController`: API endpoints for report operations

### Data Models
- Uses existing `Grade`, `Competency`, `Student`, and `ClassModel` models
- Proper relationships and data retrieval patterns

## API Usage Examples

### Generate Report Card
```bash
curl -X POST "http://localhost:9501/api/grading/reports/generate-card/{studentId}" \
  -H "Authorization: Bearer {jwt_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "class_id": "class_uuid",
    "semester": 1,
    "academic_year": "2023/2024"
  }'
```

### Generate Academic Transcript
```bash
curl -X POST "http://localhost:9501/api/grading/reports/generate-transcript/{studentId}" \
  -H "Authorization: Bearer {jwt_token}"
```

## Dependencies and Framework Notes

**Important**: This implementation is designed for the Hyperf framework. However, the project currently has missing Hyperf framework dependencies that prevent full functionality. The core logic is implemented but requires the following dependencies to be properly installed:

- `hyperf/database` for Eloquent models
- `hyperf/http-server` for HTTP request/response handling
- `hyperf/utils` for utility functions like Str

## Future Enhancements

### High Priority
- Batch report generation for multiple students
- Parent portal integration for report access
- Digital signature integration
- Export functionality for different formats (Excel, Word)

### Medium Priority
- Report scheduling and automation
- Custom report templates
- Advanced analytics and statistics
- Notification system for report availability

### Low Priority
- Report sharing capabilities
- Advanced filtering and search
- Report comparison tools
- Integration with external systems

## Testing

A basic test suite has been created in `tests/Feature/ReportGenerationTest.php` that validates:
- Service instantiation
- PDF generation functionality
- HTML template structure

## Security Considerations

- All endpoints require JWT authentication
- Input validation for all parameters
- Proper access controls for student data
- Data privacy compliance considerations

## Performance Considerations

- Efficient database queries with proper indexing
- Caching for frequently accessed data
- Background processing for large batch operations
- Memory optimization for large reports

## Known Limitations

Due to the missing Hyperf framework dependencies:
- Database model operations may not function properly
- Full Eloquent ORM functionality is unavailable
- Some controller methods return placeholder responses
- Complete integration testing is limited

These limitations will be resolved when the core framework dependencies are fixed (see issue #253).