# Project Structure Documentation

## Overview

This repository contains a single application:

1. **Main Application** (root directory) - **ACTIVE**: HyperVel framework based

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

## Development Guidelines

### For Contributors
- Always work on the **main application** (root directory)
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

### Phase 3: Consolidation (COMPLETED)
- [x] No feature migration needed (main app is more comprehensive)
- [x] Archive or remove web-sch-12 directory completely
- [x] Update CI/CD pipelines to focus on main application

## Decision Matrix

| Factor | Main App (HyperVel) | Web-sch-12 (Laravel) |
|--------|-------------------|---------------------|
| Performance | High (Swoole coroutines) | Standard |
| Architecture | Modern with coroutine support | Traditional Laravel |
| Features | More comprehensive | N/A (Removed) |
| Activity | Active development | N/A (Removed) |
| Recommendation | Primary application | **REMOVED** |

## Conclusion

The **main application (root directory)** is the **ONLY** supported application for all development efforts. The legacy web-sch-12 directory has been **COMPLETELY REMOVED** to eliminate architectural confusion and simplify maintenance.

All new features and bug fixes must be implemented in the main HyperVel application.