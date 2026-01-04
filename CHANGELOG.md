# Changelog

## Version 1.4.0 - 2026-01-04

### Added
- **Move Time Field**: Added time picker to specify preferred time for furniture move
  - Database column: `move_time` (TIME)
  - Available in contact form, admin enquiry modal, and Gravity Forms integration
  - Displays alongside move date in admin dashboard (e.g., "15/01/2026 at 2:00 PM")
  - Included in customer and admin notification emails

- **Suburb Field**: Added separate suburb/city field for better address management
  - Database column: `suburb` (VARCHAR 255)
  - Available in contact form, admin enquiry modal, and Gravity Forms integration
  - Displays below address in admin dashboard
  - Included in customer and admin notification emails
  - Automatically extracted from Gravity Forms address field components

### Fixed
- **Truck Scheduler Critical Error**: Fixed fatal error when accessing truck scheduler page with no trucks
  - Added null safety check to ensure trucks array is always initialized
  - Prevents PHP errors when database returns null instead of empty array

### Changed
- Enhanced Gravity Forms integration to support new fields:
  - Maps time fields with labels: "move time", "moving time", "preferred time", "time"
  - Maps suburb from address field city component or text fields with labels: "suburb", "city", "town"
  - Improved address field handling to extract individual components
  
- Updated admin interface to display move time and suburb information
- Updated all email templates to include move time and suburb when available

### Migration
- Database migration `hs_crm_migrate_to_1_4_0()` automatically runs on plugin activation
- Adds `suburb` and `move_time` columns to existing `hs_enquiries` table
- Existing enquiries will have NULL/empty values for new fields (backward compatible)

### Documentation
- Updated GRAVITY_FORMS_INTEGRATION.md with new field mappings
- Added examples for time field and suburb field usage

## Version 1.3.0 - Previous Release

### Added
- First email sent timestamp tracking

## Version 1.2.0 - Previous Release

### Added
- Separate notes table for better enquiry management

## Version 1.1.0 - Previous Release

### Added
- First name and last name fields
- Email sent tracking
- Admin notes functionality

## Version 1.0 - Initial Release

### Features
- Contact form for enquiry submission
- Admin dashboard for enquiry management
- Truck scheduling system
- Email notifications
- Gravity Forms integration
- Google Maps autocomplete for addresses (NZ only)
- Status management workflow
