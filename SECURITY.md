# Security Policy

## Supported Versions

| Version | Supported          |
|---------|--------------------|
| 0.1.x   | :white_check_mark: |

## Reporting a Vulnerability

If you discover a security vulnerability in this project, please report it to us privately before disclosing it publicly.

### How to Report

- **Email**: security@hypervel.com
- **Private Issue**: Create a private issue on GitHub with the "security" label

### What to Include

Please include as much of the following information as possible:

- **Type of vulnerability** (e.g., XSS, SQL injection, authentication bypass)
- **Affected versions** of the software
- **Steps to reproduce** the vulnerability
- **Potential impact** of the vulnerability
- **Proof of concept** or exploit code (if available)

### Response Time

We will acknowledge receipt of your vulnerability report within **48 hours** and provide a detailed response within **7 days** including:

- Confirmation of the vulnerability
- Expected timeline for a fix
- Any workarounds or mitigations

### Security Updates

Security updates will be released as follows:

- **Critical vulnerabilities**: Within 7 days of disclosure
- **High severity**: Within 14 days of disclosure  
- **Medium/Low severity**: Within 30 days of disclosure

### Security Best Practices

- Keep your dependencies updated
- Use environment variables for sensitive configuration
- Enable HTTPS in production
- Review the [CONTRIBUTING.md](CONTRIBUTING.md) for secure development practices

### Security Scanning

This project uses automated security scanning tools to identify vulnerabilities in dependencies and code. Security alerts are monitored and addressed promptly.

## Security Features

- Built-in CSRF protection
- Secure password hashing using bcrypt
- Input validation and sanitization
- SQL injection prevention through parameterized queries
- Secure session management

## Disclosure Policy

We follow a **responsible disclosure** policy:

1. Report vulnerabilities privately
2. Allow reasonable time for remediation
3. Coordinate public disclosure
4. Credit researchers (with permission)

Thank you for helping keep this project secure!