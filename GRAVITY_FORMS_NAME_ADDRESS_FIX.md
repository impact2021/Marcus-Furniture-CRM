# Gravity Forms Name and Address Field Mapping Fix

## Problem Statement

Gravity Forms entries were failing to import into the CRM with the error:
```
Status: Missing required fields: first_name, last_name, address
```

### Root Causes Identified

1. **Single Name Field Issue**: The form had a text field (ID: 12) labeled "Name" instead of a Gravity Forms "Name" field type. The integration code only handled the native Name field type with subfields (.3 for first name, .6 for last name).

2. **Multiple Address Fields Issue**: The form had two address fields:
   - ID: 13, Label: "Moving from:", Type: address
   - ID: 50, Label: "Moving to:", Type: address
   
   The code was overwriting the required 'address' field instead of mapping them to appropriate delivery_from_address and delivery_to_address fields.

## Solution Implemented

### 1. Single Name Field Handling

**Changes Made:**
- Added 'name' => array('name') to the field_mapping array
- Implemented two-pass matching: exact match first, then partial match
  - This prevents "first name" from incorrectly matching the generic "name" pattern
- Added post-processing logic to split a single name field into first_name and last_name
- Handles edge cases:
  - Two words: "John Doe" → first_name: "John", last_name: "Doe"
  - One word: "Johnston" → first_name: "Johnston", last_name: "Johnston"
  - Three+ words: "Mary Jane Watson" → first_name: "Mary", last_name: "Jane Watson"

**Code Location:** `marcus-furniture-crm.php`, lines 826-844

**Note:** This assumes Western naming conventions (first name, then last name). For international names with different formats, users should configure their form to use separate first/last name fields.

### 2. Multiple Address Field Handling

**Changes Made:**
- Enhanced address field detection to identify "from" vs "to" addresses based on label keywords
- Mapping logic:
  - Labels containing "from" or "pickup" → delivery_from_address + from_suburb
  - Labels containing "to", "dropoff", or "delivery" → delivery_to_address + to_suburb
  - Generic address labels → main address field
- First address field encountered populates the required 'address' field
- Each address field also extracts the city/suburb component to appropriate suburb fields

**Code Location:** `marcus-furniture-crm.php`, lines 680-751

**Keywords Detected:**
- From addresses: "from", "pickup"
- To addresses: "to", "dropoff", "delivery"

## Testing Performed

Created comprehensive unit tests covering:

1. **Name Splitting Logic**
   - ✓ Two-word names: "John Doe"
   - ✓ Single-word names: "Jane"
   - ✓ Multi-word names: "Mary Jane Watson"
   - ✓ Edge case: "Johnston"

2. **Label Matching**
   - ✓ Exact match: "name" → 'name'
   - ✓ Partial match: "phone number" → 'phone'
   - ✓ Priority matching: "first name" → 'first_name' (not 'name')

3. **Address Detection**
   - ✓ "Moving from:" → delivery_from_address
   - ✓ "Moving to:" → delivery_to_address
   - ✓ "Pickup Address" → delivery_from_address
   - ✓ "Delivery Address" → delivery_to_address
   - ✓ "Address" → address

All tests passed successfully.

## How to Verify the Fix

### Test Case 1: Import Existing Entry

1. Go to **Enquiries > Settings**
2. Scroll to **Gravity Forms Import** section
3. Select your form
4. Set number of entries to import: 1 (for testing)
5. Enable detailed debugging
6. Click **Import Entries**

**Expected Result:**
- Entry should import successfully
- Debug output should show:
  - First name and last name extracted from the "Name" field
  - Address populated from "Moving from:" field
  - delivery_from_address and delivery_to_address both populated

### Test Case 2: Submit New Entry

1. Navigate to your Gravity Forms form
2. Submit a new entry with:
   - Name: "Test User"
   - Moving from: "123 Test St, Auckland"
   - Moving to: "456 Example Ave, Wellington"
3. Check the CRM enquiries page

**Expected Result:**
- New enquiry created
- First name: "Test"
- Last name: "User"
- Address: "123 Test St, Auckland" (from first address field)
- delivery_from_address: "123 Test St, Auckland"
- delivery_to_address: "456 Example Ave, Wellington"

## Database Fields Populated

With this fix, Gravity Forms entries will now populate:

**Required Fields:**
- `first_name` - Extracted from name field or Name field type
- `last_name` - Extracted from name field or Name field type
- `email` - From Email field type or labeled field
- `phone` - From Phone field type or labeled field
- `address` - From first Address field encountered

**Optional Address Fields:**
- `delivery_from_address` - From "Moving from:" address field
- `delivery_to_address` - From "Moving to:" address field
- `from_suburb` - City component of from address
- `to_suburb` - City component of to address
- `suburb` - City component of main address

**Other Optional Fields:**
- `move_date` - From date field or labeled field
- `move_time` - From time field or labeled field
- `contact_source` - Automatically set to 'form'

## Backwards Compatibility

This fix is fully backwards compatible:

- Forms using native Gravity Forms Name field type continue to work
- Forms with separate "First Name" and "Last Name" fields continue to work
- Forms with single "Address" field continue to work
- New functionality is additive and doesn't break existing field mappings

## Best Practices for Form Configuration

For optimal results, configure your Gravity Forms as follows:

### Recommended Field Types

1. **Name**: Use Gravity Forms "Name" field type (with first/last subfields)
   - Alternative: Use a single text field labeled "Name" (will be split automatically)
   - Alternative: Use separate text fields labeled "First Name" and "Last Name"

2. **Address**: Use Gravity Forms "Address" field type
   - For moving forms, label them clearly: "Moving from:", "Moving to:", etc.
   - The system will auto-detect and map appropriately

3. **Email**: Use Gravity Forms "Email" field type (auto-detected)

4. **Phone**: Use Gravity Forms "Phone" field type (auto-detected)

5. **Date/Time**: Use appropriate Gravity Forms field types

### Field Labels Matter

The system matches fields by label when native field types aren't used. Supported labels include:

- **Name**: "name", "first name", "last name", "fname", "lname", "surname"
- **Email**: "email", "e-mail", "email address"
- **Phone**: "phone", "telephone", "mobile", "phone number"
- **Address**: "address", "street address", "location"
- **Date**: "move date", "moving date", "preferred date", "date"
- **Time**: "move time", "moving time", "preferred time", "time"

## Code Review and Security

- ✓ Code review completed
- ✓ All security sanitization in place (sanitize_text_field, sanitize_email, sanitize_textarea_field)
- ✓ No SQL injection vulnerabilities
- ✓ No XSS vulnerabilities
- ✓ Input validation maintained

## Version Information

- Fix implemented in: Version 2.2+
- Affected file: `marcus-furniture-crm.php`
- Functions modified: `hs_crm_gravity_forms_integration`

## Support

If you encounter issues with field mapping:

1. Enable debug mode when importing entries
2. Review the debug output to see exactly what fields are being detected
3. Check that field labels match the expected patterns listed above
4. For complex cases, use native Gravity Forms field types instead of text fields

For international name handling or non-Western naming conventions, configure your form to use separate first name and last name fields rather than a single name field.
