# Facility Creation Testing

This directory contains comprehensive tests for facility creation functionality in the HR Management System.

## Test Files

### 1. `FacilityCreationTest.php` - Unit Tests
This file contains unit tests for the basic facility model functionality that can run with SQLite in-memory database.

**Tests covered:**
- Facility record creation
- Attribute validation and types
- Subdomain uniqueness constraints
- Fillable attributes verification
- Translatable trait functionality
- Soft delete functionality
- Relationship methods verification
- Observer registration
- Multilingual name support

**Running the tests:**
```bash
php artisan test tests/Feature/FacilityCreationTest.php
```

### 2. `FacilityIntegrationTest.php` - Integration Tests
This file contains integration tests that test the complete multi-tenant facility creation workflow, including tenant database creation, migrations, and data seeding.

**Tests covered:**
- Complete facility creation workflow
- Tenant database creation
- Tenant migrations execution
- Default data seeding
- Roles and permissions setup
- Default users and employees creation
- Tenant data isolation
- Database switching functionality
- Error handling during tenant creation

**Requirements:**
- MySQL database (cannot run with SQLite)
- Proper database configuration

**Running the tests:**
```bash
# Make sure you have a MySQL test database configured
php artisan test tests/Feature/FacilityIntegrationTest.php

# Or with a specific testing environment
php artisan test tests/Feature/FacilityIntegrationTest.php --env=testing-mysql
```

## What Gets Tested

When you create a facility, the system should:

1. **Create Facility Record**: Store the facility in the master database with proper attributes
2. **Generate UUID**: Auto-generate a unique UUID for the facility
3. **Handle Translations**: Support multilingual names (en, ar, ku)
4. **Create Tenant Database**: Automatically create a separate database for the tenant
5. **Run Migrations**: Execute all necessary migrations on the tenant database
6. **Seed Default Data**: 
   - Create default branch (HQ)
   - Create departments (Finance, HR, IT)
   - Create positions (Manager, Employee)
   - Create shifts, leave types, banks, machines, etc.
   - Set up system settings
   - Create holidays
7. **Set Up Security**:
   - Create roles (SuperAdmin, Supervisor, Employee)
   - Create permissions for all resources
   - Assign permissions to roles
8. **Create Default Users**:
   - Admin user (admin@example.com) with SuperAdmin role
   - Default employee record (employee@example.com)

## Test Configuration

### For SQLite (Unit Tests)
The unit tests use SQLite in-memory database as configured in `phpunit.xml`. The tenant database creation is mocked to avoid MySQL dependency.

### For MySQL (Integration Tests)
Create a `.env.testing-mysql` file with MySQL configuration:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hrms_test
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

## Running All Tests
```bash
# Run all facility tests
php artisan test tests/Feature/FacilityCreationTest.php tests/Feature/FacilityIntegrationTest.php

# Run with verbose output
php artisan test --testsuite=Feature --filter=Facility

# Run specific test method
php artisan test --filter=test_complete_facility_creation_workflow
```

## Test Cleanup
Integration tests automatically clean up created tenant databases after each test to prevent conflicts.

## Expected Results
When all tests pass, you can be confident that:
- Facility creation works correctly
- Tenant databases are properly created and isolated
- All required default data is seeded
- Security roles and permissions are set up
- The multi-tenant architecture functions as expected

## Troubleshooting

### Common Issues:
1. **SQLite constraint violations**: Usually due to missing UUID generation - check if observers are properly registered
2. **MySQL connection errors**: Verify database configuration and ensure test database exists
3. **Timeout errors**: Tenant database creation might be slow - increase timeout in test configuration
4. **Permission errors**: Ensure the database user has CREATE DATABASE privileges for integration tests

### Debug Tips:
- Use `--stop-on-failure` flag to stop at first failing test
- Check Laravel logs for detailed error information
- Verify database connections and permissions
- Ensure all required PHP extensions are installed