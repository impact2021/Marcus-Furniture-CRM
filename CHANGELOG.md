# Changelog

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
