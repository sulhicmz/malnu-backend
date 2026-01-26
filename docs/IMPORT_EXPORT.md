# Import/Export Data Management

## Overview

The Import/Export system provides capabilities for bulk data operations in the school management system. This allows administrators to:

- Import data from CSV files for students, teachers, and classes
- Export data in CSV format for reporting and analytics
- Validate data integrity during imports
- Apply filters for targeted exports

## Features

### Data Import

**Supported Data Types:**
- Students
- Teachers
- Classes

**File Format:**
- CSV files with specific headers
- UTF-8 encoding
- Comma-separated values

**Import Features:**
- Data validation against required fields
- Database transaction rollback on errors
- Detailed error reporting with row numbers
- Batch processing for large files

### Data Export

**Supported Data Types:**
- Students
- Teachers
- Classes

**File Format:**
- CSV files with headers
- ISO 8601 datetime format
- UTF-8 encoding

**Export Features:**
- Apply filters by date range, status, department, etc.
- Download generated files via API
- Streaming support for large datasets
- File size reporting

## API Documentation

### Import Endpoints

#### Import Students

```http
POST /api/import/students
Content-Type: multipart/form-data
Authorization: Bearer {jwt_token}
```

**Request:**
- `csv_file` (file, required): CSV file with student data
- `dry_run` (boolean, optional): Validate without importing

**CSV Headers Required:**
- `nisn`: Student NISN number
- `class_id`: Class ID
- `birth_date`: Birth date (YYYY-MM-DD format)
- `birth_place`: Place of birth
- `address`: Student address
- `parent_id`: Parent ID

**Response (Success):**
```json
{
  "success": true,
  "data": {
    "imported": 145,
    "failed": 0,
    "errors": []
  },
  "message": "Import completed successfully",
  "timestamp": "2026-01-19T10:30:00+00:00"
}
```

**Response (Validation Error):**
```json
{
  "success": false,
  "error": {
    "message": "Validation failed with 5 errors",
    "code": "VALIDATION_ERROR",
    "details": [
      {
        "row": 12,
        "nisn": "1234567",
        "error": "Invalid date format"
      }
    ]
  },
  "timestamp": "2026-01-19T10:30:00+00:00"
}
```

#### Import Teachers

```http
POST /api/import/teachers
Content-Type: multipart/form-data
Authorization: Bearer {jwt_token}
```

**Request:**
- `csv_file` (file, required): CSV file with teacher data

**CSV Headers Required:**
- `user_id`: User account ID
- `employee_id`: Employee ID
- `department`: Department name
- `subject`: Subject taught

#### Import Classes

```http
POST /api/import/classes
Content-Type: multipart/form-data
Authorization: Bearer {jwt_token}
```

**Request:**
- `csv_file` (file, required): CSV file with class data

**CSV Headers Required:**
- `class_name`: Class name
- `grade_level`: Grade level (e.g., "10A", "11B")
- `academic_year`: Academic year
- `homeroom_teacher_id`: Homeroom teacher ID

#### Validate Import

```http
POST /api/import/students/validate
Content-Type: multipart/form-data
Authorization: Bearer {jwt_token}
```

Validates CSV file without importing. Returns validation results.

### Export Endpoints

#### Export Students

```http
GET /api/export/students?class_id={class_id}&status={status}&date_from={yyyy-mm-dd}&date_to={yyyy-mm-dd}
Authorization: Bearer {jwt_token}
```

**Query Parameters (Optional):**
- `class_id`: Filter by class ID
- `status`: Filter by status (e.g., "active", "inactive")
- `date_from`: Export students enrolled after this date
- `date_to`: Export students enrolled before this date

**Response:**
```json
{
  "success": true,
  "data": {
    "filename": "students_export_2026-01-19_103045.csv",
    "exported": 145,
    "download_url": "/api/export/download/students_export_2026-01-19_103045.csv",
    "file_size": 15234
  },
  "message": "Students exported successfully",
  "timestamp": "2026-01-19T10:30:00+00:00"
}
```

#### Export Teachers

```http
GET /api/export/teachers?department={department}&subject={subject}
Authorization: Bearer {jwt_token}
```

**Query Parameters (Optional):**
- `department`: Filter by department
- `subject`: Filter by subject (partial match)

#### Export Classes

```http
GET /api/export/classes?grade_level={grade}&academic_year={year}&status={status}
Authorization: Bearer {jwt_token}
```

**Query Parameters (Optional):**
- `grade_level`: Filter by grade level
- `academic_year`: Filter by academic year
- `status`: Filter by status

#### Download Exported File

```http
GET /api/export/download/{filename}
Authorization: Bearer {jwt_token}
```

Downloads the previously exported CSV file. Use the `download_url` from export response to construct this URL.

## Usage Examples

### Example: Import Students via cURL

```bash
curl -X POST \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -F "csv_file=@students.csv" \
  http://localhost:9501/api/import/students
```

### Example: Export Students via cURL

```bash
curl -X GET \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  "http://localhost:9501/api/export/students?status=active&date_from=2024-01-01&date_to=2024-12-31"
```

Then download the file using the returned `download_url`:

```bash
curl -X GET \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -o students_export.csv \
  "http://localhost:9501/api/export/download/students_export_2026-01-19_103045.csv"
```

### Example: Validate CSV Before Import

```bash
# Test CSV structure without importing
curl -X POST \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -F "csv_file=@test_students.csv" \
  http://localhost:9501/api/import/students/validate
```

## CSV File Format Examples

### Students CSV Template

```csv
nisn,class_id,birth_date,birth_place,address,parent_id
1234567,1,2010-05-15,Jakarta,Jl. Merdeka Barat 1,parent1
1234568,1,2011-03-22,Bandung,Jl. Sudirman 2,parent2
1234569,2,2009-11-10,Surabaya,Jl. Teuku Umar 3,parent3
```

### Teachers CSV Template

```csv
user_id,employee_id,department,subject
user123,EMP001,Mathematics,Algebra
user124,EMP002,Science,Biology
user125,EMP003,Languages,English
```

### Classes CSV Template

```csv
class_name,grade_level,academic_year,homeroom_teacher_id
Class 10A,10,2024-2025,teacher123
Class 11B,11,2024-2025,teacher124
Class 12A,12,2024-2025,teacher125
```

## Data Validation Rules

### Students
- `nisn`: Required, must be unique
- `class_id`: Required, must reference existing class
- `birth_date`: Required, valid date format (YYYY-MM-DD)
- `birth_place`: Optional, string
- `address`: Optional, string
- `parent_id`: Optional, must reference existing parent

### Teachers
- `user_id`: Required, must reference existing user
- `employee_id`: Required, must be unique
- `department`: Required, string
- `subject`: Optional, string

### Classes
- `class_name`: Required, unique within academic year
- `grade_level`: Required, valid grade format
- `academic_year`: Required, valid year
- `homeroom_teacher_id`: Optional, must reference existing teacher

## Error Handling

### Import Errors

The import system provides detailed error reporting:

**Row-Level Errors:**
```json
{
  "row": 45,
  "nisn": "1234567",
  "error": "Invalid date format. Expected YYYY-MM-DD"
}
```

**Common Import Errors:**

| Error Code | Description | Solution |
|-----------|-------------|----------|
| `MISSING_FILE` | No file uploaded | Ensure CSV file is included in request |
| `INVALID_FILE_TYPE` | Not a CSV file | Convert Excel to CSV or use CSV format |
| `MISSING_HEADERS` | Required headers missing | Check CSV headers match template |
| `INVALID_DATE_FORMAT` | Date format incorrect | Use YYYY-MM-DD format |
| `DUPLICATE_ENTRY` | Duplicate unique field value | Use unique NISN/employee IDs |
| `VALIDATION_FAILED` | General validation error | Review error details |

### Export Errors

**Common Export Errors:**

| Error Code | Description | Solution |
|-----------|-------------|----------|
| `EXPORT_FAILED` | Export generation failed | Check server logs for details |
| `NO_DATA` | No data matching filters | Adjust filters or date range |

## Security Considerations

### Authentication
- All import/export endpoints require JWT authentication
- Import endpoints require admin/staff role
- Export endpoints may have role restrictions based on data type

### File Upload Security
- File size limits enforced
- Only CSV files accepted
- Temporary storage for uploaded files
- Files are cleaned up after processing

### Data Integrity
- Database transactions used for imports
- Rollback on any row failure
- Validation before database writes
- Error reporting with row context

## Storage

### Import Files
Uploaded CSV files are temporarily stored in:
```
storage/app/imports/
```

Files are processed and then automatically cleaned up.

### Export Files
Generated CSV files are stored in:
```
storage/exports/
```

Export files are named with timestamp to prevent conflicts:
- `students_export_YYYY-MM-DD_HHMMSS.csv`
- `teachers_export_YYYY-MM-DD_HHMMSS.csv`
- `classes_export_YYYY-MM-DD_HHMMSS.csv`

## Testing

### Running Tests

```bash
# Run import/export tests
vendor/bin/co-phpunit tests/Feature/DataImportExportTest.php

# Run all tests
composer test
```

### Test Coverage

Tests cover:
- CSV parsing and validation
- Import success scenarios
- Import error scenarios
- Export functionality
- Filter application
- File download functionality

## Troubleshooting

### Import Fails with "File not found"
- Verify CSV file path is correct
- Check file permissions
- Ensure file is not corrupted

### Import Shows "Missing headers"
- Open CSV in text editor to verify headers
- Compare with required header list in documentation
- Check for extra spaces or special characters in headers

### Export Returns No Data
- Verify filters match actual data
- Check date ranges are correct
- Ensure students/teachers/classes exist for criteria

### Large File Timeout
- For very large imports (>10,000 rows), consider:
  - Splitting into smaller files
  - Increasing PHP execution time limit
  - Using background job processing (future enhancement)

## Best Practices

### Before Importing
1. **Backup Database**: Always backup before bulk imports
2. **Validate CSV**: Use dry-run endpoint first
3. **Check for Duplicates**: Verify unique fields don't conflict
4. **Prepare Rollback**: Know which changes to undo if needed

### During Import
1. **Monitor Progress**: Check import counts vs expected totals
2. **Review Errors**: Address validation errors before retrying
3. **Use Transactions**: Enabled by default for data integrity

### After Export
1. **Verify Data**: Spot-check exported data in spreadsheet
2. **Secure Storage**: Export files may contain sensitive data
3. **Clean Up**: Remove old export files from storage

## Limitations

### Current Implementation
- CSV format only (Excel not yet supported)
- Synchronous processing (large files may timeout)
- No progress tracking during import
- No undo/rollback for completed imports
- No email notification of completion

### Future Enhancements
- Excel file support (.xlsx, .xls)
- Background job processing for large files
- Real-time progress reporting via WebSocket
- Import preview and mapping UI
- Scheduled automated exports
- Email notifications on completion
- Import history and audit trail
- Undo/rollback functionality
