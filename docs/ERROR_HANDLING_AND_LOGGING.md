# Error Handling and Logging Strategy

## Overview

This document describes the comprehensive error handling and logging strategy implemented in the Malnu Backend application. The system provides structured logging, health monitoring, and error tracking capabilities for production readiness.

## Architecture

### Components

1. **ErrorLoggingService** - Centralized logging service
2. **ApiErrorHandlingMiddleware** - Exception handling for API requests
3. **RequestLoggingMiddleware** - Request/response logging with performance tracking
4. **HealthController** - Application health check endpoints
5. **BaseController** - Standard error response format with built-in error logging

### Logging Flow

```
Request → RequestLoggingMiddleware (logs request/response time)
    ↓
Controller/Service (processes request)
    ↓
Exception occurs
    ↓
ApiErrorHandlingMiddleware (catches and logs exception)
    ↓
ErrorLoggingService (structured logging)
    ↓
Log File / External Service
```

## Error Logging Service

### Location

`app/Services/ErrorLoggingService.php`

### Methods

| Method | Purpose | Log Level |
|---------|-----------|------------|
| `logError()` | Log error messages | error |
| `logWarning()` | Log warning messages | warning |
| `logInfo()` | Log informational messages | info |
| `logDebug()` | Log debug messages (dev only) | debug |
| `logSecurityEvent()` | Log security events | warning |
| `logPerformance()` | Log performance metrics | info |
| `logException()` | Log exceptions with context | based on type |

### Error Context

All log entries include:
- `log_type` - Type of log entry (error, warning, info, debug, security, performance, exception)
- `environment` - Application environment (local, staging, production)
- `timestamp` - ISO 8601 formatted timestamp
- Additional context from caller

### Exception Classification

Exceptions are automatically classified by type:

| Exception Type | Log Level | Examples |
|--------------|-----------|----------|
| Database errors | error | PDOException, QueryException |
| Network errors | warning | Guzzle exceptions |
| Authentication errors | warning | JWT errors, auth failures |
| Validation errors | notice | ValidationException |
| Other exceptions | error | Application exceptions |

### Usage Example

```php
use App\Services\ErrorLoggingService;

class ExampleController extends Controller
{
    protected ErrorLoggingService $errorLog;

    public function __construct(ErrorLoggingService $errorLog)
    {
        $this->errorLog = $errorLog;
    }

    public function someAction()
    {
        try {
            $result = $this->performOperation();
            return $this->successResponse($result);
        } catch (\Exception $e) {
            $this->errorLog->logException($e, [
                'action' => 'someAction',
                'user_id' => $this->getUserId(),
            ]);

            return $this->serverErrorResponse('Operation failed');
        }
    }
}
```

## API Error Handling Middleware

### Location

`app/Http/Middleware/ApiErrorHandlingMiddleware.php`

### Functionality

- Catches all exceptions during request processing
- Logs exceptions using `error_log()` for immediate visibility
- Returns standardized error response format:
  ```json
  {
    "success": false,
    "error": {
      "message": "An internal server error occurred",
      "code": "SERVER_ERROR",
      "details": null
    },
    "timestamp": "2024-01-19T12:00:00Z"
  }
  ```

### Error Response Format

All API errors follow this structure:

```json
{
  "success": false,
  "error": {
    "message": "Human-readable error message",
    "code": "ERROR_CODE_CONSTANT",
    "details": {  // Optional detailed error information
      "field": "email",
      "reason": "already_exists"
    }
  },
  "timestamp": "ISO-8601-timestamp"
}
```

### Standard Error Codes

| Code | HTTP Status | Description |
|------|-------------|-------------|
| `VALIDATION_ERROR` | 422 | Input validation failed |
| `UNAUTHORIZED` | 401 | Authentication required/failed |
| `FORBIDDEN` | 403 | Access denied |
| `NOT_FOUND` | 404 | Resource not found |
| `SERVER_ERROR` | 500 | Internal server error |
| `GENERAL_ERROR` | 400 | Other errors |

## Request Logging Middleware

### Location

`app/Http/Middleware/RequestLoggingMiddleware.php`

### Features

- Logs all HTTP requests and responses
- Tracks request duration for performance monitoring
- Includes client IP address (supports X-Forwarded-For, X-Real-IP, CF-Connecting-IP)
- Sanitizes sensitive data (passwords, tokens, credit cards)
- Supports configurable logging levels
- Excludes health and metrics endpoints from logging

### Configuration

Environment variables (`.env`):

```bash
# Request Logging Configuration
REQUEST_LOGGING_ENABLED=true
REQUEST_LOGGING_LEVEL=info
REQUEST_LOGGING_INCLUDE_BODY=false
REQUEST_LOGGING_EXCLUDE_PATHS=/health,/metrics,/favicon.ico
```

### Logged Information

For each request/response:

| Field | Description |
|--------|-------------|
| `type` | `http_request` |
| `method` | HTTP method (GET, POST, etc.) |
| `path` | Request URI path |
| `query` | Query parameters (sensitive data redacted) |
| `status_code` | HTTP response status code |
| `duration_ms` | Request duration in milliseconds |
| `ip` | Client IP address |
| `user_agent` | User-Agent header |
| `content_type` | Content-Type header |

### Sensitive Data Sanitization

The following query parameters and request body fields are automatically redacted in logs:

- `password`
- `current_password`
- `new_password`
- `token`
- `secret`
- `api_key`
- `credit_card`

Example: `password=secret123` becomes `password=[REDACTED]`

## Health Check Endpoints

### Location

`app/Http/Controllers/HealthController.php`

### Endpoints

| Endpoint | Description | Auth Required |
|----------|-------------|----------------|
| `GET /health` | Basic health check | No |
| `GET /health/detailed` | Detailed health with system metrics | No |
| `GET /health/metrics` | Performance metrics | No |

### Health Check Response

**Basic Health Check** (`/health`):

```json
{
  "status": "healthy",
  "timestamp": "2024-01-19T12:00:00Z",
  "checks": {
    "database": {
      "status": "up",
      "response_time_ms": 5.23
    },
    "redis": {
      "status": "up",
      "response_time_ms": 2.15
    }
  }
}
```

**Detailed Health Check** (`/health/detailed`):

```json
{
  "status": "healthy",
  "timestamp": "2024-01-19T12:00:00Z",
  "checks": {
    "database": {
      "status": "up",
      "response_time_ms": 5.23,
      "driver": "mysql",
      "database": "malnu",
      "version": "8.0.32"
    },
    "redis": {
      "status": "up",
      "response_time_ms": 2.15,
      "host": "redis",
      "port": 6379,
      "version": "7.2.5",
      "connected_clients": 1,
      "used_memory": "1.2M"
    },
    "system": {
      "load_average": {
        "1_min": 0.45,
        "5_min": 0.62,
        "15_min": 0.58
      },
      "disk_usage": {
        "total": "50.0 GB",
        "free": "25.5 GB",
        "used_percent": 49
      }
    }
  }
}
```

**Metrics Endpoint** (`/health/metrics`):

```json
{
  "status": "healthy",
  "timestamp": "2024-01-19T12:00:00Z",
  "uptime": "5 days, 12 hours, 34 minutes",
  "performance": {
    "memory_usage": "64.5 MB",
    "memory_limit": "512M",
    "peak_memory": "128.7 MB"
  },
  "system": {
    "php_version": "8.2.18",
    "sapi": "cli",
    "os": "Linux"
  }
}
```

### Health Status Codes

| Status | HTTP Code | Description |
|--------|-------------|-------------|
| `healthy` | 200 | All systems operational |
| `unhealthy` | 503 | One or more systems down |

## Base Controller Error Handling

### Location

`app/Http/Controllers/Api/BaseController.php`

### Built-in Methods

| Method | Purpose |
|---------|---------|
| `errorResponse()` | Standard error response |
| `validationErrorResponse()` | Validation error (422) |
| `notFoundResponse()` | Not found error (404) |
| `unauthorizedResponse()` | Unauthorized error (401) |
| `forbiddenResponse()` | Forbidden error (403) |
| `serverErrorResponse()` | Server error (500) |
| `logError()` | Log errors with structured context |

### Error Logging in Controllers

All controllers extending `BaseController` automatically log errors with context:

```php
return $this->errorResponse(
    $message,
    $errorCode,
    $details,
    $statusCode
);
```

The `logError()` method automatically logs:

- Error message
- Error code
- Details
- Status code
- Request URI
- Request method
- User agent
- IP address
- Timestamp

## Configuration

### Environment Variables

All logging configuration is in `.env`:

```bash
# Logging Configuration
LOG_CHANNEL=stack
LOG_CHANNELS=single
LOG_LEVEL=debug

# Request Logging Configuration
REQUEST_LOGGING_ENABLED=true
REQUEST_LOGGING_LEVEL=info
REQUEST_LOGGING_INCLUDE_BODY=false
REQUEST_LOGGING_EXCLUDE_PATHS=/health,/metrics,/favicon.ico

# Debug Mode
APP_DEBUG=true  # Shows detailed stack traces in logs
```

### Log Channels Configuration

Available log channels (`config/logging.php`):

| Channel | Description | Use Case |
|---------|-------------|-----------|
| `single` | Single log file (`storage/logs/hyperf.log`) | Development |
| `daily` | Daily rotating log files | Production |
| `stack` | Multiple channels combined | Advanced setups |
| `null` | Disable logging | Testing |

## Log Levels

| Level | Use Case | Examples |
|-------|------------|----------|
| `emergency` | System is unusable | Database completely down |
| `alert` | Action must be taken immediately | Critical security breach |
| `critical` | Critical conditions | Application crash |
| `error` | Error conditions | Uncaught exceptions |
| `warning` | Warning conditions | Failed login attempts |
| `notice` | Normal but significant | Deprecated feature usage |
| `info` | Informational messages | Request completion |
| `debug` | Debug messages | Variable values |

## Best Practices

### Logging

1. **Use Structured Logging**
   ```php
   $this->errorLog->logError('User login failed', [
       'user_id' => $userId,
       'ip_address' => $ip,
       'reason' => 'invalid_credentials',
   ]);
   ```

2. **Include Context Information**
   - User ID (when available)
   - Request ID (for tracing)
   - IP address
   - Action being performed
   - Relevant data (IDs, status, etc.)

3. **Use Appropriate Log Levels**
   - `emergency`/`alert`/`critical`: System-down scenarios
   - `error`: Uncaught exceptions
   - `warning`: Non-critical issues (authentication failures)
   - `notice`: Deprecation warnings
   - `info`: Normal operations
   - `debug`: Detailed debugging (only in development)

4. **Sanitize Sensitive Data**
   Never log:
   - Passwords
   - API tokens
   - Credit card numbers
   - Personal information (unless necessary)

5. **Log Security Events**
   ```php
   $this->errorLog->logSecurityEvent('LOGIN_FAILED', [
       'user_id' => $userId ?? 'unknown',
       'ip_address' => $ip,
       'attempts' => $attempts,
   ]);
   ```

### Error Handling

1. **Use Standardized Error Responses**
   Always return errors through BaseController methods:
   ```php
   return $this->errorResponse('Resource not found', 'NOT_FOUND', null, 404);
   return $this->validationErrorResponse($errors);
   return $this->unauthorizedResponse('Invalid credentials');
   ```

2. **Provide Meaningful Error Messages**
   - Clear and actionable
   - User-friendly language
   - Include relevant details when helpful

3. **Use Appropriate HTTP Status Codes**
   - `400`: Bad request
   - `401`: Unauthorized
   - `403`: Forbidden
   - `404`: Not found
   - `422`: Validation error
   - `500`: Server error

4. **Log All Exceptions**
   Even if you handle an exception, log it first:
   ```php
   try {
       $result = $this->riskyOperation();
   } catch (\Exception $e) {
       $this->errorLog->logException($e, [
           'operation' => 'riskyOperation',
           'context' => $context,
       ]);
       throw $e;  // Re-throw if needed
   }
   ```

## Troubleshooting

### Logs Not Appearing

1. **Check logging configuration**:
   ```bash
   grep LOG_LEVEL .env
   grep REQUEST_LOGGING_ENABLED .env
   ```

2. **Verify log file permissions**:
   ```bash
   ls -la storage/logs/
   chmod 644 storage/logs/hyperf.log
   ```

3. **Check available disk space**:
   ```bash
   df -h storage/logs/
   ```

### Health Checks Failing

1. **Database connectivity**:
   ```bash
   php -r "var_dump(new \Hyperf\DbConnection\Db()->getConnection());"
   ```

2. **Redis connectivity**:
   ```bash
   redis-cli -h redis -p 6379 ping
   ```

3. **Check environment variables**:
   ```bash
   php artisan env  # List all environment variables
   ```

### Slow Requests

1. **Check slow request logs**:
   ```bash
   grep "Slow request" storage/logs/hyperf.log
   ```

2. **Review database query performance**:
   ```bash
   grep "duration_ms" storage/logs/hyperf.log | tail -20
   ```

3. **Check database indexes** and optimize slow queries

## Monitoring

### Key Metrics to Monitor

| Metric | Target | Alert Threshold |
|--------|---------|----------------|
| API Response Time | <200ms | >500ms |
| Error Rate | <1% | >5% |
| Uptime | >99.9% | <99.9% |
| Memory Usage | <512MB | >512MB |
| Database Query Time | <100ms | >1000ms |

### Setting Up Monitoring

1. **Log Aggregation**:
   - Use log management tools (ELK, Splunk, Graylog)
   - Set up log rotation
   - Configure alerts for error spikes

2. **APM Integration**:
   - Application Performance Monitoring (Sentry, New Relic, Datadog)
   - Track response times and error rates
   - Set up custom dashboards

3. **Health Check Monitoring**:
   - External monitoring service (UptimeRobot, Pingdom)
   - Monitor `/health` endpoint
   - Configure SMS/email alerts

## Integration with External Services

### Sentry Integration

To integrate Sentry for error tracking:

1. Install Sentry SDK:
   ```bash
   composer require sentry/sentry
   ```

2. Configure Sentry in `.env`:
   ```bash
   SENTRY_DSN=https://your-dsn@sentry.io/project-id
   ```

3. Initialize in `config/autoload.php`:
   ```php
   \Sentry\init([
       'dsn' => env('SENTRY_DSN'),
       'environment' => env('APP_ENV'),
   ]);
   ```

### Log Forwarding

Forward logs to external services using syslog:

1. Configure log channel in `config/logging.php`:
   ```php
   'syslog' => [
       'driver' => 'syslog',
       'facility' => LOG_USER,
       'level' => env('LOG_LEVEL', 'debug'),
   ],
   ```

2. Set up external log management (Papertrail, Loggly)

## Security Considerations

1. **Log Access Control**
   - Restrict log file permissions (644)
   - Regular log rotation
   - Archive old logs

2. **Sensitive Data Protection**
   - Never log passwords or tokens
   - Sanitize PII when possible
   - Use redaction for sensitive fields

3. **Audit Trail**
   - Log all state-changing operations
   - Include user context
   - Track timestamp

4. **Log Tampering Protection**
   - Write logs to append-only location
   - Monitor log file integrity
   - Consider immutable logs for critical systems

## Performance Optimization

### Reducing Logging Overhead

1. **Async Logging** (future enhancement):
   ```php
   // Queue logs for async processing
   Queue::push(new LogEntry($message, $level, $context));
   ```

2. **Conditional Logging**:
   ```bash
   # Disable detailed logging in production
   APP_DEBUG=false
   LOG_LEVEL=warning  # Only warnings and above
   ```

3. **Log Exclusion**:
   - Exclude health check endpoints
   - Exclude frequent polling endpoints
   - Exclude monitoring pings

### Log Rotation

Configure log rotation in `config/logging.php`:

```php
'daily' => [
    'driver' => 'daily',
    'path' => storage_path('logs/hyperf.log'),
    'level' => env('LOG_LEVEL', 'debug'),
    'days' => 14,  // Keep 14 days of logs
    'replace_placeholders' => true,
],
```

## Future Enhancements

### Planned Improvements

1. **Structured Logging with Correlation IDs**
   - Generate unique request IDs
   - Track requests across services
   - Enable distributed tracing

2. **Advanced Error Analytics**
   - Error rate limiting
   - Pattern detection
   - Automatic alerting

3. **Log Search and Filtering**
   - Web UI for log viewing
   - Advanced search capabilities
   - Export functionality

4. **Integration with APM Services**
   - OpenTelemetry support
   - Custom metrics
   - Distributed tracing

## Appendix: Example Log Entries

### Successful Request

```
[2024-01-19T12:00:00.000+00:00].INFO: HTTP request completed successfully {
    "log_type": "http_request",
    "method": "GET",
    "path": "/api/health",
    "query": [],
    "status_code": 200,
    "duration_ms": 5.23,
    "ip": "192.168.1.100",
    "user_agent": "Mozilla/5.0"
}
```

### Error

```
[2024-01-19T12:00:00.000+00:00].ERROR: API Error: Database connection failed

[2024-01-19T12:00:01.000+00:00].ERROR: API Error occurred {
    "log_type": "exception",
    "exception_type": "PDOException",
    "exception_message": "SQLSTATE[HY000] [2002] Connection refused",
    "exception_file": "/app/Services/UserService.php",
    "exception_line": 45,
    "status_code": 500,
    "request_uri": "/api/users/123",
    "request_method": "GET",
    "ip_address": "192.168.1.100"
}
```

### Security Event

```
[2024-01-19T12:00:00.000+00:00].WARNING: Security Event: LOGIN_FAILED {
    "log_type": "security",
    "environment": "production",
    "timestamp": "2024-01-19T12:00:00Z",
    "user_id": "[REDACTED]",
    "ip_address": "203.0.113.42",
    "attempts": 3
}
```

### Performance Metric

```
[2024-01-19T12:00:00.000+00:00].INFO: Performance: query_execution_time {
    "log_type": "performance",
    "metric": "query_execution_time",
    "value": 0.045,
    "value_ms": 45,
    "environment": "production"
}
```
