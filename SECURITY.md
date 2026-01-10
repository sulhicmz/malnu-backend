# Security Policy

## Supported Versions

We currently support the following versions of Malnu Backend:

| Version | Supported          |
| ------- | ------------------ |
| main    | :white_check_mark: |

## Reporting a Vulnerability

### Private Disclosure Process

**Do NOT open a public issue for security vulnerabilities.**

To report a security vulnerability, please send an email to our security team. Please include:

- A description of the vulnerability
- Steps to reproduce the issue
- Potential impact of the vulnerability
- Any suggested patches or mitigations (if available)

### What Happens Next

1. **Confirmation**: We will acknowledge receipt of your report within 48 hours
2. **Assessment**: Our security team will investigate and validate the vulnerability
3. **Resolution**: We will develop and test a fix
4. **Disclosure**: We will work with you to coordinate public disclosure

### Responsible Disclosure Policy

We follow a responsible disclosure process to protect users while giving security researchers appropriate credit:

- **Response Time**: We aim to respond within 48 hours of initial report
- **Fix Timeline**: Critical issues are addressed within 7 days, high priority within 14 days
- **Public Disclosure**: We will coordinate with you to announce the fix and release simultaneously
- **Credit**: With your permission, we will acknowledge your contribution in security advisories

## Security Best Practices for Contributors

When contributing to Malnu Backend, please follow these security guidelines:

### Code Security

- **Input Validation**: Always validate and sanitize user input
- **Output Encoding**: Use proper output encoding to prevent XSS attacks
- **SQL Injection**: Use parameterized queries (ORM/Eloquent) - never concatenate user input into SQL
- **Authentication**: Follow existing authentication patterns and never store credentials in code
- **Authorization**: Implement proper access controls using the RBAC system
- **Secrets Management**: Never commit secrets, API keys, or credentials to the repository

### Dependencies

- **Keep Updated**: Regularly update dependencies to get security patches
- **Audit**: Run `npm audit` and `composer audit` regularly
- **Review**: Carefully review new dependencies before adding them

### Testing

- **Security Tests**: Write tests for security-critical code paths
- **Edge Cases**: Consider edge cases and potential abuse scenarios
- **Error Handling**: Don't expose sensitive information in error messages

### Code Review

- **Security Review**: Mark security-related PRs for extra review attention
- **Peer Review**: All code must be reviewed before merging
- **Changes**: Be cautious about changes to authentication, authorization, or data handling

## Security Features in Malnu Backend

Malnu Backend includes several built-in security features:

- **Security Headers**: Content Security Policy, HSTS, X-Frame-Options, etc.
- **CSRF Protection**: Built-in CSRF protection for state-changing operations
- **SQL Injection Prevention**: Parameterized queries via ORM
- **XSS Protection**: Output escaping and Content Security Policy
- **Password Hashing**: Secure password hashing using bcrypt
- **UUID Implementation**: Prevents ID enumeration attacks
- **JWT Authentication**: Token-based authentication (in progress)

## Ongoing Security Efforts

We are continuously improving our security posture. For a comprehensive security analysis, see:

- [Security Analysis Report](docs/SECURITY_ANALYSIS.md) - Detailed vulnerability assessment and recommendations
- [Contributing Guidelines](docs/CONTRIBUTING.md) - Contribution guidelines including security considerations

## Current Security Priorities

Based on our security analysis, we are currently working on:

1. :white_check_mark: Add SECURITY.md and CODEOWNERS governance files (this issue)
2. :construction: Fix frontend dependency vulnerabilities
3. :construction: Complete JWT authentication implementation
4. :construction: Implement RBAC authorization across all controllers
5. :construction: Add comprehensive input validation

## Security Contacts

- **Security Email**: [to be configured]
- **Repository**: https://github.com/sulhicmz/malnu-backend
- **Security Issues**: Use the private disclosure process above

## Security Metrics

We track the following security metrics:

- **Open Vulnerabilities**: See [Security Analysis](docs/SECURITY_ANALYSIS.md)
- **Security Coverage**: Ongoing improvement
- **Automated Scanning**: To be implemented

## Acknowledgments

We thank all security researchers who help us improve the security of Malnu Backend. Your responsible disclosure helps protect all our users.

---

For more detailed information about our security practices and current status, please review our [Security Analysis Documentation](docs/SECURITY_ANALYSIS.md).
