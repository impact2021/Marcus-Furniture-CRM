# Marcus Furniture CRM - Version 2.10 Release Notes

## Overview
Version 2.10 fixes a critical issue where the preferred time field from Gravity Forms submissions was not displaying correctly in the edit modal.

## Bug Fix

### Preferred Time Display in Edit Modal

**Issue:**
When editing an enquiry that was created from a Gravity Forms submission, the preferred time (move_time) field would not display in the edit modal, even though the time was properly stored in the database and displayed in the enquiry list.

**Root Cause:**
The database stores time values in MySQL TIME format (HH:MM:SS), but HTML5 time input fields require the format to be exactly HH:MM (without seconds). When the enquiry data was returned from the AJAX call to populate the edit modal, the time value was sent in its raw database format, which the time input field couldn't recognize.

**Solution:**
Modified the `ajax_get_enquiry()` method in `class-hs-crm-admin.php` to format the `move_time` value to HH:MM format before returning it to the JavaScript. This ensures compatibility with the HTML5 time input field in the edit modal.

**Code Changes:**
- Updated `includes/class-hs-crm-admin.php` - Added time formatting in `ajax_get_enquiry()` method
- Updated plugin version to 2.10 in `marcus-furniture-crm.php`
- Updated stable tag to 2.10 in `readme.txt`

## Technical Details

### Time Format Handling

The fix converts time values from the database format to the HTML5 time input format:

**Before:**
```
Database: 14:30:00
AJAX Response: "14:30:00"
HTML5 Time Input: Not recognized (expects HH:MM)
Result: Field appears empty
```

**After:**
```
Database: 14:30:00
AJAX Response: "14:30" (formatted)
HTML5 Time Input: Recognized
Result: Time displays correctly as 2:30 PM
```

### Affected Functionality

**Fixed:**
- Edit modal now correctly displays the preferred time for all enquiries
- Time values from Gravity Forms submissions are properly shown
- Users can now edit the preferred time without having to re-enter it

**Not Changed:**
- Time display in the enquiry list (still shows in user-friendly format like "2:30PM")
- Time storage in the database (still uses MySQL TIME column type)
- Gravity Forms integration (still captures time correctly)

## Upgrade Notes

### Automatic Update
This is a bug fix release and requires no manual intervention. Simply update the plugin and the fix will be applied immediately.

### No Database Changes
Version 2.10 does not modify the database structure. All existing enquiries and their time values remain unchanged.

### Testing After Update
To verify the fix is working:

1. Go to MF Enquiries dashboard
2. Find an enquiry that has a preferred time set
3. Click "Edit Enquiry"
4. Check that the "Preferred Time" field shows the correct time
5. You should now be able to edit and save the time

## Compatibility

### WordPress
- Tested up to: WordPress 6.8
- Minimum version: WordPress 5.0

### PHP
- Minimum version: PHP 7.0
- Recommended: PHP 7.4 or higher

### Gravity Forms
- Compatible with all recent versions of Gravity Forms
- Works with both simple text fields and time picker fields

## Support

If you encounter any issues with this update:

1. Clear your browser cache
2. Verify the plugin version is 2.10 (check in Plugins page)
3. Test with a fresh enquiry from a Gravity Forms submission
4. Contact your administrator if the issue persists

## Future Enhancements

Potential improvements for future versions:
- Support for additional time formats from various form plugins
- Enhanced time zone handling for international customers
- Time range validation (e.g., business hours only)

## Credits

This fix addresses user feedback about the edit modal not showing preferred times from Gravity Forms. Thank you to all users who reported this issue.

---

**Version:** 2.10  
**Release Date:** January 2026  
**Developer:** Impact Websites
