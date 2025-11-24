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
- Always work on the **main application** (root directory) unless specifically instructed otherwise
- The main application uses HyperVel framework which is compatible with Laravel concepts
- Follow PSR-12 coding standards
- Use feature branches for all development work

### Architecture Decisions
- The HyperVel application was chosen for its performance benefits with Swoole
- The modular approach allows for scalable development
- API-first design enables frontend flexibility

## Conclusion

The **main application (root directory)** is the **ONLY** supported application for all development efforts. The repository now contains a single application focused on the HyperVel framework to eliminate confusion and reduce maintenance overhead.