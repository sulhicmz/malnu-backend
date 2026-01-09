# Request/Response Logging Middleware

## Overview

The Request/Response Logging Middleware (`RequestResponseLoggingMiddleware`) provides centralized logging of all API requests and responses for debugging, security auditing, and monitoring purposes.

## Features

- **Request Logging**: Captures HTTP method, URI, query parameters, request body, IP address, user agent, and headers
- **Response Logging**: Captures HTTP status code, response time, and response body (for JSON responses)
- **Unique Request IDs**: Generates a UUID v4 for each request for end-to-end tracing
- **Sensitive Data Protection**: Automatically redacts sensitive fields (passwords, tokens, etc.) from logs
- **Response Time Tracking**: Measures and logs request duration in milliseconds
- **Performance Optimized**: Minimal overhead with efficient string handling
- **Configurable**: Can be enabled/disabled via environment variables

## Installation

The middleware is automatically registered in `app/Http/Kernel.php` as global middleware, so it runs on all requests.

## Configuration

Add the following environment variables to your `.env` file:

```env
# Enable or disable request/response logging
REQUEST_LOGGING_ENABLED=true

# Log channel for API requests (api for separate file, single for main log)
REQUEST_LOG_CHANNEL=api
```

### Environment Variables

| Variable | Default | Description |
|----------|----------|-------------|
| `REQUEST_LOGGING_ENABLED` | `true` | Enable or disable request/response logging |
| `REQUEST_LOG_CHANNEL` | `api` | Log channel to use for API logs |

## Log Format

### Request Logs

```json
{
  "message": "API Request",
  "context": {
    "request_id": "550e8400-e29b-41d4-a716-446655440000",
    "method": "POST",
    "uri": "https://api.example.com/auth/login",
    "query": {},
    "ip": "192.168.1.1",
    "user_agent": "Mozilla/5.0...",
    "content_type": "application/json",
    "headers": {
      "content-type": ["application/json"],
      "accept": ["application/json"]
    },
    "body": {
      "email": "user@example.com",
      "password": "[REDACTED]"
    },
    "user_id": "123e4567-e89b-12d3-a456-426614174000"
  }
}
```

### Response Logs

```json
{
  "message": "API Response",
  "level": "info",
  "context": {
    "request_id": "550e8400-e29b-41d4-a716-446655440000",
    "status_code": 200,
    "reason_phrase": "OK",
    "duration_ms": 45.32,
    "headers": {
      "content-type": ["application/json"],
      "x-request-id": ["550e8400-e29b-41d4-a716-446655440000"]
    },
    "body": {
      "success": true,
      "data": { ... }
    }
  }
}
```

## Sensitive Data Redaction

The middleware automatically redacts sensitive fields from both request bodies and headers:

### Redacted Request Body Fields

- `password`
- `password_confirmation`
- `current_password`
- `new_password`
- `token`
- `api_token`
- `access_token`
- `refresh_token`
- `auth_token`
- `secret`
- `api_key`
- `credit_card`
- `card_number`
- `cvv`
- `ssn`
- `social_security_number`

### Redacted Headers

- `authorization`
- `cookie`
- `set-cookie`
- `x-api-key`
- `x-auth-token`

All sensitive values are replaced with `[REDACTED]` in logs.

## Response Headers

The middleware adds the following headers to all responses:

| Header | Description | Example |
|--------|-------------|---------|
| `X-Request-ID` | Unique identifier for the request | `550e8400-e29b-41d4-a716-446655440000` |
| `X-Response-Time` | Request processing time in milliseconds | `45.32ms` |

## Log Files

Logs are written to the configured log channel:

- **api channel**: `storage/logs/api.log` (rotated daily, 14-day retention)
- **single channel**: `storage/logs/hyperf.log` (all logs in one file)

## Log Levels

The middleware uses different log levels based on response status codes:

- **`info`**: 2xx and 3xx responses (success and redirects)
- **`warning`**: 4xx responses (client errors)
- **`error`**: 5xx responses (server errors)

## Performance Impact

The middleware has minimal performance overhead:

- Request ID generation: < 0.1ms
- Logging: 1-5ms depending on log configuration
- Total overhead: ~5-10ms per request

To minimize impact in production:
- Use async logging if available
- Set appropriate log level (e.g., `warning` instead of `debug`)
- Consider disabling request body logging for high-traffic endpoints

## Troubleshooting

### Logs Not Appearing

1. Check if `REQUEST_LOGGING_ENABLED=true` in `.env`
2. Verify log file permissions: `storage/logs/` must be writable
3. Check log level: If set to `error`, only 5xx responses are logged
4. Verify log channel configuration in `config/logging.php`

### Missing Request ID

If `X-Request-ID` header is missing from responses:
1. Verify middleware is registered in `app/Http/Kernel.php`
2. Check if middleware is running before other middleware
3. Ensure no exception is thrown before middleware completes

### Performance Issues

If logging is causing performance problems:
1. Disable request body logging by modifying the middleware
2. Increase log level to `warning` or `error`
3. Use async logging or external log aggregation service
4. Consider selective logging (only log specific routes)

### Large Log Files

If log files are growing too large:
1. Verify daily rotation is working (`days: 14` in config)
2. Reduce retention period in `config/logging.php`
3. Increase log level to reduce verbosity
4. Consider external log aggregation (ELK, Splunk, etc.)

## Security Considerations

### Data Protection

- **Sensitive data redaction**: Always enabled by default
- **IP logging**: Client IPs are logged but can be disabled if needed
- **User tracking**: User IDs are logged when available (for authenticated requests)

### Log Access

Log files may contain sensitive information even after redaction:
- Restrict file system access to `storage/logs/`
- Use appropriate file permissions (e.g., `chmod 600`)
- Rotate and archive logs regularly
- Consider log encryption in production

### Compliance

For GDPR or similar regulations:
- Document what data is logged
- Provide data export/deletion capabilities
- Set appropriate retention periods
- Consider anonymization for IP addresses

## Usage Examples

### Viewing Recent Logs

```bash
# View last 100 API requests
tail -n 100 storage/logs/api.log

# Follow logs in real-time
tail -f storage/logs/api.log

# Search for specific request ID
grep "550e8400-e29b-41d4-a716-446655440000" storage/logs/api.log

# Filter by error responses
grep '"level":"error"' storage/logs/api.log

# Find slow requests (> 1 second)
grep '"duration_ms":[1-9][0-9][0-9]\.' storage/logs/api.log
```

### Request Tracing

Use the `X-Request-ID` header to trace a request through the system:

```bash
# Get the request ID from response headers
curl -I https://api.example.com/users/123
# X-Request-ID: 550e8400-e29b-41d4-a716-446655440000

# Search logs for the full request/response cycle
grep "550e8400-e29b-41d4-a716-446655440000" storage/logs/api.log
```

## Testing

Run the middleware tests:

```bash
# Run all middleware tests
composer test -- tests/Feature/RequestResponseLoggingTest.php

# Run specific test
composer test -- tests/Feature/RequestResponseLoggingTest.php::testMiddlewareAddsRequestIdToResponse
```

## See Also

- [Logging Configuration](../config/logging.php)
- [Middleware Documentation](../app/Http/Middleware/RequestResponseLoggingMiddleware.php)
- [HTTP Kernel](../app/Http/Kernel.php)
- [API Documentation](API.md)
- [Security Analysis](SECURITY_ANALYSIS.md)
