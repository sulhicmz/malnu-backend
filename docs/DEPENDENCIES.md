# Dependency Health & Management

**Last Updated**: January 8, 2026
**Maintainer**: Principal Security Engineer

---

## Overview

This document tracks the health status of all project dependencies, including security vulnerabilities, abandoned packages, and update recommendations.

---

## PHP Dependencies (Composer)

### Production Dependencies

| Package | Current | Latest | Status | Notes |
|---------|---------|--------|--------|-------|
| hypervel/framework | v0.1.7 | v0.2.11 | 🟡 Outdated | Major update available, deferred |
| friendsofhyperf/tinker | v3.1.75 | v3.1.75 | ✅ Up to date | Latest stable version |
| ext-redis | - | - | ✅ Required | System dependency |

### Development Dependencies

| Package | Current | Latest | Status | Notes |
|---------|---------|--------|--------|-------|
| fakerphp/faker | 2.0.x-dev | 2.0.x-dev | ✅ Up to date | Dev version |
| filp/whoops | v2.18.4 | v2.18.4 | ✅ Up to date | Error handler |
| friendsofphp/php-cs-fixer | v3.92.4 | v3.92.4 | ✅ Up to date | Latest stable |
| hyperf/testing | v3.1.63 | v3.1.63 | ✅ Up to date | Testing framework |
| hyperf/watcher | v3.1.63 | v3.1.63 | ✅ Up to date | Hot reload |
| hypervel/devtool | v0.1.7 | v0.3.17 | 🟡 Outdated | Major update available, deferred |
| laravel/envoy | ^2.10 | - | ✅ Stable | Deploy tool |
| mockery/mockery | ^1.5.1 | - | ✅ Stable | Mocking |
| nunomaduro/collision | v8.8.3 | v8.8.3 | ✅ Up to date | CLI error handling |
| phpstan/phpstan | v1.12.24 | v2.1.33 | 🟡 Outdated | Major update available, deferred |
| phpunit/phpunit | v10.5.45 | v12.5.4 | 🟡 Outdated | Major update available, deferred |
| swoole/ide-helper | v5.1.7 | v6.0.2 | 🟡 Outdated | Major update available, deferred |

---

## Abandoned Dependencies

### laminas/laminas-mime

**Status**: ⚠️ ABANDONED - Use symfony/mime instead

**Dependency Chain**:
```
hypervel/framework → hyperf/http-message → laminas/laminas-mime
```

**Current Version**: Latest installed (managed by Composer)

**Risk Assessment**: 🟡 LOW RISK
- Package is abandoned but actively maintained by community
- No known security vulnerabilities
- Used by Hyperf framework (managed dependency)
- Can be replaced when Hyperf migrates to symfony/mime

**Action Plan**:
1. ✅ Monitor Hyperf updates for migration to symfony/mime
2. ✅ Track security advisories for laminas/laminas-mime
3. 🔄 Update when Hyperf officially switches to symfony/mime
4. 📌 Create GitHub issue to track migration progress

**Alternatives**:
- **symfony/mime**: Recommended replacement
- **Migration**: Requires Hyperf framework update
- **Timeline**: Dependent on Hyperf release schedule

---

## Frontend Dependencies (npm)

### Core Dependencies

| Package | Current | Latest | Status | Notes |
|---------|---------|--------|--------|-------|
| react | ^18.3.1 | ^18.3.1 | ✅ Latest | UI framework |
| react-dom | ^18.3.1 | ^18.3.1 | ✅ Latest | React renderer |
| react-router-dom | ^6.22.1 | ^6.22.1 | ✅ Latest | Routing |
| axios | ^1.13.2 | - | ✅ Stable | HTTP client |
| lucide-react | ^0.344.0 | - | ✅ Stable | Icons |
| recharts | ^2.11.0 | - | ✅ Stable | Charts |

### Development Dependencies

| Package | Current | Latest | Status | Notes |
|---------|---------|--------|--------|-------|
| vite | ^5.4.21 | - | ✅ Latest | Build tool |
| tailwindcss | ^3.4.1 | - | ✅ Stable | CSS framework |
| typescript | ^5.5.3 | - | ✅ Stable | Type safety |
| eslint | ^9.9.1 | - | ✅ Latest | Linting |
| postcss | ^8.4.35 | - | ✅ Stable | CSS processing |

### Security Overrides

The following packages have security overrides in `package.json`:

| Package | Override | Vulnerability Fixed |
|---------|----------|-------------------|
| esbuild | $esbuild | Multiple CVEs |
| cross-spawn | ^7.0.6 | ReDoS (CVE-2024-21516) |
| glob | ^10.5.0 | ReDoS (CVE-2024-21502) |
| minimatch | ^9.0.5 | ReDoS (CVE-2022-3517) |

**Vulnerability Status**: ✅ ALL FIXES APPLIED
- `npm audit`: 0 vulnerabilities found
- All known vulnerabilities patched via overrides

---

## Recent Updates

### January 8, 2026

**Security Fixes**:
- ✅ symfony/http-foundation: v6.4.18 → v6.4.31 (CVE-2025-64500)

**Dependency Updates**:
- ✅ hypervel/framework: v0.1.5 → v0.1.7
- ✅ hypervel/devtool: v0.1.5 → v0.1.7
- ✅ friendsofhyperf/tinker: v3.1.48 → v3.1.75
- ✅ hyperf/testing: v3.1.53 → v3.1.63
- ✅ hyperf/watcher: v3.1.43 → v3.1.63
- ✅ friendsofphp/php-cs-fixer: v3.75.0 → v3.92.4
- ✅ filp/whoops: v2.18.0 → v2.18.4
- ✅ nunomaduro/collision: v8.5.0 → v8.8.3

---

## Planned Updates

### Short-term (Next Sprint)

1. **Form Request Validators**
   - Create validators for all endpoints (TASK-284)
   - Improve input validation coverage
   - Priority: HIGH

### Medium-term (Next Quarter)

1. **Major Version Updates**
   - phpstan/phpstan: v1.x → v2.x
   - phpunit/phpunit: v10.x → v12.x
   - swoole/ide-helper: v5.x → v6.x
   - hypervel/framework: v0.1.7 → v0.2.11
   - hypervel/devtool: v0.1.7 → v0.3.17
   - Priority: MEDIUM
   - Requires: Thorough testing and documentation

2. **Abandoned Dependency Migration**
   - Monitor Hyperf for laminas/laminas-mime → symfony/mime migration
   - Create migration plan when announced
   - Priority: MEDIUM

### Long-term (Next 6 Months)

1. **Automated Dependency Scanning**
   - Integrate security scanning in CI/CD pipeline
   - Automated PR checks for vulnerabilities
   - Dependency update bot (dependabot/renovate)
   - Priority: HIGH

2. **Dependency Pinning**
   - Pin critical security-related packages
   - Implement strict version ranges
   - Prevent accidental security regressions
   - Priority: MEDIUM

---

## Security Audit Schedule

- **Weekly**: `composer audit` and `npm audit`
- **Monthly**: Review outdated packages
- **Quarterly**: Comprehensive security audit
- **Bi-annually**: Major dependency updates

**Last Audit**: January 8, 2026
**Next Audit**: April 8, 2026

---

## Vulnerability Response Process

### Critical Vulnerability (CVE)
1. Immediate assessment of impact
2. Check if package is in direct dependencies
3. Update to patched version within 24 hours
4. Run full test suite
5. Deploy to production
6. Document in SECURITY.md

### High Severity Advisory
1. Assess impact on production
2. Schedule update within 72 hours
3. Test thoroughly
4. Deploy during maintenance window
5. Document resolution

### Medium/Low Severity Advisory
1. Include in next sprint planning
2. Update with other dependencies
3. Test and deploy
4. Document resolution

---

## Monitoring

### Tools Used

- **Composer Audit**: `composer audit`
- **NPM Audit**: `cd frontend && npm audit`
- **Outdated Check**: `composer outdated --direct`
- **Security Advisories**: Packagist, npm security

### Alerts

Configure alerts for:
- New CVEs in production dependencies
- Abandoned package warnings
- Major version releases
- Security advisories from package maintainers

---

## Best Practices

### Dependency Updates

1. **Always review release notes** before updating
2. **Run full test suite** after updates
3. **Update in staging first**, then production
4. **Keep security patches up to date**
5. **Monitor for breaking changes**

### Security

1. **Never commit secrets** (API keys, passwords)
2. **Use environment variables** for configuration
3. **Regular security audits** (quarterly minimum)
4. **Update dependencies frequently**
5. **Monitor abandoned packages**

### Version Management

1. **Use semantic versioning** for releases
2. **Pin critical dependencies** for production
3. **Allow patch/minor updates** for non-critical deps
4. **Test major updates** thoroughly
5. **Document breaking changes**

---

## References

- [Composer Security Advisories](https://packagist.org/apidoc#get-security-advisories)
- [NPM Security](https://docs.npmjs.com/cli/v6/commands/npm-audit)
- [OWASP Dependency Check](https://owasp.org/www-project-dependency-check/)
- [Security Best Practices](https://github.com/sulhicmz/malnu-backend/blob/main/docs/SECURITY.md)

---

## Contact

**Security Issues**: Report to security team or create GitHub issue with "security" label
**Dependency Questions**: Contact development team
**Emergencies**: Security channel on communication platform

---

*This document is maintained by the Principal Security Engineer and updated with each dependency update or security audit.*
