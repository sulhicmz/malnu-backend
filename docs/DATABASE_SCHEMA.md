# Database Schema Documentation

## Overview
This document describes the database schema for the malnu-backend school management system.

## Core Tables

### users
The main users table containing authentication and basic user information.
- **id**: UUID (Primary Key)
- **name**: User display name
- **username**: Unique username (50 chars)
- **email**: Unique email (100 chars)
- **password**: Hashed password
- **full_name**: User's full name (100 chars)
- **phone**: Phone number (20 chars, nullable)
- **avatar_url**: Profile picture URL (255 chars, nullable)
- **is_active**: Boolean, defaults to true
- **last_login_time**: Timestamp of last login (nullable)
- **last_login_ip**: IP address of last login (50 chars, nullable)
- **remember_token**: Authentication token (100 chars, nullable)
- **email_verified_at**: Email verification timestamp (nullable)
- **created_at/updated_at**: Timestamps

### roles & permissions
Role-based access control (RBAC) implementation:
- **roles**: id, name, guard_name
- **permissions**: id, name, guard_name
- **model_has_roles**: Links roles to users/models
- **model_has_permissions**: Links permissions to users/models
- **role_has_permissions**: Links permissions to roles

## School Management Tables

### parents
Parent/guardian information linked to users:
- **id**: UUID (Primary Key)
- **user_id**: Foreign key to users (unique)
- **occupation**: Parent's occupation (100 chars, nullable)
- **address**: Parent's address (text, nullable)

### teachers
Teacher information linked to users:
- **id**: UUID (Primary Key)
- **user_id**: Foreign key to users (unique)
- **nip**: Teacher identification number (20 chars, unique)
- **expertise**: Teaching specialization (100 chars, nullable)
- **join_date**: Date when teacher joined
- **status**: Teacher status (20 chars, defaults to 'active')

### students
Student information linked to users:
- **id**: UUID (Primary Key)
- **user_id**: Foreign key to users (unique)
- **nisn**: Student national identification number (20 chars, unique)
- **class_id**: Foreign key to classes (nullable)
- **parent_id**: Foreign key to parents (nullable)
- **birth_date**: Student's birth date (nullable)
- **birth_place**: Student's birth place (50 chars, nullable)
- **address**: Student's address (text, nullable)
- **enrollment_date**: Date of enrollment
- **status**: Student status (20 chars, defaults to 'active')

### staff
Staff information linked to users:
- **id**: UUID (Primary Key)
- **user_id**: Foreign key to users (unique)
- **position**: Staff position (100 chars)
- **department**: Staff department (100 chars, nullable)
- **join_date**: Date when staff joined
- **status**: Staff status (20 chars, defaults to 'active')

### classes
Class/grade information:
- **id**: UUID (Primary Key)
- **name**: Class name (50 chars)
- **level**: Grade level (20 chars)
- **homeroom_teacher_id**: Foreign key to teachers (nullable)
- **academic_year**: Academic year (9 chars)
- **capacity**: Maximum students (integer, nullable)

### subjects
Subject information:
- **id**: UUID (Primary Key)
- **code**: Subject code (20 chars, unique)
- **name**: Subject name (100 chars)
- **description**: Subject description (text, nullable)
- **credit_hours**: Credit hours (integer, nullable)

### class_subjects
Many-to-many relationship between classes and subjects:
- **id**: UUID (Primary Key)
- **class_id**: Foreign key to classes
- **subject_id**: Foreign key to subjects
- **teacher_id**: Foreign key to teachers (nullable)
- **schedule_info**: Schedule information (text, nullable)

### schedules
Class scheduling information:
- **id**: UUID (Primary Key)
- **class_subject_id**: Foreign key to class_subjects
- **day_of_week**: Day of week (small integer)
- **start_time**: Class start time
- **end_time**: Class end time
- **room**: Room number (50 chars, nullable)

### school_inventory
School asset management:
- **id**: UUID (Primary Key)
- **name**: Item name (100 chars)
- **category**: Item category (50 chars)
- **quantity**: Item quantity (integer)
- **location**: Storage location (100 chars, nullable)
- **condition**: Item condition (50 chars, nullable)
- **purchase_date**: Date of purchase (nullable)
- **last_maintenance**: Last maintenance date (nullable)

## Model Relationships

### User Model Relationships
- **parent()**: hasOne ParentOrtu
- **teacher()**: hasOne Teacher
- **student()**: hasOne Student
- **staff()**: hasOne Staff
- Plus many hasMany relationships for various modules

### ParentOrtu Model Relationships
- **user()**: belongsTo User
- **students()**: hasMany Student

### Teacher Model Relationships
- **user()**: belongsTo User
- **homeroomClasses()**: hasMany ClassModel
- **classSubjects()**: hasMany ClassSubject

### Student Model Relationships
- **user()**: belongsTo User
- **class()**: belongsTo ClassModel
- **parent()**: belongsTo ParentOrtu

## Migration Order
1. 2023_08_03_000000_create_users_table.php
2. 2025_05_18_002108_create_core_table.php (roles, permissions)
3. 2025_05_18_002538_create_school_management_table.php (school tables)
4. Other module-specific migrations

## Seeders
- **DatabaseSeeder**: Main seeder that calls all others
- **RoleSeeder**: Creates default roles
- **PermissionSeeder**: Creates default permissions
- **UserSeeder**: Creates default users with roles
- **SchoolManagementSeeder**: Creates school structure (classes, subjects, etc.)

## Notes
- All primary keys are UUIDs for better security and scalability
- All tables use the `datetimes()` method for created_at/updated_at timestamps
- Foreign key constraints ensure data integrity
- Indexes are added for frequently queried columns
- The system uses a soft-delete pattern through status fields rather than actual deletion