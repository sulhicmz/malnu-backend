# Project Structure Documentation

## Overview

This repository previously contained two separate applications, but now has a clear primary application:

1. **Main Application** (root directory) - **ACTIVE**: HyperVel framework based
2. **Legacy Application** (`web-sch-12/` directory) - **FULLY DEPRECATED**: Laravel 12 with modular architecture

## Main Application (Root Directory)

### Framework
- **HyperVel** - A Laravel-style PHP framework with native coroutine support for ultra-high performance
- Based on Hyperf framework with Swoole support
- PHP 8.2+ required

### Architecture
- Traditional Laravel-style architecture adapted for Swoole
- Contains comprehensive school management modules:
  - AI Assistant
  - Analytics
  - Attendance Management
  - Career Development
  - Digital Library
  - E-Learning
  - E-Raport
  - Monetization
  - Online Exam
  - PPDB (Admission System)
  - Parent Portal
  - School Management

### Key Features
- High-performance with Swoole coroutines
- Comprehensive school management system
- API-first design
- Modern PHP architecture

## Web-Sch-12 Application (DEPRECATED)

### Status: **FULLY DEPRECATED - WILL BE REMOVED**

### Framework
- **Laravel 12** with modular architecture (DEPRECATED)
- Uses `nwidart/laravel-modules` package for modular development
- PHP 8.2+ required
- **NO LONGER MAINTAINED**

### Architecture
- Modular Laravel application structure
- Uses Laravel's built-in authentication
- Modular components for different features

### Modules (DEPRECATED)
- ERaport (Report Management)
- LaporanAnalitik (Analytical Reporting)
- ManajemenSekolah (School Management)
- SistemMonetisasi (Monetization System)

## Relationship Between Applications

### Current Status
- **Main application (HyperVel)** is the **ONLY** actively developed and supported application
- **web-sch-12 application is FULLY DEPRECATED** and will be removed in the next major release
- The main application has more comprehensive features than the legacy application
- **NO NEW DEVELOPMENT** should occur in the web-sch-12 application

### Recommendations

#### Immediate Actions
1. **Development Focus**: Direct **ALL** new development efforts to the main application (root directory)
2. **No New Features**: Do **NOT** add any new features to the web-sch-12 application
3. **Migration**: If you need functionality from web-sch-12, plan to implement it in the main application

#### Long-term Strategy
1. **Complete Removal**: The web-sch-12 directory will be completely removed
2. **Feature Migration**: Any critical features from web-sch-12 should be migrated to the main application if needed
3. **Repository Cleanup**: Remove the deprecated application entirely

## Development Guidelines

### For Contributors
- Always work on the **main application** (root directory) unless specifically instructed otherwise
- The main application uses HyperVel framework which is compatible with Laravel concepts
- Follow PSR-12 coding standards
- Use feature branches for all development work

### Architecture Decisions
- The HyperVel application was chosen for its performance benefits with Swoole
- The modular approach allows for scalable development
- API-first design enables frontend flexibility

## Migration/Consolidation Plan

### Phase 1: Assessment (COMPLETED)
- [x] Document current structure
- [x] Identify primary application
- [x] Determine that no features from web-sch-12 need to be migrated (main app is more comprehensive)

### Phase 2: Documentation (COMPLETED)
- [x] Update all documentation to reflect primary application
- [x] Create migration guide if needed
- [x] Update README files
- [x] Add deprecation notices to web-sch-12
- [x] Create DEPRECATED.md file in web-sch-12 directory
- [x] Update composer.json in web-sch-12 with deprecation markers

### Phase 3: Consolidation (Future)
- [x] No feature migration needed (main app is more comprehensive)
- [ ] Archive or remove web-sch-12 directory completely
- [ ] Update CI/CD pipelines to focus on main application

## Decision Matrix

| Factor | Main App (HyperVel) | Web-sch-12 (Laravel) |
|--------|-------------------|---------------------|
| Performance | High (Swoole coroutines) | Standard |
| Architecture | Modern with coroutine support | Traditional Laravel |
| Features | More comprehensive | Limited |
| Activity | Active development | Fully deprecated |
| Recommendation | Primary application | **FULLY DEPRECATED - WILL BE REMOVED** |

## Conclusion

The **main application (root directory)** is the **ONLY** supported application for all development efforts. The web-sch-12 directory is **FULLY DEPRECATED** and will be completely removed in the next major release.

**CRITICAL**: All new features and bug fixes must be implemented in the main HyperVel application. No work should be done in the web-sch-12 application.