# Gravity Forms Import Debug Mode - User Guide

## Overview
Version 2.2 adds a comprehensive debug mode to help identify exactly why Gravity Forms entries are being skipped during import.

## Problem Being Solved
When you see:
```
Import complete! Imported: 0, Skipped: 5 (missing required fields or duplicates)
```

You need to know **WHY** those 5 entries were skipped. Is it because:
- Required fields are missing from the form?
- Field names don't match the expected labels?
- The entries are duplicates?
- Something else?

## How to Use Debug Mode

### Step 1: Access Settings
1. Log into WordPress admin
2. Go to **Enquiries > Settings**
3. Scroll down to the **Gravity Forms Import** section

### Step 2: Enable Debug Mode
1. Select the form you want to import
2. Set the number of entries (start small, like 5 entries, for testing)
3. **Check the "Enable detailed debugging" checkbox**
4. Click "Import Entries"

### Step 3: Review Debug Output
The debug output will show detailed information for EACH entry:

#### Entry Header
- Entry number and ID
- Date the entry was submitted
- **Status**: Either "SUCCESS" (imported) or the reason it was skipped

#### Fields Found
Lists all fields in the form with:
- Field ID
- Field Label
- Field Type (name, address, email, phone, text, etc.)

#### Name Field Debug (if present)
Shows how the name field was processed:
- Field ID being used
- Subfield keys being looked up (.3 for first name, .6 for last name)
- Actual values found for first and last name
- Combined value (what Gravity Forms stores as the main field value)

#### Address Field Debug (if present)
Shows how the address field was processed:
- Field ID being used
- Subfield values for each component:
  - Street address (.1)
  - Address Line 2 (.2)
  - City/Suburb (.3)
  - State/Province (.4)
  - ZIP/Postal Code (.5)
- Combined address value

#### Data Extracted
Shows the final data that was extracted for CRM import:
```json
{
  "contact_source": "form",
  "first_name": "John",
  "last_name": "Doe",
  "email": "john@example.com",
  "phone": "021234567",
  "address": "123 Main St, Auckland, Auckland Region, 1010",
  "suburb": "Auckland"
}
```

#### Missing Required Fields
If any required fields are missing, they'll be listed here:
- first_name
- last_name
- email
- phone
- address

#### All Entry Keys Available
Shows all the keys available in the Gravity Forms entry array. This helps identify:
- What subfield keys exist
- If the form structure is different than expected
- Custom fields that might be present

## Common Issues and Solutions

### Issue 1: "Missing required fields: first_name, last_name"
**Problem**: The form has a text field for name instead of a Name field type.

**Solution**: 
- Change the field to use Gravity Forms "Name" field type, OR
- Update the field label to include "first name" and add a separate field for "last name"

### Issue 2: "Missing required fields: address"
**Problem**: The form doesn't have an address field or it's labeled differently.

**Solution**:
- Add a Gravity Forms "Address" field type, OR
- Add a text field with label containing "address"

### Issue 3: "Missing required fields: email"
**Problem**: Email field is missing or labeled incorrectly.

**Solution**:
- Use Gravity Forms "Email" field type (automatically detected), OR
- Ensure the field label contains "email"

### Issue 4: "Duplicate entry"
**Problem**: An entry with the same email AND phone already exists in the CRM.

**Solution**:
- This is expected behavior to prevent duplicates
- Check if the entry already exists in your CRM
- If it's a false positive, you may need to manually create the entry

### Issue 5: Name/Address fields show "NOT SET"
**Problem**: The field exists but subfield values aren't being found.

**Solution**:
- Check the "All Entry Keys Available" section
- Look for keys like "1.3", "1.6" (for name fields) or "2.1", "2.3" (for address fields)
- The field ID might be different than expected
- Contact support with the debug output if the keys don't match the pattern

## Required Fields for Import

For an entry to be imported successfully, it MUST have all of these:

1. **first_name** - From:
   - Gravity Forms Name field (auto-detected), OR
   - Text field with label containing "first name", "first", or "fname"

2. **last_name** - From:
   - Gravity Forms Name field (auto-detected), OR
   - Text field with label containing "last name", "last", "surname", or "lname"

3. **email** - From:
   - Gravity Forms Email field (auto-detected), OR
   - Text field with label containing "email" or "e-mail"

4. **phone** - From:
   - Gravity Forms Phone field (auto-detected), OR
   - Text field with label containing "phone", "telephone", or "mobile"

5. **address** - From:
   - Gravity Forms Address field (auto-detected and all parts combined), OR
   - Text field with label containing "address" or "street address"

## Optional Fields

These fields are extracted if available but not required:

- **suburb** - From Address field city component or text field with "suburb", "city", or "town"
- **move_date** - From date field or text field with "move date", "moving date", or "preferred date"
- **move_time** - From time field or text field with "move time", "moving time", or "preferred time"

## Troubleshooting Tips

### 1. Start Small
When debugging, import just 1-5 entries first to make the debug output manageable.

### 2. Check Field Types
The import works best with native Gravity Forms field types:
- Use "Name" field type for names
- Use "Address" field type for addresses
- Use "Email" field type for email
- Use "Phone" field type for phone

### 3. Check Field Labels
If using text fields, make sure labels contain the expected keywords:
- "First Name" for first name
- "Email Address" for email
- "Phone Number" for phone
- etc.

### 4. Look at All Entry Keys
The "All Entry Keys Available" section shows the exact structure of the entry. This is the most technical part but can help identify issues.

### 5. Contact Support
If you're still stuck, copy the entire debug output and send it to support. The detailed information will help identify the exact issue.

## Version Information
- Debug mode added in version 2.2
- Compatible with Gravity Forms 2.0+
- Works with all standard Gravity Forms field types

## Security Note
Debug information may contain customer data (names, emails, addresses). Only use debug mode in a secure environment and don't share debug output publicly without redacting sensitive information.
