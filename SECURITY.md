# Security Policy

## Supported Versions

| Version | Supported          |
| ------- | ------------------ |
| 0.1.x   | :white_check_mark: Yes |
| < 0.1   | :x: No              |

## Reporting a Vulnerability

The Malnu Backend team and community take security vulnerabilities seriously. We appreciate your efforts to responsibly disclose your findings.

### How to Report

**Do NOT** open a public issue for security vulnerabilities.

**Please DO** report security vulnerabilities privately:

1. **Email**: security@sulhicmz.com (if configured)
2. **GitHub Private Vulnerability Reporting**: 
   - Visit https://github.com/sulhicmz/malnu-backend/security/advisories
   - Click "Report a vulnerability"
   - Fill out the form with details

### What to Include

Please include the following information in your report:

- **Description**: A clear description of the vulnerability
- **Impact**: Potential impact of the vulnerability
- **Steps to Reproduce**: Detailed steps to reproduce the issue
- **Affected Versions**: Which versions are affected (if known)
- **Proposed Fix**: Any suggestions for fixing the vulnerability (optional)
- **PoC**: Proof of concept or exploit code (if applicable)

### Response Timeline

We aim to respond to security reports within **48 hours** and provide regular updates on our progress.

- **Initial Response**: Within 48 hours
- **Triage**: Within 5 business days
- **Fix Timeline**: Depending on severity and complexity
- **Public Disclosure**: After fix is deployed and coordinated with reporter

### Vulnerability Severity Classification

We use the [Common Vulnerability Scoring System (CVSS)](https://www.first.org/cvss/) for severity classification:

- **Critical** (9.0-10.0): Immediate action required
- **High** (7.0-8.9): Urgent action required
- **Medium** (4.0-6.9): Action required in next release
- **Low** (0.1-3.9): Action required when feasible

## Responsible Disclosure Policy

We follow a **coordinated disclosure** approach:

1. **Private Report**: Reporter privately discloses vulnerability
2. **Confirmation**: We confirm receipt and validate the vulnerability
3. **Development**: We develop a fix
4. **Testing**: We test the fix thoroughly
5. **Deployment**: We deploy the fix
6. **Disclosure**: We publish security advisory and credit the reporter

### Expectations from Reporters

- **Wait for Fix**: Please do not publicly disclose the vulnerability before we've had a reasonable time to fix it
- **No Exploitation**: Do not exploit the vulnerability beyond proof of concept
- **Limited Access**: Limit knowledge of the vulnerability to necessary parties

### What You Can Expect From Us

- **Acknowledgment**: We will acknowledge receipt of your report within 48 hours
- **Regular Updates**: We'll keep you updated on our progress
- **Credit**: We'll credit you in the security advisory (unless you request anonymity)
- **Coordination**: We'll work with you on disclosure timeline

## Security Best Practices for Contributors

When contributing to Malnu Backend, please follow these security best practices:

### 1. Never Commit Secrets

- Never commit API keys, passwords, or other sensitive data
- Use environment variables for sensitive configuration
- Use `.env.example` for placeholder values only

### 2. Input Validation

- Always validate and sanitize user input
- Use Form Request classes for validation
- Follow the [Input Validation guidelines](docs/SECURITY_ANALYSIS.md#input-validation-gaps)

### 3. Authentication & Authorization

- Always implement proper authentication
- Use role-based access control (RBAC)
- Validate user permissions before actions

### 4. Dependency Security

- Keep dependencies up to date
- Run security scans regularly
- Review vulnerability reports

### 5. Code Review

- All code changes require review
- Security-sensitive changes require additional review
- Follow the [code quality standards](docs/CONTRIBUTING.md)

## Security Features

Malnu Backend implements several security features:

- **JWT Authentication**: Token-based authentication with refresh tokens
- **CSRF Protection**: Cross-site request forgery protection
- **Security Headers**: CSP, HSTS, X-Frame-Options, etc.
- **Rate Limiting**: API rate limiting to prevent abuse
- **SQL Injection Prevention**: Parameterized queries
- **XSS Protection**: Input sanitization and output encoding

For detailed security analysis, see [Security Analysis Report](docs/SECURITY_ANALYSIS.md).

## Current Security Status

### Known Vulnerabilities

As of the latest security assessment (November 24, 2025):

- **Frontend Dependencies**: 3 HIGH severity vulnerabilities (react-router-dom)
- **Authentication**: Incomplete JWT implementation
- **Security Headers**: Not properly applied (middleware imports)
- **Database Services**: Currently disabled in Docker Compose

See [Application Status](docs/APPLICATION_STATUS.md) for current security issues and remediation progress.

### Security Metrics

- **Vulnerability Count**: 12+ (Target: 0)
- **Security Coverage**: 60% (Target: 95%)
- **Monitoring**: 0% (Target: 100%)
- **Compliance**: 70% (Target: 100%)

## Security Advisories

Security advisories will be published at:
- https://github.com/sulhicmz/malnu-backend/security/advisories

## Contact

For general security questions or concerns:

- **Security Issues**: Use the private vulnerability reporting process above
- **General Questions**: Open an issue with the `security` label
- **Emergencies**: Contact repository maintainers via GitHub private messaging

## Resources

- [GitHub Security Documentation](https://docs.github.com/en/code-security)
- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [CVE Database](https://cve.mitre.org/)
- [Security Analysis Report](docs/SECURITY_ANALYSIS.md)
- [Application Status](docs/APPLICATION_STATUS.md)

## Acknowledgments

We thank all security researchers who have responsibly disclosed vulnerabilities to help improve Malnu Backend security.

---

**Last Updated**: January 9, 2026
**Security Policy Version**: 1.0
