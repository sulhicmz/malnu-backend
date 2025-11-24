# Deprecation Summary: Dual Application Structure

## Overview
This document summarizes the removal of the dual application structure in the malnu-backend repository. The repository previously contained two applications but now contains only one:
1. Main application (root directory) - HyperVel framework

## Changes Made

### 1. web-sch-12 Directory Removal
- Complete removal of the web-sch-12/ directory from the repository
- Updated composer.json to remove any references to the legacy application
- Removed DEPRECATED.md file as the directory is now gone

### 2. Main Documentation Updates
- Updated docs/README.md to reflect single application structure
- Updated docs/CONTRIBUTING.md to reflect single application focus
- Updated docs/APPLICATION_STATUS.md to reflect single application
- Updated docs/PROJECT_STRUCTURE.md to reflect single application focus
- Updated documentation to reflect completed removal

### 3. Key Changes
- **Main application (root)**: Primary and only supported application
- **web-sch-12 application**: Completely removed from repository
- All future work should focus on the HyperVel main application

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
1. Ensure all team members are aware of the single application structure
2. Focus all development efforts on the main HyperVel application
3. Update any external references to reflect the single application

## Timeline
- **Completed**: Complete removal of web-sch-12 directory

## Contact
For questions about this deprecation, please refer to the updated documentation or contact the development team.