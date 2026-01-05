# Gravity Forms Import Fix

## Issue
When importing historical Gravity Forms entries, all entries were being skipped with the message:
```
Import complete! Imported: 0, Skipped: 3 (missing required fields or duplicates)
```

## Root Cause
The field extraction logic in both the import function (`class-hs-crm-settings.php`) and live integration (`marcus-furniture-crm.php`) was checking field labels BEFORE field types.

### Problem Example
A Gravity Forms **Name field** with label "Name" was being checked against these patterns:
- first_name: ['first name', 'first', 'fname']
- last_name: ['last name', 'last', 'surname', 'lname']

Since "name" doesn't contain "first name", "first", or "fname", the code never matched the field, even though it was a Name field type that contained the data in the correct array indices (3 = first name, 6 = last name).

## Solution
Restructured the field extraction logic to **prioritize field types over label matching**:

### New Processing Order
1. **Check field type FIRST** for special Gravity Forms field types:
   - `type === 'name'` → Extract first_name and last_name from array indices
   - `type === 'address'` → Extract address and suburb from array indices
   - `type === 'email'` → Extract email directly
   - `type === 'phone'` → Extract phone directly

2. **Fall back to label matching** only for generic text/date/time fields

### Before (Old Logic)
```php
foreach ($field_mapping as $crm_field => $possible_labels) {
    foreach ($possible_labels as $label) {
        if (strpos($field_label, $label) !== false) {
            if ($field->type === 'name' && is_array($field_value)) {
                // Extract name...
            }
        }
    }
}
```

### After (New Logic)
```php
// Check type FIRST
if ($field->type === 'name' && is_array($field_value)) {
    if (isset($field_value[3]) && !empty($field_value[3])) {
        $data['first_name'] = sanitize_text_field($field_value[3]);
    }
    if (isset($field_value[6]) && !empty($field_value[6])) {
        $data['last_name'] = sanitize_text_field($field_value[6]);
    }
    continue;
}

if ($field->type === 'email') {
    $data['email'] = sanitize_email($field_value);
    continue;
}

// ... etc for other types

// THEN fall back to label matching
foreach ($field_mapping as $crm_field => $possible_labels) {
    // ...
}
```

## Files Modified
1. **includes/class-hs-crm-settings.php**
   - Function: `import_single_gravity_form_entry()`
   - Lines: ~420-485

2. **marcus-furniture-crm.php**
   - Function: `hs_crm_gravity_forms_integration()`
   - Lines: ~643-713

## Testing
Created test script (`/tmp/test_field_extraction.php`) that confirmed:
- **OLD logic**: Extracted 0 fields (all entries skipped)
- **NEW logic**: Extracted all 5 required fields (entries imported successfully)

### Test Results
```
OLD logic - Has all required fields: NO ❌
Missing: first_name, last_name, email, phone, address

NEW logic - Has all required fields: YES ✅
Missing: (none)
```

## Impact
This fix ensures that:
- ✅ Gravity Forms with standard field types (Name, Address, Email, Phone) work correctly
- ✅ Historical imports no longer skip valid entries
- ✅ Live integration captures data from new form submissions
- ✅ Both generic labels ("Name") and specific labels ("First Name") work correctly

## Backward Compatibility
The fix is fully backward compatible:
- Forms with specific labels like "First Name" will still work (label matching fallback)
- Forms using Gravity Forms advanced field types now work better
- No breaking changes to existing functionality

## Recommendations for Users
For best results, use Gravity Forms advanced field types:
- **Name field** (type: name) instead of separate text fields
- **Address field** (type: address) instead of separate text fields
- **Email field** (type: email) instead of text field
- **Phone field** (type: phone) instead of text field

These field types are automatically recognized regardless of their label.
