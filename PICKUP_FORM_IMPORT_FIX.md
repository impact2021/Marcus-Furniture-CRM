# Pickup Form Import Fix - Missing Fields

## Problem

After importing from Gravity Forms using the historical import feature, several fields were not being imported for pickup/delivery forms. These fields were missing when viewing enquiries in the edit modal:

- Alternate delivery date
- Stairs involved for either location (pickup and delivery)
- Items being collected
- Special instructions
- Help assembling
- Existing furniture removed

## Root Cause

The historical import function (`import_single_gravity_form_entry()` in `includes/class-hs-crm-settings.php`) had an incomplete field mapping array compared to the live Gravity Forms integration.

**Before the fix:**
- Historical import: Only 9 fields mapped (basic contact and date/time info)
- Live integration: 24 fields mapped (all pickup/delivery and moving house fields)

This discrepancy meant that while new form submissions captured all fields, importing historical entries missed most of the job-specific details.

## Solution

Updated the historical import function to match the live integration by adding all missing fields to the field mapping array:

### Fields Added

**Pickup/Delivery Fields:**
- `alternate_date` - Alternate delivery date
- `stairs_from` - Stairs at pickup location
- `stairs_to` - Stairs at delivery location
- `items_being_collected` - What items are being picked up/delivered
- `special_instructions` - Any special requests or notes
- `assembly_help` - Whether assembly help is needed
- `furniture_moved_question` - Whether existing furniture needs to be moved

**Moving House Fields (also added for completeness):**
- `move_type` - Type of move (Residential/Office)
- `house_size` - Size of the move
- `property_notes` - Additional property information
- `outdoor_plants` - Whether outdoor plants are involved
- `oversize_items` - Any oversized items (piano, spa, etc.)
- `driveway_concerns` - Driveway accessibility concerns

### Additional Improvements

1. **Proper Sanitization**: Updated field processing to use `sanitize_textarea_field()` for multi-line text fields
2. **Time Conversion**: Added logic to convert 12-hour format times (e.g., "9:00am") to MySQL 24-hour format
3. **Metadata Tracking**: Added `source_form_name`, `gravity_forms_entry_id`, and `gravity_forms_form_id` to better track import sources

## Files Modified

- `includes/class-hs-crm-settings.php`
  - Lines 577-602: Expanded field mapping array
  - Lines 604-609: Added metadata tracking
  - Lines 796-801, 824-833: Updated sanitization for textarea fields
  - Lines 874-900: Added time conversion logic

## How to Test

1. Go to Settings → Gravity Forms Import
2. Select a pickup/delivery form that has historical entries
3. Import the entries
4. Open an imported enquiry in the edit modal
5. Verify that all pickup/delivery fields are populated:
   - Alternate delivery date should be visible
   - Stairs selections should be preserved
   - Items being collected should be displayed
   - Special instructions should be shown
   - Assembly help preference should be saved
   - Existing furniture moved question should be captured

## Expected Behavior

**Before the Fix:**
- Historical imports would only capture basic contact info (name, email, phone, address)
- Pickup-specific fields would be empty even if the original form submission included them
- Edit modal would show blank fields for job details

**After the Fix:**
- Historical imports capture all fields, matching live form submissions
- All pickup/delivery details are preserved during import
- Edit modal displays complete information for imported entries
- Consistent behavior between live submissions and historical imports

## Compatibility

This fix is fully backward compatible:
- Existing imported entries are not affected
- Forms without these fields will continue to work (fields are optional)
- The field mapping uses flexible label matching, so various field label styles are supported

## Related Documentation

- `GRAVITY_FORMS_MAPPING_SUMMARY.md` - Complete field mapping reference
- `GRAVITY_FORMS_IMPORT_FIX.md` - Previous import fixes
- `PICKUP_DELIVERY_ENHANCEMENTS.md` - Original pickup/delivery feature documentation
