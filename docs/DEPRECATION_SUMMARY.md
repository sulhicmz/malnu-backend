# Deprecation Summary: Dual Application Structure

## Overview
This document summarizes the deprecation of the dual application structure in the malnu-backend repository. The repository previously contained two applications:
1. Main application (root directory) - HyperVel framework
2. Legacy application (web-sch-12 directory) - Laravel 12 modular application

## Changes Made

### 1. web-sch-12 Directory Deprecation
- Updated README.md with comprehensive deprecation notice
- Modified composer.json to mark as abandoned with deprecation warnings
- Created DEPRECATED.md file with clear deprecation message
- Added "abandoned": true flag to composer.json

### 2. Main Documentation Updates
- Updated docs/README.md with clear deprecation notice and migration strategy
- Updated docs/CONTRIBUTING.md to emphasize no new development in legacy app
- Updated docs/APPLICATION_STATUS.md to mark legacy app as fully deprecated
- Updated docs/PROJECT_STRUCTURE.md to reflect single application focus
- Updated docs/MIGRATION_PLAN.md to reflect completed deprecation tasks

### 3. Key Messages Communicated
- **Main application (root)**: Primary and only supported application
- **web-sch-12 application**: Fully deprecated, will be removed, no new development
- All future work should focus on the HyperVel main application
- Legacy application contains modules: ERaport, LaporanAnalitik, ManajemenSekolah, SistemMonetisasi

## Technical Impact
- No changes needed to CI/CD pipelines (already focused on main application)
- No changes needed to build processes (already focused on main application)
- Composer dependencies remain separate between applications
- No functionality loss as main application is more comprehensive

## Migration Status
- **Assessment**: Completed - main application has more comprehensive features
- **Documentation**: Completed - all docs updated with deprecation notices
- **Development Guidance**: Completed - clear direction to use main application only
- **Feature Migration**: Not required - main application already has superior functionality

## Next Steps
1. Ensure all team members are aware of deprecation
2. Monitor for any dependencies on the legacy application
3. Plan complete removal of web-sch-12 directory in future release
4. Update any external references to the legacy application

## Timeline
- **Immediate**: All development stops on legacy application
- **Short-term**: Monitor for any required functionality not in main app
- **Long-term**: Complete removal of web-sch-12 directory

## Contact
For questions about this deprecation, please refer to the updated documentation or contact the development team.