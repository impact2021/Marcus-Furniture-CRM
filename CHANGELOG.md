# Changelog

## Version 2.1 - 2026-01-05

### Changed
- **Version Update**: Updated plugin version to 2.1
  - Updated plugin header version in main plugin file
  - Updated stable tag in readme.txt
  - Ensures consistency across all version references

### Verified
- **CRM Manager Role**: Confirmed CRM Manager role functionality is working correctly
  - Role is properly created on plugin activation
  - Role includes correct capabilities: `read`, `manage_crm_enquiries`, `view_crm_dashboard`
  - Role is visible in WordPress user creation/editing interface via `editable_roles` filter
  - Administrators automatically receive CRM capabilities
  - Users can be assigned the CRM Manager role when creating new users

## Version 1.9 - 2026-01-04

### Fixed
- **Critical Format Specifier Bug**: Fixed "Failed to update enquiry" error in `update_enquiry()` method
  - The auto-sync logic for the `address` field was adding duplicate format specifiers to `$update_format`
  - When `$data['address']` was explicitly set AND delivery addresses were being updated, two `%s` entries were added for the same field
  - This caused a mismatch between the number of fields and format specifiers, causing `$wpdb->update()` to fail
  - Added conditional check to only add format specifier if `address` wasn't already in the update data
  - This completes the fix started in version 1.8 for enquiry update issues

### Technical
- Updated plugin version to 1.9
- Modified `HS_CRM_Database::update_enquiry()` at line 372 to check `!isset($data['address'])` before adding format specifier
- Prevents duplicate format strings when both explicit address and delivery address updates occur

## Version 1.8 - 2026-01-04

### Fixed
- **Critical Enquiry Update Bug**: Fixed data inconsistency issue when editing enquiries
  - The legacy `address` field now auto-syncs when `delivery_from_address` or `delivery_to_address` are updated
  - This ensures data consistency between the old and new address field formats
  - Resolves the "Failed to update registry" issue that occurred when editing enquiry delivery addresses
  - The `address` field is automatically populated as a concatenation of from/to addresses (matching insert behavior)
  - Handles cases where only one delivery address is provided

### Technical
- Updated plugin version to 1.8
- Enhanced `HS_CRM_Database::update_enquiry()` method with automatic address field synchronization
- Added logic to fetch current enquiry data when partial delivery address updates are made
- Improved data integrity for enquiry records

## Version 1.7 - 2026-01-04

### Changed
- **Address Fields Simplified**: Removed generic "Address" field from enquiry forms
  - Now only uses "From Address" and "To Address" fields
  - Both from/to addresses are now required fields when creating enquiries
  - Updated admin table display to show from/to addresses prominently
  - Improved visual clarity with better formatting for address information

- **Removed House Size Field**: Removed "House size" field from UI
  - Field removed from enquiry modal form
  - Database column retained for backward compatibility
  - No longer displayed or editable in the admin interface

- **Stairs Fields Cleanup**: Removed legacy "stairs" field
  - Now only uses "Stairs Involved (From Address)" and "Stairs Involved (To Address)"
  - Legacy stairs field removed from all forms and handlers
  - Database column retained for backward compatibility

### Fixed
- **Enquiry Creation Error**: Fixed "Failed to create enquiry" error
  - Updated validation to require from/to addresses
  - Removed references to deprecated address field
  - Improved error handling in AJAX handlers
  - Fixed field population in edit modal

### Technical
- Updated plugin version to 1.7
- Updated database version to 1.7.0
- Cleaned up AJAX handlers to remove deprecated field processing
- Updated JavaScript to remove address and stairs field handling
- Improved data validation for required fields

## Version 3.5 - 2026-01-04

### Fixed
- **Enquiry Edit Fields**: Fixed missing fields when editing enquiries
  - Added `house_size` field to enquiry modal form
  - Added `number_of_rooms` field to enquiry modal form  
  - Added `delivery_from_address` field to enquiry modal form
  - Added `delivery_to_address` field to enquiry modal form
  - Updated JavaScript to populate all missing fields when loading edit modal
  - Updated AJAX handlers to process all fields on create and update operations
  - Ensures all enquiry data is properly displayed and saved during editing

### Enhanced
- Updated plugin version to 3.5
- Improved data completeness for enquiry management

## Version 1.4 - 2026-01-04

### Fixed
- **Enquiry Update Error**: Fixed "Failed to update enquiry" error when editing enquiry details
  - Added missing `contact_source` field handling in update enquiry AJAX handler
  - Added `contact_source` field support to database update method
  - Fixed name field concatenation logic to properly handle partial name updates
  - Improved error handling to prevent data loss when database queries fail
  - Changes ensure all form fields are properly saved when editing enquiries

### Technical
- Updated `HS_CRM_Admin::ajax_update_enquiry()` to process `contact_source` field
- Updated `HS_CRM_Database::update_enquiry()` to handle `contact_source` field
- Enhanced name field update logic to fetch existing values when only one name field is updated
- Added proper null checks to avoid overwriting data on database query failures

## Version 1.3 - 2026-01-04

### Added
- **Delivery Address Fields**: Added delivery from and delivery to address fields to the enquiry table
  - Track pick-up location separately with `delivery_from_address` field
  - Track drop-off location separately with `delivery_to_address` field
  - Both fields are optional and appear in the enquiry edit modal
  - Delivery addresses display in the admin table view when populated
  - Database migration (v1.6.0) automatically adds new columns on plugin update

### Enhanced
- **Edit Enquiry Functionality**: Confirmed and tested edit functionality is fully operational
  - Edit Details option available in Action dropdown for each enquiry
  - Modal form now includes all fields including new delivery addresses
  - Edit capability includes phone numbers, email, addresses, and all other enquiry fields
  - Changes saved via AJAX without page reload

### Technical
- Updated plugin version to 1.3
- Added database migration function `hs_crm_migrate_to_1_6_0()`
- Updated `HS_CRM_Database::create_tables()` schema
- Updated `HS_CRM_Database::insert_enquiry()` for delivery fields
- Updated `HS_CRM_Database::update_enquiry()` for delivery fields
- Enhanced admin UI to display delivery addresses
- Updated JavaScript handlers for new fields

## Version 1.1 - 2026-01-04

### Fixed
- **Truck Scheduler Critical Error**: Fixed fatal error when accessing truck scheduler page
  - Added null safety check to ensure trucks array is always initialized
  - Added null safety check to ensure bookings array is always initialized
  - Added null safety check to ensure enquiries array is always initialized
  - Prevents PHP errors when database returns null instead of empty array

## Version 1.0 - Initial Release

### Features
- Contact form for enquiry submission
- Admin dashboard for enquiry management
- Truck scheduling system
- Email notifications
- Gravity Forms integration
- Google Maps autocomplete for addresses (NZ only)
- Status management workflow
