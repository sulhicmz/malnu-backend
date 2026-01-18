# Monitoring and Health Check System

This document describes the monitoring and health check system implemented for the Malnu Backend application.

## Overview

The monitoring system provides:
- Health check endpoints for application status monitoring
- Performance metrics tracking
- Error tracking and analysis
- System resource monitoring

## Health Check Endpoints

### GET /health

Returns basic health status of the application.

**Authentication**: None required (public)

**Response Format**:
```json
{
  "status": "ok|degraded",
  "timestamp": "2024-01-18T12:00:00Z",
  "checks": {
    "database": true,
    "redis": true
  }
}
```

**Status Values**:
- `ok`: All checks passing
- `degraded`: One or more checks failing

### GET /health/detailed

Returns detailed health status with response times and system metrics.

**Authentication**: None required (public)

**Response Format**:
```json
{
  "status": "ok|degraded",
  "timestamp": "2024-01-18T12:00:00Z",
  "checks": {
    "database": {
      "status": true,
      "message": "Connection successful",
      "response_time_ms": 12.34
    },
    "redis": {
      "status": true,
      "message": "Connection successful",
      "response_time_ms": 5.67
    },
    "system": {
      "status": true,
      "message": "Resources within normal limits",
      "data": {
        "memory_usage_bytes": 52428800,
        "memory_usage_mb": 50.0,
        "memory_limit": "128M",
        "cpu_load_1min": 0.5,
        "cpu_load_5min": 0.6,
        "cpu_load_15min": 0.7
      }
    }
  }
}
```

## Monitoring Endpoints

All monitoring endpoints require JWT authentication with `Super Admin` role.

### GET /api/monitoring/metrics

Returns current system metrics including memory, database, and Redis status.

**Authentication**: Required (JWT, Super Admin role)

**Response Format**:
```json
{
  "system": {
    "memory_usage_bytes": 52428800,
    "memory_usage_mb": 50.0,
    "memory_peak_bytes": 67108864,
    "memory_peak_mb": 64.0,
    "memory_limit": "128M",
    "memory_usage_percent": 39.06
  },
  "database": {
    "status": true,
    "message": "OK",
    "response_time_ms": 12.34
  },
  "redis": {
    "status": true,
    "message": "OK",
    "response_time_ms": 5.67,
    "connected_clients": 5,
    "used_memory": "10.5M"
  },
  "timestamp": "2024-01-18T12:00:00Z"
}
```

### GET /api/monitoring/errors

Returns recent error logs with statistics.

**Authentication**: Required (JWT, Super Admin role)

**Query Parameters**:
- `limit` (optional, default: 50, max: 500): Number of errors to return

**Response Format**:
```json
{
  "errors": [
    {
      "message": "Error message",
      "code": 500,
      "file": "/path/to/file.php",
      "line": 123,
      "trace": "Stack trace...",
      "type": "ExceptionType",
      "timestamp": "2024-01-18T12:00:00Z",
      "context": {}
    }
  ],
  "stats": {
    "total_errors": 100,
    "error_types": {
      "ExceptionType1": 50,
      "ExceptionType2": 30,
      "ExceptionType3": 20
    },
    "timestamp": "2024-01-18T12:00:00Z"
  },
  "limit": 50,
  "count": 50,
  "timestamp": "2024-01-18T12:00:00Z"
}
```

### GET /api/monitoring/errors/stats

Returns error statistics only.

**Authentication**: Required (JWT, Super Admin role)

**Response Format**:
```json
{
  "total_errors": 100,
  "error_types": {
    "ExceptionType1": 50,
    "ExceptionType2": 30,
    "ExceptionType3": 20
  },
  "timestamp": "2024-01-18T12:00:00Z"
}
```

## Configuration

### Environment Variables

Add the following to your `.env` file:

```bash
# Monitoring Configuration
MONITORING_ENABLED=true
MONITORING_SLOW_REQUEST_THRESHOLD_MS=1000
MONITORING_ERROR_RETENTION_DAYS=7
MONITORING_ALERT_ENABLED=false
MONITORING_ALERT_EMAIL=admin@example.com
MONITORING_ALERT_WEBHOOK_URL=
```

### Configuration Options

| Option | Description | Default |
|---------|-------------|----------|
| `MONITORING_ENABLED` | Enable/disable monitoring system | `true` |
| `MONITORING_SLOW_REQUEST_THRESHOLD_MS` | Threshold for logging slow requests (ms) | `1000` |
| `MONITORING_ERROR_RETENTION_DAYS` | Days to keep error logs in Redis | `7` |
| `MONITORING_ALERT_ENABLED` | Enable alert notifications | `false` |
| `MONITORING_ALERT_EMAIL` | Email address for alerts | `null` |
| `MONITORING_ALERT_WEBHOOK_URL` | Webhook URL for alert notifications | `null` |

## Usage Examples

### Health Check (Public)

```bash
curl http://localhost:9501/health
```

### Detailed Health Check

```bash
curl http://localhost:9501/health/detailed
```

### Metrics Endpoint (Authenticated)

```bash
curl -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  http://localhost:9501/api/monitoring/metrics
```

### Errors Endpoint (Authenticated)

```bash
curl -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  http://localhost:9501/api/monitoring/errors?limit=20
```

### Error Statistics (Authenticated)

```bash
curl -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  http://localhost:9501/api/monitoring/errors/stats
```

## Error Tracking Service

The `ErrorTrackingService` provides centralized error logging and tracking:

### Features

- Automatic error logging with Redis storage
- Error deduplication by file, line, and message
- Configurable error retention period
- Error statistics and aggregation
- Recent error retrieval with pagination

### Methods

```php
// Log an exception
$errorTrackingService->logError($exception, ['user_id' => 123]);

// Get recent errors
$errors = $errorTrackingService->getRecentErrors(100);

// Get error statistics
$stats = $errorTrackingService->getErrorStats();

// Clear old errors
$deletedCount = $errorTrackingService->clearOldErrors(168); // 7 days
```

## Performance Monitoring

### Slow Request Logging

Requests exceeding `MONITORING_SLOW_REQUEST_THRESHOLD_MS` are automatically logged as warnings in the application logs.

### Response Time Tracking

All health checks and monitoring metrics include response time measurements in milliseconds.

### System Resource Monitoring

- Memory usage (current and peak)
- CPU load averages (1, 5, and 15 minutes)
- Memory limit and usage percentage

## Security Considerations

### Public vs Protected Endpoints

- **Public endpoints** (`/health`, `/health/detailed`): No authentication required
- **Protected endpoints** (`/api/monitoring/*`): JWT authentication with Super Admin role required

### Rate Limiting

Health check endpoints should be excluded from rate limiting to allow continuous monitoring tools.

## Troubleshooting

### Health Check Returns Degraded Status

1. Check individual check status in `/health/detailed`
2. Verify database connection in `.env`
3. Verify Redis connection in `.env`
4. Check system resource limits

### Monitoring Endpoints Return 401 Unauthorized

1. Verify JWT token is valid and not expired
2. Ensure user has `Super Admin` role
3. Check token format: `Authorization: Bearer YOUR_TOKEN`

### Error Logs Not Appearing

1. Verify Redis is running: `redis-cli ping`
2. Check `MONITORING_ENABLED=true` in `.env`
3. Verify Redis configuration in `.env`
4. Check Redis connection logs

### High Memory Usage

1. Review `memory_usage_percent` in metrics
2. Check for memory leaks in application code
3. Adjust `memory_limit` in `php.ini`
4. Review error logs for exceptions

## Best Practices

1. **Regular Health Checks**: Set up external monitoring tools (e.g., UptimeRobot, Pingdom) to check `/health` every minute
2. **Alert Thresholds**: Configure alerts when status is degraded
3. **Log Rotation**: Use log rotation to prevent disk space issues
4. **Monitor Response Times**: Track database and Redis response times for performance degradation
5. **Error Trends**: Review error statistics regularly for patterns
6. **System Resources**: Monitor CPU load and memory usage for capacity planning

## Integration with External Tools

### Uptime Monitoring

Configure external monitoring tools to:
- Check `/health` endpoint every 1-5 minutes
- Alert on non-200 status codes
- Alert on degraded status

### Log Aggregation

For production deployments, consider:
- Centralized log management (ELK Stack, Splunk, Graylog)
- Structured log parsing
- Log retention policies

### APM Integration

For advanced monitoring, integrate with:
- New Relic
- Datadog
- AppDynamics
- Prometheus + Grafana

## API Response Standards

All monitoring endpoints follow the project's standard API response format:
- ISO 8601 timestamps
- JSON format
- Consistent field names (snake_case)
- Appropriate HTTP status codes

## Dependencies

- Hyperf framework
- Redis (for error storage and caching)
- MySQL/PostgreSQL (for database health checks)
- PSR-3 Logger interface

## Future Enhancements

Potential improvements to the monitoring system:

- [ ] Metrics export in Prometheus format
- [ ] Webhook notifications for errors
- [ ] Email alerts for critical errors
- [ ] Performance threshold alerts
- [ ] Historical metrics storage
- [ ] Dashboard UI for monitoring
- [ ] Distributed tracing support
- [ ] Custom metrics registration

## Support

For issues or questions about the monitoring system:

1. Check this documentation
2. Review error logs in `/api/monitoring/errors`
3. Check health status at `/health/detailed`
4. Open an issue on GitHub
