# Security Policy

## Supported Versions

The following versions of the Malnu Kananga School Management System are currently supported with security updates:

| Version | Supported          |
| ------- | ------------------ |
| 1.x.x   | ✅ Supported       |
| < 1.0   | ❌ Not supported   |

## Reporting a Vulnerability

If you discover a security vulnerability in this project, please follow these steps:

### Disclosure Process
1. **Do not** create a public GitHub issue for the vulnerability
2. Contact the maintainers directly via security report
3. Provide detailed information about the vulnerability including:
   - Description of the vulnerability
   - Steps to reproduce
   - Potential impact
   - Suggested remediation (if any)

### Response Timeline
- Initial response: Within 48 hours of your report
- Update on status: Within 1 week of your report
- Final resolution: Typically within 2-4 weeks depending on complexity

### Security Update Policy
- Critical vulnerabilities: Patched and released within 1-2 weeks
- High severity vulnerabilities: Patched within 2-4 weeks
- Medium severity vulnerabilities: Addressed in the next planned release
- Low severity vulnerabilities: Addressed as part of regular maintenance

## Security Best Practices

### For Users
- Keep your application updated to the latest supported version
- Use strong authentication and authorization practices
- Regularly audit user permissions and access controls
- Monitor application logs for suspicious activity
- Use HTTPS in production environments
- Secure your database and environment variables

### For Developers
- Follow secure coding practices
- Validate and sanitize all user inputs
- Use parameterized queries to prevent SQL injection
- Implement proper authentication and authorization
- Keep dependencies updated
- Regular security testing and code reviews

## Security Features

This application includes several built-in security features:
- JWT authentication with configurable token expiration
- Role-based access control (RBAC)
- Input validation and sanitization
- CSRF protection
- Rate limiting capabilities
- Secure session management

## Dependencies and Security

We regularly monitor and update dependencies to address known security vulnerabilities:
- PHP dependencies are checked via Composer audit
- Frontend dependencies are monitored for security issues
- Automated security scanning is performed in CI/CD pipeline

## Versioning and Updates

Security updates are released as part of our regular versioning process:
- Patch versions (x.x.1) contain security fixes and bug fixes
- Minor versions (x.1.x) may contain security improvements
- Major versions (1.x.x) may contain breaking security enhancements

## Questions?

If you have questions about this security policy or the security of this project, please contact the maintainers through the appropriate channels.