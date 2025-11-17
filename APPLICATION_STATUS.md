# Application Status and Purpose

## Primary Application: HyperVel (Main Directory)

### Status: **ACTIVE - Primary Development Target**

### Purpose:
- Main school management system backend
- High-performance application using Swoole coroutines
- Comprehensive feature set for educational institutions

### Technology Stack:
- HyperVel framework (Laravel-style with Swoole support)
- PHP 8.2+
- Swoole for asynchronous processing
- Modern architecture with comprehensive modules

### Features:
- Complete school management system
- AI Assistant integration
- E-Learning platform
- Online Exam system
- Digital Library
- PPDB (School Admission)
- Parent Portal
- Analytics and reporting

### Development Status:
- Actively maintained
- Primary focus for new feature development
- Current development team working on this application

## Secondary Application: Laravel (web-sch-12 Directory)

### Status: **PASSIVE - Under Evaluation for Deprecation**

### Purpose:
- Appears to be an alternative or legacy implementation
- Modular Laravel architecture using nwidart/laravel-modules
- Contains subset of features compared to main application

### Technology Stack:
- Laravel 12
- PHP 8.2+
- Modular architecture approach
- Traditional synchronous processing

### Features:
- Basic user management and authentication
- Some school-related modules (AiLearning, Career, etc.)
- Limited compared to main application

### Development Status:
- Not actively developed
- Under evaluation for deprecation
- May contain unique features that could be migrated

## Decision and Recommendation

### Primary Application for Development: **HyperVel (Main Directory)**

All development efforts should focus on the main HyperVel application for the following reasons:

1. **Performance**: HyperVel with Swoole provides superior performance
2. **Completeness**: Main application has more comprehensive features
3. **Activity**: More recent development activity in the main application
4. **Architecture**: Modern coroutine-based architecture for scalability

### Future of web-sch-12 Directory:

The web-sch-12 directory should be evaluated for one of the following actions:

1. **Archive**: If no unique features exist that aren't in the main application
2. **Migrate**: If there are unique features that should be incorporated into the main application
3. **Document**: If it serves a specific purpose that needs to be preserved

## Action Items

### Immediate:
- [x] Document the status of each application
- [x] Clarify which application is primary for development
- [ ] Review web-sch-12 for any unique features that should be migrated

### Short-term:
- [ ] Update all documentation to reflect the primary application
- [ ] Ensure all team members are aware of the primary application
- [ ] Plan for potential deprecation of web-sch-12 directory

### Long-term:
- [ ] Archive or remove web-sch-12 if not needed
- [ ] Consolidate any remaining functionality into the main application
- [ ] Update deployment and CI/CD processes to focus on the primary application