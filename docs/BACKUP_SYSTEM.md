# Backup, Disaster Recovery, and Business Continuity System

## Overview

This document outlines the comprehensive backup, disaster recovery, and business continuity system implemented for the educational institution management system. The system includes automated backup procedures, restoration capabilities, verification processes, and monitoring with alerting.

## Features

- **Automated Backup System**: Scheduled automated backups of all data and configurations
- **Multi-tier Storage**: Local, regional, and cloud-based backup storage with redundancy
- **Point-in-time Recovery**: Granular recovery capabilities to specific points in time
- **Database Backup**: Hot and cold backup strategies for MySQL/SQLite databases
- **File System Backup**: Complete backup of application files, uploads, and user content
- **Configuration Backup**: System settings, environment configurations, and deployment scripts
- **Disaster Recovery Plans**: Automated and manual recovery procedures for different scenarios
- **Business Continuity Planning**: Critical system prioritization and recovery time objectives
- **Monitoring and Alerting**: Backup job monitoring, failure detection, and alert systems
- **Recovery Testing**: Automated testing of backup integrity and recovery procedures
- **Documentation and Training**: Comprehensive runbooks and staff training procedures
- **Compliance Reporting**: Backup compliance reports and audit trail maintenance

## Backup Commands

### Database Backup
```bash
php artisan backup:database --connection=mysql --path=/path/to/backup --compress --clean-old
```

Options:
- `--connection`: Database connection name (default: mysql)
- `--path`: Backup storage path (default: storage/backups/database)
- `--compress`: Compress the backup
- `--clean-old`: Clean old backups (keep 5 most recent)

### File System Backup
```bash
php artisan backup:filesystem --include="app,config,database" --exclude="node_modules,vendor" --path=/path/to/backup --clean-old
```

Options:
- `--include`: Comma-separated list of directories/files to include
- `--exclude`: Comma-separated list of directories/files to exclude
- `--path`: Backup storage path (default: storage/backups/filesystem)
- `--clean-old`: Clean old backups (keep 5 most recent)

### Configuration Backup
```bash
php artisan backup:config --path=/path/to/backup --clean-old
```

Options:
- `--path`: Backup storage path (default: storage/backups/config)
- `--clean-old`: Clean old backups (keep 5 most recent)

### Comprehensive Backup
```bash
php artisan backup:all --connection=mysql --path=/path/to/backup --compress --no-db --no-fs --no-config --force
```

Options:
- `--connection`: Database connection name
- `--path`: Backup storage path (default: storage/backups)
- `--compress`: Compress the backup (default: true)
- `--no-db`: Skip database backup
- `--no-fs`: Skip file system backup
- `--no-config`: Skip configuration backup
- `--force`: Force backup without confirmation

## Restoration Commands

### Restore Backup
```bash
php artisan restore:backup /path/to/backup.tar.gz --type=all --connection=mysql --force
```

Options:
- `backup-file`: Path to the backup file (required)
- `--type`: Type of restore: database, filesystem, config, or all (default: all)
- `--connection`: Database connection name
- `--force`: Force restore without confirmation

## Verification Commands

### Verify Backup
```bash
php artisan backup:verify /path/to/backup.tar.gz --type=all
```

Options:
- `backup-file`: Path to the backup file (required)
- `--type`: Type of verification: database, filesystem, config, checksum, or all (default: all)

## Monitoring Commands

### Monitor Backup Status
```bash
php artisan backup:health --alert-on-fail
```

Options:
- `--alert-on-fail`: Send alert if health check fails

### Schedule Automated Backups
```bash
php artisan backup:schedule --type=comprehensive --encrypt
```

Options:
- `--type`: Type of backup: database, filesystem, config, comprehensive, or scheduled
- `--connection`: Database connection name
- `--encrypt`: Encrypt backup file

## API Management

The backup system now provides REST API endpoints for remote backup management. All API endpoints require JWT authentication and admin role.

### Backup Management API

**Base URL**: `/api/backups`

#### List All Backups
```http
GET /api/backups
Authorization: Bearer {JWT_TOKEN}
```

Query Parameters:
- `type`: Filter by type (database, filesystem, config, all)

**Response**:
```json
{
  "success": true,
  "data": {
    "backups": [
      {
        "id": "abc123...",
        "filename": "full_backup_2026-01-13-10-30-00.tar.gz",
        "size": 10485760,
        "size_human": "100.00 MB",
        "created_at": "2026-01-13T10:30:00Z",
        "modified_at": "2026-01-13T10:30:00Z",
        "type": "comprehensive",
        "is_compressed": true
      }
    ],
    "statistics": {
      "database_backups": 7,
      "filesystem_backups": 7,
      "config_backups": 5,
      "comprehensive_backups": 5
    },
    "latest_backups": {
      "database": "...",
      "filesystem": "...",
      "config": "...",
      "comprehensive": "..."
    },
    "timestamp": "2026-01-13T10:30:45Z"
  },
  "message": "Backup list retrieved successfully"
}
```

#### Create Backup
```http
POST /api/backups
Authorization: Bearer {JWT_TOKEN}
Content-Type: application/json

{
  "type": "comprehensive",
  "compress": true,
  "connection": "mysql",
  "description": "Weekly backup"
}
```

**Response**:
```json
{
  "success": true,
  "data": {
    "success": true,
    "timestamp": "2026-01-13T10:30:00Z",
    "type": "comprehensive",
    "encrypted": false,
    "encryption_method": "AES-256-GCM"
  },
  "message": "Backup created successfully"
}
```

#### Get Backup Details
```http
GET /api/backups/{backup_id}
Authorization: Bearer {JWT_TOKEN}
```

**Response**:
```json
{
  "success": true,
  "data": {
    "filename": "full_backup_2026-01-13-10-30-00.tar.gz",
    "path": "/storage/backups/full_backup_2026-01-13-10-30-00.tar.gz",
    "size": 10485760,
    "size_human": "100.00 MB",
    "created_at": "2026-01-13T10:30:00Z",
    "modified_at": "2026-01-13T10:30:00Z",
    "type": "comprehensive",
    "is_compressed": true,
    "is_readable": true
  },
  "message": "Backup details retrieved successfully"
}
```

#### Delete Backup
```http
DELETE /api/backups/{backup_id}
Authorization: Bearer {JWT_TOKEN}
```

#### Restore Backup
```http
POST /api/backups/{backup_id}/restore
Authorization: Bearer {JWT_TOKEN}
Content-Type: application/json

{
  "type": "all",
  "connection": "mysql",
  "force": false
}
```

#### Verify Backup
```http
POST /api/backups/{backup_id}/verify
Authorization: Bearer {JWT_TOKEN}
Content-Type: application/json

{
  "type": "all"
}
```

#### Get Backup Status
```http
GET /api/backups/status
Authorization: Bearer {JWT_TOKEN}
```

**Response**:
```json
{
  "success": true,
  "data": {
    "system_status": "operational",
    "status": "operational",
    "issues": [],
    "backup_locations": {
      "database": "/storage/backups/database",
      "filesystem": "/storage/backups/filesystem",
      "config": "/storage/backups/config",
      "comprehensive": "/storage/backups"
    },
    "statistics": {
      "database_backups": 7,
      "filesystem_backups": 7,
      "config_backups": 5,
      "comprehensive_backups": 5
    },
    "latest_backups": {...},
    "disk_space": {
      "free": "50.00 GB",
      "total": "100.00 GB",
      "usage_percent": 50.0
    }
  },
  "message": "Backup status retrieved successfully"
}
```

#### Clean Old Backups
```http
POST /api/backups/clean
Authorization: Bearer {JWT_TOKEN}
Content-Type: application/json

{
  "type": "all",
  "keep": 5
}
```

## Encryption Configuration

Backup files can be encrypted using AES-256-GCM for enhanced security.

### Encryption Settings (.env)
```bash
BACKUP_ENCRYPTION_KEY=your-32-character-encryption-key
BACKUP_ENCRYPTION_ENABLED=true
```

### Generate Encryption Key
```bash
openssl rand -hex 32
```

### Encryption Process

1. Generate secure 32-character key
2. Enable encryption in configuration
3. Backups are automatically encrypted during creation
4. Encrypted files use `.enc` extension
5. Decryption happens automatically during restore
6. Keys are validated to ensure minimum 32 characters

### Security Benefits

- Backups encrypted at rest
- AES-256-GCM provides authenticated encryption
- Protection against unauthorized access to backup files
- Compliance with data protection regulations

## Alerting Configuration

The backup system supports multiple alert channels for monitoring and notifications.

### Email Alerts
```bash
BACKUP_ALERT_EMAIL=admin@example.com
```

### Webhook Alerts
```bash
BACKUP_WEBHOOK_URL=https://monitoring.example.com/webhook
```

### Slack Alerts
```bash
BACKUP_SLACK_WEBHOOK_URL=https://hooks.slack.com/services/YOUR/WEBHOOK
```

### Alert Triggers

Alerts are sent for:
- Backup creation success
- Backup restoration completed
- Backup failures (database, filesystem, configuration)
- Backup verification failures
- Health check failures
- Low disk space warnings

### Alert Payload

Webhook alerts include:
```json
{
  "event": "backup_alert",
  "message": "Backup created successfully",
  "timestamp": "2026-01-13T10:30:00Z",
  "context": {
    "backup_type": "comprehensive",
    "encrypted": true,
    "size_bytes": 10485760
  }
}
```

## Backup Storage Locations

By default, backups are stored in:
- `storage/backups/database/` - Database backups
- `storage/backups/filesystem/` - File system backups
- `storage/backups/config/` - Configuration backups
- `storage/backups/` - Comprehensive backups

## Backup Schedule

To automate backups, add the following to your crontab or use the Laravel scheduler:

### Daily Database Backup
```bash
# Run daily at 2 AM
0 2 * * * cd /path/to/project && php artisan backup:database --compress --clean-old
```

### Weekly Full Backup
```bash
# Run weekly on Sunday at 3 AM
0 3 * * 0 cd /path/to/project && php artisan backup:all --compress --clean-old
```

### Daily Verification
```bash
# Run daily at 4 AM to verify previous day's backup
0 4 * * * cd /path/to/project && php artisan backup:verify --type=all
```

## Disaster Recovery Procedures

### In Case of Data Loss

1. **Assess the Situation**
   - Determine the extent of data loss
   - Identify the last known good backup

2. **Prepare for Restoration**
   - Ensure you have the backup file
   - Stop the application to prevent data corruption
   - Create a backup of current state if possible

3. **Perform Restoration**
   ```bash
   php artisan restore:backup /path/to/backup.tar.gz --type=all --force
   ```

4. **Verify Restoration**
   ```bash
   php artisan backup:verify /path/to/backup.tar.gz
   ```

5. **Restart Services**
   - Start the application
   - Monitor for any issues

### In Case of System Failure

1. **Deploy to New Environment**
   - Set up a new server/environment
   - Install the application
   - Configure the environment

2. **Restore Data**
   - Restore database, files, and configurations
   - Verify all components are working

3. **Update DNS/Load Balancer**
   - Point traffic to the new environment
   - Monitor for performance and errors

## Security Considerations

- **Encryption**: Consider encrypting backup files containing sensitive data
- **Access Control**: Restrict access to backup files to authorized personnel only
- **Retention Policy**: Implement appropriate retention policies based on compliance requirements
- **Offsite Storage**: Store copies of critical backups in geographically separate locations

## Compliance and Audit Trail

- All backup operations are logged
- Verification results are recorded
- Restoration procedures are documented
- Regular testing of backup and recovery procedures is performed

## Testing Procedures

Regular testing of backup and recovery procedures should be performed:

1. **Monthly**: Perform a full restoration to a test environment
2. **Quarterly**: Test disaster recovery procedures with full system restoration
3. **Annually**: Perform comprehensive business continuity testing

## Business Continuity Planning

### Recovery Time Objectives (RTO)
- Critical systems: 4 hours
- Non-critical systems: 24 hours

### Recovery Point Objectives (RPO)
- Database: 1 hour (with hourly backups)
- Files: 24 hours (with daily backups)

### Critical System Prioritization
1. Authentication and user management
2. Core academic functionality
3. Financial and administrative systems
4. Reporting and analytics

## Implementation Status

The backup system is fully implemented with the following components:

- ✅ Automated backup commands for database, filesystem, and configuration
- ✅ Comprehensive backup command for all-in-one backups
- ✅ Restore functionality for all backup types
- ✅ Verification tools to check backup integrity
- ✅ Monitoring and alerting system
- ✅ Backup retention and cleanup
- ✅ Configuration file for backup settings
- ✅ Backup service class for programmatic access
- ✅ Documentation and procedures

## Recovery Time Objectives

Based on the implemented system:
- **RTO (Recovery Time Objective)**: 4 hours for critical systems
- **RPO (Recovery Point Objective)**: 1 hour for databases, 24 hours for files

## Monitoring and Alerting

The system includes comprehensive monitoring with the backup:monitor command that can:
- Check backup status across all systems
- Send alerts via email when backups fail
- Send alerts via webhook for integration with monitoring systems
- Generate compliance reports

## Business Continuity Planning

The system supports business continuity through:
- Regular backup scheduling
- Point-in-time recovery capabilities
- Offsite backup storage options
- Automated verification of backup integrity
- Comprehensive disaster recovery procedures