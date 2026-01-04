# Marcus Furniture CRM - Changes from Home Shield Painters

## Summary of Adaptations

This document outlines the changes made to adapt the Home Shield Painters CRM for Marcus Furniture moving company.

## Branding Changes

### Company Name
- **Old**: Home Shield Painters / HS Painters
- **New**: Marcus Furniture

### Terminology Changes
- **Old**: Painting jobs, interior wall painting, etc.
- **New**: Furniture moving, moves, relocations

### Menu Items
- **Old**: "HS Enquiries" (menu icon: dashicons-email)
- **New**: "MF Enquiries" (menu icon: dashicons-move)

## New Features Added

### 1. Move Date Field
- Added `move_date` field to database schema
- Added date picker to contact form
- Displayed prominently in admin interface
- Used as default sort field

### 2. Contact Source Tracking
- Added `contact_source` field to database
- Options: form, whatsapp, phone, email, other
- Displayed in admin dashboard
- Allows manual entry creation from multiple sources

### 3. Manual Entry Creation
- "Add New Enquiry" button in admin
- Full form with all customer details
- Contact source selector
- Status selector
- Creates entries for non-web enquiries (phone calls, WhatsApp, etc.)

### 4. Updated Workflow Statuses
**Old Statuses:**
- Not Actioned
- Emailed
- Quoted
- Completed
- Archived/Dead

**New Statuses:**
- First Contact (replaces "Not Actioned")
- Quote Sent (replaces "Emailed")
- Booking Confirmed (NEW)
- Deposit Paid (NEW)
- Completed
- Archived

### 5. Truck Scheduling System (NEW)
Entirely new feature set:

**Truck Management:**
- Add/edit/remove trucks
- Track registration and capacity
- Active/inactive status

**Visual Calendar:**
- Month view showing all trucks
- Click cells to add bookings
- Color-coded booking display
- Navigate between months

**Booking System:**
- Link to customer enquiries
- Time ranges (start/end time)
- Notes field
- Quick booking creation

### 6. Sorting Enhancements
- Added sortable columns (move date, contact date)
- Default sort: move date (ascending)
- Nulls last logic for records without move dates

### 7. Edit Details Feature
- Added "Edit Details" to action dropdown
- Inline editing of customer information
- Update move date, contact source, status

## Database Schema Changes

### wp_hs_enquiries table
**Added fields:**
- `move_date` (date, NULL) - Requested moving date
- `contact_source` (varchar, default 'form') - Source of enquiry

**Modified fields:**
- Default status changed from 'Not Actioned' to 'First Contact'

**Added indexes:**
- KEY on `move_date` for sorting performance

### New Tables
**wp_hs_trucks:**
- id
- name
- registration
- capacity
- status (active/inactive)
- created_at

**wp_hs_truck_bookings:**
- id
- truck_id (FK)
- enquiry_id (FK, nullable)
- booking_date
- start_time (nullable)
- end_time (nullable)
- notes
- created_at

## File Changes

### New Files Created
1. `includes/class-hs-crm-truck-scheduler.php` - Complete truck scheduling system
2. `marcus-furniture-crm/README.md` - Documentation

### Modified Files from Original

**marcus-furniture-crm.php:**
- Updated plugin header (name, description, version)
- Added truck scheduler class include
- Initialized truck scheduler in admin

**includes/class-hs-crm-database.php:**
- Updated create_tables() to include truck tables
- Modified insert_enquiry() for move_date and contact_source
- Modified get_enquiries() for sorting by move_date
- Added update_enquiry() method
- Updated status counts for new statuses
- Added complete truck management methods
- Added booking management methods

**includes/class-hs-crm-form.php:**
- Added move_date field to contact form
- Updated customer email template
- Updated admin notification template
- Changed branding to Marcus Furniture

**includes/class-hs-crm-email.php:**
- Updated email templates for furniture moving context
- Changed quote table descriptions
- Updated "Job Details" to "Move Details"
- Changed branding to Marcus Furniture

**includes/class-hs-crm-admin.php:**
- Updated menu name and icon
- Added move date column
- Added contact source column
- Added manual entry creation (ajax_create_enquiry)
- Added edit enquiry function (ajax_update_enquiry)
- Updated status dropdown options
- Added edit details to action dropdown
- Added sortable column headers
- Updated tab counts for new statuses

**includes/class-hs-crm-settings.php:**
- Updated page title to "Marcus Furniture CRM Settings"
- Kept timezone and Google Maps features (NZ location)

**assets/js/scripts.js:**
- Added manual entry creation handlers
- Added edit details functionality
- Added complete truck scheduler JavaScript
  - Truck CRUD operations
  - Booking CRUD operations
  - Calendar interaction
  - Modal handling

**assets/css/styles.css:**
- Copied as-is (no changes needed)

## Configuration Preserved

The following settings were intentionally kept from the original:

- **Timezone options**: Pacific/Auckland, Australia cities, UTC
- **Google Maps restriction**: New Zealand only
- **GST calculation**: 15% (New Zealand tax rate)
- **Date format**: d/m/Y (NZ/AU standard)
- **Email template structure**: Professional HTML emails

## Testing Checklist

Before deployment, verify:

- [ ] Plugin activates without errors
- [ ] Database tables created correctly
- [ ] Contact form submission works
- [ ] Manual enquiry creation works
- [ ] All status transitions work
- [ ] Sorting by move date works
- [ ] Truck CRUD operations work
- [ ] Booking system works
- [ ] Calendar displays correctly
- [ ] Email sending works
- [ ] Notes system works
- [ ] Google Maps autocomplete works (if API key configured)

## Migration Notes

If migrating from Home Shield Painters CRM:

1. This uses the same database table prefix (`hs_`)
2. Existing enquiries will remain but:
   - Will have NULL move_date (shown as "Not set")
   - Will have contact_source = 'form' by default
   - Old statuses will still work but appear in filters
3. Run the plugin activation to add new tables
4. Manually update old status values if desired:
   - "Not Actioned" → "First Contact"
   - "Emailed" → "Quote Sent"
   - "Quoted" → "Quote Sent" (if applicable)

## Development Approach

**Minimal Changes Philosophy:**
- Reused existing code structure where possible
- Kept same database prefixes and class names for compatibility
- Extended rather than replaced existing functionality
- Preserved working features (timezone, emails, notes)

**New Code Added:**
- Truck scheduler class (~550 lines)
- Database methods for trucks/bookings (~250 lines)
- JavaScript for new features (~200 lines)
- Updated admin interface (~100 lines of changes)

Total: Approximately 75% reuse, 25% new code
