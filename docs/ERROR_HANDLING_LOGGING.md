# Error Handling and Logging Strategy

## Overview

This document describes the comprehensive error handling and logging strategy implemented for the Malnu Backend school management system. The system provides centralized error handling, structured logging, performance monitoring, and consistent API responses.

## Table of Contents

1. [Architecture](#architecture)
2. [Error Logging Service](#error-logging-service)
3. [Performance Monitoring](#performance-monitoring)
4. [API Error Responses](#api-error-responses)
5. [Configuration](#configuration)
6. [Usage Examples](#usage-examples)
7. [Monitoring and Debugging](#monitoring-and-debugging)
8. [Best Practices](#best-practices)

## Architecture

The error handling system consists of the following components:

### Components

1. **ErrorLoggingService** (`app/Services/ErrorLoggingService.php`)
   - Centralized logging service for all application events
   - Supports multiple log levels: emergency, alert, critical, error, warning, notice, info, debug
   - Provides methods for different log types: errors, warnings, info, debug, security, performance, audit

2. **PerformanceMonitoringMiddleware** (`app/Http/Middleware/PerformanceMonitoringMiddleware.php`)
   - Middleware that tracks request/response times
   - Automatically logs slow requests (configurable threshold)
   - Adds performance headers to responses

3. **BaseController** (`app/Http/Controllers/Api/BaseController.php`)
   - Provides standardized error response methods
   - Integrates with error logging service
   - Consistent error response format across all API endpoints

4. **Exception Handler** (`app/Exceptions/Handler.php`)
   - Catches uncaught exceptions
   - Logs with appropriate context
   - Classifies exceptions by severity

## Error Logging Service

The `ErrorLoggingService` provides a unified interface for all logging operations.

### Methods

#### `logError(string $message, array $context = []): void`
Logs error-level messages for application errors and exceptions.

```php
use App\Services\ErrorLoggingService;

class ExampleController extends BaseController
{
    private ErrorLoggingService $errorLogger;

    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        ContainerInterface $container,
        ErrorLoggingService $errorLogger
    ) {
        parent::__construct($request, $response, $container);
        $this->errorLogger = $errorLogger;
    }

    public function someMethod()
    {
        try {
            // Business logic here
        } catch (Exception $e) {
            $this->errorLogger->logError('Failed to process data', [
                'user_id' => $userId,
                'data' => $data,
                'exception' => $e->getMessage(),
            ]);
        }
    }
}
```

#### `logWarning(string $message, array $context = []): void`
Logs warning-level messages for potential issues that don't prevent execution.

```php
$this->errorLogger->logWarning('Deprecated API endpoint used', [
    'endpoint' => '/api/v1/old-endpoint',
    'user_id' => $userId,
]);
```

#### `logInfo(string $message, array $context = []): void`
Logs informational messages about normal operations.

```php
$this->errorLogger->logInfo('User action completed', [
    'user_id' => $userId,
    'action' => 'profile_updated',
]);
```

#### `logDebug(string $message, array $context = []): void`
Logs debug-level messages for development and troubleshooting.

```php
$this->errorLogger->logDebug('Processing request', [
    'request_data' => $request->all(),
    'validation_rules' => $rules,
]);
```

#### `logSecurityEvent(string $event, array $context = []): void`
Logs security-related events with appropriate classification.

```php
$this->errorLogger->logSecurityEvent('LOGIN_ATTEMPT_FAILED', [
    'email' => $email,
    'ip_address' => $request->getIp(),
    'user_agent' => $request->userAgent(),
]);
```

#### `logPerformance(string $endpoint, float $duration, int $statusCode, array $context = []): void`
Logs performance metrics for API endpoints. Automatically flags slow requests.

```php
$this->errorLogger->logPerformance('/api/students', 1.234, 200, [
    'user_id' => $userId,
    'query_count' => $queryCount,
]);
```

#### `logAudit(string $action, array $context = []): void`
Logs audit trail events for compliance and tracking.

```php
$this->errorLogger->logAudit('GRADE_UPDATED', [
    'user_id' => $teacherId,
    'student_id' => $studentId,
    'old_grade' => $oldGrade,
    'new_grade' => $newGrade,
]);
```

## Performance Monitoring

The `PerformanceMonitoringMiddleware` automatically tracks API performance.

### Configuration

Add to `config/autoload/middlewares.php`:

```php
return [
    'http' => [
        [
            App\Http\Middleware\PerformanceMonitoringMiddleware::class,
        ],
    ],
];
```

### Environment Variables

Configure in `.env`:

```
PERFORMANCE_SLOW_THRESHOLD=2.0
```

Threshold in seconds. Requests exceeding this time will be logged as warnings and flagged in response headers.

### Response Headers

The middleware adds the following headers to all API responses:

- `X-Response-Time`: Request duration in milliseconds
- `X-Slow-Request`: "true" if the request exceeded the slow threshold

Example:
```
HTTP/1.1 200 OK
X-Response-Time: 123.45ms
X-Slow-Request: true
Content-Type: application/json
```

### Slow Request Logging

Slow requests are automatically logged with WARNING level:

```json
{
    "message": "[PERFORMANCE] Slow request detected: GET /api/students",
    "context": {
        "timestamp": "2024-01-15T10:30:00+00:00",
        "type": "performance",
        "endpoint": "GET /api/students",
        "duration_ms": 3456.78,
        "status_code": 200
    }
}
```

## API Error Responses

The system uses a consistent error response format across all API endpoints.

### Standard Error Response Format

```json
{
    "success": false,
    "error": {
        "message": "Error message here",
        "code": "ERROR_CODE",
        "details": {
            "field": "Specific error details"
        }
    },
    "timestamp": "2024-01-15T10:30:00+00:00"
}
```

### Error Response Methods in BaseController

#### `errorResponse(string $message, ?string $errorCode = null, ?array $details = null, int $statusCode = 400)`
Generic error response method.

```php
return $this->errorResponse(
    'Resource not found',
    'NOT_FOUND',
    null,
    404
);
```

#### `validationErrorResponse(array $errors)`
Validation error response.

```php
return $this->validationErrorResponse([
    'email' => ['The email field is required.'],
    'name' => ['The name must be at least 3 characters.'],
]);
```

#### `notFoundResponse(string $message = 'Resource not found')`
404 Not Found response.

```php
return $this->notFoundResponse('Student not found');
```

#### `unauthorizedResponse(string $message = 'Unauthorized')`
401 Unauthorized response.

```php
return $this->unauthorizedResponse('Invalid authentication token');
```

#### `forbiddenResponse(string $message = 'Forbidden')`
403 Forbidden response.

```php
return $this->forbiddenResponse('You do not have permission to access this resource');
```

#### `serverErrorResponse(string $message = 'Internal server error')`
500 Internal Server Error response.

```php
return $this->serverErrorResponse('Something went wrong');
```

### Standard Error Codes

| Code | Description | HTTP Status |
|------|-------------|---------------|
| `VALIDATION_ERROR` | Request validation failed | 422 |
| `NOT_FOUND` | Resource not found | 404 |
| `UNAUTHORIZED` | Authentication required | 401 |
| `ACCESS_DENIED` | Permission denied | 403 |
| `SERVER_ERROR` | Internal server error | 500 |
| `GENERAL_ERROR` | Generic error | 400 |

## Configuration

### Environment Variables

Add to `.env` file:

```
# Application Debug Mode
APP_DEBUG=true

# Logging Configuration
LOG_CHANNEL=stack
LOG_CHANNELS=single,daily
LOG_LEVEL=debug

# Performance Monitoring
PERFORMANCE_SLOW_THRESHOLD=2.0
```

### Debug Mode

When `APP_DEBUG=true`, detailed error information is included in API responses:

```json
{
    "success": false,
    "error": {
        "message": "SQLSTATE[42S02]: Base table or view not found",
        "code": "INTERNAL_ERROR",
        "debug": {
            "exception": "Illuminate\Database\QueryException",
            "file": "/app/Models/Student.php",
            "line": 145,
            "trace": "#0 /app/Services/StudentService.php(45)..."
        }
    },
    "timestamp": "2024-01-15T10:30:00+00:00"
}
```

**Important**: Always set `APP_DEBUG=false` in production environments.

## Usage Examples

### Example 1: API Controller with Error Handling

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;
use App\Services\ErrorLoggingService;
use App\Models\Student;

class ExampleController extends BaseController
{
    private ErrorLoggingService $errorLogger;

    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        ContainerInterface $container,
        ErrorLoggingService $errorLogger
    ) {
        parent::__construct($request, $response, $container);
        $this->errorLogger = $errorLogger;
    }

    public function updateStudent(string $id)
    {
        try {
            $student = Student::find($id);

            if (!$student) {
                $this->errorLogger->logWarning('Student not found', [
                    'student_id' => $id,
                    'action' => 'update_attempt',
                ]);

                return $this->notFoundResponse('Student not found');
            }

            $data = $this->request->all();
            $student->update($data);

            $this->errorLogger->logAudit('STUDENT_UPDATED', [
                'student_id' => $id,
                'updated_fields' => array_keys($data),
            ]);

            return $this->successResponse($student, 'Student updated successfully');

        } catch (\Exception $e) {
            $this->errorLogger->logError('Failed to update student', [
                'student_id' => $id,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->serverErrorResponse('Failed to update student');
        }
    }
}
```

### Example 2: Logging Security Events

```php
public function login()
{
    $credentials = $this->request->all();

    if ($this->attemptLogin($credentials)) {
        $this->errorLogger->logSecurityEvent('LOGIN_SUCCESS', [
            'email' => $credentials['email'],
            'ip_address' => $this->request->getServerParams()['remote_addr'],
        ]);

        return $this->successResponse($token, 'Login successful');
    } else {
        $this->errorLogger->logSecurityEvent('LOGIN_ATTEMPT_FAILED', [
            'email' => $credentials['email'],
            'ip_address' => $this->request->getServerParams()['remote_addr'],
        ]);

        return $this->unauthorizedResponse('Invalid credentials');
    }
}
```

### Example 3: Performance Tracking

The `PerformanceMonitoringMiddleware` automatically tracks all API endpoints. View logs to analyze performance:

```bash
# View all logs
tail -f storage/logs/hyperf.log

# Filter for performance warnings
grep "PERFORMANCE" storage/logs/hyperf.log

# Find slow requests
grep "Slow request" storage/logs/hyperf.log
```

## Monitoring and Debugging

### Log Files

Logs are stored in `storage/logs/` directory:

- `hyperf.log` - Main application log
- `hyperf-YYYY-MM-DD.log` - Daily rotated logs (if using daily channel)

### Log Levels

| Level | Description | Use Case |
|-------|-------------|-----------|
| `emergency` | System is unusable | Critical system failures |
| `alert` | Action must be taken immediately | Database connection failures |
| `critical` | Critical conditions | Application crashes |
| `error` | Error conditions | Unhandled exceptions |
| `warning` | Warning conditions | Slow requests, deprecated usage |
| `notice` | Normal but significant | Important events |
| `info` | Informational messages | Normal operations |
| `debug` | Debug-level messages | Development troubleshooting |

### Viewing Logs in Production

```bash
# Recent errors
grep "level":"error" storage/logs/hyperf.log | tail -20

# Security events
grep "SECURITY" storage/logs/hyperf.log | tail -50

# Performance issues
grep "PERFORMANCE" storage/logs/hyperf.log | grep "Slow request"

# Audit trail
grep "AUDIT" storage/logs/hyperf.log | tail -100
```

### Debugging Slow Requests

1. Check the response header `X-Response-Time` to see request duration
2. Look for `X-Slow-Request: true` flag
3. Check logs for detailed performance data:
   ```bash
   grep "SLOW_REQUEST_WARNING" storage/logs/hyperf.log
   ```
4. Analyze the context to identify bottlenecks:
   - Database queries
   - External API calls
   - Complex calculations

## Best Practices

### 1. Always Log with Context

Provide relevant context information in all log messages:

```php
// Good
$this->errorLogger->logError('Database query failed', [
    'query' => $sql,
    'bindings' => $bindings,
    'user_id' => $userId,
    'trace_id' => $traceId,
]);

// Bad
$this->errorLogger->logError('Database query failed');
```

### 2. Use Appropriate Log Levels

Choose the right log level for the situation:

- `debug`: Development and troubleshooting only
- `info`: Normal operations that are useful to track
- `warning`: Potential issues that don't prevent execution
- `error`: Errors that prevent successful completion
- `critical`: Serious errors requiring immediate attention

### 3. Log Security Events Separately

Use `logSecurityEvent()` for security-related events:

```php
$this->errorLogger->logSecurityEvent('PASSWORD_RESET_REQUEST', [
    'user_id' => $userId,
    'ip_address' => $request->getIp(),
]);
```

### 4. Use Audit Logging for Compliance

Use `logAudit()` for actions that require audit trails:

```php
$this->errorLogger->logAudit('DATA_EXPORTED', [
    'user_id' => $userId,
    'export_type' => 'student_records',
    'record_count' => $count,
]);
```

### 5. Handle Exceptions Gracefully

Always use try-catch blocks and provide meaningful error messages:

```php
try {
    // Operation
} catch (DatabaseException $e) {
    $this->errorLogger->logError('Database operation failed', [
        'operation' => 'update_student',
        'exception' => $e->getMessage(),
    ]);

    return $this->serverErrorResponse('Database operation failed');
}
```

### 6. Production Configuration

Always ensure production environment has:

```env
APP_DEBUG=false
LOG_LEVEL=warning
PERFORMANCE_SLOW_THRESHOLD=1.0
```

### 7. Monitor Performance Regularly

Set up log monitoring to track:
- Slow requests (> 2 seconds)
- Error rates
- Security events
- Resource usage

## Troubleshooting

### Issue: Logs Not Being Written

**Solution**: Check log directory permissions:
```bash
chmod -R 775 storage/logs
```

### Issue: Too Much Debug Output

**Solution**: Set appropriate log level in `.env`:
```
LOG_LEVEL=warning  # Only log warnings and errors
```

### Issue: Performance Monitoring Not Working

**Solution**: Ensure middleware is registered in `config/autoload/middlewares.php`.

### Issue: Debug Information Exposed in Production

**Solution**: Set `APP_DEBUG=false` in `.env` file.

## Future Enhancements

Potential improvements for the error handling and logging system:

1. **External Log Aggregation**
   - Integration with ELK Stack (Elasticsearch, Logstash, Kibana)
   - Integration with Datadog or New Relic
   - Centralized log dashboard

2. **Error Alerting**
   - Email notifications for critical errors
   - Slack integration for error alerts
   - PagerDuty integration for emergency issues

3. **Advanced Performance Monitoring**
   - Database query performance tracking
   - Memory usage monitoring
   - Request queue depth monitoring

4. **Structured Error Tracking**
   - Error aggregation and grouping
   - Error rate monitoring
   - Automatic error reporting

5. **Debug Mode Improvements**
   - Request tracer
   - Query profiler
   - Memory profiler

## Support

For issues or questions about the error handling and logging system, please:

1. Check the logs in `storage/logs/`
2. Review this documentation for usage examples
3. Open an issue on GitHub with:
   - Error messages
   - Log snippets (with sensitive data removed)
   - Steps to reproduce

---

**Document Version**: 1.0
**Last Updated**: 2026-01-08
