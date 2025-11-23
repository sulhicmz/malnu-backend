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

### Status: **FULLY DEPRECATED - WILL BE REMOVED**

### Purpose:
- Legacy alternative implementation
- Modular Laravel architecture using nwidart/laravel-modules
- Contains subset of features compared to main application
- **NO LONGER MAINTAINED OR SUPPORTED**

### Technology Stack:
- Laravel 12
- PHP 8.2+
- Modular architecture approach
- Traditional synchronous processing

### Features:
- ERaport (Report Management)
- LaporanAnalitik (Analytical Reporting)
- ManajemenSekolah (School Management)
- SistemMonetisasi (Monetization System)
- Limited compared to main application

### Development Status:
- **NOT ACTIVELY DEVELOPED**
- **FULLY DEPRECATED**
- **WILL BE REMOVED in the next major release**
- **NO NEW FEATURES OR BUG FIXES**

## Decision and Recommendation

### Primary Application for Development: **HyperVel (Main Directory)**

All development efforts must focus on the main HyperVel application for the following reasons:

1. **Performance**: HyperVel with Swoole provides superior performance
2. **Completeness**: Main application has more comprehensive features
3. **Activity**: Only actively maintained application
4. **Architecture**: Modern coroutine-based architecture for scalability
5. **Future**: Only application that will continue to exist

### Future of web-sch-12 Directory:

The web-sch-12 directory will be **COMPLETELY REMOVED** in the next major release:

1. **Archive**: Currently archived with deprecation notices
2. **Migration**: Any critical features should be migrated to main application (if needed)
3. **Removal**: Directory will be deleted entirely from repository

## Action Items

### Immediate:
- [x] Document the status of each application
- [x] Clarify which application is primary for development
- [x] Add comprehensive deprecation notices to web-sch-12
- [x] Update all documentation to reflect deprecation

### Short-term:
- [x] Update all documentation to reflect the primary application
- [x] Ensure all team members are aware of the primary application
- [x] Complete deprecation of web-sch-12 directory
- [x] Add clear warnings about development restrictions

### Long-term:
- [ ] Remove web-sch-12 directory completely
- [ ] Ensure no dependencies exist on deprecated application
- [ ] Update deployment and CI/CD processes to focus solely on the primary application