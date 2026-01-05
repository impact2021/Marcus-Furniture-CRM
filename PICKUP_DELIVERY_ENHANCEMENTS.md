# Pickup/Delivery Form Enhancements - Implementation Summary

## Overview
This document summarizes the implementation of enhancements to the Marcus Furniture CRM for better handling of pickup/delivery enquiries versus moving house enquiries.

## Changes Made

### 1. Database Schema Updates

#### New Columns Added (Migration v2.3.0)
- `items_being_collected` (TEXT) - Stores items being picked up/delivered
- `furniture_moved_question` (VARCHAR 50) - Stores yes/no response to "Do you need existing furniture moved?"
- `special_instructions` (TEXT) - Stores special delivery instructions

#### Migration Function
- Added `hs_crm_migrate_to_2_3_0()` function in `marcus-furniture-crm.php`
- Migration runs automatically on plugin activation or when database version is detected as < 2.3.0

### 2. Gravity Forms Integration

#### Updated Field Mapping
Added new field mappings in `hs_crm_gravity_forms_integration()`:
- `stairs` → Maps from labels: "stairs", "stairs involved", "are there stairs"
- `items_being_collected` → Maps from labels: "items being delivered", "items being collected", "what items", "items to collect", "what item(s) are being collected"
- `furniture_moved_question` → Maps from labels: "existing furniture moved", "furniture moved", "do you need any existing furniture moved"
- `special_instructions` → Maps from labels: "special instructions", "additional instructions", "instructions", "special requests"

#### Special Instructions Handling
Special instructions are automatically added as a note when an enquiry is created from Gravity Forms, making them visible in the notes section.

### 3. Admin Table UI Updates

#### Form Type Detection
The system now automatically detects whether an enquiry is for pickup/delivery or moving house based on:
- Presence of `delivery_from_address` or `delivery_to_address`
- Presence of `items_being_collected`

#### Visual Differentiation
- **Pickup/Delivery**: Orange header (#FF8C00) with white text
- **Moving House**: Blue header (#061257) with white text
- Form type label displayed in "Source & Dates" column

#### New Column Layout
Updated from 8 columns to new structure:
1. **Source & Dates** (14%) - Shows form source, type (Pickup/Delivery or Moving House), contact date, and move date
2. **Contact & Address** (18%) - Customer name, phone, email, and addresses
3. **House Details** (14%) - Content varies by type:
   - Moving House: Shows bedrooms, total rooms, stairs
   - Pickup/Delivery: Shows stairs only
4. **Items & Instructions** (16%) - NEW COLUMN for pickup/delivery:
   - Items being collected
   - Furniture moved question response
5. **Status** (8%) - Current status badge
6. **Truck** (10%) - Truck assignment dropdown
7. **Status / Action** (12%) - Combined column with:
   - Status change dropdown
   - Action dropdown (send quote/invoice/receipt)
8. **Edit / Delete** (8%) - Combined column with:
   - Edit button
   - Delete button (archives enquiry)

### 4. Edit Modal Updates

#### Removed Fields
- From Suburb (removed per requirement)
- To Suburb (removed per requirement)

#### Added Fields
- **Stairs** - Dropdown (Yes/No)
- **What item(s) are being collected?** - Text area for pickup/delivery items
- **Do you need any existing furniture moved?** - Dropdown (Yes/No)

#### Field Organization
Fields are organized in a two-column grid layout for better use of space on desktop.

### 5. AJAX Handlers

#### Updated Handlers
- `ajax_create_enquiry()` - Now handles new fields
- `ajax_update_enquiry()` - Now handles new fields

#### New Handler
- `ajax_delete_enquiry()` - Archives enquiry by setting status to "Archived" and adds a note

### 6. JavaScript Updates

#### Modal Population
Updated `hs-crm-edit-enquiry` click handler to populate new fields:
- stairs
- items_being_collected
- furniture_moved_question

#### Delete Functionality
Added `hs-crm-delete-enquiry` click handler:
- Confirms deletion with user
- Archives enquiry via AJAX
- Removes enquiry table from DOM with fade-out animation
- Shows success/error message

### 7. Database Layer Updates

#### Insert Enquiry
Updated `HS_CRM_Database::insert_enquiry()` to handle new columns.

#### Update Enquiry
Updated `HS_CRM_Database::update_enquiry()` to handle new columns with proper format specifiers.

## File Changes Summary

### Modified Files
1. **marcus-furniture-crm.php**
   - Added migration function for v2.3.0
   - Updated Gravity Forms field mapping
   - Added special instructions to notes

2. **includes/class-hs-crm-database.php**
   - Updated table creation SQL with new columns
   - Updated insert_enquiry method
   - Updated update_enquiry method

3. **includes/class-hs-crm-admin.php**
   - Redesigned table layout with form type detection
   - Added visual differentiation (orange vs blue headers)
   - Updated column structure and content
   - Removed suburb fields from modal
   - Added new fields to modal
   - Added delete AJAX handler

4. **assets/js/scripts.js**
   - Updated modal field population
   - Added delete button handler with confirmation

## Testing Recommendations

### 1. Database Migration
- Test on a fresh installation
- Test on an existing installation
- Verify all new columns are created
- Verify existing data is preserved

### 2. Gravity Forms Integration
Test with forms containing:
- Stairs field (various label variations)
- Items being collected field
- Furniture moved question
- Special instructions field
- Verify special instructions appear in notes

### 3. UI Testing
- Verify orange header for pickup/delivery enquiries
- Verify blue header for moving house enquiries
- Check "Items & Instructions" column only shows data for pickup/delivery
- Verify all dropdowns and buttons work correctly
- Test delete functionality
- Test edit modal with all new fields

### 4. Responsive Testing
- Test table layout on mobile devices
- Verify modal is usable on small screens

## Gravity Forms Setup Guide

To use the new fields in Gravity Forms:

### Example Pickup/Delivery Form Fields

1. **Stairs Involved**
   - Type: Radio Buttons or Dropdown
   - Label: "Stairs involved" or "Are there stairs?"
   - Options: Yes, No

2. **What items are being collected?**
   - Type: Paragraph Text
   - Label: "What item(s) are being collected?"
   - Description: "Please list all items"

3. **Existing Furniture**
   - Type: Radio Buttons or Dropdown
   - Label: "Do you need any existing furniture moved?"
   - Options: Yes, No

4. **Special Instructions**
   - Type: Paragraph Text
   - Label: "Special Instructions"
   - Description: "Any special delivery requirements"

### Form Setup
- Ensure form title contains "pickup" or "delivery" for auto-integration
- Or add CSS class "crm-integration" to form settings

## Known Limitations

1. **Form Type Detection**: Automatic detection based on presence of delivery addresses. If a moving house enquiry has both from/to addresses, it might be detected as pickup/delivery.

2. **Backward Compatibility**: Existing enquiries created before this update will not have values in new fields.

3. **Special Instructions**: Are added to notes rather than being editable in the modal. This is by design as they are considered one-time information.

## Future Enhancements

Potential improvements for future versions:
1. Add ability to manually set form type
2. Make special instructions editable
3. Add bulk archive/delete functionality
4. Export functionality for pickup/delivery vs moving house separately
5. Dashboard widgets showing pickup/delivery vs moving house statistics

## Version Information
- **Plugin Version**: 2.3
- **Database Version**: 2.3.0
- **Date**: January 2026
- **Author**: Impact Websites

## Support

For issues or questions about these enhancements:
1. Check existing enquiries to ensure they display correctly
2. Test Gravity Forms integration with new fields
3. Verify database migration completed successfully
4. Review browser console for JavaScript errors
