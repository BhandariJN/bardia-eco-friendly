# Requirements Document

## Introduction

This document specifies the requirements for removing the booking feature from the Bardiya Eco Friendly homestay project. The booking functionality is no longer needed and must be completely removed from the codebase, including database tables, API endpoints, routing configuration, and all references. The removal must be performed safely to ensure the rest of the application (homestays, packages, gallery, contact, etc.) continues to function properly.

## Glossary

- **Booking_System**: The collection of database tables, API endpoints, and code that handles guest reservations for homestays
- **Bookings_Table**: The MySQL database table storing booking records with foreign key to homestays table
- **Booking_API_Endpoints**: The REST API endpoints in api/bookings/ directory (create.php, list.php, update.php, cancel.php)
- **Router**: The routing configuration in public/index.php that maps URLs to API endpoint files
- **Database_Schema**: The database.sql file containing table definitions and seed data
- **Email_Templates**: Pre-configured email templates that may reference booking functionality
- **Foreign_Key_Constraint**: The database constraint linking bookings.homestay_id to homestays.id with CASCADE delete

## Requirements

### Requirement 1: Remove Booking API Endpoints

**User Story:** As a system administrator, I want all booking API endpoints removed, so that the booking functionality is no longer accessible via the API.

#### Acceptance Criteria

1. THE System SHALL delete the api/bookings/create.php file
2. THE System SHALL delete the api/bookings/list.php file
3. THE System SHALL delete the api/bookings/update.php file
4. THE System SHALL delete the api/bookings/cancel.php file
5. THE System SHALL delete the api/bookings/ directory after all files are removed

### Requirement 2: Remove Booking Routes

**User Story:** As a system administrator, I want booking routes removed from the router configuration, so that requests to booking endpoints return 404 errors.

#### Acceptance Criteria

1. THE System SHALL remove the booking route mapping for /api/bookings/list from public/index.php
2. THE System SHALL remove the booking route mapping for /api/bookings/create from public/index.php
3. THE System SHALL remove the booking route mapping for /api/bookings/update from public/index.php
4. THE System SHALL remove the booking route mapping for /api/bookings/cancel from public/index.php
5. THE System SHALL remove the booking section comment from public/index.php

### Requirement 3: Remove Bookings Database Table

**User Story:** As a database administrator, I want the bookings table removed from the database schema, so that booking data is no longer stored.

#### Acceptance Criteria

1. THE System SHALL remove the CREATE TABLE statement for bookings from database.sql
2. THE System SHALL remove the INSERT statements for sample booking data from database.sql
3. THE System SHALL remove the DROP TABLE IF EXISTS statement for bookings from database.sql
4. THE System SHALL preserve the homestays table definition without modification
5. WHEN the bookings table is removed, THE System SHALL ensure no orphaned foreign key references remain

### Requirement 4: Remove Booking References from Documentation

**User Story:** As a developer, I want booking references removed from documentation and comments, so that the codebase accurately reflects current functionality.

#### Acceptance Criteria

1. THE System SHALL remove booking-related comments from database.sql
2. THE System SHALL remove booking-related queries from the reference section in database.sql
3. THE System SHALL update the database schema comment count to reflect removed tables
4. WHERE booking functionality is mentioned in code comments, THE System SHALL remove those references

### Requirement 5: Remove Booking Email Templates

**User Story:** As a system administrator, I want booking-related email templates removed, so that the email system only contains relevant templates.

#### Acceptance Criteria

1. THE System SHALL remove the booking_enquiry_response template from install-email-system.php
2. THE System SHALL remove the booking_confirmation template from install-email-system.php
3. THE System SHALL preserve all non-booking email templates without modification

### Requirement 6: Verify System Integrity

**User Story:** As a quality assurance engineer, I want to verify that removing the booking feature does not break existing functionality, so that the application remains stable.

#### Acceptance Criteria

1. WHEN the booking feature is removed, THE System SHALL ensure the homestays API endpoints remain functional
2. WHEN the booking feature is removed, THE System SHALL ensure the packages API endpoints remain functional
3. WHEN the booking feature is removed, THE System SHALL ensure the gallery API endpoints remain functional
4. WHEN the booking feature is removed, THE System SHALL ensure the contact API endpoints remain functional
5. WHEN the database.sql file is executed, THE System SHALL create all remaining tables without errors

### Requirement 7: Clean Database Migration

**User Story:** As a database administrator, I want a clean migration path to remove the bookings table from existing databases, so that production systems can be updated safely.

#### Acceptance Criteria

1. IF the bookings table exists in the database, THEN THE System SHALL provide a DROP TABLE statement to remove it
2. WHEN the bookings table is dropped, THE Foreign_Key_Constraint SHALL be automatically removed due to CASCADE behavior
3. THE System SHALL ensure the DROP TABLE operation does not affect the homestays table data
4. THE System SHALL ensure the DROP TABLE operation completes successfully even if the table does not exist (using IF EXISTS clause)
