-- Database Initialization Script for Malnu School Management System
-- This script runs automatically when the MySQL container starts for the first time

-- Set character set and collation for the database
ALTER DATABASE malnu CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create database user with proper permissions
-- The user is already created by MySQL initialization, we just need to ensure proper permissions

FLUSH PRIVILEGES;
