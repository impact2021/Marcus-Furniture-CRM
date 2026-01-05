# Marcus Furniture CRM - Version 2.6 Release Notes

## Overview
Version 2.6 introduces significant improvements to form handling, user feedback, and data collection for both Moving House and Pickup/Delivery enquiries.

## New Features

### 1. Enhanced Gravity Forms Integration

The system now better distinguishes between different types of Gravity Forms submissions:

#### How to Set Form Type
You can now explicitly set whether a Gravity Form should be treated as "Moving House" or "Pickup/Delivery" by adding CSS classes to your form:

**For Moving House forms:**
- Add CSS class: `moving-house` or `house-move`

**For Pickup/Delivery forms:**
- Add CSS class: `pickup-delivery` or `delivery`

#### Adding CSS Classes to Gravity Forms
1. Open your Gravity Form in the editor
2. Go to Form Settings
3. Look for "CSS Class Name" field
4. Add one of the above CSS classes (e.g., `moving-house`)
5. Save the form

The system will prioritize the CSS class when determining form type, then fall back to checking the form title.

### 2. Truck Assignment Confirmation

When assigning a truck to an enquiry via the dropdown selector, the system now displays a confirmation alert to ensure the change was saved successfully. This provides immediate feedback and prevents uncertainty about whether the assignment was successful.

### 3. Redesigned Add/Edit Enquiry Modal

The enquiry modal has been completely redesigned with conditional fields based on enquiry type.

#### New Conditional Field Structure

**Common Fields (always visible):**
- Name (First and Last)
- Phone Number
- Email
- Moving/Delivery Date
- Preferred Time

**House Move or Pickup/Delivery Checkbox:**
Check this box to switch between the two modes. The form will show different fields based on your selection.

#### Moving House Fields
When "House move" is selected, the following fields are shown:

- **Moving from:** Street Address (Required)
- **Stairs involved? (From):** Yes/No (Required)
- **Moving to:** Street Address (Required)
- **Stairs involved? (To):** Yes/No (Required)
- **What's the type of your move?** Residential/Office (Required)
- **What's the size of your move?** (Required)
  - 1 Room Worth of Items Only
  - 1 BR House - Big Items Only
  - 1 BR House - Big Items and Boxes
  - 2 BR House - Big Items Only
  - 2 BR House - Big Items and Boxes
  - 3 BR House - Big Items Only
  - 3 BR House - Big Items and Boxes
  - 4 BR Houses or above
- **Additional info:** Free text field
- **Any outdoor plants?** Yes/No (Required)
- **Any oversize items such as piano, double-door fridge or spa?** Yes/No (Required)
- **Anything that could be a concern with the driveway?** Yes/No (Required)

#### Pickup/Delivery Fields
When unchecked (Pickup/Delivery mode), the following fields are shown:

- **Alternate delivery date:** Date picker
- **Where is the item(s) being collected from?** Street Address (Required)
- **Stairs involved? (Pickup):** Yes/No (Required)
- **Where is the item(s) being delivered to?** Street Address (Required)
- **Stairs involved? (Delivery):** Yes/No (Required)
- **What item(s) are being collected?** Free text (Required)
- **Any special Instructions?** Free text
- **Do you need help assembling the item we're collecting?** Yes/No
- **Do you need any existing furniture moved?** Yes/No (Required)

## Database Changes

Version 2.6 includes a database migration that adds the following new fields:
- `move_type` - Type of move (Residential/Office)
- `outdoor_plants` - Whether outdoor plants need to be moved
- `oversize_items` - Whether there are oversize items
- `driveway_concerns` - Any concerns with driveway access
- `assembly_help` - Whether assembly help is needed
- `alternate_date` - Alternate delivery date
- `house_size` - Now used for detailed move size selection

The migration will run automatically when the plugin is updated.

## Backward Compatibility

All existing enquiries will continue to work normally. When editing an old enquiry:
- The form will automatically detect if it's a Moving House or Pickup/Delivery enquiry based on the `job_type` field
- The appropriate fields will be shown
- Any previously saved data will be preserved

## Tips for Users

1. **Gravity Forms Setup**: For best results, add the appropriate CSS class to your Gravity Forms to ensure they're correctly categorized as either "Moving House" or "Pickup/Delivery"

2. **Creating Manual Enquiries**: When using the "Add New Enquiry" button, make sure to check the checkbox if it's a house move, or leave it unchecked for pickup/delivery

3. **Editing Enquiries**: The edit modal will automatically show the correct fields based on the enquiry type. You can still switch between modes if needed.

4. **Truck Assignment**: Always wait for the confirmation alert after assigning a truck to ensure your change was saved

## Support

If you encounter any issues with the new features, please check:
1. That your database has been migrated to version 2.6.0 (check WordPress admin)
2. That your Gravity Forms have the correct CSS classes
3. Clear your browser cache if the modal fields don't appear correctly

For additional support, please contact your administrator.
