# Security Policy

## Supported Versions

We provide security updates for the following versions of malnu-backend:

| Version | Supported          |
| ------- | ------------------ |
| 1.x.x   | :white_check_mark: |
| < 1.0   | :x:                |

## Reporting a Vulnerability

We take the security of the malnu-backend project seriously. If you discover a security vulnerability, please report it responsibly using the following process:

### How to Report

1. **Do not report security vulnerabilities through public GitHub issues**
2. Instead, please send an email to [security@malnu-kananga.edu](mailto:security@malnu-kananga.edu) with the subject line "SECURITY VULNERABILITY: [brief description]"
3. Include the following information in your report:
   - A detailed description of the vulnerability
   - Steps to reproduce the issue
   - Potential impact of the vulnerability
   - Any possible mitigations you've identified

### What to Expect

- **Acknowledgment**: We will acknowledge receipt of your vulnerability report within 48 hours
- **Updates**: You will receive regular updates (at least once a week) on the status of your report
- **Resolution Timeline**: We aim to resolve critical vulnerabilities within 30 days of confirmation
- **Disclosure**: We will coordinate with you on the public disclosure timeline

## Security Best Practices

### For Contributors
- Follow the [PSR-12](https://www.php-fig.org/psr/psr-12/) coding standard
- Never commit credentials, API keys, or sensitive data to the repository
- Use environment variables for sensitive configuration
- Implement proper input validation and sanitization
- Follow secure coding practices for PHP and Laravel/HyperVel applications

### For Users
- Keep your application updated to the latest supported version
- Regularly update dependencies using `composer update`
- Configure proper authentication and authorization mechanisms
- Implement proper logging and monitoring
- Follow security best practices for your hosting environment

## Security Features

The malnu-backend application includes several security features by default:

- JWT authentication with proper token management
- Input validation and sanitization
- SQL injection prevention through Eloquent ORM
- Cross-site scripting (XSS) protection
- Cross-site request forgery (CSRF) protection
- Rate limiting for API endpoints
- Secure session management

## Incident Response

In case of a security incident:

1. **Immediate Response**: The security team will acknowledge the incident within 2 hours during business hours
2. **Assessment**: A security impact assessment will be performed within 24 hours
3. **Mitigation**: Temporary mitigations will be implemented if necessary
4. **Resolution**: A permanent fix will be developed and deployed
5. **Communication**: Stakeholders will be notified of the incident and resolution

## Dependencies Security

We regularly monitor and update our dependencies:
- Use `composer audit` to check for known vulnerabilities in dependencies
- Subscribe to security mailing lists for our core dependencies
- Regular security updates are part of our maintenance cycle

## Contact

For security-related inquiries:
- Email: [security@malnu-kananga.edu](mailto:security@malnu-kananga.edu)
- For urgent matters: Contact the project maintainers directly through private communication

## Additional Resources

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [PHP Security](https://www.php.net/manual/en/security.php)
- [Laravel Security](https://laravel.com/docs/10.x/security)
- [Hyperf Security](https://hyperf.wiki/2.2/#/en/security/security)

---
Last updated: November 20, 2025