# Version 2.5 Implementation Summary

## Overview
This release addresses three key issues related to Gravity Forms integration, move date visibility, and data cleanup on uninstall.

## Changes Made

### 1. Gravity Form Name Display ✅

**Problem**: Enquiries created from Gravity Forms were showing only the job type (e.g., "Moving House" or "Pickup/Delivery") but not the actual form name. This made it impossible to identify which specific Gravity Form an enquiry came from.

**Solution**:
- Added new database column `source_form_name` to store the Gravity Form title
- Created database migration `hs_crm_migrate_to_2_5_0()` to add the column to existing installations
- Updated Gravity Forms integration to capture and store the form title: `$data['source_form_name'] = $form['title']`
- Modified admin table display to show the form name in blue above the job type
- Updated `insert_enquiry()` method to handle the new field

**Display Changes**:
```
Before:
Form
Moving House

After:
Form
Moving House Enquiry Form  (← New: shown in blue)
Moving House
```

### 2. Enhanced Move Date Visibility ✅

**Problem**: Move dates were difficult to see as they were just small text mixed with other information.

**Solution**:
- Added highlighted styling with background color and border
- Increased font size for the date (15px, bold)
- Added color coding: date in red/pink, time in blue
- Wrapped in a distinctive box with yellow background and orange left border
- Applied CSS classes: `.hs-crm-move-date`, `.hs-crm-date-highlight`, `.hs-crm-time-highlight`

**Visual Changes**:
```
Before:
Move: 05/01/2026 2:30PM

After:
[Yellow Box with Orange Border]
Move: 05/01/2026  2:30PM
      ^^^^^^^^^^  ^^^^^^
      (larger,     (blue,
       red/pink,   medium
       bold)       bold)
```

### 3. Data Removal on Uninstall ✅

**Problem**: No uninstall script existed, so plugin data remained in the database even after uninstalling.

**Solution**:
- Created `uninstall.php` file that executes when plugin is deleted via WordPress admin
- Removes all 4 database tables:
  - `wp_hs_enquiries`
  - `wp_hs_enquiry_notes`
  - `wp_hs_trucks`
  - `wp_hs_truck_bookings`
- Removes all plugin options:
  - `hs_crm_db_version`
  - `hs_crm_admin_email`
  - `hs_crm_google_api_key`
  - `hs_crm_timezone`
  - `hs_crm_default_booking_duration`
- Removes custom user role `crm_manager`
- Removes CRM capabilities from administrator role

**Important Note**: Users previously assigned to the CRM Manager role will need to be manually reassigned to a different role. Their user accounts are preserved.

## Database Changes

### New Column: `source_form_name`
```sql
ALTER TABLE wp_hs_enquiries 
ADD COLUMN source_form_name varchar(255) DEFAULT '' NOT NULL 
AFTER contact_source;
```

- Type: VARCHAR(255)
- Default: Empty string
- Position: After `contact_source` column
- Purpose: Store the name/title of the Gravity Form that created the enquiry

## Files Modified

1. **marcus-furniture-crm.php**
   - Updated version to 2.5
   - Added migration check for version 2.5.0
   - Added `hs_crm_migrate_to_2_5_0()` function
   - Updated Gravity Forms integration to set `source_form_name`

2. **includes/class-hs-crm-database.php**
   - Added `source_form_name` column to table schema
   - Updated `insert_enquiry()` to handle new field

3. **includes/class-hs-crm-admin.php**
   - Added `$form_source_label` variable to store form name
   - Updated display to show form name when available
   - Enhanced move date display with new HTML structure

4. **assets/css/styles.css**
   - Added `.hs-crm-source-badge` styles for form source badge
   - Added `.hs-crm-move-date` styles for move date container
   - Added `.hs-crm-date-highlight` styles for date emphasis
   - Added `.hs-crm-time-highlight` styles for time emphasis

5. **uninstall.php** (NEW)
   - Complete data cleanup script
   - Removes tables, options, and custom role

6. **readme.txt**
   - Updated stable tag to 2.5

## Backward Compatibility

- ✅ Existing enquiries without `source_form_name` will display normally (field is optional)
- ✅ Migration runs automatically on plugin update
- ✅ All existing functionality preserved
- ✅ No breaking changes

## Testing Recommendations

1. **Gravity Forms Integration**:
   - Create test submissions from different Gravity Forms
   - Verify each shows the correct form name in the admin table
   - Confirm job type is still correctly identified

2. **Move Date Display**:
   - Check admin enquiries table for visual improvements
   - Verify dates are larger and easier to read
   - Confirm color coding is working

3. **Uninstall Process** (use test site!):
   - Deactivate and delete plugin
   - Verify all tables are removed
   - Verify all options are removed
   - Verify custom role is removed

## Migration Path

For existing installations:
1. Plugin auto-detects current database version
2. Runs migration 2.5.0 to add `source_form_name` column
3. Updates `hs_crm_db_version` option to '2.5.0'
4. No manual intervention required

## Known Limitations

- Form name is only captured for NEW enquiries created after this update
- Existing enquiries will not show a form name (field will be empty)
- To populate historical data, you would need to cross-reference notes (which contain the form name)
