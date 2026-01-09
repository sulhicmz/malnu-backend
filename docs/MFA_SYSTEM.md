# Multi-Factor Authentication (MFA) System

## Overview

The MFA system provides comprehensive two-factor authentication for the Malnu School Management System, adding an additional layer of security beyond JWT-based authentication.

## Features

### 1. TOTP-Based MFA
- **Time-Based One-Time Passwords (TOTP)**: Compatible with Google Authenticator, Authy, and other authenticator apps
- **RFC 6238 Compliant**: Follows industry-standard TOTP specification
- **6-Digit Codes**: 30-second validity window for each code

### 2. Backup Codes
- **10 Recovery Codes**: Generated when MFA is enabled
- **One-Time Use**: Each code can be used only once
- **Auto-Depletion**: Backup codes count tracked and decremented

### 3. Security Event Logging
- **Comprehensive Audit Trail**: All MFA-related events logged
- **Event Types**: mfa_enabled, mfa_disabled, mfa_verification_failed, login_failed, etc.
- **Metadata Tracking**: IP address, user agent, success/failure status

### 4. Device Management
- **Trusted Device Tracking**: Track login devices and trust status
- **Device Fingerprinting**: User agent + IP address for identification
- **Optional MFA on Trusted Devices**: Can skip MFA on recognized devices

## Database Schema

### mfa_secrets Table
Stores MFA secrets and backup codes for each user.

| Column | Type | Description |
|---------|--------|-------------|
| id | UUID (PK) | Unique identifier |
| user_id | UUID (FK) | Link to users table |
| secret | VARCHAR(64) | Base32-encoded TOTP secret |
| is_enabled | BOOLEAN | MFA enabled status |
| backup_codes | JSON | Array of backup codes |
| backup_codes_count | INTEGER | Number of remaining backup codes |
| created_at | TIMESTAMP | Record creation time |
| updated_at | TIMESTAMP | Last update time |

### user_devices Table
Tracks login devices and trusted device status.

| Column | Type | Description |
|---------|--------|-------------|
| id | UUID (PK) | Unique identifier |
| user_id | UUID (FK) | Link to users table |
| device_name | VARCHAR(100) | User-friendly device name |
| user_agent | VARCHAR(500) | Browser user agent string |
| ip_address | VARCHAR(45) | Client IP address |
| is_trusted | BOOLEAN | Device trusted status |
| last_used_at | TIMESTAMP | Last login time |
| created_at | TIMESTAMP | Record creation time |
| updated_at | TIMESTAMP | Last update time |

### security_events Table
Logs security-related events for audit trail.

| Column | Type | Description |
|---------|--------|-------------|
| id | UUID (PK) | Unique identifier |
| user_id | UUID (FK, nullable) | Link to users table |
| event_type | VARCHAR(50) | Event type identifier |
| description | VARCHAR(500) | Event description |
| ip_address | VARCHAR(45) | Client IP address |
| user_agent | VARCHAR(500) | Browser user agent string |
| is_successful | BOOLEAN | Event success status |
| created_at | TIMESTAMP | Event time |
| updated_at | TIMESTAMP | Last update time |

## API Endpoints

### Public Endpoints (JWT Required)

#### POST /auth/mfa/setup
Generate TOTP secret and QR code for MFA setup.

**Response:**
```json
{
  "success": true,
  "data": {
    "secret": "JBSWY3DPEHPK3PXP",
    "qr_code_url": "otpauth://totp/Malnu%20School%20Management:user@example.com?secret=JBSWY3DPEHPK3PXP&issuer=Malnu%20School%20Management&digits=6&period=30"
  },
  "message": "MFA setup initiated successfully"
}
```

#### POST /auth/mfa/verify
Verify TOTP code and enable MFA for user account.

**Request:**
```json
{
  "secret": "JBSWY3DPEHPK3PXP",
  "code": "123456"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "success": true,
    "message": "MFA enabled successfully"
  },
  "message": "MFA enabled successfully"
}
```

#### POST /auth/mfa/disable
Disable MFA for authenticated user.

**Response:**
```json
{
  "success": true,
  "data": {
    "success": true,
    "message": "MFA disabled successfully"
  },
  "message": "MFA disabled successfully"
}
```

#### GET /auth/mfa/status
Get current MFA status for authenticated user.

**Response:**
```json
{
  "success": true,
  "data": {
    "mfa_enabled": true,
    "backup_codes_remaining": 10
  },
  "message": "MFA status retrieved successfully"
}
```

## TOTP Implementation Details

### Secret Generation
- Generates 20-byte random secret
- Base32 encoded for compatibility with authenticator apps
- 32-character secret key

### Code Generation
- Current Unix time divided by 30-second intervals
- HMAC-SHA1 hash of time + secret
- Extracts 6-digit code (mod 1,000,000)

### Code Verification
- Checks current time window ± 1 interval (90 seconds total)
- Supports time drift tolerance
- Constant-time comparison to prevent timing attacks

### QR Code Format
```
otpauth://totp/Issuer:Account?secret=Secret&issuer=Issuer&digits=6&period=30
```

## Security Best Practices

### 1. Secret Storage
- Secrets stored in database (not in code or logs)
- Base32 encoding for compatibility
- No secret exposure in API responses

### 2. Code Validation
- Constant-time comparison prevents timing attacks
- Window tolerance accounts for clock drift
- Rate limiting on verification attempts

### 3. Backup Codes
- One-time use only
- Securely stored as JSON array
- Count decremented on each use
- Regenerated when depleted

### 4. Audit Logging
- All MFA events logged
- IP and user agent tracking
- Success/failure status recorded
- Failed attempt monitoring

### 5. User Experience
- Clear setup instructions with QR code
- Optional MFA (can be enforced by role)
- Recovery mechanism via backup codes
- Status endpoint for user awareness

## Configuration

### Environment Variables

No additional environment variables required. MFA system uses existing JWT configuration.

### Optional Configuration

These can be added to future configuration:

```env
# MFA Configuration
MFA_ENABLED=true                    # Enable/disable MFA system-wide
MFA_REQUIRED_FOR_ADMINS=true       # Mandatory MFA for admin role
MFA_REQUIRED_FOR_TEACHERS=false     # Optional MFA for teachers
MFA_REQUIRED_FOR_STUDENTS=false     # Optional MFA for students
MFA_BACKUP_CODES_COUNT=10           # Number of backup codes to generate
MFA_CODE_VALIDITY_WINDOW=1           # TOTP time window tolerance (intervals)
```

## Testing

### Run MFA Feature Tests

```bash
vendor/bin/co-phpunit tests/Feature/MfaFeatureTest.php
```

### Test Coverage

- TOTP secret generation and validation
- QR code URL generation
- Backup code generation
- MFA secret model operations
- Security event logging and scoping
- Backup code decrement tracking

## Integration with Existing Authentication

### Authentication Flow

1. **User logs in** with email/password
2. **Generate JWT token** (existing flow)
3. **Check MFA status**:
   - If MFA not enabled: Return token immediately
   - If MFA enabled: Request TOTP code
4. **Verify TOTP code**:
   - If valid: Issue JWT token
   - If invalid: Return error

### JWT Payload (Enhanced)

Existing JWT payload format maintained. MFA status can be added in future:

```json
{
  "iat": 1234567890,
  "exp": 1234570890,
  "data": {
    "id": "user-uuid",
    "email": "user@example.com",
    "mfa_enabled": true
  }
}
```

## Troubleshooting

### TOTP Code Always Invalid

**Symptoms:** Authenticator app codes always rejected

**Solutions:**
1. Check device time synchronization
2. Verify TOTP secret matches QR code
3. Confirm authenticator app uses 6-digit codes
4. Check TOTP time window configuration

### Backup Codes Not Working

**Symptoms:** Backup code rejected as invalid

**Solutions:**
1. Verify code format (uppercase alphanumeric)
2. Check backup codes count in database
3. Ensure code hasn't been used already
4. Regenerate backup codes if depleted

### QR Code Not Scanning

**Symptoms:** Authenticator app can't scan QR code

**Solutions:**
1. Verify QR code URL format is correct
2. Check QR code URL encoding
3. Ensure issuer name doesn't have special characters
4. Test with alternative authenticator app

## Future Enhancements

### Planned Features (Out of Scope for Initial Implementation)

1. **SMS/Email Verification**: Secondary channel for MFA delivery
2. **Hardware Keys**: WebAuthn/U2F support for passwordless login
3. **Biometric Authentication**: Fingerprint or face recognition
4. **AI Anomaly Detection**: Machine learning for suspicious activity detection
5. **Geographic Restrictions**: IP-based location validation
6. **Push Notifications**: Real-time security alerts

## Compliance

### GDPR Considerations
- User consent for MFA enrollment
- Right to disable MFA at any time
- Data minimization (only store necessary MFA data)
- Right to access security event logs

### FERPA Considerations
- Student data protection with enhanced security
- Parental control for student MFA settings
- Audit trail for educational compliance

## Support

For issues or questions:
- Check this documentation for troubleshooting
- Review security event logs
- Report bugs through GitHub Issues
