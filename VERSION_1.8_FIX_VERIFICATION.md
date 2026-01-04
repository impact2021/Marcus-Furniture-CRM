# Version 1.8 Fix Verification Guide

## Issue Fixed
**"Failed to update registry" when editing enquiries**

## Root Cause
When editing an enquiry and updating the `delivery_from_address` or `delivery_to_address` fields, the legacy `address` field was not being synchronized with the new values. This caused data inconsistency between the old and new address field formats.

## The Fix
Modified `HS_CRM_Database::update_enquiry()` method to automatically sync the legacy `address` field whenever either `delivery_from_address` or `delivery_to_address` is updated. The `address` field is now populated as a concatenation of the two delivery addresses (matching the behavior used when creating new enquiries).

## Test Scenarios

### Test 1: Edit Both Delivery Addresses
**Steps:**
1. Navigate to WP Admin → MF Enquiries
2. Click "Edit" on any enquiry
3. Change both "From Address" and "To Address" fields
4. Click "Save Enquiry"

**Expected Result:**
- Enquiry updates successfully
- Both delivery address fields are saved
- Legacy `address` field is updated to: `{from_address} → {to_address}`
- No error messages appear

### Test 2: Edit Only From Address
**Steps:**
1. Navigate to WP Admin → MF Enquiries
2. Click "Edit" on any enquiry
3. Change only the "From Address" field
4. Click "Save Enquiry"

**Expected Result:**
- Enquiry updates successfully
- From address is updated
- To address remains unchanged
- Legacy `address` field is updated to: `{new_from_address} → {existing_to_address}`
- No error messages appear

### Test 3: Edit Only To Address
**Steps:**
1. Navigate to WP Admin → MF Enquiries
2. Click "Edit" on any enquiry
3. Change only the "To Address" field
4. Click "Save Enquiry"

**Expected Result:**
- Enquiry updates successfully
- To address is updated
- From address remains unchanged
- Legacy `address` field is updated to: `{existing_from_address} → {new_to_address}`
- No error messages appear

### Test 4: Edit Other Fields (No Address Change)
**Steps:**
1. Navigate to WP Admin → MF Enquiries
2. Click "Edit" on any enquiry
3. Change only non-address fields (e.g., phone, email, move date)
4. Click "Save Enquiry"

**Expected Result:**
- Enquiry updates successfully
- Changed fields are saved
- Address fields remain unchanged
- No error messages appear

### Test 5: Truck Assignment
**Steps:**
1. Navigate to WP Admin → MF Enquiries
2. Select a truck from the "Truck" dropdown for any enquiry
3. Wait for confirmation

**Expected Result:**
- Truck assignment updates successfully
- Confirmation message appears
- Note is added indicating truck assignment
- No error messages appear

### Test 6: Status Change
**Steps:**
1. Navigate to WP Admin → MF Enquiries
2. Select a new status from the "Status Change" dropdown
3. Confirm the change

**Expected Result:**
- Status updates successfully
- Status badge updates to show new status
- Note is added with status change details
- No error messages appear

### Test 7: Create New Enquiry
**Steps:**
1. Navigate to WP Admin → MF Enquiries
2. Click "+ Add New Enquiry"
3. Fill in all required fields including both delivery addresses
4. Click "Save Enquiry"

**Expected Result:**
- Enquiry creates successfully
- Both delivery address fields are saved
- Legacy `address` field is populated as: `{from_address} → {to_address}`
- Page reloads showing new enquiry in the list

## Database Verification

To verify the fix at the database level:

```sql
-- Before editing an enquiry, check current values
SELECT id, delivery_from_address, delivery_to_address, address 
FROM wp_hs_enquiries 
WHERE id = [ENQUIRY_ID];

-- After editing delivery addresses, verify sync
-- The 'address' column should match the pattern: "{delivery_from_address} → {delivery_to_address}"
SELECT id, delivery_from_address, delivery_to_address, address 
FROM wp_hs_enquiries 
WHERE id = [ENQUIRY_ID];
```

## What Was Changed

### File: `includes/class-hs-crm-database.php`
**Method:** `update_enquiry()`

**Change:**
Added automatic synchronization logic after the delivery address updates:

```php
// Auto-update the legacy address field when delivery addresses change
if (isset($data['delivery_from_address']) || isset($data['delivery_to_address'])) {
    // Get current enquiry to fetch any missing address parts
    $current_enquiry = self::get_enquiry($id);
    
    if ($current_enquiry) {
        $from_address = isset($data['delivery_from_address']) 
            ? sanitize_textarea_field($data['delivery_from_address']) 
            : $current_enquiry->delivery_from_address;
            
        $to_address = isset($data['delivery_to_address']) 
            ? sanitize_textarea_field($data['delivery_to_address']) 
            : $current_enquiry->delivery_to_address;
        
        // Update the address field with concatenated format
        if (!empty($from_address) && !empty($to_address)) {
            $update_data['address'] = $from_address . ' → ' . $to_address;
            $update_format[] = '%s';
        } elseif (!empty($from_address)) {
            $update_data['address'] = $from_address;
            $update_format[] = '%s';
        } elseif (!empty($to_address)) {
            $update_data['address'] = $to_address;
            $update_format[] = '%s';
        }
    }
}
```

### File: `marcus-furniture-crm.php`
- Updated plugin version from 1.7 to 1.8
- Updated HS_CRM_VERSION constant from 1.7 to 1.8

### File: `readme.txt`
- Updated Stable tag from 1.7 to 1.8
- Added version 1.8 changelog entry

### File: `CHANGELOG.md`
- Added version 1.8 entry with detailed fix description

## Version Information
- **Old Version:** 1.7
- **New Version:** 1.8
- **Fix Date:** 2026-01-04

## Success Criteria
All 7 test scenarios pass without errors, and the legacy `address` field properly syncs with delivery address changes.
