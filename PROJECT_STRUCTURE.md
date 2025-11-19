# Project Structure Documentation

## Overview

This repository contains two separate applications that serve different purposes within the overall system:

1. **Main Application** (root directory) - HyperVel framework based
2. **Legacy/Alternative Application** (`web-sch-12/` directory) - Laravel 12 with modular architecture

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

## Web-Sch-12 Application

### Framework
- **Laravel 12** with modular architecture
- Uses `nwidart/laravel-modules` package for modular development
- PHP 8.2+ required

### Architecture
- Modular Laravel application structure
- Uses Laravel's built-in authentication
- Modular components for different features

### Modules
- AiLearning
- Career
- SistemMonetisasi (Monetization System)
- UjianOnline (Online Exam)

## Relationship Between Applications

### Current Status
- Both applications appear to serve similar purposes (school management systems)
- The main application (HyperVel) seems to be the more comprehensive and actively developed version
- The web-sch-12 application appears to be either legacy code or an alternative implementation

### Recommendations

#### Short-term Actions
1. **Documentation**: Clearly document which application is the primary one
2. **Configuration**: Consolidate shared configurations where possible
3. **Development Focus**: Direct all new development efforts to the primary application

#### Long-term Strategy
1. **Migration Plan**: If web-sch-12 is legacy, plan for its deprecation
2. **Feature Consolidation**: Migrate any unique features from web-sch-12 to the main application if needed
3. **Repository Cleanup**: Remove or archive the non-primary application

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

### Phase 1: Assessment (Current)
- [x] Document current structure
- [x] Identify primary application
- [ ] Determine if any features from web-sch-12 need to be migrated

### Phase 2: Documentation
 - [x] Update all documentation to reflect primary application
 - [x] Create migration guide if needed
 - [x] Update README files

### Phase 3: Consolidation (Future)
- [ ] Migrate any unique features from web-sch-12 if needed
- [ ] Archive or remove web-sch-12 directory if no longer needed
- [ ] Update CI/CD pipelines

## Decision Matrix

| Factor | Main App (HyperVel) | Web-sch-12 (Laravel) |
|--------|-------------------|---------------------|
| Performance | High (Swoole coroutines) | Standard |
| Architecture | Modern with coroutine support | Traditional Laravel |
| Features | More comprehensive | Limited |
| Activity | More recent commits | Less active |
| Recommendation | Primary application | Consider for deprecation |

## Conclusion

The **main application (root directory)** should be considered the primary application for all development efforts. The web-sch-12 directory appears to be either a legacy implementation or an alternative approach that should be evaluated for deprecation.

All new features and bug fixes should be implemented in the main HyperVel application.