# Application Monitoring and Observability

## Overview

The Malnu Backend application includes comprehensive monitoring and observability infrastructure to ensure production readiness, enable proactive issue detection, and provide insights into application performance and health.

## Components

### 1. Health Check Endpoints

Health check endpoints provide real-time status of application components and infrastructure.

#### Endpoints

**Overall Health**
```
GET /health
```

Returns comprehensive health status including:
- Overall status (`healthy` or `unhealthy`)
- Timestamp
- Application version
- Individual component checks (database, redis, system)

Response example (200 - Healthy):
```json
{
  "status": "healthy",
  "timestamp": "2026-01-20T12:00:00+00:00",
  "version": "1.0.0",
  "checks": {
    "database": {
      "status": "ok",
      "message": "Database connection successful",
      "latency_ms": 2.34,
      "connection": "malnu"
    },
    "redis": {
      "status": "ok",
      "message": "Redis connection successful",
      "latency_ms": 0.87,
      "used_memory": "50.25M",
      "connected_clients": 5
    },
    "system": {
      "status": "ok",
      "message": "System resources healthy",
      "memory_usage": "128.50 MB",
      "memory_limit": "512M",
      "load_average": {
        "1min": 0.5,
        "5min": 0.6,
        "15min": 0.4
      },
      "disk": {
        "free": "45.20 GB",
        "total": "100.00 GB",
        "usage_percent": 54.8
      }
    }
  }
}
```

Response example (503 - Unhealthy):
```json
{
  "status": "unhealthy",
  "timestamp": "2026-01-20T12:00:00+00:00",
  "version": "1.0.0",
  "checks": {
    "database": {
      "status": "error",
      "message": "Database connection failed",
      "error": "Connection refused",
      "latency_ms": 10.23
    },
    "redis": {
      "status": "ok",
      ...
    },
    "system": {
      "status": "ok",
      ...
    }
  }
}
```

**Individual Component Health**
```
GET /health/database
GET /health/redis
GET /health/system
```

Each endpoint returns detailed health information for that specific component.

### 2. Metrics Endpoint

Metrics endpoint provides comprehensive application performance metrics.

**Endpoint**
```
GET /metrics
```

Returns application metrics including:
- Request metrics (count, response times, success rate)
- Error metrics (error rate, top errors)
- Database metrics (query count, slow queries)
- Cache metrics (hit rate, memory usage)
- System metrics (memory, load, uptime)

Response example:
```json
{
  "generated_at": "2026-01-20T12:00:00+00:00",
  "requests": {
    "total_requests": 15432,
    "successful_requests": 15238,
    "failed_requests": 194,
    "avg_response_time_ms": 45.23,
    "p95_response_time_ms": 89.45,
    "p99_response_time_ms": 156.78
  },
  "errors": {
    "total_errors": 194,
    "error_rate_percent": 1.26,
    "top_errors": {
      "InvalidArgumentException": 89,
      "PDOException": 45,
      "RuntimeException": 38,
      ...
    },
    "critical_errors": 12,
    "warning_errors": 182
  },
  "database": {
    "connection_pool_size": 100,
    "active_connections": 15,
    "query_count": 4523,
    "avg_query_time_ms": 2.34,
    "slow_queries_count": 23
  },
  "cache": {
    "hit_rate_percent": 87.45,
    "total_hits": 12543,
    "total_misses": 1807,
    "used_memory": "50.25M",
    "evictions": 15,
    "connected_clients": 5
  },
  "system": {
    "memory_usage_mb": 128.50,
    "memory_limit_mb": 512.00,
    "memory_usage_percent": 25.10,
    "load_average": {
      "1min": 0.5,
      "5min": 0.6,
      "15min": 0.4
    },
    "uptime_seconds": 86400
  }
}
```

### 3. Metrics Collection Middleware

The `MetricsCollectionMiddleware` automatically collects request and error metrics for all HTTP requests (excluding health/metrics endpoints).

**Features:**
- Request counting and categorization
- Response time tracking (avg, p95, p99)
- Error tracking and classification
- Status code distribution
- Automatic aggregation and percentile calculation

### 4. Monitoring Service

The `MonitoringService` provides proactive alerting based on configurable thresholds.

**Alert Types:**
- High error rate (> threshold)
- High response time (> threshold)
- High disk usage (> threshold)
- High memory usage (> threshold)

**Alert Channels:**
- Email notifications (configured via `MONITORING_ALERT_EMAIL`)
- Slack webhooks (configured via `MONITORING_ALERT_SLACK_WEBHOOK`)

**Alert Deduplication:**
Alerts are deduplicated to prevent spam - same alert won't be sent more than once every 5 minutes.

## Configuration

Monitoring is configured via `config/monitoring.php`.

### Environment Variables

| Variable | Description | Default |
|-----------|-------------|----------|
| `MONITORING_ENABLED` | Enable/disable monitoring | `true` |
| `MONITORING_ALERTING_ENABLED` | Enable alerting | `false` |
| `MONITORING_ERROR_RATE_THRESHOLD` | Error rate threshold for alerts (%) | `5` |
| `MONITORING_RESPONSE_TIME_THRESHOLD` | Response time threshold (ms) | `1000` |
| `MONITORING_DISK_USAGE_THRESHOLD` | Disk usage threshold (%) | `90` |
| `MONITORING_MEMORY_USAGE_THRESHOLD` | Memory usage threshold (%) | `85` |
| `MONITORING_ALERT_EMAIL` | Email for alerts | `null` |
| `MONITORING_ALERT_SLACK_WEBHOOK` | Slack webhook for alerts | `null` |
| `SENTRY_ENABLED` | Enable Sentry APM | `false` |
| `SENTRY_DSN` | Sentry DSN | - |
| `NEW_RELIC_ENABLED` | Enable New Relic APM | `false` |
| `NEW_RELIC_LICENSE_KEY` | New Relic license key | - |
| `DATADOG_ENABLED` | Enable Datadog APM | `false` |
| `DATADOG_API_KEY` | Datadog API key | - |

## Usage

### Basic Health Checks

Use `/health` endpoint for load balancer health checks:

```bash
curl http://localhost:9501/health
```

Expected response for healthy system:
- HTTP 200 status code
- `status: "healthy"`
- All component checks with `status: "ok"`

### Monitoring Metrics

Retrieve metrics for dashboard display:

```bash
curl http://localhost:9501/metrics
```

### Alerting Setup

1. **Enable alerting** in `.env`:
   ```
   MONITORING_ALERTING_ENABLED=true
   MONITORING_ALERT_EMAIL=admin@example.com
   MONITORING_ALERT_SLACK_WEBHOOK=https://hooks.slack.com/services/YOUR/WEBHOOK
   ```

2. **Configure thresholds** based on your requirements:
   ```
   MONITORING_ERROR_RATE_THRESHOLD=5
   MONITORING_RESPONSE_TIME_THRESHOLD=500
   MONITORING_DISK_USAGE_THRESHOLD=85
   MONITORING_MEMORY_USAGE_THRESHOLD=80
   ```

3. **Set up monitoring cron job** (optional):
   ```bash
   # Run alert checks every 5 minutes
   */5 * * * * php artisan monitoring:check-alerts
   ```

## Integration with External APM Services

The monitoring system provides structure for integration with external APM services.

### Sentry

```env
SENTRY_ENABLED=true
SENTRY_DSN=https://your-dsn@sentry.io/project
SENTRY_ENVIRONMENT=production
SENTRY_SAMPLE_RATE=1.0
```

### New Relic

```env
NEW_RELIC_ENABLED=true
NEW_RELIC_APP_NAME=Malnu Backend
NEW_RELIC_LICENSE_KEY=your-license-key
```

### Datadog

```env
DATADOG_ENABLED=true
DATADOG_API_KEY=your-api-key
DATADOG_HOST=app.datadoghq.com
```

## Monitoring Best Practices

### 1. Health Check Configuration

- Configure load balancers to check `/health` every 10-30 seconds
- Use 5-second timeout for health checks
- Configure 2-3 consecutive failures before marking as unhealthy
- Set up alerts for service downtime

### 2. Metrics Collection

- Monitor key metrics: response time, error rate, throughput
- Set realistic alerting thresholds
- Track trends over time (week-over-week comparisons)
- Monitor specific endpoints, not just averages

### 3. Alerting Strategy

- Start with conservative thresholds (higher values)
- Gradually tighten thresholds as you understand baseline
- Use multiple channels (email + Slack) for critical alerts
- Implement on-call rotation for critical alerts

### 4. Data Retention

- Configure appropriate retention periods:
  - Short-term metrics: 5-15 minutes (for real-time dashboards)
  - Long-term metrics: 7-30 days (for trend analysis)
- Archive historical metrics for compliance if needed

### 5. Performance Targets

| Metric | Target | Critical Threshold |
|--------|---------|-------------------|
| Response Time (avg) | <200ms | >1000ms |
| Response Time (p95) | <500ms | >2000ms |
| Error Rate | <1% | >5% |
| Cache Hit Rate | >80% | <60% |
| Disk Usage | <80% | >90% |
| Memory Usage | <75% | >85% |

## Troubleshooting

### Health Check Returns 503

1. Check individual component health:
   ```bash
   curl http://localhost:9501/health/database
   curl http://localhost:9501/health/redis
   curl http://localhost:9501/health/system
   ```

2. Verify database connection in `.env`
3. Check Redis service status
4. Review system resources (disk, memory)

### Metrics Not Updating

1. Check if monitoring is enabled: `MONITORING_ENABLED=true`
2. Verify Redis is running
3. Check `MetricsCollectionMiddleware` is registered in middleware config
4. Review monitoring logs

### Alerting Not Working

1. Verify `MONITORING_ALERTING_ENABLED=true`
2. Check alert threshold configuration
3. Verify alert channel configuration (email/Slack)
4. Review monitoring service logs

## Future Enhancements

1. **WebSocket Push**: Real-time metric streaming to dashboards
2. **Time-Series Database**: Migrate from Redis to InfluxDB/TimescaleDB
3. **Dashboard**: Built-in monitoring dashboard
4. **Advanced Analytics**: Machine learning for anomaly detection
5. **Distributed Tracing**: Request tracing across services
6. **Custom Metrics**: User-defined metrics for business KPIs

## References

- [Hyperf Documentation](https://hyperf.wiki)
- [Redis Documentation](https://redis.io/documentation)
- [Prometheus Best Practices](https://prometheus.io/docs/practices/)
- [APM Comparison](https://apmcomparison.com)
