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
php artisan backup:monitor --last-hours=24 --alert-email=admin@example.com --webhook-url=https://webhook.example.com
```

Options:
- `--last-hours`: Check backups from last N hours (default: 24)
- `--alert-email`: Email address for alerts
- `--webhook-url`: Webhook URL for alerts

## Backup Encryption

### Overview

The backup system supports AES-256-GCM encryption for backup files at rest. This provides military-grade protection for sensitive data in backup archives.

### Enabling Encryption

1. Generate a secure encryption key:
   ```bash
   openssl rand -hex 32
   ```

2. Add to `.env` file:
   ```bash
   BACKUP_ENCRYPTION_KEY=your-32-character-or-more-encryption-key
   BACKUP_ENCRYPTION_ENABLED=true
   ```

3. Restart the application to apply changes.

### Encryption Behavior

- **Automatic**: When encryption is enabled, all backups are automatically encrypted
- **Transparent**: Encrypted backups are automatically decrypted during restore operations
- **File Naming**: Encrypted files have `.encrypted` extension appended
- **Key Requirements**: Encryption key must be at least 32 characters

### Using Encryption

Create encrypted backup:
```bash
php artisan backup:all --encrypt
```

Restore encrypted backup:
```bash
php artisan restore:backup /path/to/backup.tar.gz.encrypted
```

Note: The system automatically detects encrypted files and decrypts them during restoration.

### Security Considerations

- Store `BACKUP_ENCRYPTION_KEY` in environment variables, never in code
- Rotate encryption keys quarterly for enhanced security
- Never commit encryption keys to version control
- Consider using a secrets management service for production environments

## REST API

The backup system provides a comprehensive REST API for backup management and monitoring.

### Authentication

All backup API endpoints require:
- JWT authentication via `Authorization: Bearer {token}` header
- Super Admin role authorization
- Rate limiting

### Endpoints

#### List Backups

```http
GET /api/backups?type=all
```

Query Parameters:
- `type`: Filter by backup type (database, filesystem, config, all)

Response:
```json
{
  "success": true,
  "data": {
    "timestamp": "2026-01-13T10:30:00Z",
    "backup_locations": {...},
    "statistics": {...},
    "latest_backups": {...}
  }
}
```

#### Get Backup Details

```http
GET /api/backups/{id}
```

Response:
```json
{
  "success": true,
  "data": {
    "path": "/storage/backups/database/db_backup_2026-01-13-10-30-00.sql",
    "size": 5242880,
    "size_formatted": "5.00 MB",
    "modified": "2026-01-13T10:30:00Z",
    "checksum": {
      "md5": "a1b2c3d4e5f6...",
      "sha256": "abc123..."
    }
  }
}
```

#### Create Backup

```http
POST /api/backups
Content-Type: application/json
```

Request Body:
```json
{
  "type": "all",
  "encrypt": true
}
```

Types: database, filesystem, config, all

#### Restore Backup

```http
POST /api/backups/{id}/restore
Content-Type: application/json
```

Request Body:
```json
{
  "type": "all"
}
```

#### Verify Backup

```http
POST /api/backups/{id}/verify
Content-Type: application/json
```

Request Body:
```json
{
  "type": "all"
}
```

#### Delete Backup

```http
DELETE /api/backups/{id}
```

#### Get System Status

```http
GET /api/backups/status
```

Response includes disk space, backup directory status, and statistics.

#### Clean Old Backups

```http
POST /api/backups/clean
Content-Type: application/json
```

Request Body:
```json
{
  "type": "all",
  "keep": 5
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