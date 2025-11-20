# Security Policy

## Supported Versions

This section outlines the security support policy for different versions of the malnu-backend project.

| Version | Supported          | Security Updates |
|---------|-------------------|------------------|
| Current main branch | ✅ | ✅ |
| Previous release | ❌ | ❌ |
| Legacy versions | ❌ | ❌ |

**Note:** Only the current main branch receives security updates. Users should always run the latest version.

## Reporting a Vulnerability

The maintainers of malnu-backend take security seriously. If you discover a security vulnerability, please report it responsibly.

### How to Report

**Please do NOT report security vulnerabilities through public GitHub issues.**

Instead, please send your report to:

- **Email:** security@ma-malnukananga.sch.id
- **GitHub Security Advisory:** Use the [GitHub private vulnerability reporting](https://docs.github.com/en/code-security/security-advisories/guidance-on-reporting-and-writing/privately-reporting-a-security-vulnerability) feature

### What to Include

Please include the following information in your report:

1. **Vulnerability Type:** Brief description of the vulnerability type (e.g., XSS, SQL injection, authentication bypass)
2. **Affected Versions:** Which versions are affected
3. **Proof of Concept:** Steps to reproduce the vulnerability
4. **Impact:** Potential impact of the vulnerability
5. **Suggested Fix (Optional):** Any suggestions for fixing the issue

### Response Timeline

- **Initial Response:** We will acknowledge receipt of your report within 48 hours
- **Detailed Response:** We will provide a detailed response and estimated timeline within 7 days
- **Patch Release:** Security patches will be released as soon as possible, typically within 30 days of report receipt

### Coordination Policy

We follow a responsible disclosure policy:

- **Private Disclosure Period:** 90 days from initial report
- **Extended Disclosure:** May be extended if the vulnerability is complex or requires extensive coordination
- **Public Disclosure:** Will be coordinated with you and published with credit (if desired)

## Security Best Practices

### For Users

1. **Keep Updated:** Always use the latest version of the software
2. **Environment Security:** 
   - Use strong, unique passwords for all accounts
   - Enable two-factor authentication where available
   - Keep your server and dependencies updated
3. **Network Security:**
   - Use HTTPS in production
   - Implement proper firewall rules
   - Restrict database access to trusted sources only
4. **Data Protection:**
   - Regular backups of critical data
   - Encrypt sensitive data at rest and in transit
   - Follow principle of least privilege

### For Developers

1. **Code Security:**
   - Follow secure coding practices
   - Validate and sanitize all user inputs
   - Use parameterized queries to prevent SQL injection
   - Implement proper authentication and authorization
2. **Dependencies:**
   - Regularly update dependencies
   - Use dependency scanning tools
   - Review security advisories for used packages
3. **Testing:**
   - Include security testing in CI/CD pipeline
   - Perform regular security audits
   - Use automated security scanning tools

## Security Features

This application includes several built-in security features:

### Authentication & Authorization
- JWT-based authentication with secure token handling
- Role-based access control (RBAC)
- Session management with secure configuration

### Data Protection
- Input validation and sanitization
- SQL injection prevention through parameterized queries
- XSS protection with content security policy
- CSRF protection for state-changing operations

### Infrastructure Security
- Security headers configuration (HSTS, CSP, X-Frame-Options, etc.)
- Rate limiting to prevent abuse
- Secure file upload handling
- Environment-based configuration management

## Security Headers

The application implements the following security headers:

- **Content Security Policy (CSP):** Prevents XSS attacks by controlling resource loading
- **HTTP Strict Transport Security (HSTS):** Enforces HTTPS connections
- **X-Frame-Options:** Prevents clickjacking attacks
- **X-Content-Type-Options:** Prevents MIME-type sniffing attacks
- **X-XSS-Protection:** Enables browser XSS protection
- **Referrer Policy:** Controls referrer information in requests
- **Permissions Policy:** Controls access to browser features

## Vulnerability Management

### Monitoring
- Continuous monitoring for security vulnerabilities in dependencies
- Regular security audits and penetration testing
- Monitoring of security advisories and CVE databases

### Patch Management
- Priority-based patching system
- Automated dependency updates where safe
- Security patches prioritized over feature updates

### Incident Response
In the event of a security incident:

1. **Immediate Assessment:** Evaluate impact and scope
2. **Containment:** Implement immediate measures to limit damage
3. **Communication:** Notify affected users and stakeholders
4. **Remediation:** Develop and deploy fixes
5. **Post-Mortem:** Analyze and improve processes

## Security Contact Information

- **Security Team:** security@ma-malnukananga.sch.id
- **General Inquiries:** info@ma-malnukananga.sch.id
- **GitHub Issues:** For non-security related issues only

## Acknowledgments

We thank all security researchers who help us keep malnu-backend secure. Your responsible disclosure helps protect our users and improve our security posture.

## Legal Disclaimer

This security policy is provided "as is" without warranty of any kind. We reserve the right to modify this policy at any time without notice.

---

*Last updated: November 20, 2025*