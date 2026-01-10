# Security Policy

## Supported Versions

This project currently supports the following versions:

| Version | Supported          |
|---------|--------------------|
| main    | ✅ Actively supported |

## Reporting a Vulnerability

If you discover a security vulnerability, please **DO NOT** open a public issue. Instead, send a report to our private security contact.

### Private Disclosure Process

To report a security vulnerability privately:

1. **Email**: security@sulhicmz
2. **GitHub Security Advisory**: Use GitHub's [private vulnerability reporting](https://github.com/sulhicmz/malnu-backend/security/advisories/new) feature
3. **Subject**: `Security Vulnerability Report - [Brief Description]`

### What to Include

Please include the following information in your report:

- **Description**: Detailed description of the vulnerability
- **Impact**: Potential impact of the vulnerability
- **Steps to Reproduce**: Clear steps to reproduce the issue
- **Affected Versions**: Which versions are affected
- **Suggested Fix**: If known, any suggestions for fixing the issue
- **Additional Context**: Any other relevant information

### Response Timeline

- **Initial Response**: Within 48 hours of receiving the report
- **Assessment**: We will assess the vulnerability and determine severity
- **Fix Timeline**: We will aim to fix critical issues within 7 days, high severity within 14 days
- **Disclosure**: We will coordinate with you on public disclosure timing

## Responsible Disclosure Policy

We follow a responsible disclosure policy:

1. **Private Disclosure**: Security researchers are expected to report vulnerabilities privately before public disclosure
2. **Time for Fix**: Allow us reasonable time to investigate and fix the vulnerability (typically 90 days)
3. **Coordinated Disclosure**: We will work with you to determine the best time for public disclosure
4. **Credit**: We will credit you in the security advisory for responsibly reported vulnerabilities
5. **No Legal Action**: We will not take legal action against security researchers who follow this policy

### Safe Harbor

If you follow this policy, we will:
- Not pursue legal action against you
- Work with you to understand and fix the issue
- Credit you for the discovery

## Security Best Practices for Contributors

### Code Security

- **Input Validation**: Always validate and sanitize user inputs
- **SQL Injection**: Use parameterized queries or ORM methods
- **XSS Prevention**: Use framework-provided escaping functions
- **Authentication**: Follow established authentication patterns
- **Authorization**: Implement proper role-based access control (RBAC)
- **Sensitive Data**: Never commit credentials, API keys, or secrets

### Dependency Management

- **Regular Updates**: Keep dependencies up to date
- **Security Audits**: Run `composer audit` regularly
- **Vulnerability Scanning**: Use automated tools like `npm audit` for frontend dependencies

### Testing

- **Security Testing**: Include security tests in your pull requests
- **Penetration Testing**: Perform security testing for critical features
- **Code Review**: All security-sensitive changes require thorough code review

### Development

- **Environment Variables**: Use `.env` files for sensitive configuration
- **Secure Defaults**: Use secure default values in code
- **Logging**: Avoid logging sensitive information
- **Error Messages**: Don't expose sensitive information in error messages

## Security Features

This application includes the following security features:

- **Authentication**: JWT-based authentication with token refresh
- **Authorization**: Role-Based Access Control (RBAC)
- **CSRF Protection**: CSRF tokens for state-changing operations
- **Input Validation**: Comprehensive validation using Form Request classes
- **SQL Injection Prevention**: Parameterized queries and ORM
- **XSS Protection**: Content Security Policy and output escaping
- **Security Headers**: HTTP security headers (CSP, HSTS, X-Frame-Options, etc.)
- **Password Security**: Password hashing and complexity validation

## Known Security Issues

For a comprehensive analysis of current security posture, please see:
- **[Security Analysis Report](docs/SECURITY_ANALYSIS.md)** - Detailed security assessment

## Security Resources

- **OWASP Top 10**: https://owasp.org/www-project-top-ten/
- **PHP Security**: https://www.php.net/manual/en/security.php
- **Hyperf Security**: https://hyperf.wiki/3.0/en/security/
- **GitHub Security**: https://docs.github.com/en/code-security

## Security Team

For security-related questions or concerns:

- **Repository Maintainer**: [@sulhicmz](https://github.com/sulhicmz)
- **Security Email**: security@sulhicmz

## Receiving Security Notifications

To receive security notifications:

1. Watch the repository on GitHub
2. Enable "Custom" → "Security alerts" in your watch settings
3. Monitor the [Security Advisories](https://github.com/sulhicmz/malnu-backend/security/advisories) page

## Security Updates

We will:

- Publish security advisories for all vulnerabilities
- Provide clear upgrade instructions for security fixes
- Announce security updates via repository releases
- Update this security policy as needed

---

**Last Updated**: January 10, 2026
