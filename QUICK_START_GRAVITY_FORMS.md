# Quick Start Guide - Gravity Forms Setup

## What's Been Implemented

✅ **Exclusion Logic** - You can now exclude forms from CRM integration  
✅ **Updated Keywords** - Added "pickup" and "delivery", removed "contact"  
✅ **Historical Import** - Import old Gravity Forms entries into the CRM  
✅ **Menu Updates** - Changed menu label to "Enquiries" with truck icon  

---

## Setup Your 3 Forms (Do This First!)

### Form 1: General Contact (DO NOT integrate)

**Your form title:** Whatever you named it  
**What to do:**
1. Edit the form in Gravity Forms
2. Go to **Form Settings** → **Advanced** tab
3. In **CSS Class Name** field, enter: `no-crm-integration`
4. Click **Update Form**

**Result:** This form will NOT create enquiries in the CRM ✅

---

### Form 2: Moving House (DO integrate)

**Your form title:** Whatever you named it  
**What to do:**

**Option A** - If title contains "moving", "enquiry", "furniture", or "quote":
- Nothing! It will work automatically ✅

**Option B** - If title doesn't contain those words:
1. Edit the form in Gravity Forms
2. Go to **Form Settings** → **Advanced** tab
3. In **CSS Class Name** field, enter: `crm-integration`
4. Click **Update Form**

**Result:** This form WILL create enquiries in the CRM ✅

---

### Form 3: Pickup and Delivery (DO integrate)

**Your form title:** Whatever you named it  
**What to do:**

**Option A** - If title contains "pickup" or "delivery":
- Nothing! It will work automatically ✅

**Option B** - If title doesn't contain those words:
1. Edit the form in Gravity Forms
2. Go to **Form Settings** → **Advanced** tab
3. In **CSS Class Name** field, enter: `crm-integration`
4. Click **Update Form**

**Result:** This form WILL create enquiries in the CRM ✅

---

## Import Historical Entries

After setting up your forms, import old entries:

1. Go to WordPress Admin → **Enquiries** → **Settings**
2. Scroll down to **Gravity Forms Import** section
3. Select your **Moving House** form from dropdown
4. Enter number of entries (try 50 first)
5. Click **Import Entries**
6. Wait for success message
7. Repeat for **Pickup and Delivery** form
8. **DO NOT** import from General Contact form

---

## Check Required Fields

For forms to work, they MUST have these fields with labels that contain:

✅ **First Name** - "first name", "first", or "fname"  
✅ **Last Name** - "last name", "last", "surname", or "lname"  
✅ **Email** - "email", "e-mail", or "email address"  
✅ **Phone** - "phone", "telephone", "mobile", or "phone number"  
✅ **Address** - "address", "street address", or "location"  

**Pro Tip:** Use Gravity Forms' advanced field types:
- Use **Name** field (Advanced Fields) for first/last name
- Use **Email** field for email
- Use **Phone** field for phone
- Use **Address** field for address

---

## Test Each Form

### Test Moving House Form:
1. Submit a test entry with fake data
2. Go to **Enquiries** dashboard
3. You should see the new enquiry ✅
4. Check the note - should say "Enquiry created from Gravity Forms: [Your Form Name]"

### Test Pickup and Delivery Form:
1. Same as above
2. Should create an enquiry ✅

### Test General Contact Form:
1. Submit a test entry
2. Go to **Enquiries** dashboard
3. Should NOT see an enquiry ✅ (this is correct!)

---

## Troubleshooting

### ❌ Moving House form not creating enquiries

**Fix:**
1. Check form title - does it contain "moving", "enquiry", "furniture", or "quote"?
2. If NO, add CSS class `crm-integration` to form
3. Check all required fields are present and have correct labels
4. Submit a test with real data in all required fields

### ❌ General Contact form IS creating enquiries

**Fix:**
1. Edit the General Contact form
2. Go to Form Settings → Advanced
3. Add CSS class `no-crm-integration`
4. Save form

### ❌ Import shows "Imported: 0, Skipped: X"

**Possible reasons:**
- Entries missing required fields
- Entries already imported (duplicates)
- Field labels don't match expected patterns

**Fix:**
1. Check one entry manually in Gravity Forms
2. Verify all required fields have data
3. Check field labels match expected patterns
4. If entries already imported, this is normal (duplicate prevention)

---

## Where to Find Things

- **Enquiries Dashboard:** WordPress Admin → Enquiries
- **Import Tool:** WordPress Admin → Enquiries → Settings (scroll down)
- **Form Settings:** Gravity Forms → Forms → [Select Form] → Settings → Advanced
- **Documentation:** 
  - `GRAVITY_FORMS_CONFIGURATION_GUIDE.md` - Detailed setup guide
  - `GRAVITY_FORMS_INTEGRATION.md` - Complete integration guide
  - `TESTING_GUIDE_GRAVITY_FORMS.md` - Testing checklist

---

## What Changed in the Menu

Look at your WordPress admin sidebar:
- Menu now says **"Enquiries"** (was "MF Enquiries")
- Icon is now a **truck** 🚚 (was arrows)

---

## Summary Checklist

After following this guide, you should have:

- [ ] General Contact form has `no-crm-integration` CSS class
- [ ] Moving House form creates enquiries (auto or manual)
- [ ] Pickup and Delivery form creates enquiries (auto or manual)
- [ ] Historical entries imported from Moving House form
- [ ] Historical entries imported from Pickup and Delivery form
- [ ] All forms tested with sample submissions
- [ ] Menu shows "Enquiries" with truck icon

---

## Need More Help?

Detailed guides are available:

1. **GRAVITY_FORMS_CONFIGURATION_GUIDE.md** - Complete setup instructions
2. **GRAVITY_FORMS_INTEGRATION.md** - Technical integration details
3. **GRAVITY_FORMS_MAPPING_SUMMARY.md** - Field mapping reference
4. **TESTING_GUIDE_GRAVITY_FORMS.md** - Full testing checklist
5. **IMPLEMENTATION_SUMMARY.md** - Technical implementation details

---

## That's It!

Your Gravity Forms should now be properly configured to:
- ✅ Exclude general contact enquiries from CRM
- ✅ Auto-create enquiries for moving house requests
- ✅ Auto-create enquiries for pickup and delivery requests
- ✅ Show professional "Enquiries" menu with truck icon

**Questions?** Check the detailed guides or contact support.
