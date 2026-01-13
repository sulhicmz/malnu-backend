# Security Policy

## Supported Versions

The Malnu Backend team maintains security updates for the following versions:

| Version | Supported |
|---------|-----------|
| 0.1.x   | ✅ Current |
| < 0.1   | ❌ Unsupported |

## Reporting a Vulnerability

**Please do NOT report security vulnerabilities through GitHub issues.** Instead, use one of the following methods:

### Private Disclosure (Preferred)

To report a security vulnerability privately, please send an email to:

**Email:** `security@example.com`

Please include the following information in your report:
- Description of the vulnerability
- Steps to reproduce the issue
- Potential impact
- Suggested fix (if known)

### What to Expect

1. **Acknowledgment**: We will acknowledge receipt of your vulnerability report within 48 hours
2. **Investigation**: We will investigate and validate the vulnerability within 7 business days
3. **Resolution**: We will work on a fix and provide an estimated timeline
4. **Disclosure**: We will coordinate disclosure with you, typically after the fix is deployed

## Responsible Disclosure Policy

We follow responsible disclosure practices to protect our users while acknowledging the valuable contributions of security researchers:

- **No Legal Action**: We will not pursue legal action against security researchers who follow this policy
- **Credit**: With your permission, we will credit you in our security advisories
- **Timeline**: We aim to disclose vulnerabilities within 90 days of reporting, depending on severity and complexity

## Security Best Practices for Contributors

When contributing to Malnu Backend, please follow these security guidelines:

### Never Commit Sensitive Data
- **Credentials**: API keys, passwords, tokens, certificates
- **Personal Data**: Real emails, phone numbers, addresses
- **Secrets**: JWT secrets, encryption keys

Use `.env.example` with **empty values** and clear warning comments. Never use placeholder values like `your-secret-key-here`, `change-me`, or `secret` in production.

**Example:**
```env
# Generate a secure JWT secret using: openssl rand -hex 32
# WARNING: NEVER use placeholder values in production!
# Always generate a unique, random secret for each environment.
JWT_SECRET=
```

### Input Validation
- Always validate and sanitize user input
- Use HyperVel's built-in validation features
- Implement Form Request validation classes
- See [Issue #349](https://github.com/sulhicmz/malnu-backend/issues/349) for validation improvements

### Authentication & Authorization
- Ensure all protected endpoints use `JWTMiddleware`
- Implement proper role-based access control (RBAC)
- See [Issue #359](https://github.com/sulhicmz/malnu-backend/issues/359) for RBAC implementation
- Never bypass security middleware

### CSRF Protection
- Ensure state-changing operations (POST/PUT/DELETE) are protected
- See [Issue #358](https://github.com/sulhicmz/malnu-backend/issues/358) for CSRF middleware fixes

### Dependencies
- Regularly update dependencies
- Run `composer audit` and `npm audit` regularly
- Address security vulnerabilities promptly

### Code Review
- All code changes require review
- Security-sensitive changes require additional scrutiny
- See `CODEOWNERS` for approval requirements

## Current Security Status

For comprehensive security analysis, see:
- [Security Analysis Report](docs/SECURITY_ANALYSIS.md) - Complete security assessment
- [Application Status](docs/APPLICATION_STATUS.md) - Current security posture

### Known Critical Issues

The following critical security issues are being addressed:

- 🔴 **RBAC Not Implemented** - RoleMiddleware bypasses authorization ([#359](https://github.com/sulhicmz/malnu-backend/issues/359))
- 🔴 **CSRF Protection Broken** - Middleware extends non-existent class ([#358](https://github.com/sulhicmz/malnu-backend/issues/358))
- 🔴 **MD5 Hashing** - Weak hashing in token blacklist ([#347](https://github.com/sulhicmz/malnu-backend/issues/347))
- 🔴 **Weak Password Validation** - No complexity requirements ([#351](https://github.com/sulhicmz/malnu-backend/issues/351))

## Security Features Implemented

### ✅ Security Headers
- Content Security Policy (CSP)
- HTTP Strict Transport Security (HSTS)
- X-Frame-Options
- X-Content-Type-Options
- Referrer Policy

### ✅ Framework Security
- Built-in CSRF protection (when middleware is fixed)
- SQL injection prevention (parameterized queries)
- XSS protection
- Secure password hashing (bcrypt)

### ✅ Rate Limiting
- API rate limiting middleware
- Redis-backed rate limiting
- Configurable thresholds

### ✅ JWT Authentication
- Token-based authentication
- Token blacklisting
- Refresh token support

## Security Monitoring

We are implementing automated security monitoring:
- Vulnerability scanning
- Security event logging
- Intrusion detection
- Alerting system

## Contact

### Security Team
- **Security Lead**: [To be assigned]
- **Development Team**: Repository maintainers
- **Security Advisories**: [GitHub Security Advisories](https://github.com/sulhicmz/malnu-backend/security/advisories)

### External Resources
- **Vulnerability Database**: [CVE](https://cve.mitre.org/), [NVD](https://nvd.nist.gov/)
- **Security Communities**: [OWASP](https://owasp.org/), [SANS](https://www.sans.org/)

---

## Security Checklist

### Before Opening a PR
- [ ] No secrets or credentials committed
- [ ] Input validation implemented
- [ ] Authentication/authorization verified
- [ ] Dependencies audited
- [ ] Security tests passing

### Before Merging to Production
- [ ] All security vulnerabilities patched
- [ ] Security tests passing
- [ ] Security headers configured
- [ ] Monitoring enabled
- [ ] Backup procedures tested

---

**Last Updated**: January 10, 2026

For questions or concerns about this security policy, please open an issue with the `security` label.
