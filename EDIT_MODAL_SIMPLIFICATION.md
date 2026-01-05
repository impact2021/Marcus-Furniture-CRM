# Edit Modal Simplification - Testing Guide

## Changes Made

Following the problem statement to "Remove every field in that modal apart from name", the following changes were made:

### 1. Modal Form (includes/class-hs-crm-admin.php)
- **Removed all fields except:**
  - First Name (required)
  - Last Name (required)
  
- **Removed fields:**
  - Email
  - Phone
  - From Address
  - To Address
  - Suburb
  - Number of Bedrooms
  - Number of Rooms
  - Total Number of Rooms
  - Property Notes
  - Stairs From
  - Stairs To
  - Move Date
  - Move Time
  - Booking Start Time
  - Booking End Time
  - Contact Source
  - Status

### 2. JavaScript (assets/js/scripts.js)
- Simplified the Edit Enquiry handler to only populate first_name and last_name fields
- Removed all other field population code

### 3. AJAX Handlers (includes/class-hs-crm-admin.php)

#### ajax_update_enquiry()
- Simplified to only process first_name and last_name
- Removed all other field processing

#### ajax_create_enquiry()
- Simplified to only accept first_name and last_name from user
- Added temporary placeholder values for required database fields:
  - email: 'TEMP_PLACEHOLDER@example.com'
  - phone: 'TEMP_000-000-0000'
  - delivery_from_address: 'TEMP_TBD'
  - delivery_to_address: 'TEMP_TBD'
- Added note: "Enquiry manually created - TEMPORARY PLACEHOLDER DATA USED (needs proper details)"

## Testing Instructions

### Test 1: Edit Existing Enquiry
1. Navigate to the Marcus Furniture Enquiries admin page
2. Click "Edit" button on any enquiry
3. Verify the modal shows only:
   - First Name field (populated with existing value)
   - Last Name field (populated with existing value)
   - Save Enquiry button
   - Cancel button
4. Change the name values
5. Click "Save Enquiry"
6. Expected Result: 
   - Success message: "Enquiry updated successfully."
   - Page reloads showing the updated name
   - NO error "Failed to update enquiry."

### Test 2: Create New Enquiry
1. Click "+ Add New Enquiry" button
2. Verify the modal shows only:
   - First Name field (empty)
   - Last Name field (empty)
   - Save Enquiry button
   - Cancel button
3. Enter a first name and last name
4. Click "Save Enquiry"
5. Expected Result:
   - Success message: "Enquiry created successfully."
   - Page reloads showing the new enquiry with:
     - The entered name
     - Temporary placeholder email: TEMP_PLACEHOLDER@example.com
     - Temporary placeholder phone: TEMP_000-000-0000
     - Temporary placeholder addresses: TEMP_TBD
   - A note: "Enquiry manually created - TEMPORARY PLACEHOLDER DATA USED (needs proper details)"

### Test 3: Cancel Modal
1. Click "Edit" on any enquiry
2. Click "Cancel" or the X button
3. Expected Result: Modal closes without changes

## Purpose

This simplification helps diagnose the "Failed to update enquiry" error by:
1. Reducing the number of fields being processed
2. Eliminating potential issues with field validation or data transformation
3. Focusing on the core update mechanism (first_name and last_name only)

If the simplified modal works correctly:
- The issue was likely with one of the removed fields or their processing
- Fields can be added back one at a time to identify the problematic field

If the error persists:
- The issue is in the core update mechanism itself
- Further investigation needed in the database update logic
