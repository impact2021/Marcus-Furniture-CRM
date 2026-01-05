# Version 2.5 Testing Guide

This guide provides step-by-step testing procedures for the three main features added in version 2.5.

## Prerequisites

- WordPress installation with the plugin activated
- Gravity Forms plugin installed (for testing Gravity Forms integration)
- At least 2 different Gravity Forms set up with moving/enquiry keywords in their titles
- Admin access to the WordPress site

## Test 1: Gravity Form Name Display

### Objective
Verify that enquiries show which specific Gravity Form they came from.

### Test Steps

1. **Create Test Forms in Gravity Forms**
   - Create or verify you have at least 2 forms:
     - Example: "Moving House Enquiry Form"
     - Example: "Pickup Delivery Request"
   - Ensure each form has the `crm-integration` CSS class OR contains keywords like "moving", "enquiry", "pickup", or "delivery" in the title

2. **Submit Test Enquiries**
   - Submit an enquiry through the first form
   - Submit an enquiry through the second form
   - Ensure all required fields are filled (first name, last name, email, phone, address)

3. **Verify Admin Display**
   - Navigate to MF Enquiries > Enquiries
   - Find the two test enquiries
   - Check the "Source & Dates" column

### Expected Results

For each enquiry, you should see (from top to bottom):
```
Form  (← Source badge in blue)
Moving House Enquiry Form  (← Form name in blue - NEW)
Moving House  (← Job type in grey - existing)
Contact: 05/01/2026
Move: [highlighted date]
```

The form name should:
- ✅ Match the Gravity Form title exactly
- ✅ Appear in blue color (#0073aa)
- ✅ Be positioned between the source badge and job type
- ✅ Be different for each form

### Troubleshooting

If the form name doesn't appear:
- Check that the Gravity Form has integration keywords in the title
- Verify the form doesn't have the `no-crm-integration` CSS class
- Check the enquiry notes - the form name should be recorded there as "Enquiry created from Gravity Forms: [Form Name]"

## Test 2: Enhanced Move Date Visibility

### Objective
Verify that move dates are more prominent and easier to see.

### Test Steps

1. **View Enquiries with Move Dates**
   - Navigate to MF Enquiries > Enquiries
   - Find enquiries that have move dates set
   - Look at the "Source & Dates" column

2. **Check Visual Styling**
   - Observe the move date presentation
   - Compare with other text on the same row

### Expected Results

The move date should display with:
- ✅ **Yellow background box** (#fff9e6) with padding
- ✅ **Orange left border** (3px, #ffa500)
- ✅ **Larger date font** (15px, bold)
- ✅ **Red/pink color** for the date (#c7254e with light pink background)
- ✅ **Blue color** for the time (#0066cc)
- ✅ Stand out significantly from other information

Example of what you should see:
```
┌─────────────────────────────────┐
│ [Orange] Move: 05/01/2026 2:30PM│  ← Yellow background box
│   border      ^^^^^^^^^^  ^^^^^^ │
│                (red/pink) (blue) │
└─────────────────────────────────┘
```

### Before vs After Comparison

**Before Version 2.5:**
```
Move: 05/01/2026 2:30PM
(small text, grey color, no highlighting)
```

**After Version 2.5:**
```
[Highlighted yellow box with orange border]
Move: 05/01/2026  2:30PM
      ^^^^^^^^^^  ^^^^^^
      (larger,    (blue,
       bold,      bold,
       red/pink)  medium)
```

## Test 3: Data Removal on Uninstall

### ⚠️ WARNING
**DO NOT perform this test on a production site!** 
Use a development/staging environment with test data only.

### Objective
Verify that all plugin data is removed when the plugin is uninstalled.

### Pre-Test Verification

1. **Check Current Database**
   - Access phpMyAdmin or database management tool
   - Verify these tables exist:
     - `wp_hs_enquiries`
     - `wp_hs_enquiry_notes`
     - `wp_hs_trucks`
     - `wp_hs_truck_bookings`

2. **Check Plugin Options**
   - Go to Settings > MF CRM Settings
   - Note the configured email, timezone, etc.

3. **Check User Roles**
   - Go to Users > Add New
   - Verify "CRM Manager" appears in the Role dropdown

### Test Steps

1. **Deactivate Plugin**
   - Go to Plugins
   - Find "Marcus Furniture CRM"
   - Click "Deactivate"
   - Plugin should deactivate normally

2. **Delete Plugin**
   - After deactivation, click "Delete"
   - Confirm deletion when prompted
   - **This triggers uninstall.php**

3. **Verify Database Tables Removed**
   - Access phpMyAdmin
   - Check that these tables are GONE:
     - `wp_hs_enquiries` ❌
     - `wp_hs_enquiry_notes` ❌
     - `wp_hs_trucks` ❌
     - `wp_hs_truck_bookings` ❌

4. **Verify Options Removed**
   - Run this SQL query:
     ```sql
     SELECT * FROM wp_options WHERE option_name LIKE '%hs_crm%';
     ```
   - Should return 0 rows

5. **Verify Role Removed**
   - Go to Users > Add New (or use another plugin that shows roles)
   - "CRM Manager" should NOT appear in role dropdown

6. **Check Administrator Capabilities**
   - Verify admin can still access other parts of WordPress normally
   - CRM-specific capabilities should be removed from admin role

### Expected Results

After uninstall:
- ✅ All 4 database tables completely removed
- ✅ All plugin options deleted
- ✅ CRM Manager role removed
- ✅ CRM capabilities removed from Administrator role
- ✅ No orphaned data in the database
- ✅ WordPress functions normally

### Important Notes

- **User accounts** that were assigned to CRM Manager role are preserved
  - They still exist as users
  - They need to be manually reassigned to a different role
- **This is permanent** - there is no undo
- **Backup before testing** if you have any data you might want to keep

## Test 4: Database Migration

### Objective
Verify that existing installations properly migrate to version 2.5 database schema.

### Prerequisites
- Access to a WordPress installation with version 2.4 (or earlier) of the plugin
- Some existing enquiries in the database

### Test Steps

1. **Check Current Database Version**
   - Before updating, run this SQL:
     ```sql
     SELECT option_value FROM wp_options WHERE option_name = 'hs_crm_db_version';
     ```
   - Note the current version (should be < 2.5.0)

2. **Update Plugin**
   - Upload version 2.5 plugin files
   - The migration should run automatically

3. **Verify Migration**
   - Run this SQL to check the new column exists:
     ```sql
     SHOW COLUMNS FROM wp_hs_enquiries LIKE 'source_form_name';
     ```
   - Should return 1 row showing the column details

4. **Check Database Version Updated**
   - Run this SQL:
     ```sql
     SELECT option_value FROM wp_options WHERE option_name = 'hs_crm_db_version';
     ```
   - Should return '2.5.0'

5. **Verify Existing Data Intact**
   - All existing enquiries should still display normally
   - No data should be lost
   - The `source_form_name` field will be empty for old enquiries (this is expected)

### Expected Results

- ✅ Migration runs automatically on plugin update
- ✅ New column added successfully
- ✅ Database version updated to 2.5.0
- ✅ All existing data preserved
- ✅ No errors in debug log

## Regression Testing

### Verify Existing Features Still Work

1. **Create Manual Enquiry**
   - Click "+ Add New Enquiry"
   - Fill in all fields
   - Save and verify it appears in the list

2. **Edit Enquiry**
   - Click "Edit" on an existing enquiry
   - Modify some fields
   - Save and verify changes are saved

3. **Add Notes**
   - Add a note to an enquiry
   - Verify note appears with timestamp

4. **Update Status**
   - Change status using dropdown
   - Verify status updates immediately

5. **Send Email**
   - Try sending a quote email
   - Verify email sends successfully

6. **Truck Scheduler**
   - Navigate to Truck Scheduler
   - Create a booking
   - Verify it appears in the calendar

## Success Criteria

All tests should pass with:
- ✅ No PHP errors in debug log
- ✅ No JavaScript console errors
- ✅ All new features working as documented
- ✅ All existing features still functioning
- ✅ Database migrations completing successfully
- ✅ Proper data cleanup on uninstall (when tested)

## Reporting Issues

If any test fails, document:
- Which test failed
- Steps to reproduce
- Expected result
- Actual result
- Screenshots (especially for visual issues)
- Browser and WordPress version
- Any error messages from debug log
