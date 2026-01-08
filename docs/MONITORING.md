# Monitoring and Observability System

## Overview

The Malnu Backend application includes a comprehensive monitoring and observability system that provides real-time health checks, performance metrics, error tracking, and structured logging. This system helps ensure application reliability, enables proactive issue detection, and provides insights for performance optimization.

## Table of Contents

- [Health Check Endpoints](#health-check-endpoints)
- [Monitoring Endpoints](#monitoring-endpoints)
- [Monitoring Middleware](#monitoring-middleware)
- [Error Tracking](#error-tracking)
- [Performance Metrics](#performance-metrics)
- [Configuration](#configuration)
- [Troubleshooting](#troubleshooting)

## Health Check Endpoints

### Basic Health Check

**Endpoint:** `GET /health`

Returns the overall health status of the application and core components.

**Response Example:**
```json
{
  "success": true,
  "data": {
    "status": "healthy",
    "timestamp": "2026-01-08T22:30:45+00:00",
    "checks": {
      "app": {
        "status": "ok",
        "message": "Application is running"
      },
      "database": {
        "status": "ok",
        "message": "Database connection successful"
      },
      "redis": {
        "status": "ok",
        "message": "Redis connection successful"
      }
    }
  }
}
```

**Status Codes:**
- `200` - All components healthy
- `503` - One or more components unhealthy

**Use Cases:**
- Load balancer health checks
- Container orchestration (Kubernetes, Docker Compose)
- Infrastructure monitoring tools

### Detailed Health Check

**Endpoint:** `GET /health/detailed`

Comprehensive health status including system resources and performance metrics.

**Response Example:**
```json
{
  "success": true,
  "data": {
    "status": "healthy",
    "timestamp": "2026-01-08T22:30:45+00:00",
    "checks": { ... },
    "system": {
      "memory": {
        "current": 52428800,
        "peak": 104857600,
        "limit": "128M"
      },
      "php_version": "8.2.0",
      "server_time": "2026-01-08T22:30:45+00:00"
    },
    "performance": {
      "average_response_time_ms": 125.5,
      "slow_requests_count": 15,
      "slow_database_queries": 3
    },
    "uptime": {
      "seconds": 86400,
      "human_readable": "1d 0h 0m 0s"
    }
  }
}
```

**Use Cases:**
- Detailed system diagnostics
- Performance analysis
- Resource monitoring
- Application profiling

## Monitoring Endpoints

### Performance Metrics

**Endpoint:** `GET /monitoring/metrics`

Real-time performance metrics and status evaluation.

**Response Example:**
```json
{
  "success": true,
  "data": {
    "metrics": {
      "total_requests": 1000,
      "successful_requests": 995,
      "failed_requests": 5,
      "total_response_time": 125000,
      "average_response_time": 125.0,
      "error_rate": 0.5,
      "success_rate": 99.5,
      "slow_requests": 20,
      "database_queries": 5000,
      "slow_database_queries": 10
    },
    "thresholds": {
      "max_response_time": 200,
      "max_error_rate": 1,
      "min_success_rate": 99,
      "slow_query_threshold": 1000
    },
    "status": "healthy"
  }
}
```

**Status Values:**
- `healthy` - All metrics within thresholds
- `degraded` - Response time above threshold
- `critical` - Error rate above threshold

**Use Cases:**
- Real-time performance monitoring
- Capacity planning
- SLA compliance checking
- Dashboard metrics

### Recent Errors

**Endpoint:** `GET /monitoring/errors`

List of recent errors with classification and summary.

**Response Example:**
```json
{
  "success": true,
  "data": {
    "total": 50,
    "errors": [
      {
        "message": "SQL connection failed",
        "timestamp": "2026-01-08T22:30:45+00:00",
        "context": {
          "path": "/api/students",
          "method": "GET",
          "user_id": "123"
        },
        "type": "database"
      }
    ],
    "summary": {
      "by_type": {
        "database": 30,
        "network": 10,
        "validation": 8,
        "authentication": 2
      },
      "by_message": {
        "SQL connection failed": 20,
        "Connection timeout": 10,
        "Validation failed": 8
      }
    }
  }
}
```

**Error Types:**
- `database` - Database-related errors
- `network` - Connection and timeout errors
- `authentication` - Auth-related errors
- `validation` - Input validation errors
- `general` - Other errors

**Use Cases:**
- Error monitoring and alerting
- Root cause analysis
- Debugging production issues
- Error trend analysis

## Monitoring Middleware

The `MonitoringMiddleware` automatically tracks all HTTP requests and responses.

### Features

- **Request Tracking:** Logs method, path, query parameters, and client IP
- **Response Timing:** Measures and logs response times
- **Error Tracking:** Captures and logs exceptions
- **Slow Request Detection:** Flags requests exceeding 200ms threshold
- **User Context:** Includes authenticated user information when available

### Excluded Paths

The middleware excludes monitoring endpoints from logging to prevent infinite loops:
- `/health`
- `/health/detailed`
- `/monitoring/metrics`

### Log Levels

- `INFO` - Successful requests under threshold
- `WARNING` - Slow requests (>200ms) or client errors (4xx)
- `ERROR` - Server errors (5xx) or exceptions

### Log Format

Logs are structured in JSON format:

```json
{
  "timestamp": "2026-01-08T22:30:45+00:00",
  "level": "info",
  "message": "GET /api/students - 200 (125ms)",
  "context": {
    "method": "GET",
    "path": "/api/students",
    "query": "",
    "ip": "192.168.1.1",
    "user_agent": "Mozilla/5.0...",
    "user_id": "123",
    "request_id": "a1b2c3d4",
    "response": {
      "status": 200,
      "response_time_ms": 125
    }
  }
}
```

## Error Tracking

The monitoring system automatically tracks and classifies errors.

### Error Classification

Errors are automatically classified into types:

| Type | Description | Examples |
|------|-------------|----------|
| `database` | Database-related errors | SQL connection failed, query timeout |
| `network` | Connection issues | Connection timeout, network unreachable |
| `authentication` | Auth-related errors | Invalid token, unauthorized access |
| `validation` | Input validation failures | Invalid input, required field missing |
| `general` | Other errors | Generic exceptions |

### Error Storage

- Errors are stored in Redis for 24 hours
- Maximum 1,000 recent errors retained
- Automatically rotated (FIFO)

### Error Context

Each error includes:
- Error message
- Timestamp
- Request context (method, path, user)
- Error type classification
- Stack trace (in logs)

## Performance Metrics

### Tracked Metrics

| Metric | Description | Threshold |
|---------|-------------|------------|
| Total Requests | Total number of requests processed | N/A |
| Successful Requests | Requests with 2xx/3xx status | N/A |
| Failed Requests | Requests with 5xx status | N/A |
| Average Response Time | Mean response time in milliseconds | <200ms |
| Error Rate | Percentage of failed requests | <1% |
| Success Rate | Percentage of successful requests | >99% |
| Slow Requests | Requests >200ms response time | N/A |
| Database Queries | Total database queries executed | N/A |
| Slow Database Queries | Queries >1000ms | N/A |

### Performance Baselines

The system monitors against these baselines:

- **Response Time:** <200ms target
- **Error Rate:** <1% target
- **Success Rate:** >99% target
- **Memory:** <512MB per request
- **Database Queries:** Optimized to prevent N+1 queries

## Configuration

### Monitoring Service Configuration

The `MonitoringService` can be configured in `app/Services/MonitoringService.php`:

```php
// Slow query threshold in milliseconds
private int $slowQueryThreshold = 1000;

// Slow endpoint threshold in milliseconds
private array $slowEndpoints = [
    'threshold' => 200,
    'log_slow' => true,
];
```

### Middleware Configuration

The `MonitoringMiddleware` can be configured in `app/Http/Middleware/MonitoringMiddleware.php`:

```php
// Paths to exclude from monitoring
private array $excludePaths = [
    '/health',
    '/health/detailed',
    '/monitoring/metrics',
];

// Slow endpoint threshold
private array $slowEndpoints = [
    'threshold' => 200,
    'log_slow' => true,
];
```

### Enabling Monitoring Middleware

Add the middleware to your route groups in `routes/api.php`:

```php
Route::group(['middleware' => ['jwt', 'monitoring']], function () {
    // Your protected routes here
});
```

**Note:** Currently, monitoring middleware is not applied to all routes to avoid performance overhead during development. Apply it as needed.

## Troubleshooting

### Health Check Returns Unhealthy

**Symptom:** `/health` endpoint returns 503 status

**Possible Causes:**
1. Database connection failure
2. Redis connection failure
3. Application crash

**Solution:**
1. Check database configuration in `.env`
2. Verify Redis service is running: `docker-compose ps redis`
3. Check application logs: `tail -f storage/logs/hyperf.log`

### High Error Rate

**Symptom:** `/monitoring/metrics` shows error rate >1%

**Possible Causes:**
1. Database connectivity issues
2. External service failures
3. Application bugs

**Solution:**
1. Check `/monitoring/errors` for error details
2. Review error types in summary
3. Check application logs for stack traces
4. Fix identified issues

### Slow Response Times

**Symptom:** `/monitoring/metrics` shows average response time >200ms

**Possible Causes:**
1. Slow database queries
2. External API calls
3. Resource contention

**Solution:**
1. Check `slow_database_queries` metric
2. Review database query performance
3. Optimize slow queries with indexes
4. Check system resources
5. Review `/health/detailed` for memory usage

### Errors Not Being Tracked

**Symptom:** `/monitoring/errors` shows no recent errors

**Possible Causes:**
1. Redis connection issues
2. Error data expired (24-hour retention)
3. Errors not reaching middleware

**Solution:**
1. Verify Redis connectivity: `redis-cli ping`
2. Check Redis error storage: `redis-cli lrange monitoring:errors:recent 0 9`
3. Verify middleware is applied to routes

### High Memory Usage

**Symptom:** `/health/detailed` shows memory near limit

**Possible Causes:**
1. Memory leaks
2. Large data sets loaded
3. Inefficient queries

**Solution:**
1. Check for memory leaks with profiling tools
2. Optimize database queries
3. Use pagination for large datasets
4. Clear caches: `php artisan cache:clear`

## Best Practices

### 1. Regular Health Checks

- Set up load balancer health checks to `/health` every 30 seconds
- Configure container orchestration health checks
- Monitor health check failures with alerts

### 2. Monitor Key Metrics

- Track error rate trends
- Monitor response time percentiles
- Set up alerts for threshold breaches
- Review metrics regularly

### 3. Error Investigation

- Check `/monitoring/errors` when errors spike
- Review error types to identify patterns
- Investigate frequent error messages
- Fix root causes proactively

### 4. Performance Optimization

- Identify slow endpoints from metrics
- Optimize slow database queries
- Implement caching where appropriate
- Monitor resource usage trends

### 5. Log Management

- Configure log rotation in `config/logging.php`
- Set appropriate log retention periods
- Archive logs for compliance if needed
- Monitor log volume and storage

## Integration with External Tools

### Prometheus Exporter

To integrate with Prometheus:

```php
// Create metrics endpoint that returns Prometheus format
Route::get('/metrics', function () {
    $metrics = $monitoringService->getMetrics();
    
    $output = "# HELP http_requests_total Total HTTP requests\n";
    $output .= "# TYPE http_requests_total counter\n";
    $output .= "http_requests_total " . $metrics['metrics']['total_requests'] . "\n";
    
    return response($output)->withHeader('Content-Type', 'text/plain');
});
```

### Elasticsearch/Kibana

Configure logging to use Elasticsearch handler in `config/logging.php`:

```php
'elasticsearch' => [
    'driver' => 'monolog',
    'handler' => ElasticsearchHandler::class,
    'with' => [
        'hosts' => [env('ELASTICSEARCH_HOST', 'localhost:9200')],
        'index' => 'malnu-backend',
    ],
],
```

### Sentry Integration

Add Sentry for enhanced error tracking:

```bash
composer require sentry/sentry
```

Configure in `app/Exceptions/Handler.php`:

```php
use Sentry\State\Scope;

public function report(Throwable $exception)
{
    Sentry\captureException($exception);
    parent::report($exception);
}
```

## API Reference

### HealthController

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|----------------|
| GET | `/health` | Basic health check | No |
| GET | `/health/detailed` | Detailed health status | No |
| GET | `/monitoring/metrics` | Performance metrics | No |
| GET | `/monitoring/errors` | Recent errors | No |

### MonitoringService

| Method | Description |
|--------|-------------|
| `getBasicHealth()` | Returns basic health status |
| `getDetailedHealth()` | Returns detailed health with system info |
| `getMetrics()` | Returns performance metrics |
| `getRecentErrors()` | Returns recent errors with summary |
| `trackRequest(array $data)` | Tracks a request |
| `trackError(string $error, array $context)` | Tracks an error |
| `trackDatabaseQuery(int $duration)` | Tracks a database query |

## Support

For issues or questions about the monitoring system:
1. Check this documentation
2. Review logs in `storage/logs/hyperf.log`
3. Check the troubleshooting section above
4. Open a GitHub issue with details

---

*Last Updated: January 8, 2026*
