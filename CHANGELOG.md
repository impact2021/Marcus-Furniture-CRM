# Changelog

## Version 2.7 - 2026-01-06

### Updated
- **Version Update**: Updated plugin version to 2.7
  - Updated plugin header version in main plugin file from 2.6 to 2.7
  - Updated HS_CRM_VERSION constant from 2.6 to 2.7
  - Updated stable tag in readme.txt from 2.6 to 2.7
  - Ensures consistency across all version references

- **WordPress Compatibility**: Updated WordPress compatibility to version 6.8
  - Updated "Tested up to" field in readme.txt
  - Plugin tested and verified compatible with WordPress 6.8

## Version 2.5 - 2026-01-05

### Added
- **Gravity Form Name Display**: Enquiries now show which specific Gravity Form they came from
  - Added `source_form_name` database column to track the originating form
  - Form name displays in blue above the job type in the admin table
  - Database migration automatically adds the column on plugin update
  - Makes it easy to identify which form (e.g., "Moving House Enquiry", "Pickup Request") created each enquiry

- **Uninstall Data Cleanup**: Complete data removal when plugin is deleted
  - Created `uninstall.php` to handle plugin deletion
  - Removes all 4 database tables (enquiries, notes, trucks, bookings)
  - Removes all plugin options (settings, API keys, etc.)
  - Removes custom CRM Manager role and capabilities
  - Ensures clean uninstall with no orphaned data

### Enhanced
- **Move Date Visibility**: Made move dates much more prominent and easier to see
  - Increased date font size to 15px with bold styling
  - Added color coding: dates in red/pink background, times in blue
  - Wrapped move date in yellow highlighted box with orange left border
  - Significantly improved visual hierarchy in the enquiries table
  
### Technical
- Updated plugin version to 2.5
- Updated database version to 2.5.0
- Added migration function `hs_crm_migrate_to_2_5_0()`
- Enhanced CSS with new classes: `.hs-crm-move-date`, `.hs-crm-date-highlight`, `.hs-crm-time-highlight`
- Updated `HS_CRM_Database::insert_enquiry()` to handle `source_form_name` field
- Modified Gravity Forms integration to capture form title on submission

## Version 2.4 - 2026-01-05

### Fixed
- **Job Type Detection**: Fixed incorrect categorization of forms as Pickup/Delivery vs Moving House
  - Changed keyword priority to check for "moving" keywords BEFORE "delivery/pickup" keywords
  - This prevents forms like "Moving House Delivery" from being incorrectly tagged as Pickup/Delivery
  - Moving house forms are now correctly identified even if they contain the word "delivery"
  - Improved with word boundary regex matching to avoid false positives (e.g., "remove" won't match "move")
  
- **Auto-Archive Feature**: Implemented automatic archiving of enquiries with past move dates
  - When viewing the Active tab, enquiries with move dates in the past are automatically moved to Archived status
  - Uses WordPress `current_time()` to respect the site's timezone setting
  - Only affects enquiries in active statuses (First Contact, Quote Sent, Booking Confirmed, Deposit Paid)
  - Enquiries without a move date are not affected
  - Throttled to run once per hour using WordPress transients to optimize performance
  
- **Address Truncation**: Fixed address truncation in truck scheduler page
  - Removed `wp_trim_words()` function that was limiting addresses to 4 words
  - Full addresses are now displayed in the calendar view
  - Improves readability and ensures all address details are visible

### Changed
- **Version Update**: Updated plugin version to 2.4
  - Updated plugin header version in main plugin file from 2.3 to 2.4
  - Updated stable tag in readme.txt from 2.2 to 2.4
  - Ensures consistency across all version references

## Version 2.1 - 2026-01-05

### Changed
- **Version Update**: Updated plugin version to 2.1
  - Updated plugin header version in main plugin file from 2.0 to 2.1
  - Updated stable tag in readme.txt from 1.9 to 2.1 (aligning WordPress stable tag with plugin version)
  - Ensures consistency across all version references
  - Note: The main plugin file was at version 2.0 while readme.txt was at 1.9; both are now aligned at 2.1

### Verified
- **CRM Manager Role**: Confirmed CRM Manager role functionality is working correctly
  - Role is properly created on plugin activation
  - Role includes correct capabilities: `read`, `manage_crm_enquiries`, `view_crm_dashboard`
  - Role is visible in WordPress user creation/editing interface via `editable_roles` filter
  - Administrators automatically receive CRM capabilities
  - Users can be assigned the CRM Manager role when creating new users

### Documentation
- Added comprehensive CRM_MANAGER_ROLE_GUIDE.md with instructions for creating and managing CRM Manager users

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
