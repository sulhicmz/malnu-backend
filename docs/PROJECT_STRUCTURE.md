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



## Application Status

### Current Status
- **Main application (HyperVel)** is the **ONLY** actively developed and supported application
- The main application has comprehensive features for school management
- **ALL DEVELOPMENT** should occur in the main application

### Recommendations

#### Immediate Actions
1. **Development Focus**: Direct **ALL** new development efforts to the main application (root directory)

#### Long-term Strategy
1. **Repository Focus**: The repository now focuses entirely on the main application
2. **Repository Cleanup**: Removed deprecated application for architectural clarity

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

## Architecture Plan

### Phase 1: Consolidation (COMPLETED)
- [x] Remove deprecated application directory
- [x] Consolidate to single main application
- [x] Update all documentation to reflect single application structure

## Decision Focus

The repository now focuses entirely on the main application:

| Factor | Main App (HyperVel) |
|--------|-------------------|
| Performance | High (Swoole coroutines) |
| Architecture | Modern with coroutine support |
| Features | Comprehensive school management |
| Activity | Active development |
| Recommendation | Primary and only application |

## Conclusion

The **main application (root directory)** is the **ONLY** supported application for all development efforts.

**CRITICAL**: All new features and bug fixes must be implemented in the main HyperVel application.