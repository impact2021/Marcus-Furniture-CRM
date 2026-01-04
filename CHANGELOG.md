# Changelog

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
