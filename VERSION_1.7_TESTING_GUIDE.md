# Version 1.7 Testing Guide

## Changes Made

### 1. Address Fields Simplified
- **Removed**: Generic "Address" field
- **Kept**: "From Address" and "To Address" fields (now required)
- **Impact**: Both fields must be filled when creating/editing enquiries

### 2. House Size Field Removed
- **Removed from UI**: House size field no longer appears in forms
- **Database**: Column retained for backward compatibility
- **Impact**: Existing data is preserved but field is hidden

### 3. Stairs Fields Cleanup
- **Removed**: Legacy "stairs" field
- **Kept**: "Stairs Involved (From Address)" and "Stairs Involved (To Address)"
- **Impact**: Only the two specific stairs fields remain

### 4. Fixed "Failed to create enquiry" Error
- **Issue**: Error occurred when creating or editing enquiries
- **Fix**: Updated validation to use new required fields (from/to addresses)
- **Impact**: Enquiry creation and editing should now work properly

## Testing Instructions

### Test 1: Create New Enquiry
1. Log into WordPress admin
2. Navigate to MF Enquiries
3. Click "+ Add New Enquiry" button
4. Fill in the form:
   - First Name: Test
   - Last Name: User
   - Email: test@example.com
   - Phone: 021234567
   - **From Address**: 123 Main St, Auckland (REQUIRED)
   - **To Address**: 456 Park Ave, Wellington (REQUIRED)
   - Suburb: Central
   - Number of Bedrooms: 3
   - Stairs (From Address): Yes - 1 floor
   - Stairs (To Address): No
   - Contact Source: Website Form
5. Click Submit
6. **Expected Result**: Enquiry should be created successfully without any errors

### Test 2: Edit Existing Enquiry
1. In the MF Enquiries list, find any enquiry
2. Click the "Edit" button
3. **Verify**: Form should show:
   - From Address field (populated if data exists)
   - To Address field (populated if data exists)
   - NO generic "Address" field
   - NO "House size" field
   - Only "Stairs (From Address)" and "Stairs (To Address)" fields
4. Make changes to the addresses
5. Click Submit
6. **Expected Result**: Changes should be saved successfully

### Test 3: View Enquiry in Admin Table
1. After creating/editing enquiries, view the admin table
2. **Verify**: Contact & Address column should display:
   - Customer name
   - Phone | Email
   - **From:** [from address]
   - **To:** [to address]
   - Suburb (if set)
3. House Details column should show:
   - Number of bedrooms
   - Total rooms
   - Stairs (From): [value]
   - Stairs (To): [value]
   - Notes (if set)
4. **No reference to**: generic address or house size

### Test 4: Required Field Validation
1. Try to create a new enquiry
2. Fill in name, email, phone but leave addresses blank
3. Click Submit
4. **Expected Result**: Error message "Please fill in all required fields"
5. Fill in From Address but not To Address
6. **Expected Result**: Same error message
7. Fill in both addresses
8. **Expected Result**: Enquiry creates successfully

## Version Information
- Plugin Version: 1.7
- Database Version: 1.7.0
- Release Date: 2026-01-04

## Files Changed
- `marcus-furniture-crm.php` - Version bumps and migration
- `includes/class-hs-crm-admin.php` - Form fields and validation
- `includes/class-hs-crm-database.php` - Insert/update logic
- `assets/js/scripts.js` - Form population and submission
- `readme.txt` - Changelog
- `CHANGELOG.md` - Detailed changelog
- `marcus-furniture-crm.zip` - Updated plugin package

## Rollback Instructions
If issues occur, you can rollback by:
1. Deactivating the plugin
2. Installing version 1.6 from backup
3. Reactivating the plugin

Note: No data will be lost as all database columns are retained.
