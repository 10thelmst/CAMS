# Changelog

All notable changes to CAMS will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2026-07-13

### Added
- Multiple role selection system for users (Admin, Superadmin, Employee)
- User roles table for many-to-many relationship between users and roles
- Checkbox-based role selection in user creation and editing
- Multiple role badges display in user management
- OWWA database integration for user authentication
- Auto-import functionality for OWWA users on first login
- Manual bulk import from OWWA database
- Database configuration file for CAMS and OWWA connections
- Migration script for user_roles table creation
- Import script for OWWA user synchronization
- Username and email authentication support
- OWWA-ECARES styled layouts for all role dashboards
- Sidebar collapse by default
- User panel in sidebar displaying username and roles
- Comprehensive README documentation
- Git version control initialization

### Changed
- Updated login form to accept both username and email
- Updated authentication check to support multiple roles
- Updated user management to display multiple role badges
- Updated all layout files with OWWA-ECARES styling
- Updated database schema to support multiple roles per user

### Security
- Password hashing for all users
- SQL injection prevention using prepared statements
- Session timeout implementation (30 minutes)
- Role-based access control with backward compatibility

### Fixed
- mysqli_stmt double close errors in create_user.php and edit_user.php
- Undefined variable $roles error in create_user.php
- Login redirect logic for multiple role support

## [Unreleased]

### Planned
- Change password functionality for users
- Enhanced user profile management
- Additional role permissions configuration
- Audit logging for user actions
