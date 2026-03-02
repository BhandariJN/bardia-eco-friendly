# Implementation Plan: Remove Booking Feature

## Overview

This plan outlines the steps to safely remove the booking feature from the Bardiya Eco Friendly homestay project. The removal includes API endpoints, routing configuration, database schema, email templates, and all references to booking functionality. Each step is designed to maintain system integrity and ensure all other features (homestays, packages, gallery, contact) continue to function properly.

## Tasks

- [x] 1. Create database migration script
  - Create `migration_remove_bookings.sql` with DROP TABLE statement
  - Use `IF EXISTS` clause for idempotent execution
  - Include verification query in comments
  - _Requirements: 7.1, 7.4_

- [ ]* 1.1 Write property test for migration script idempotency
  - **Property 7: Migration Script Idempotency**
  - **Validates: Requirements 7.4**
  - Test migration executes successfully with and without bookings table present
  - Minimum 100 iterations with varied database states
  - _Requirements: 7.4_

- [x] 2. Delete booking API endpoint files
  - [x] 2.1 Delete `api/bookings/create.php` file
    - _Requirements: 1.1_
  
  - [x] 2.2 Delete `api/bookings/list.php` file
    - _Requirements: 1.2_
  
  - [x] 2.3 Delete `api/bookings/update.php` file
    - _Requirements: 1.3_
  
  - [x] 2.4 Delete `api/bookings/cancel.php` file
    - _Requirements: 1.4_
  
  - [x] 2.5 Delete `api/bookings/` directory
    - Verify directory is empty before deletion
    - _Requirements: 1.5_

- [ ]* 2.6 Write unit tests for file deletions
  - Verify each booking API file is deleted
  - Verify bookings directory is deleted
  - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5_

- [x] 3. Remove booking routes from router configuration
  - [x] 3.1 Remove booking route mappings from `public/index.php`
    - Remove `/api/bookings/list` route
    - Remove `/api/bookings/create` route
    - Remove `/api/bookings/update` route
    - Remove `/api/bookings/cancel` route
    - Remove "// Bookings" section comment
    - _Requirements: 2.1, 2.2, 2.3, 2.4, 2.5_

- [ ]* 3.2 Write unit tests for route removal
  - Verify booking routes are removed from router array
  - Verify booking section comment is removed
  - Verify requests to booking endpoints return 404
  - _Requirements: 2.1, 2.2, 2.3, 2.4, 2.5_

- [x] 4. Checkpoint - Verify API layer changes
  - Ensure all tests pass, ask the user if questions arise.

- [x] 5. Remove bookings table from database schema
  - [x] 5.1 Remove bookings table definitions from `database.sql`
    - Remove `DROP TABLE IF EXISTS bookings` statement
    - Remove `CREATE TABLE bookings` statement
    - Remove all `INSERT INTO bookings` statements
    - _Requirements: 3.1, 3.2, 3.3_
  
  - [x] 5.2 Remove booking references from schema comments
    - Remove booking-related queries from reference section
    - Update schema header comment to reflect new table count
    - Remove inline comments referencing bookings
    - _Requirements: 4.1, 4.2, 4.3_

- [ ]* 5.3 Write unit tests for schema modifications
  - Verify `DROP TABLE IF EXISTS bookings` is removed
  - Verify `CREATE TABLE bookings` is removed
  - Verify `INSERT INTO bookings` statements are removed
  - Verify booking queries are removed from reference section
  - _Requirements: 3.1, 3.2, 3.3, 4.1, 4.2_

- [ ]* 5.4 Write property test for database schema integrity
  - **Property 2: Database Schema Integrity**
  - **Validates: Requirements 3.5, 7.2**
  - Query information_schema for foreign key constraints
  - Verify none reference the bookings table
  - Minimum 100 iterations with varied database states
  - _Requirements: 3.5, 7.2_

- [ ]* 5.5 Write property test for schema file executability
  - **Property 6: Schema File Executability**
  - **Validates: Requirements 6.5**
  - Execute modified database.sql on fresh database
  - Verify all tables are created successfully
  - Verify no SQL errors occur
  - Minimum 100 iterations
  - _Requirements: 6.5_

- [x] 6. Remove booking email templates
  - [x] 6.1 Remove booking templates from `install-email-system.php`
    - Remove `booking_enquiry_response` template
    - Remove `booking_confirmation` template
    - Preserve all non-booking templates
    - _Requirements: 5.1, 5.2, 5.3_

- [ ]* 6.2 Write unit tests for email template removal
  - Verify `booking_enquiry_response` template is removed
  - Verify `booking_confirmation` template is removed
  - Verify `general_enquiry_response` template is preserved
  - _Requirements: 5.1, 5.2, 5.3_

- [ ]* 6.3 Write property test for non-booking email templates
  - **Property 5: Non-Booking Email Templates Preserved**
  - **Validates: Requirements 5.3**
  - Load all email templates from installation script
  - Verify non-booking templates are present and unchanged
  - Minimum 100 iterations
  - _Requirements: 5.3_

- [x] 7. Remove booking references from code comments
  - [x] 7.1 Scan and remove booking references from all code files
    - Search for booking-related keywords in comments
    - Remove or update comments referencing booking functionality
    - _Requirements: 4.4_

- [ ]* 7.2 Write property test for booking reference removal
  - **Property 4: Booking Reference Removal from Comments**
  - **Validates: Requirements 4.1, 4.4**
  - Scan all code files for comments containing booking keywords
  - Verify no booking references remain
  - Test with various file types (PHP, SQL, MD)
  - Minimum 100 iterations
  - _Requirements: 4.1, 4.4_

- [x] 8. Checkpoint - Verify data layer changes
  - Ensure all tests pass, ask the user if questions arise.

- [x] 9. Verify system integrity
  - [-] 9.1 Test all preserved API endpoints
    - Test homestays API endpoints
    - Test packages API endpoints
    - Test gallery API endpoints
    - Test contact API endpoints
    - _Requirements: 6.1, 6.2, 6.3, 6.4_

- [ ]* 9.2 Write property test for non-booking API functionality
  - **Property 1: Non-Booking API Endpoints Remain Functional**
  - **Validates: Requirements 6.1, 6.2, 6.3, 6.4**
  - Generate random requests to all non-booking endpoints
  - Verify each returns successful response
  - Minimum 100 iterations with varied inputs
  - _Requirements: 6.1, 6.2, 6.3, 6.4_

- [ ]* 9.3 Write property test for homestays table preservation
  - **Property 3: Homestays Table Preservation**
  - **Validates: Requirements 3.4, 7.3**
  - Generate random homestay records
  - Execute migration script
  - Verify all homestay data remains unchanged
  - Minimum 100 iterations
  - _Requirements: 3.4, 7.3_

- [ ]* 9.4 Write integration tests for full system verification
  - Test complete removal process end-to-end
  - Test migration on database with bookings data
  - Test fresh installation with modified schema
  - _Requirements: 6.1, 6.2, 6.3, 6.4, 6.5_

- [ ] 10. Final checkpoint - Complete verification
  - Ensure all tests pass, ask the user if questions arise.
  - Verify manual checklist items from design document
  - Confirm all booking functionality is removed
  - Confirm all other features remain functional

## Notes

- Tasks marked with `*` are optional and can be skipped for faster implementation
- Each task references specific requirements for traceability
- Property tests validate universal correctness properties (minimum 100 iterations each)
- Unit tests validate specific examples and edge cases
- Integration tests verify end-to-end system behavior
- Checkpoints ensure incremental validation at key milestones
- Execute tasks in order to maintain system consistency
- Create backups before executing removal steps
- Use version control to commit each major step separately
