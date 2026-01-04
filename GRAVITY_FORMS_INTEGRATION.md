# Gravity Forms Integration Guide

The Marcus Furniture CRM plugin automatically integrates with Gravity Forms, allowing you to use Gravity Forms' advanced features while still capturing enquiries in your CRM system.

## Overview

When a Gravity Form is submitted, the plugin automatically:
1. Extracts customer information from the form fields
2. Creates a new enquiry in the CRM database
3. Adds a note indicating the source was Gravity Forms
4. Sends an admin notification email (Gravity Forms handles customer notifications)

## Enabling Integration

There are two ways to enable CRM integration for a Gravity Form:

### Method 1: Form Title (Automatic)

If your form title contains any of these keywords, integration is automatically enabled:
- "moving"
- "enquiry" 
- "contact"
- "furniture"
- "quote"

**Examples:**
- "Moving Quote Request" ✅
- "Furniture Enquiry Form" ✅
- "Contact Us" ✅
- "Book a Truck" ❌ (use Method 2)

### Method 2: CSS Class (Manual)

For forms that don't match the automatic keywords:

1. Edit your Gravity Form
2. Go to **Form Settings** > **Advanced**
3. In the **CSS Class Name** field, add: `crm-integration`
4. Save the form

This method works for any form, regardless of title.

## Field Mapping

The plugin intelligently maps Gravity Forms fields to CRM fields based on field labels. The mapping is case-insensitive and flexible.

### Required Fields

These fields must be present for an enquiry to be created:

| CRM Field | Gravity Forms Field Labels |
|-----------|----------------------------|
| First Name | "first name", "first", "fname" |
| Last Name | "last name", "last", "surname", "lname" |
| Email | "email", "e-mail", "email address" |
| Phone | "phone", "telephone", "mobile", "phone number" |
| Address | "address", "street address", "location" |

### Optional Fields

| CRM Field | Gravity Forms Field Labels |
|-----------|----------------------------|
| Move Date | "move date", "moving date", "preferred date", "date" |
| Move Time | "move time", "moving time", "preferred time", "time" |
| Suburb | "suburb", "city", "town" |

## Supported Field Types

### Name Field
If you use Gravity Forms' **Name** field (Advanced Fields):
- The plugin automatically extracts first and last name from the name field
- Works with both simple and complex name field formats

### Address Field  
If you use Gravity Forms' **Address** field (Advanced Fields):
- All address components are combined into a single address string
- The suburb/city field is automatically extracted to the separate "Suburb" field
- Format: Street, City, State, ZIP, Country

### Date Field
If you use a **Date Picker** field for the move date:
- Date is automatically formatted and stored in the CRM
- Works with all Gravity Forms date formats

### Time Field
If you use a **Time** field for the move time:
- Time is automatically formatted and stored in the CRM
- Works with all Gravity Forms time formats

### Standard Text Fields
For simple text input fields, just ensure the field label matches one of the expected labels listed above.

## Example Form Setup

Here's a recommended setup for a moving enquiry form:

### Form Title
"Moving Quote Request"

### Form Fields

1. **Name** (Advanced Field)
   - Label: "Your Name"
   - Required: Yes
   - Format: First and Last

2. **Email** (Standard Field)
   - Label: "Email Address"
   - Required: Yes

3. **Phone** (Standard Field)
   - Label: "Phone Number"
   - Required: Yes

4. **Address** (Advanced Field)
   - Label: "Current Address"
   - Required: Yes
   - Type: US or International

5. **Date** (Advanced Field)
   - Label: "Preferred Move Date"
   - Required: No
   - Date Format: dd/mm/yyyy

6. **Time** (Advanced Field)
   - Label: "Preferred Move Time"
   - Required: No
   - Time Format: 12-hour or 24-hour

7. **Paragraph Text** (Standard Field)
   - Label: "Additional Details"
   - Required: No
   - Note: This won't be captured in CRM but will be in Gravity Forms entry

### Form Settings

- **Confirmations**: Set up your custom confirmation message
- **Notifications**: Configure Gravity Forms to send customer email
- **Advanced** > **CSS Class**: Add `crm-integration` if title doesn't contain keywords

## Verification

To verify integration is working:

1. **Submit a test form**
2. **Check Gravity Forms Entries**: Form submission should appear in Gravity Forms
3. **Check MF Enquiries**: New enquiry should appear in CRM dashboard
4. **Check the note**: Enquiry should have a note saying "Enquiry created from Gravity Forms: [Form Title]"
5. **Check email**: Admin should receive notification from CRM

## Troubleshooting

### Enquiries Not Being Created

**Check Form Title/CSS Class:**
- Verify form title contains a keyword OR has `crm-integration` CSS class
- Check spelling and capitalization

**Verify Field Labels:**
- Go to form editor and check exact field labels
- Ensure labels match expected patterns (see Field Mapping table)
- Labels are case-insensitive, so "First Name" = "first name"

**Check Required Fields:**
- All 5 required fields must be present in the form
- Fields must have values submitted (not left blank)

**Enable WordPress Debug:**
```php
// Add to wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

Then check `/wp-content/debug.log` for errors.

### Field Mapping Not Working

If a specific field isn't being captured:

1. **Check field label** in Gravity Forms editor
2. **Compare to expected labels** in Field Mapping table
3. **Add partial match**: Field label just needs to *contain* the keyword
   - Label "Customer Email Address" will match "email" ✅
   - Label "What is your email?" will match "email" ✅

### Duplicate Entries

If you see duplicate entries (one from built-in form, one from Gravity Forms):
- This is normal if you're using both forms
- Each form submission creates its own enquiry
- Consider using only one form type per page

## Advanced Customization

### Custom Field Mapping

If you need to customize field mapping, edit the `hs_crm_gravity_forms_integration` function in `marcus-furniture-crm.php`.

Look for the `$field_mapping` array around line 295:

```php
$field_mapping = array(
    'first_name' => array('first name', 'first', 'fname'),
    'last_name' => array('last name', 'last', 'surname', 'lname'),
    'email' => array('email', 'e-mail', 'email address'),
    'phone' => array('phone', 'telephone', 'mobile', 'phone number'),
    'address' => array('address', 'street address', 'location'),
    'suburb' => array('suburb', 'city', 'town'),
    'move_date' => array('move date', 'moving date', 'preferred date', 'date'),
    'move_time' => array('move time', 'moving time', 'preferred time', 'time')
);
```

You can add additional label matches to this array.

### Disable Integration for Specific Forms

To prevent integration for a specific form even if it matches keywords:

1. Edit the form
2. Add CSS class `no-crm-integration`
3. Update the integration function to check for this class

### Custom Integration Logic

The integration hook is:
```php
add_action('gform_after_submission', 'hs_crm_gravity_forms_integration', 10, 2);
```

You can modify the function logic or add additional hooks as needed.

## Benefits of Using Gravity Forms

While the plugin includes a built-in contact form, Gravity Forms offers additional features:

✅ **Advanced Field Types**: Conditional logic, file uploads, calculations
✅ **Spam Protection**: Built-in reCAPTCHA and honeypot
✅ **Entry Management**: View and export all submissions
✅ **Multiple Forms**: Different forms for different purposes
✅ **Add-ons**: Payment processing, email marketing, etc.
✅ **User Registration**: Create WordPress users from form submissions
✅ **Post Creation**: Automatically create posts/pages from submissions

## Using Both Forms

You can use both the built-in contact form and Gravity Forms:

- **Built-in Form**: Simple, lightweight, perfectly adequate for basic contact forms
- **Gravity Forms**: Advanced features, complex forms, payment processing

Both will create enquiries in the CRM with the same data structure.

## Support

If you need help with Gravity Forms integration:

1. **Check Gravity Forms Documentation**: https://docs.gravityforms.com/
2. **Test with Debug Logging**: Enable WP_DEBUG and check logs
3. **Review Field Mapping**: Ensure field labels match expected patterns
4. **Contact Plugin Developer**: Impact Websites

## Example Forms

### Basic Contact Form

- Name (First and Last)
- Email
- Phone  
- Address
- Message

**Integration**: Add CSS class `crm-integration`

### Moving Quote Form

- Name (First and Last)
- Email
- Phone
- Current Address
- Moving To Address (won't be captured, but useful for quote)
- Preferred Move Date
- Preferred Move Time
- Property Size (dropdown)
- Special Requirements (paragraph)

**Integration**: Title contains "quote" - automatic

### Quick Callback Form

- Name (First and Last)
- Phone
- Email
- Address
- Best Time to Call

**Integration**: Add CSS class `crm-integration`

## Best Practices

1. **Use Clear Field Labels**: Make them match expected labels for automatic mapping
2. **Make Required Fields Required**: Set first name, last name, email, phone, address as required
3. **Test After Setup**: Submit a test form to verify integration
4. **Check Notifications**: Ensure both Gravity Forms and CRM emails are working
5. **Monitor Entries**: Regularly check both Gravity Forms entries and CRM enquiries
6. **Keep Forms Simple**: Only collect information you actually need
7. **Use Conditional Logic**: Show/hide fields based on user selections
8. **Enable Spam Protection**: Use reCAPTCHA or honeypot to prevent spam entries

---

**Plugin Version:** 1.0  
**Gravity Forms Compatibility:** All versions  
**Last Updated:** January 2026
