# Gravity Forms Dropdown Import Fix

## Issue
Dropdown menu items from Gravity Forms (stairs involved, help assembling, existing furniture, etc.) were not being imported into the CRM. The dropdowns showed "Select..." instead of the actual values.

## Root Cause
The Gravity Forms field labels in the actual forms didn't exactly match the expected labels in the CRM's field mapping array. For example:
- Form label: "Do you need help assembling the item we're collecting?"
- Expected label: "Do you need help assembling"

The partial matching logic should have caught this, but some label variations were missing from the mapping array.

## Solution

### 1. Enhanced Label Matching
Added more label variations to the field mapping to catch different phrasings used in Gravity Forms:

```php
'stairs_from' => array(
    'stairs from', 
    'stairs involved? (from)', 
    'stairs (from)', 
    'stairs at pickup', 
    'stairs involved from', 
    'stairs involved? (pickup)' // NEW
),
'stairs_to' => array(
    'stairs to', 
    'stairs involved? (to)', 
    'stairs (to)', 
    'stairs at delivery', 
    'stairs involved to', 
    'stairs involved? (delivery)' // NEW
),
'furniture_moved_question' => array(
    'existing furniture moved', 
    'furniture moved', 
    'do you need any existing furniture moved', 
    'furniture',  // NEW
    'need any existing furniture' // NEW
),
'assembly_help' => array(
    'assembly help', 
    'help assembling', 
    'do you need help assembling', 
    'assembly', 
    'assembling', // NEW
    'help assembling the item' // NEW
),
// ... and more
```

### 2. Debug Logging
Added automatic logging of unmatched dropdown fields to WordPress error log:

```
Marcus Furniture CRM: Unmatched dropdown field in Gravity Forms import - 
Label: "Some Label", Type: select, Value: "Yes", Form: "Moving Enquiry" (ID: 5)
```

This helps identify which field labels in your Gravity Forms don't match the expected patterns.

## How to Use This Fix

### Step 1: Update the Plugin
Deploy the updated code to your WordPress site.

### Step 2: Test the Import
1. Go to **Enquiries > Settings** in WordPress admin
2. Select a Gravity Form from the dropdown
3. Click "Import Entries"
4. Check if dropdown values are now imported correctly

### Step 3: Verify in CRM Dashboard
1. Go to **Enquiries** in WordPress admin
2. Click "Edit" on an imported enquiry
3. Verify that dropdown fields show the selected values (not "Select...")

### Step 4: Check for Unmatched Fields
If some dropdown fields still show "Select...":

1. Enable WordPress debug logging by adding to `wp-config.php`:
   ```php
   define('WP_DEBUG', true);
   define('WP_DEBUG_LOG', true);
   define('WP_DEBUG_DISPLAY', false);
   ```

2. Import (or re-import) a Gravity Forms entry

3. Check `wp-content/debug.log` for messages like:
   ```
   Marcus Furniture CRM: Unmatched dropdown field...
   ```

4. Note the exact field label that's not matching

5. Add that label to the appropriate field mapping in:
   - `marcus-furniture-crm.php` (line ~821-833)
   - `includes/class-hs-crm-settings.php` (line ~589-601)

## Affected Dropdown Fields

The fix improves import for these dropdown fields:

| CRM Field | Form Labels That Match |
|-----------|------------------------|
| `stairs_from` | "Stairs involved? (from)", "Stairs (from)", "Stairs at pickup", "Stairs involved? (pickup)" |
| `stairs_to` | "Stairs involved? (to)", "Stairs (to)", "Stairs at delivery", "Stairs involved? (delivery)" |
| `assembly_help` | "Help assembling", "Do you need help assembling", "Assembly", "Assembling", "Help assembling the item" |
| `furniture_moved_question` | "Existing furniture moved", "Furniture moved", "Do you need any existing furniture moved", "Furniture", "Need any existing furniture" |
| `outdoor_plants` | "Outdoor plants", "Any outdoor plants", "Plants", "Outdoor" |
| `oversize_items` | "Oversize items", "Any oversize items", "Piano", "Spa", "Large items", "Oversize" |
| `driveway_concerns` | "Driveway concerns", "Driveway", "Anything that could be a concern with the driveway", "Concern with the driveway" |
| `move_type` | "Move type", "Type of move", "What's the type of your move", "Type of your move" |
| `house_size` | "House size", "Size of move", "What's the size of your move", "Move size", "Size of your move" |

## How Label Matching Works

The import process uses two-stage matching:

### Stage 1: Exact Match (Case-Insensitive)
The field label is converted to lowercase and matched exactly against the expected labels:
```php
if ($field_label === $label) {
    // Match found!
}
```

### Stage 2: Partial Match
If no exact match is found, partial matching is used:
```php
if (strpos($field_label, $label) !== false) {
    // Match found!
}
```

### Example
Form label: "Do you need help assembling the item we're collecting?"

1. Converted to lowercase: "do you need help assembling the item we're collecting?"
2. Exact match attempt fails (no exact match in array)
3. Partial match succeeds: Contains "help assembling" ✅
4. Field is mapped to `assembly_help`

## Troubleshooting

### Problem: Dropdown still shows "Select..." after import
**Solution**: Check WordPress error log for unmatched dropdown field messages. Add the exact label from your Gravity Form to the field mapping array.

### Problem: Can't find the error log
**Solution**: 
1. Enable debugging in `wp-config.php` (see Step 4 above)
2. Log file is at `wp-content/debug.log`
3. On some hosts, logs may be in a different location - check with your hosting provider

### Problem: Want to add a custom field label
**Solution**: Edit both files:
1. `marcus-furniture-crm.php` - Find the `$field_mapping` array around line 821
2. `includes/class-hs-crm-settings.php` - Find the `$field_mapping` array around line 589
3. Add your custom label to the appropriate field's array

Example:
```php
'assembly_help' => array(
    'assembly help', 
    'help assembling', 
    'do you need help assembling', 
    'assembly', 
    'assembling', 
    'help assembling the item',
    'your custom label here' // ADD THIS
)
```

### Problem: Some fields import but others don't
**Solution**: This usually means some field labels match but others don't. Check the error log to identify which specific fields aren't matching, then add their labels to the mapping.

## Testing

Two comprehensive test scripts are included in `/tmp/`:

1. **test_dropdown_matching.php** - Tests 13 different label variations
2. **test_complete_import.php** - Simulates complete end-to-end import of 9 dropdown fields

Both tests pass successfully with the enhanced label matching.

## Files Modified

1. **marcus-furniture-crm.php**
   - Enhanced field label mappings (line ~821-833)
   - Added debug logging for unmatched dropdowns (after line 1038)

2. **includes/class-hs-crm-settings.php**
   - Enhanced field label mappings (line ~589-601)
   - Added debug logging for unmatched dropdowns (after line 838)

## Version
This fix was implemented in version 2.10.1

## Related Issues
- Previous fix: GRAVITY_FORMS_IMPORT_FINAL_FIX.md (fixed name and address field imports)
- This fix: Dropdown menu import for select/radio fields
