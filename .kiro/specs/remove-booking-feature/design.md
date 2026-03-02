# Design Document: Remove Booking Feature

## Overview

This design document specifies the technical approach for safely removing the booking feature from the Bardiya Eco Friendly homestay project. The booking functionality is no longer needed and must be completely removed from the codebase while ensuring the rest of the application continues to function properly.

The removal encompasses:
- API endpoints in the `api/bookings/` directory
- Routing configuration in `public/index.php`
- Database table and schema definitions in `database.sql`
- Email templates in `install-email-system.php`
- Documentation and code comments referencing bookings

The design prioritizes safety and data integrity, ensuring that removing the booking feature does not impact other features such as homestays, packages, gallery, and contact functionality.

## Architecture

### Current System Structure

The Bardiya Eco Friendly application follows a simple PHP-based REST API architecture:

```
bardia-eco-friendly/
├── api/
│   ├── bookings/          # TO BE REMOVED
│   │   ├── create.php
│   │   ├── list.php
│   │   ├── update.php
│   │   └── cancel.php
│   ├── homestays/         # PRESERVE
│   ├── packages/          # PRESERVE
│   ├── gallery-images/    # PRESERVE
│   └── contact-submissions/ # PRESERVE
├── public/
│   └── index.php          # Router - MODIFY
├── database.sql           # Schema - MODIFY
└── install-email-system.php # Email setup - MODIFY
```

### Dependency Analysis

The bookings feature has the following dependencies:

1. **Database Dependencies**:
   - `bookings` table has a foreign key to `homestays.id` with CASCADE delete
   - No other tables reference the `bookings` table
   - Removing the `bookings` table will not affect `homestays` table data

2. **API Dependencies**:
   - Booking endpoints are independent and not called by other endpoints
   - No other API endpoints depend on booking functionality

3. **Email Dependencies**:
   - Two email templates reference booking functionality:
     - `booking_enquiry_response`
     - `booking_confirmation`
   - These templates are not used by other features

### Removal Strategy

The removal will follow a layered approach:

1. **Application Layer**: Remove API endpoint files
2. **Routing Layer**: Remove route mappings
3. **Data Layer**: Remove database table and schema definitions
4. **Integration Layer**: Remove email templates
5. **Documentation Layer**: Remove references in comments and documentation

This order ensures that the application remains in a consistent state throughout the removal process.

## Components and Interfaces

### 1. API Endpoint Removal

**Files to Delete**:
- `api/bookings/create.php` - Creates new booking records
- `api/bookings/list.php` - Lists all bookings with homestay details
- `api/bookings/update.php` - Updates existing booking records
- `api/bookings/cancel.php` - Cancels bookings (soft delete)

**Directory to Delete**:
- `api/bookings/` - After all files are removed

**Impact**: After removal, requests to `/api/bookings/*` will return 404 errors (handled by the router).

### 2. Router Configuration Modification

**File**: `public/index.php`

**Routes to Remove**:
```php
'/api/bookings/list'     => $apiDir . '/bookings/list.php',
'/api/bookings/create'   => $apiDir . '/bookings/create.php',
'/api/bookings/update'   => $apiDir . '/bookings/update.php',
'/api/bookings/cancel'   => $apiDir . '/bookings/cancel.php',
```

**Comments to Remove**:
- The "// Bookings" section comment

**Preserved Routes**: All other routes (homestays, packages, gallery, contact, etc.) remain unchanged.

### 3. Database Schema Modification

**File**: `database.sql`

**Elements to Remove**:

1. **DROP TABLE Statement**:
```sql
DROP TABLE IF EXISTS `bookings`;
```

2. **CREATE TABLE Statement**:
```sql
CREATE TABLE `bookings` (
    -- entire table definition
) ENGINE=InnoDB ...;
```

3. **Sample Data INSERT Statements**:
```sql
INSERT INTO `bookings` (...) VALUES (...);
```

4. **Reference Queries** (in comments section):
   - Queries involving bookings table
   - JOIN queries with bookings
   - Booking count and revenue queries

5. **Comments**:
   - Section header comments for bookings table
   - Inline comments referencing booking functionality

**Preserved Elements**:
- `homestays` table definition (parent table)
- All other table definitions
- Foreign key constraints on other tables

**Schema Comment Update**: Update the database schema header comment to reflect the new table count (removing bookings from the count).

### 4. Email Template Removal

**File**: `install-email-system.php`

**Templates to Remove**:

1. **booking_enquiry_response**:
   - Subject: "Re: Your Enquiry - Bardiya Eco Friendly"
   - Used for responding to booking enquiries

2. **booking_confirmation**:
   - Subject: "Booking Confirmed - Bardiya Eco Friendly"
   - Used for confirming bookings

**Preserved Templates**:
- `general_enquiry_response` - General enquiries (not booking-specific)
- Any other non-booking templates

**Implementation**: Remove the template array entries from the `$templates` array in the installation script.

### 5. Database Migration Script

**Purpose**: Provide a clean migration path for existing databases that have the bookings table.

**Migration Script** (`migration_remove_bookings.sql`):
```sql
-- Migration: Remove Bookings Feature
-- Date: [Current Date]
-- Description: Safely removes the bookings table and all related data

-- Drop bookings table (foreign key constraints will be automatically removed)
DROP TABLE IF EXISTS `bookings`;

-- Verification query (should return 0 rows)
-- SELECT COUNT(*) FROM information_schema.tables 
-- WHERE table_schema = 'bardiya_eco_friendly' 
-- AND table_name = 'bookings';
```

**Safety Features**:
- Uses `IF EXISTS` clause to prevent errors if table doesn't exist
- Foreign key constraints are automatically removed due to CASCADE behavior
- Does not affect homestays table or its data
- Idempotent (can be run multiple times safely)

## Data Models

### Removed Data Model

**Bookings Table** (TO BE REMOVED):
```
bookings
├── id (INT, PK, AUTO_INCREMENT)
├── homestay_id (INT, FK → homestays.id)
├── guest_name (VARCHAR(255))
├── guest_email (VARCHAR(255))
├── guest_phone (VARCHAR(50))
├── check_in (DATE)
├── check_out (DATE)
├── guests_count (INT)
├── total_price (DECIMAL(10,2))
├── status (VARCHAR(50))
└── created_at (DATETIME)
```

**Foreign Key Constraint** (TO BE REMOVED):
- `fk_bookings_homestay`: `bookings.homestay_id` → `homestays.id`
- ON DELETE CASCADE
- ON UPDATE CASCADE

### Preserved Data Models

**Homestays Table** (PRESERVED):
- Structure remains unchanged
- Data remains unchanged
- No foreign keys reference bookings table
- Continues to function independently

**Other Tables** (PRESERVED):
- users
- pages
- packages
- package_categories
- package_features
- gallery_categories
- gallery_images
- contact_methods
- contact_submissions
- social_links
- email_history
- email_templates

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system-essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Property 1: Non-Booking API Endpoints Remain Functional

*For any* API endpoint that is not part of the booking feature (homestays, packages, gallery, contact, etc.), that endpoint should continue to function correctly and return successful responses after the booking feature is removed.

**Validates: Requirements 6.1, 6.2, 6.3, 6.4**

### Property 2: Database Schema Integrity

*For any* table in the database schema after removal, there should be no foreign key constraints that reference the removed bookings table.

**Validates: Requirements 3.5, 7.2**

### Property 3: Homestays Table Preservation

*For any* homestay record in the database, removing the bookings table should not affect the homestay data or the homestays table structure.

**Validates: Requirements 3.4, 7.3**

### Property 4: Booking Reference Removal from Comments

*For any* code file or documentation file in the project, there should be no comments or documentation that reference booking functionality after the removal.

**Validates: Requirements 4.1, 4.4**

### Property 5: Non-Booking Email Templates Preserved

*For any* email template that is not booking-related, that template should remain unchanged and functional after the booking templates are removed.

**Validates: Requirements 5.3**

### Property 6: Schema File Executability

*For any* fresh database, executing the modified database.sql file should successfully create all remaining tables without errors.

**Validates: Requirements 6.5**

### Property 7: Migration Script Idempotency

*For any* database state (whether bookings table exists or not), executing the migration script should complete successfully without errors.

**Validates: Requirements 7.4**

## Error Handling

### File Deletion Errors

**Scenario**: File or directory cannot be deleted due to permissions or locks.

**Handling**:
- Check file permissions before deletion
- Provide clear error messages indicating which file failed
- Suggest manual deletion if automated deletion fails
- Verify all files are deleted before proceeding to next step

### Database Migration Errors

**Scenario**: Migration script fails due to database connection issues or permission problems.

**Handling**:
- Use `IF EXISTS` clause to prevent errors if table doesn't exist
- Wrap migration in transaction if possible (DDL transactions are limited in MySQL)
- Provide rollback instructions if migration fails mid-way
- Log all database errors with detailed messages

### Route Configuration Errors

**Scenario**: Syntax errors introduced while modifying router configuration.

**Handling**:
- Validate PHP syntax after modification
- Test router with sample requests to all preserved endpoints
- Keep backup of original router configuration
- Provide clear error messages if routing fails

### Email Template Removal Errors

**Scenario**: Removing templates breaks the email system installation script.

**Handling**:
- Validate PHP syntax after template removal
- Ensure array structure remains valid
- Test installation script after modification
- Verify non-booking templates still install correctly

## Testing Strategy

### Dual Testing Approach

This feature removal requires both unit tests and property-based tests to ensure comprehensive coverage:

- **Unit tests**: Verify specific examples, edge cases, and concrete removal steps
- **Property tests**: Verify universal properties across all affected components

Together, these approaches provide comprehensive coverage where unit tests catch concrete bugs in specific removal steps, and property tests verify general correctness across the entire system.

### Unit Testing

**Test Framework**: PHPUnit

**Test Categories**:

1. **File Deletion Tests**:
   - Verify `api/bookings/create.php` is deleted
   - Verify `api/bookings/list.php` is deleted
   - Verify `api/bookings/update.php` is deleted
   - Verify `api/bookings/cancel.php` is deleted
   - Verify `api/bookings/` directory is deleted

2. **Router Configuration Tests**:
   - Verify `/api/bookings/list` route is removed
   - Verify `/api/bookings/create` route is removed
   - Verify `/api/bookings/update` route is removed
   - Verify `/api/bookings/cancel` route is removed
   - Verify booking section comment is removed
   - Verify requests to booking endpoints return 404

3. **Database Schema Tests**:
   - Verify `DROP TABLE IF EXISTS bookings` is removed from schema
   - Verify `CREATE TABLE bookings` is removed from schema
   - Verify `INSERT INTO bookings` statements are removed
   - Verify booking-related queries are removed from reference section
   - Verify schema comment count is updated

4. **Email Template Tests**:
   - Verify `booking_enquiry_response` template is removed
   - Verify `booking_confirmation` template is removed
   - Verify `general_enquiry_response` template is preserved

5. **Migration Script Tests**:
   - Verify migration script contains `DROP TABLE IF EXISTS bookings`
   - Verify migration script executes successfully on database with bookings table
   - Verify migration script executes successfully on database without bookings table

### Property-Based Testing

**Test Framework**: PHPUnit with custom property test helpers (minimum 100 iterations per test)

**Property Test Configuration**:
- Each test runs minimum 100 iterations with varied inputs
- Each test references its design document property using comment tags
- Tag format: `// Feature: remove-booking-feature, Property {number}: {property_text}`

**Property Tests**:

1. **Non-Booking API Functionality** (Property 1):
   ```php
   // Feature: remove-booking-feature, Property 1: Non-Booking API Endpoints Remain Functional
   ```
   - Generate random requests to all non-booking endpoints
   - Verify each returns successful response (200 status)
   - Verify response structure matches expected format

2. **Database Schema Integrity** (Property 2):
   ```php
   // Feature: remove-booking-feature, Property 2: Database Schema Integrity
   ```
   - Query information_schema for all foreign key constraints
   - Verify none reference the bookings table
   - Test with various database states

3. **Homestays Table Preservation** (Property 3):
   ```php
   // Feature: remove-booking-feature, Property 3: Homestays Table Preservation
   ```
   - Generate random homestay records
   - Execute migration script
   - Verify all homestay data remains unchanged
   - Verify homestays table structure is intact

4. **Booking Reference Removal** (Property 4):
   ```php
   // Feature: remove-booking-feature, Property 4: Booking Reference Removal from Comments
   ```
   - Scan all code files for comments
   - Verify no comments contain booking-related keywords
   - Test with various file types (PHP, SQL, MD)

5. **Non-Booking Email Templates Preserved** (Property 5):
   ```php
   // Feature: remove-booking-feature, Property 5: Non-Booking Email Templates Preserved
   ```
   - Load all email templates from installation script
   - Verify non-booking templates are present and unchanged
   - Verify template structure is valid

6. **Schema File Executability** (Property 6):
   ```php
   // Feature: remove-booking-feature, Property 6: Schema File Executability
   ```
   - Execute modified database.sql on fresh database
   - Verify all tables are created successfully
   - Verify no SQL errors occur
   - Test with various MySQL/MariaDB versions

7. **Migration Script Idempotency** (Property 7):
   ```php
   // Feature: remove-booking-feature, Property 7: Migration Script Idempotency
   ```
   - Execute migration script multiple times
   - Verify no errors occur on repeated execution
   - Test with bookings table present and absent

### Integration Testing

**Test Scenarios**:

1. **Full System Test**:
   - Execute complete removal process
   - Test all preserved API endpoints
   - Verify database integrity
   - Verify email system functionality

2. **Migration Test**:
   - Start with database containing bookings data
   - Execute migration script
   - Verify bookings table is removed
   - Verify all other tables and data are preserved

3. **Fresh Installation Test**:
   - Execute modified database.sql on fresh database
   - Verify all tables are created
   - Verify no bookings table exists
   - Test all API endpoints

### Manual Verification Checklist

After automated tests pass, manually verify:

- [ ] All booking API files are deleted
- [ ] Booking routes return 404 errors
- [ ] Homestays API endpoints work correctly
- [ ] Packages API endpoints work correctly
- [ ] Gallery API endpoints work correctly
- [ ] Contact API endpoints work correctly
- [ ] Database schema file has no booking references
- [ ] Email templates file has no booking templates
- [ ] Migration script executes successfully
- [ ] Fresh database installation works correctly
- [ ] No booking references in code comments
- [ ] Documentation is updated

## Implementation Notes

### Execution Order

The removal should be executed in the following order to maintain system consistency:

1. **Backup**: Create backup of database and codebase
2. **Database Migration**: Execute migration script to remove bookings table from live database
3. **API Files**: Delete booking API endpoint files and directory
4. **Router**: Remove booking routes from public/index.php
5. **Schema File**: Remove booking definitions from database.sql
6. **Email Templates**: Remove booking templates from install-email-system.php
7. **Comments**: Remove booking references from all code comments
8. **Testing**: Execute full test suite to verify system integrity
9. **Documentation**: Update any remaining documentation

### Rollback Plan

If issues are discovered after removal:

1. **Database Rollback**: Restore database from backup
2. **Code Rollback**: Restore code from version control (git)
3. **Selective Restore**: If only specific components need restoration, use git to restore individual files

### Version Control

- Commit each major step separately for granular rollback capability
- Tag the commit before removal as `pre-booking-removal`
- Tag the commit after removal as `post-booking-removal`

### Communication

- Notify all stakeholders before executing removal
- Document the removal in CHANGELOG.md
- Update README.md to reflect removed functionality
- Update API documentation to remove booking endpoints
