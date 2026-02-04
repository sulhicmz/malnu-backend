# Security Monitoring Guide

This document provides comprehensive guidance on security monitoring practices for the Malnu Backend project.

## Overview

The project implements automated security scanning through multiple layers:

1. **Automated Dependency Updates** - Dependabot keeps dependencies up-to-date
2. **Dependency Vulnerability Scanning** - Composer and NPM audits detect vulnerable packages
3. **Static Code Analysis** - CodeQL and PHPStan detect security issues in code
4. **Secrets Detection** - TruffleHog scans for leaked credentials
5. **Dependency Review** - Automated review of new dependency additions

## Security Tools

### 1. Dependabot (`.github/dependabot.yml`)

Automates dependency updates for both backend (Composer) and frontend (NPM).

**Features:**
- Weekly automated dependency updates
- Requires reviewer approval before merging
- Groups security updates separately
- Labels PRs with `dependencies` and appropriate package manager

**Configuration:**
- Updates run every Monday at 09:00 UTC
- PHP: 10 concurrent PRs max
- JavaScript: 10 concurrent PRs max

**Monitoring:**
- Review Dependabot PRs regularly
- Test updates before merging
- Pay special attention to security update groups

### 2. Composer Audit (`.github/workflows/security.yml`)

Scans PHP dependencies for known security vulnerabilities.

**When it runs:**
- On every pull request to `main` or `develop`
- On every push to `main`
- Weekly (Monday 09:00 UTC)

**What it checks:**
- Known vulnerabilities in Composer packages
- Outdated packages with security fixes available

**Handling failures:**
1. Review the failed audit output
2. Update affected packages
3. Re-run `composer audit` locally to verify
4. Push fix and re-check

### 3. NPM Audit (`.github/workflows/security.yml`)

Scans frontend dependencies for known security vulnerabilities.

**When it runs:**
- On every pull request to `main` or `develop`
- On every push to `main`
- Weekly (Monday 09:00 UTC)

**What it checks:**
- Known vulnerabilities in NPM packages
- Moderate severity and above by default

**Handling failures:**
1. Review the failed audit output
2. Update affected packages with `npm update <package>`
3. Re-run `npm audit` locally to verify
4. Push fix and re-check

**Note:** For development-only dependencies with vulnerabilities, consider adding to `package.json` overrides or ignore if acceptable.

### 4. CodeQL Analysis (`.github/workflows/security.yml`)

Performs deep static analysis of code for security issues.

**When it runs:**
- On every pull request to `main` or `develop`
- On every push to `main`
- Weekly (Monday 09:00 UTC)

**Languages analyzed:**
- PHP
- JavaScript

**Query sets:**
- Standard security queries
- Security-extended queries for broader coverage

**Monitoring:**
- Check Security tab in GitHub repository
- Review CodeQL alerts regularly
- Investigate high-severity findings

**Handling alerts:**
1. Evaluate severity and exploitability
2. Fix or dismiss with proper justification
3. Document why alerts were dismissed (if applicable)

### 5. Secrets Detection (`.github/workflows/security.yml`)

Scans codebase for accidentally committed secrets and credentials.

**When it runs:**
- On every pull request to `main` or `develop`
- On every push to `main`
- Weekly (Monday 09:00 UTC)

**What it detects:**
- API keys and tokens
- Database credentials
- AWS/GCP/Azure keys
- Certificates and keys
- passwords in code

**Handling findings:**
1. IMMEDIATE ACTION REQUIRED
2. Rotate any exposed secrets immediately
3. Remove secrets from code and git history
4. Use environment variables or secrets manager
5. Force push to remove from history (if recent)

**Prevention:**
- Never commit secrets or credentials
- Use `.env` files (excluded from git)
- Use GitHub Secrets for workflow secrets
- Use secret management services in production

### 6. PHPStan Static Analysis (`.github/workflows/security.yml`)

Runs static analysis at the highest level (level 5) to detect code quality and potential security issues.

**When it runs:**
- On every pull request to `main` or `develop`
- On every push to `main`
- Weekly (Monday 09:00 UTC)

**What it detects:**
- Type errors
- Potential security vulnerabilities (through strict type checking)
- Code quality issues
- Deprecation warnings (via phpstan-deprecation-rules)

**Configuration:**
- Analysis level: 5 (strictest)
- Memory limit: 300MB
- Path: `app` and `config` directories

**Handling issues:**
1. Review security rule violations
2. Fix using recommended security patterns
3. Add input validation where needed
4. Use parameterized queries (for SQL)
5. Escape output (for XSS prevention)

### 7. Dependency Review (`.github/workflows/security.yml`)

Reviews new dependency additions for license and security issues.

**When it runs:**
- Only on pull requests

**What it checks:**
- Known vulnerabilities in new dependencies
- License compatibility (blocks GPL-3.0, AGPL-3.0)
- Severity threshold: moderate and above

**Handling failures:**
1. Review the dependency report
2. Choose alternative packages if needed
3. Document why specific packages are required (if acceptable risk)

## Development Best Practices

### Input Validation
- Always validate and sanitize user input
- Use HyperVel/Laravel validation rules
- Never trust client-side validation
- Parameterize all database queries

### Authentication & Authorization
- Use JWT tokens for authentication
- Implement proper role-based access control (RBAC)
- Validate tokens on every authenticated request
- Implement token refresh mechanism

### Password Security
- Use `password_hash()` with PASSWORD_DEFAULT
- Never store plaintext passwords
- Implement password complexity requirements
- Use password_verify() for authentication

### Sensitive Data
- Never log sensitive data (passwords, tokens, PII)
- Use environment variables for secrets
- Encrypt data at rest when possible
- Use HTTPS for all communications

### Error Handling
- Don't expose system internals in error messages
- Use generic error messages for users
- Log detailed errors securely
- Handle exceptions appropriately

## Troubleshooting

### Issue: Dependabot PRs not creating

**Possible causes:**
1. GitHub Actions not enabled for repository
2. Dependabot configuration errors

**Solutions:**
1. Verify Actions tab is enabled in repository settings
2. Check `.github/dependabot.yml` syntax
3. Review Dependabot logs in repository settings

### Issue: Composer/NPM audit fails randomly

**Possible causes:**
1. Network issues accessing vulnerability database
2. Temporarily out-of-date vulnerability database

**Solutions:**
1. Re-run the workflow manually
2. Check if vulnerabilities are actually present
3. Verify package versions are correct

### Issue: CodeQL scans fail with errors

**Possible causes:**
1. Build configuration issues
2. Incompatible dependencies

**Solutions:**
1. Check build logs in GitHub Actions
2. Verify dependencies are compatible
3. Review Autobuild step for issues

### Issue: Secrets detection has false positives

**Possible causes:**
1. Development/test data that looks like secrets
2. Configuration files with example values

**Solutions:**
1. Add false positive to TruffleHog ignore list
2. Use obvious placeholder values (e.g., `YOUR_API_KEY_HERE`)
3. Document why specific strings are not secrets

### Issue: PHPStan security rules too strict

**Possible causes:**
1. New security extension version has false positives
2. Code pattern triggers rule incorrectly

**Solutions:**
1. Review specific rule violation
2. Report false positive to phpstan-security-extension
3. Add baseline for acceptable violations (last resort)
4. Update security extension to latest version

## Alert Management

### Severity Levels

1. **Critical** - Immediate action required
   - Exploitable vulnerabilities
   - Exposed secrets/credentials

2. **High** - Address within 24-48 hours
   - Known vulnerabilities with available exploits
   - Security best practice violations

3. **Medium** - Address within 1 week
   - Potential vulnerabilities
   - Outdated dependencies

4. **Low** - Address in next release cycle
   - Informational findings
   - Code quality issues

### Response Workflow

1. **Acknowledge** - Comment on finding with initial assessment
2. **Triage** - Determine severity and urgency
3. **Assign** - Assign to appropriate developer
4. **Fix** - Implement fix following security best practices
5. **Test** - Verify fix resolves issue without regressions
6. **Deploy** - Merge fix with proper review
7. **Close** - Close alert with resolution details

## Compliance

### Data Privacy

- Implement data minimization principles
- Provide data deletion on request
- Use secure data storage
- Comply with applicable regulations

### Audit Trail

- Log all authentication events
- Track data access patterns
- Monitor configuration changes
- Retain logs for compliance period

### Incident Response

1. **Detect** - Automated monitoring alerts
2. **Contain** - Limit scope of breach
3. **Eradicate** - Remove attacker access
4. **Recover** - Restore from clean backups
5. **Lessons Learned** - Document and improve

## Related Documentation

- [SECURITY.md](SECURITY.md) - Security policy and reporting
- [API_ERROR_HANDLING.md](API_ERROR_HANDLING.md) - Error handling patterns
- [CONTRIBUTING.md](CONTRIBUTING.md) - Security in contribution process

## Support

For security questions or to report vulnerabilities:
- Review [SECURITY.md](SECURITY.md) for reporting procedures
- Create a security advisory via GitHub private vulnerability reporting
- Contact security team directly (see SECURITY.md for contact info)
