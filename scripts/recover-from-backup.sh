#!/bin/bash

# Standalone Recovery Script for Malnu Backend
# This script allows recovery without requiring the full artisan environment to be running
# Usage: ./scripts/recover-from-backup.sh <backup-file> [options]

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Default values
BACKUP_FILE=""
RESTORE_TYPE="all"
DB_CONNECTION="mysql"
FORCE=false
DB_HOST="localhost"
DB_PORT="3306"
DB_NAME="malnu"
DB_USER="malnu_user"
DB_PASS=""

# Function to display usage
usage() {
    cat << EOF
Usage: $0 <backup-file> [options]

Standalone recovery script for Malnu Backend database and file system.

Arguments:
  backup-file              Path to the backup file (.tar.gz)

Options:
  -t, --type TYPE         Type of restore: database, filesystem, config, or all (default: all)
  -c, --connection CONN   Database connection: mysql or postgres (default: mysql)
  -f, --force             Force restore without confirmation
  -h, --host HOST         Database host (default: localhost)
  -p, --port PORT         Database port (default: 3306 for mysql, 5432 for postgres)
  -d, --database NAME     Database name (default: malnu)
  -u, --user USER         Database user (default: malnu_user)
  -P, --password PASS     Database password (prompt if not provided)
  --help                   Display this help message

Examples:
  # Restore database only
  $0 /backups/backup_mysql_2024-01-15.sql.tar.gz -t database

  # Restore all with custom database settings
  $0 /backups/backup_comprehensive.tar.gz -h db.example.com -u dbuser -P secret

  # Force restore without confirmation
  $0 /backups/backup_mysql_2024-01-15.sql.tar.gz --force

EOF
    exit 1
}

# Function to print colored messages
print_info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Function to prompt for password
prompt_password() {
    if [ -z "$DB_PASS" ]; then
        read -sp "Enter database password: " DB_PASS
        echo
    fi
}

# Parse command line arguments
while [[ $# -gt 0 ]]; do
    case $1 in
        -t|--type)
            RESTORE_TYPE="$2"
            shift 2
            ;;
        -c|--connection)
            DB_CONNECTION="$2"
            shift 2
            ;;
        -f|--force)
            FORCE=true
            shift
            ;;
        -h|--host)
            DB_HOST="$2"
            shift 2
            ;;
        -p|--port)
            DB_PORT="$2"
            shift 2
            ;;
        -d|--database)
            DB_NAME="$2"
            shift 2
            ;;
        -u|--user)
            DB_USER="$2"
            shift 2
            ;;
        -P|--password)
            DB_PASS="$2"
            shift 2
            ;;
        --help)
            usage
            ;;
        *)
            if [ -z "$BACKUP_FILE" ]; then
                BACKUP_FILE="$1"
            else
                print_error "Unknown option: $1"
                usage
            fi
            shift
            ;;
    esac
done

# Validate required arguments
if [ -z "$BACKUP_FILE" ]; then
    print_error "Backup file path is required"
    usage
fi

# Check if backup file exists
if [ ! -f "$BACKUP_FILE" ]; then
    print_error "Backup file not found: $BACKUP_FILE"
    exit 1
fi

# Check if backup file is valid tar.gz
if ! tar -tzf "$BACKUP_FILE" >/dev/null 2>&1; then
    print_error "Invalid backup file format. Expected .tar.gz file"
    exit 1
fi

# Confirmation
if [ "$FORCE" = false ]; then
    print_warning "This will overwrite existing data!"
    read -p "Are you sure you want to proceed? (y/N): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        print_info "Restore operation cancelled"
        exit 0
    fi
fi

# Create temporary directory for extraction
TEMP_DIR=$(mktemp -d)
trap "rm -rf $TEMP_DIR" EXIT

print_info "Starting restore operation..."
print_info "Backup file: $BACKUP_FILE"
print_info "Restore type: $RESTORE_TYPE"
print_info "Temporary directory: $TEMP_DIR"

# Extract backup file
print_info "Extracting backup file..."
if ! tar -xzf "$BACKUP_FILE" -C "$TEMP_DIR"; then
    print_error "Failed to extract backup file"
    exit 1
fi
print_success "Backup extracted successfully"

# Restore database if requested
if [ "$RESTORE_TYPE" = "database" ] || [ "$RESTORE_TYPE" = "all" ]; then
    print_info "Restoring database..."
    
    # Find database backup file
    DB_BACKUP_FILE=""
    
    for pattern in "db_backup_*.sql" "db_backup_*.db" "backup_*.sql" "backup_*.db"; do
        DB_BACKUP_FILE=$(find "$TEMP_DIR" -name "$pattern" -type f 2>/dev/null | head -1)
        if [ -n "$DB_BACKUP_FILE" ]; then
            break
        fi
    done
    
    # Check in subdirectories
    if [ -z "$DB_BACKUP_FILE" ]; then
        DB_BACKUP_FILE=$(find "$TEMP_DIR" -type f \( -name "*.sql" -o -name "*.db" \) 2>/dev/null | head -1)
    fi
    
    if [ -z "$DB_BACKUP_FILE" ]; then
        print_warning "No database backup file found, skipping database restore"
    else
        print_info "Found database backup: $DB_BACKUP_FILE"
        
        # Prompt for password if needed
        prompt_password
        
        # Restore based on connection type
        if [ "$DB_CONNECTION" = "mysql" ]; then
            print_info "Restoring MySQL database..."
            
            if ! command -v mysql &> /dev/null; then
                print_error "mysql command not found. Please install MySQL client"
                exit 1
            fi
            
            if ! mysql --host="$DB_HOST" --port="$DB_PORT" --user="$DB_USER" --password="$DB_PASS" "$DB_NAME" < "$DB_BACKUP_FILE"; then
                print_error "Failed to restore database"
                exit 1
            fi
            
        elif [ "$DB_CONNECTION" = "postgres" ]; then
            print_info "Restoring PostgreSQL database..."
            
            if ! command -v psql &> /dev/null; then
                print_error "psql command not found. Please install PostgreSQL client"
                exit 1
            fi
            
            # Set environment variable for password (more secure than command line)
            export PGPASSWORD="$DB_PASS"
            
            if ! psql --host="$DB_HOST" --port="$DB_PORT" --username="$DB_USER" --dbname="$DB_NAME" < "$DB_BACKUP_FILE"; then
                print_error "Failed to restore database"
                exit 1
            fi
            
            unset PGPASSWORD
        else
            print_error "Unsupported database connection: $DB_CONNECTION"
            exit 1
        fi
        
        print_success "Database restored successfully"
    fi
fi

# Restore file system if requested
if [ "$RESTORE_TYPE" = "filesystem" ] || [ "$RESTORE_TYPE" = "all" ]; then
    print_info "Restoring file system..."
    
    # Determine source directory
    FS_SRC=""
    if [ -d "$TEMP_DIR/filesystem" ]; then
        FS_SRC="$TEMP_DIR/filesystem"
    elif [ -d "$TEMP_DIR/app" ] || [ -d "$TEMP_DIR/config" ]; then
        FS_SRC="$TEMP_DIR"
    else
        print_warning "No filesystem backup found, skipping filesystem restore"
    fi
    
    if [ -n "$FS_SRC" ]; then
        # Get script directory
        SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
        PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"
        
        # Define directories to restore
        RESTORE_DIRS=("app" "config" "database" "resources" "tests")
        
        for dir in "${RESTORE_DIRS[@]}"; do
            if [ -d "$FS_SRC/$dir" ]; then
                print_info "Restoring $dir/"
                
                # Remove existing directory
                if [ -d "$PROJECT_ROOT/$dir" ]; then
                    rm -rf "$PROJECT_ROOT/$dir"
                fi
                
                # Copy backup directory
                cp -r "$FS_SRC/$dir" "$PROJECT_ROOT/$dir"
                
                print_success "Restored $dir/"
            fi
        done
    fi
fi

# Restore configuration if requested
if [ "$RESTORE_TYPE" = "config" ] || [ "$RESTORE_TYPE" = "all" ]; then
    print_info "Restoring configuration..."
    
    CONFIG_SRC=""
    if [ -d "$TEMP_DIR/config" ]; then
        CONFIG_SRC="$TEMP_DIR/config"
    elif [ -d "$TEMP_DIR/configuration" ]; then
        CONFIG_SRC="$TEMP_DIR/configuration"
    elif [ -d "$TEMP_DIR/cfg" ]; then
        CONFIG_SRC="$TEMP_DIR/cfg"
    fi
    
    if [ -n "$CONFIG_SRC" ]; then
        # Get script directory
        SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
        PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"
        
        # Restore .env file if exists
        if [ -f "$CONFIG_SRC/.env" ]; then
            print_info "Restoring .env file"
            cp "$CONFIG_SRC/.env" "$PROJECT_ROOT/.env"
            print_success "Restored .env file"
        fi
        
        # Restore config directory
        if [ -d "$CONFIG_SRC/config" ]; then
            print_info "Restoring config/ directory"
            if [ -d "$PROJECT_ROOT/config" ]; then
                rm -rf "$PROJECT_ROOT/config"
            fi
            cp -r "$CONFIG_SRC/config" "$PROJECT_ROOT/config"
            print_success "Restored config/ directory"
        fi
    else
        print_warning "No configuration backup found, skipping config restore"
    fi
fi

print_success "Restore operation completed successfully!"
print_info "Please verify your application is working correctly"
print_info "Run: php artisan migrate (if needed)"
print_info "Run: php artisan cache:clear"