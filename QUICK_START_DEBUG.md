# Quick Start: Gravity Forms Import Debug Mode

## TL;DR
If your Gravity Forms import shows "Imported: 0, Skipped: X", enable debug mode to see exactly why.

## 3-Step Quick Fix

### Step 1: Enable Debug Mode
1. Go to **WordPress Admin > Enquiries > Settings**
2. Scroll to **"Gravity Forms Import"** section
3. ✅ Check **"Enable detailed debugging"**

### Step 2: Run Import
1. Select your form from the dropdown
2. Set number of entries (start with 5 for testing)
3. Click **"Import Entries"**

### Step 3: Read the Output
Look for red-bordered entries (these were skipped):

#### If you see: "Missing required fields: first_name, last_name"
**Fix**: Change your form field from "Single Line Text" to "Name" field type
- OR -
**Fix**: Add two separate text fields labeled "First Name" and "Last Name"

#### If you see: "Missing required fields: phone"
**Fix**: Add a Phone field to your form (or text field labeled "Phone Number")

#### If you see: "Missing required fields: address"
**Fix**: Add an Address field to your form (or text field labeled "Address")

#### If you see: "Missing required fields: email"
**Fix**: Add an Email field to your form (or text field labeled "Email Address")

#### If you see: "Duplicate entry"
**Fix**: This entry already exists in your CRM (same email + phone)
- Check your CRM for the existing entry
- This is normal behavior to prevent duplicates

## Common Form Fixes

### Best Practice: Use Gravity Forms Field Types
✅ **DO THIS**:
- Name field (type: Name)
- Email field (type: Email)
- Phone field (type: Phone)
- Address field (type: Address)

❌ **NOT THIS**:
- Text field labeled "Full Name"
- Text field labeled "Your Email"
- Text field labeled "Contact Number"

### Why Field Types Matter
Gravity Forms stores compound fields (Name, Address) differently:
- Text field = single value
- Name field = separate first and last name values
- Address field = separate street, city, state, zip values

The importer needs these separate values to fill in the CRM correctly.

## What Each Status Means

### ✅ Success (Green Border)
Entry was imported successfully into the CRM.

### ❌ Missing Fields (Red Border)
Entry is missing one or more required fields. Check which fields are listed.

### ❌ Duplicate (Red Border)
An entry with the same email AND phone already exists in the CRM.

### ❌ Error (Red Border)
Database error occurred. Check error logs or contact support.

## Required Fields

Every entry MUST have:
1. First Name
2. Last Name
3. Email
4. Phone
5. Address

If ANY of these are missing, the entry will be skipped.

## Example: Fixing a Common Problem

**You see**: "Imported: 0, Skipped: 5 (missing required fields)"

**You enable debug and see**: "Missing required fields: first_name, last_name"

**The problem**: Your form has a text field labeled "Full Name" instead of a Name field type

**The fix**:
1. Edit your Gravity Form
2. Delete the "Full Name" text field
3. Add a new "Name" field (found in Advanced Fields)
4. Configure it to show First and Last Name
5. Make it required
6. Save the form

**Result**: Next import will extract first_name and last_name correctly!

## Still Need Help?

1. Enable debug mode
2. Import just 1-2 entries
3. Copy the entire debug output
4. Send to support with your form configuration

The debug output contains everything needed to diagnose the issue.

## Pro Tips

- Always test with 1-5 entries first when debugging
- Check that all required fields are marked "required" in Gravity Forms
- Use native Gravity Forms field types whenever possible
- Read the "Data Extracted" section to see what values were found
- Check "All Entry Keys Available" if you suspect a field mapping issue

## More Documentation

- Full Guide: `GRAVITY_FORMS_DEBUG_GUIDE.md`
- Visual Examples: `GRAVITY_FORMS_DEBUG_EXAMPLES.md`
- Release Notes: `VERSION_2.2_RELEASE_SUMMARY.md`
