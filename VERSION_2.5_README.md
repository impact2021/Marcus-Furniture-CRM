# Version 2.5 - Quick Start Guide

## What's New in Version 2.5?

Three major improvements based on user feedback:

1. **See Which Gravity Form Created Each Enquiry** - Form names now display clearly
2. **Bigger, Clearer Move Dates** - Highlighted and color-coded for easy scanning
3. **Clean Uninstall** - Removes all data when plugin is deleted

## For Administrators

### After Updating to 2.5

You'll immediately notice:

#### In the Enquiries Table:
- **Form names appear in blue** under the "Form" badge
  - Example: "Moving House Enquiry Form", "Pickup Request Form"
- **Move dates are highlighted** in a yellow box with larger, bold text
  - Dates in red/pink, times in blue
  - Much easier to spot at a glance

#### What Happens Automatically:
- Database migrates to add new column (no action needed)
- All existing enquiries continue to work normally
- New enquiries from Gravity Forms will show the form name

#### What You Need to Do:
- **Nothing!** Just update and you're done.

## For Developers

### Technical Changes

**Database:**
- New column: `source_form_name` VARCHAR(255)
- Migration: `hs_crm_migrate_to_2_5_0()` runs automatically
- Database version: Updated to 2.5.0

**Code:**
- Modified: 5 files (main plugin, database, admin, CSS, readme)
- Created: 1 file (uninstall.php)
- Lines changed: ~100 LOC modified, ~640 LOC added (mostly docs)

**Backward Compatibility:**
- ✅ 100% backward compatible
- ✅ Existing enquiries work unchanged
- ✅ No breaking changes

### Files Modified

```
marcus-furniture-crm.php
├─ Version updated to 2.5
├─ Added migration check for 2.5.0
├─ Added hs_crm_migrate_to_2_5_0() function
└─ Updated Gravity Forms integration to capture form title

includes/class-hs-crm-database.php
├─ Added source_form_name to table schema
└─ Updated insert_enquiry() to handle new field

includes/class-hs-crm-admin.php
├─ Added form_source_label variable
├─ Modified display to show form name
└─ Enhanced move date HTML with highlighting

assets/css/styles.css
├─ Added .hs-crm-source-badge styles
├─ Added .hs-crm-move-date styles
├─ Added .hs-crm-date-highlight styles
└─ Added .hs-crm-time-highlight styles

readme.txt
├─ Updated stable tag to 2.5
└─ Added version 2.5 to changelog
```

### New File Created

```
uninstall.php
├─ Drops all 4 database tables
├─ Deletes all plugin options
├─ Removes CRM Manager role
└─ Cleans up admin capabilities
```

## Documentation Included

| Document | Purpose |
|----------|---------|
| VERSION_2.5_IMPLEMENTATION.md | Technical details of all changes |
| VERSION_2.5_TESTING_GUIDE.md | Step-by-step testing procedures |
| VERSION_2.5_FINAL_SUMMARY.md | Complete implementation summary |
| VERSION_2.5_VISUAL_GUIDE.md | Visual before/after examples |
| CHANGELOG.md | User-friendly change history |
| readme.txt | WordPress plugin repository info |

## Installation

### Fresh Install
1. Upload plugin files to `/wp-content/plugins/marcus-furniture-crm/`
2. Activate plugin
3. Database tables created with version 2.5 schema
4. Configure settings at MF Enquiries > Settings

### Upgrading from Earlier Version
1. Upload version 2.5 files (overwrites existing)
2. Activate/reactivate plugin
3. Migration runs automatically
4. Check that `hs_crm_db_version` option = '2.5.0'
5. Done!

## Testing

### Quick Smoke Test (5 minutes)

1. **Check Gravity Forms Integration:**
   - Submit a test enquiry via Gravity Forms
   - Go to MF Enquiries > Enquiries
   - Verify form name appears in blue

2. **Check Move Date Display:**
   - Find an enquiry with a move date
   - Verify it's highlighted in yellow box
   - Verify date is larger and in red/pink

3. **Check Migration:**
   - Go to database or phpMyAdmin
   - Check `wp_hs_enquiries` table
   - Verify `source_form_name` column exists

### Full Testing
See VERSION_2.5_TESTING_GUIDE.md for comprehensive test procedures.

## Rollback Plan

If issues occur:

1. Deactivate version 2.5
2. Restore version 2.4 from backup
3. Database will still work (new column is harmless)
4. Report issue to developers

The `source_form_name` column won't cause problems even if version 2.4 is restored - it will simply be ignored.

## FAQ

**Q: Will this work with my existing enquiries?**  
A: Yes! Existing enquiries will display normally. They just won't have a form name (since that wasn't captured before version 2.5). All new enquiries will show the form name.

**Q: Do I need to reconfigure anything?**  
A: No! The update is completely automatic.

**Q: What if I don't use Gravity Forms?**  
A: The update still works fine. The form name field will simply be empty for manually created enquiries.

**Q: Will the move date highlighting affect mobile view?**  
A: Yes, it works on mobile too. The highlighting makes dates even easier to see on small screens.

**Q: Does the uninstall script run when I deactivate?**  
A: No! It only runs when you DELETE the plugin completely. Deactivation is safe and preserves all data.

**Q: What happens to users with the CRM Manager role when I uninstall?**  
A: The user accounts remain, but they'll need to be reassigned to a different role since the CRM Manager role will be removed.

## Support

For issues or questions:
1. Check VERSION_2.5_TESTING_GUIDE.md
2. Review VERSION_2.5_VISUAL_GUIDE.md for examples
3. Check debug.log for error messages
4. Contact Impact Websites support

## Credits

- Developed by Impact Websites
- Version 2.5 addresses user feedback and feature requests
- Special thanks to beta testers

## License

GPL v2 or later
