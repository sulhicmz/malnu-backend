# Security Policy

## Supported Versions

Only the main application (root directory) is actively supported. The legacy `web-sch-12/` directory is deprecated and does not receive security updates.

| Version | Supported          |
|---------|--------------------|
| Main app (root) | :white_check_mark: Yes |
| Legacy (web-sch-12/) | :x: No |

## Reporting a Vulnerability

If you discover a security vulnerability, please report it privately before disclosing it publicly.

### How to Report

**Preferred Method**: Create a [Security Advisory](https://github.com/maskom-team/malnu-backend/security/advisories) on GitHub.

**Alternative**: Send an email to our security team at `security@ma-malnukananga.sch.id`

### What to Include

Please include as much of the following information as possible:

- **Type of vulnerability** (e.g., XSS, SQL injection, authentication bypass)
- **Affected versions** of the software
- **Steps to reproduce** the vulnerability
- **Potential impact** if exploited
- **Proof of concept** (if available)

### Response Time

We aim to respond to security reports within **48 hours** and provide a fix within **7 days** for critical vulnerabilities.

### Security Best Practices

- Never commit real credentials, API keys, or sensitive data to the repository
- Use environment variables for all sensitive configuration
- Keep dependencies updated regularly
- Follow the principle of least privilege
- Enable security headers in production

## Security Features

This application includes the following security measures:

- **Authentication**: JWT-based authentication with secure token handling
- **Input Validation**: Comprehensive input sanitization and validation
- **CSRF Protection**: Cross-site request forgery protection
- **SQL Injection Prevention**: Parameterized queries and ORM usage
- **Environment Security**: Sensitive data stored in environment variables only

## Security Scanning

We use automated security scanning tools to identify potential vulnerabilities:
- Dependency scanning for known vulnerable packages
- Static code analysis for security anti-patterns
- Secret scanning to prevent credential leaks

## Disclosure Policy

We follow a responsible disclosure policy:

1. **Private Report**: Security vulnerabilities are reported privately
2. **Assessment**: We assess and validate the vulnerability
3. **Fix Development**: We develop and test a patch
4. **Coordinated Disclosure**: We disclose the vulnerability after a fix is available
5. **Credit**: We credit researchers who discover vulnerabilities (with permission)

## Security Updates

Security updates are handled through:
- **Patch releases** for security fixes
- **Security advisories** for critical vulnerabilities
- **Automated dependency updates** where safe

For questions about this security policy, please open an issue with the `security` label.