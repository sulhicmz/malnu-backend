# Security Policy

## Supported Versions

We take security issues seriously and appreciate your efforts to responsibly disclose them. Only the latest version of our application receives security updates. As this is a school management system, we recommend keeping your installation up to date with the latest version.

| Version | Supported          |
| ------- | ------------------ |
| Latest  | ✅ Supported       |
| Older   | ❌ Not supported   |

## Reporting a Vulnerability

If you discover a security vulnerability, please report it to us as follows:

### Email Reporting
Send your security report to: **security@malnu-kananga.org** (replace with actual security contact)

Please include the following details in your report:
- A clear description of the vulnerability
- Steps to reproduce the issue
- Potential impact of the vulnerability
- Any proof-of-concept code (if applicable and safe to share)

### What We Consider Security Issues

- Authentication bypass
- Authorization failures
- SQL injection
- Cross-site scripting (XSS)
- Cross-site request forgery (CSRF)
- Insecure direct object references
- Security misconfigurations
- Sensitive data exposure
- Any other vulnerability that could compromise user data or system integrity

### What We Don't Consider Security Issues

- Issues related to local development environments
- Social engineering attacks
- Physical security issues
- Attacks requiring physical access to user devices
- Outdated dependencies that don't directly impact security

## Response Timeline

- **Acknowledgment**: We will acknowledge receipt of your vulnerability report within 48 hours.
- **Initial Assessment**: Within 5 business days, we will provide an initial assessment of the vulnerability.
- **Resolution Timeline**: We will work to fix accepted vulnerabilities within 30 days and provide updates on progress.

## Security Best Practices for Users

### For Administrators
- Keep the application updated to the latest version
- Use strong, unique passwords for all accounts
- Regularly review user permissions and access rights
- Enable two-factor authentication where available
- Regularly backup your data
- Monitor access logs for suspicious activity

### For Developers
- Follow secure coding practices
- Validate and sanitize all user inputs
- Use parameterized queries to prevent SQL injection
- Implement proper authentication and authorization
- Keep dependencies updated
- Regularly review and test security controls

## Security Features

### Built-in Security Measures
- JWT-based authentication with proper token management
- Role-based access control (RBAC)
- Input validation and sanitization
- Password hashing using industry-standard algorithms
- Secure session management
- API rate limiting to prevent abuse

### Data Protection
- Encryption at rest for sensitive data
- HTTPS enforcement for all communications
- Regular security audits of the codebase
- Secure configuration management

## Security Updates

Security updates are released as part of our regular release cycle. We will announce significant security fixes in our release notes. For critical vulnerabilities, we may issue emergency releases.

## Third-Party Dependencies

We regularly audit our dependencies for known vulnerabilities using automated tools. We maintain an up-to-date list of dependencies in our `composer.json` and `package.json` files.

## Bug Bounty Program

Currently, we do not have a formal bug bounty program. However, we greatly appreciate responsible disclosure of security issues and will publicly acknowledge your contribution (unless you prefer to remain anonymous).

## Questions?

If you have any questions about this security policy, please contact us at: **security@malnu-kananga.org** (replace with actual contact)

---

*This policy is effective as of November 20, 2025 and may be updated periodically to reflect changes in our application or security practices.*