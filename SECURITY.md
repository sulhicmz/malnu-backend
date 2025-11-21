# Security Policy

## Supported Versions

Only the main application (root directory) is actively supported and maintained.

| Version | Supported          |
|---------|--------------------|
| Main app (root) | :white_check_mark: Yes |
| Legacy app (web-sch-12/) | :x: No - Deprecated |

## Reporting a Vulnerability

If you discover a security vulnerability, please report it responsibly.

### How to Report

**Preferred Method**: Create a private vulnerability advisory via GitHub:
1. Go to [Security Advisories](https://github.com/sulhicmz/malnu-backend/security/advisories)
2. Click "New draft security advisory"
3. Follow the form to provide details

**Alternative Method**: Email the maintainers directly:
- Send details to: security@ma-malnukananga.sch.id
- Include "VULNERABILITY REPORT" in subject line

### What to Include

Please provide:
- **Description**: Clear description of the vulnerability
- **Impact**: Potential security impact
- **Steps to Reproduce**: Detailed reproduction steps
- **Affected Versions**: Which versions are affected
- **Proof of Concept**: Code examples or screenshots (if applicable)

### Response Timeline

- **Initial Response**: Within 48 hours
- **Detailed Assessment**: Within 7 days
- **Public Disclosure**: After fix is released (typically 14-90 days)

### Security Best Practices

We follow responsible disclosure principles:
- We'll acknowledge receipt within 48 hours
- We'll provide regular updates on our progress
- We'll work with you to understand and validate the issue
- We'll credit you in the advisory (with your permission)

## Security Features

This application includes several security features:

### Built-in Protections
- Content Security Policy (CSP) headers
- HTTP Strict Transport Security (HSTS)
- XSS protection headers
- Frame protection (X-Frame-Options)
- Input validation and sanitization
- SQL injection protection via ORM

### Configuration
Security headers are configurable via environment variables:
- `SECURITY_HEADERS_ENABLED=true`
- `CSP_ENABLED=true`
- `HSTS_ENABLED=true`

## Security Guidelines for Contributors

When contributing to this project:

1. **Never commit secrets** - API keys, passwords, or tokens
2. **Use environment variables** for all sensitive configuration
3. **Follow secure coding practices**:
   - Validate all inputs
   - Use parameterized queries
   - Implement proper authentication/authorization
   - Sanitize outputs
4. **Test security changes** - Ensure new features don't introduce vulnerabilities
5. **Report suspicious findings** - If you discover potential security issues

## Security Scanning

We use automated security scanning:
- GitHub Dependabot for dependency vulnerabilities
- Manual security reviews for critical changes
- Static analysis tools for code security

## Contact

For security-related questions not related to vulnerability reports:
- General security inquiries: security@ma-malnukananga.sch.id
- Repository maintainers: via GitHub issues (non-sensitive topics only)

## Acknowledgments

We thank all security researchers who help us maintain the security of this application through responsible disclosure.