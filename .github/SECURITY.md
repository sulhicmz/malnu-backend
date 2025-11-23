# Security Policy

## Supported Versions

The following versions of malnu-backend are currently being supported with security updates:

| Version | Supported          |
| ------- | ------------------ |
| 1.x.x   | ✅ Yes             |
| < 1.0   | ❌ No              |

## Reporting a Vulnerability

We take the security of malnu-backend seriously. If you believe you have found a security vulnerability, please report it to us as described below.

### How to Report

**Please do not report security vulnerabilities through public GitHub issues.**

Instead, please send an email to: **security [at] malnu-kananga [dot] org** (replace [at] and [dot] with @ and . respectively)

In your report, please include:
- A description of the vulnerability
- Steps to reproduce the issue
- Potential impact of the vulnerability
- Any proof-of-concept code (if applicable and safe to share)

### What to Expect

After you submit a report:
- You will receive an acknowledgment within 48 hours
- We will investigate and respond with our assessment within 5 business days
- If the vulnerability is accepted, we will work on a fix and release it as soon as possible
- If the vulnerability is declined, we will provide an explanation

### Security Update Policy

- Critical vulnerabilities will be addressed within 72 hours
- High severity vulnerabilities will be addressed within 1 week
- Medium severity vulnerabilities will be addressed within 2 weeks
- Low severity vulnerabilities will be addressed within 1 month

## Security Best Practices

### For Contributors
- Never commit sensitive credentials, API keys, or tokens to the repository
- Use environment variables for sensitive configuration
- Follow the principle of least privilege when implementing features
- Validate and sanitize all user inputs
- Use prepared statements for database queries
- Implement proper authentication and authorization checks

### For Users
- Keep your application updated to the latest supported version
- Regularly update dependencies
- Use strong authentication mechanisms
- Monitor your application for suspicious activity
- Follow security best practices for your hosting environment

## Dependencies

We regularly update and audit our dependencies to ensure they don't introduce security vulnerabilities:
- PHP dependencies are managed via Composer
- Frontend dependencies are managed via npm
- Security advisories are monitored using automated tools

## Contact

For general security questions or concerns, please contact our security team at: security [at] malnu-kananga [dot] org