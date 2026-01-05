# Gravity Forms Configuration Guide

## Overview

This guide explains how to configure your three Gravity Forms to work correctly with the Marcus Furniture CRM.

## Your Forms Setup

Based on your requirements, here's how to configure each form:

### 1. General Contact Form (DO NOT integrate)

**Form:** General contact form  
**Action:** Add exclusion class to prevent CRM integration

**Steps:**
1. Edit your General Contact form in Gravity Forms
2. Go to **Form Settings** → **Advanced**
3. In the **CSS Class Name** field, add: `no-crm-integration`
4. Click **Update Form**

**Result:** This form will NOT create enquiries in the CRM, even though "contact" might trigger auto-integration.

---

### 2. Moving House Form (DO integrate)

**Form:** Moving house enquiry form  
**Action:** Ensure it integrates automatically or add integration class

**Option A - Automatic Integration (Recommended):**
- If your form title contains "moving", "enquiry", "furniture", or "quote", it will automatically integrate
- No additional configuration needed

**Option B - Manual Integration:**
If your form title doesn't contain these keywords:
1. Edit your Moving House form in Gravity Forms
2. Go to **Form Settings** → **Advanced**
3. In the **CSS Class Name** field, add: `crm-integration`
4. Click **Update Form**

**Result:** This form WILL create enquiries in the CRM automatically.

---

### 3. Pickup and Delivery Form (DO integrate)

**Form:** Pickup and delivery booking form  
**Action:** Will integrate automatically due to keywords

**Automatic Integration:**
- If your form title contains "pickup" or "delivery", it will automatically integrate
- No additional configuration needed

**Result:** This form WILL create enquiries in the CRM automatically.

---

## Field Requirements

For any form to create enquiries in the CRM, it MUST have these required fields:

1. **First Name** - Label must contain: "first name", "first", or "fname"
2. **Last Name** - Label must contain: "last name", "last", "surname", or "lname"
3. **Email** - Label must contain: "email", "e-mail", or "email address"
4. **Phone** - Label must contain: "phone", "telephone", "mobile", or "phone number"
5. **Address** - Label must contain: "address", "street address", or "location"

**Optional Fields:**
- **Suburb** - Label contains: "suburb", "city", or "town"
- **Move Date** - Label contains: "move date", "moving date", "preferred date", or "date"
- **Move Time** - Label contains: "move time", "moving time", "preferred time", or "time"

**Tip:** Use Gravity Forms' advanced field types (Name, Address, Date, Time) for best results. They automatically map correctly.

---

## Importing Historical Entries

To import existing form submissions from before the integration was set up:

1. Go to WordPress Admin → **Enquiries** → **Settings**
2. Scroll down to **Gravity Forms Import** section
3. Select the form you want to import from the dropdown
4. Enter the number of entries to import (default: 50, max: 1000)
5. Click **Import Entries**

**What gets imported:**
- ✅ Entries with all required fields (first name, last name, email, phone, address)
- ✅ Most recent entries first
- ✅ Each entry gets a note: "Imported from Gravity Forms: [Form Name] (Entry ID: X, Submitted: Date)"

**What gets skipped:**
- ❌ Entries missing required fields
- ❌ Duplicate entries (same email AND phone number already in CRM)
- ❌ Entries marked as spam or trash in Gravity Forms

**Recommendations:**
- Import your "Moving House" form entries
- Import your "Pickup and Delivery" form entries
- DO NOT import your "General Contact" form entries

---

## Menu Changes

The WordPress admin menu now displays:
- **Menu Label:** "Enquiries" (previously "MF Enquiries")
- **Menu Icon:** 🚚 Truck icon (dashicons-truck)

This makes it clearer and more professional in the WordPress admin sidebar.

---

## Testing Your Setup

After configuring your forms:

### Test Moving House Form:
1. Submit a test entry with all required fields
2. Check **Gravity Forms** → **Entries** - should see submission ✅
3. Check **Enquiries** dashboard - should see new enquiry ✅
4. Check the enquiry notes - should say "Enquiry created from Gravity Forms: [Form Title]" ✅
5. Admin should receive notification email ✅

### Test Pickup and Delivery Form:
1. Same steps as above
2. Verify enquiry is created ✅

### Test General Contact Form:
1. Submit a test entry
2. Check **Gravity Forms** → **Entries** - should see submission ✅
3. Check **Enquiries** dashboard - should NOT see enquiry ✅ (this is correct!)

---

## Quick Reference

| Form Type | Form Title Contains | CSS Class | Creates Enquiry? |
|-----------|-------------------|-----------|------------------|
| General Contact | Any | `no-crm-integration` | ❌ No |
| Moving House | "moving" or similar | None needed | ✅ Yes |
| Pickup & Delivery | "pickup" or "delivery" | None needed | ✅ Yes |

---

## Auto-Integration Keywords

Forms with these keywords in the title automatically integrate:
- moving
- enquiry
- pickup
- delivery
- furniture
- quote

---

## CSS Classes

- **`crm-integration`** - Forces integration even if title doesn't match keywords
- **`no-crm-integration`** - Prevents integration even if title matches keywords

---

## Troubleshooting

### Problem: Moving House form not creating enquiries

**Check 1:** Form Title or CSS Class
- Does title contain keyword? OR
- Does form have `crm-integration` CSS class?

**Check 2:** Required Fields
- All 5 required fields must be present
- Field labels must match expected patterns
- Use Gravity Forms Name field type for best results

**Check 3:** Field Values
- Submit form with actual data in all fields
- Don't leave required fields empty

### Problem: General Contact form IS creating enquiries (unwanted)

**Solution:**
1. Edit the General Contact form
2. Go to Form Settings → Advanced
3. Add `no-crm-integration` to CSS Class Name
4. Save form

### Problem: Import shows "0 imported"

**Possible causes:**
- Entries missing required fields (first name, last name, email, phone, address)
- All entries already exist in CRM (duplicates)
- Field labels don't match expected patterns

**Solution:**
- Check one entry manually to see which fields are missing
- Verify field labels in form editor
- Ensure fields are not empty in the actual entries

---

## Support

For more detailed information:
- See **GRAVITY_FORMS_INTEGRATION.md** for complete integration guide
- See **GRAVITY_FORMS_MAPPING_SUMMARY.md** for field mapping reference

---

## Summary

✅ **What's Working Now:**
1. Forms with "moving", "pickup", "delivery" automatically create enquiries
2. General contact forms can be excluded with `no-crm-integration` class
3. Historical entries can be imported via Settings page
4. Menu shows "Enquiries" with truck icon

✅ **What You Need to Do:**
1. Add `no-crm-integration` CSS class to your General Contact form
2. Verify your Moving House and Pickup & Delivery forms have all required fields
3. Test each form to confirm expected behavior
4. Import historical entries from Moving House and Pickup & Delivery forms
