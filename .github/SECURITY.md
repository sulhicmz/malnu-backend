# Security Policy

## Supported Versions

We provide security updates for the following versions of our application:

| Version | Supported          |
| ------- | ------------------ |
| 1.x.x   | ✅ Yes             |
| < 1.0   | ❌ No              |

## Reporting a Vulnerability

We take the security of our application seriously. If you discover a security vulnerability, please report it to us as soon as possible.

### How to Report

**Please do not report security vulnerabilities through public GitHub issues.**

Instead, please send an email to [security@malnu-kananga.com](mailto:security@malnu-kananga.com) or create a private security advisory through GitHub's security features.

When reporting a vulnerability, please include:
- A detailed description of the vulnerability
- Steps to reproduce the issue
- Potential impact of the vulnerability
- Any suggested fixes or mitigations

### What to Expect

After you report a vulnerability:
1. We will acknowledge your report within 48 hours
2. We will investigate and confirm the vulnerability
3. We will work on a fix and provide an estimated timeline
4. Once fixed, we will publicly acknowledge your contribution (if you wish)

### Scope

We welcome reports about:
- Authentication bypasses
- Authorization flaws
- SQL injection vulnerabilities
- Cross-site scripting (XSS) issues
- Cross-site request forgery (CSRF) vulnerabilities
- Information disclosure
- Any other security-related issues

## Security Best Practices

### For Contributors
- Always use secure coding practices
- Validate and sanitize all user inputs
- Follow the principle of least privilege
- Keep dependencies up to date
- Use parameterized queries to prevent SQL injection

### For Users
- Keep your application updated
- Use strong, unique passwords
- Enable two-factor authentication where available
- Regularly review access permissions

## Security Updates

Security updates are released as needed. We will announce significant security updates in our release notes and through our security mailing list.

## Additional Resources

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [Secure Coding Guidelines](https://wiki.sei.cmu.edu/confluence/display/seccode/SEI+Secure+Coding)
- [SANS Secure Coding](https://www.sans.org/tips/secure-coding-principles/)