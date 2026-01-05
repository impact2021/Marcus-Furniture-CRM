# Testing Guide for Gravity Forms Integration Updates

## Test Scenarios

### 1. Test Exclusion Logic (`no-crm-integration` CSS class)

**Setup:**
1. Create a test Gravity Form titled "General Contact"
2. Add all required fields (First Name, Last Name, Email, Phone, Address)
3. Go to Form Settings → Advanced
4. Add CSS class: `no-crm-integration`
5. Save the form

**Test:**
1. Submit the form with valid data
2. Check Gravity Forms → Entries - submission should exist ✅
3. Check Enquiries dashboard - NO enquiry should be created ✅

**Expected Result:** Form submission is saved in Gravity Forms but does NOT create an enquiry in CRM.

---

### 2. Test Auto-Integration with "Pickup" Keyword

**Setup:**
1. Create a test Gravity Form titled "Pickup and Delivery Request"
2. Add all required fields (First Name, Last Name, Email, Phone, Address)
3. Do NOT add any CSS classes
4. Save the form

**Test:**
1. Submit the form with valid data
2. Check Gravity Forms → Entries - submission should exist ✅
3. Check Enquiries dashboard - enquiry should be created ✅
4. Check enquiry notes - should say "Enquiry created from Gravity Forms: Pickup and Delivery Request" ✅

**Expected Result:** Form submission creates an enquiry in CRM automatically due to "pickup" keyword.

---

### 3. Test Auto-Integration with "Delivery" Keyword

**Setup:**
1. Create a test Gravity Form titled "Furniture Delivery Booking"
2. Add all required fields (First Name, Last Name, Email, Phone, Address)
3. Do NOT add any CSS classes
4. Save the form

**Test:**
1. Submit the form with valid data
2. Check Gravity Forms → Entries - submission should exist ✅
3. Check Enquiries dashboard - enquiry should be created ✅
4. Check enquiry notes - should say "Enquiry created from Gravity Forms: Furniture Delivery Booking" ✅

**Expected Result:** Form submission creates an enquiry in CRM automatically due to "delivery" keyword.

---

### 4. Test "Contact" Keyword Does NOT Auto-Integrate

**Setup:**
1. Create a test Gravity Form titled "Contact Us"
2. Add all required fields (First Name, Last Name, Email, Phone, Address)
3. Do NOT add any CSS classes
4. Save the form

**Test:**
1. Submit the form with valid data
2. Check Gravity Forms → Entries - submission should exist ✅
3. Check Enquiries dashboard - NO enquiry should be created ✅

**Expected Result:** "Contact" keyword no longer triggers auto-integration, so no enquiry is created unless you add `crm-integration` CSS class.

---

### 5. Test Historical Import Functionality

**Prerequisites:**
- Have at least one Gravity Form with existing entries
- Form should have all required fields mapped correctly

**Test:**
1. Go to WordPress Admin → Enquiries → Settings
2. Scroll to "Gravity Forms Import" section
3. Select a form from the dropdown
4. Set limit to 10 entries
5. Click "Import Entries"

**Expected Results:**
- Progress message appears: "Importing entries, please wait..." ✅
- Success message appears: "Import complete! Imported: X, Skipped: Y" ✅
- Check Enquiries dashboard - imported entries should appear ✅
- Check notes on imported entries - should say "Imported from Gravity Forms: [Form Name] (Entry ID: X, Submitted: Date)" ✅
- Duplicate entries (same email + phone) should be skipped ✅

---

### 6. Test Menu Label and Icon Update

**Test:**
1. Log in to WordPress Admin
2. Look at the left sidebar menu

**Expected Results:**
- Menu item should say "Enquiries" (not "MF Enquiries") ✅
- Menu icon should be a truck icon 🚚 (not arrows) ✅

**Visual Verification:**
- The truck icon (dashicons-truck) should be clearly visible
- Menu label should be clean and professional

---

### 7. Test Duplicate Prevention in Import

**Setup:**
1. Create a form with existing entries
2. Import entries once
3. Note the number of entries imported

**Test:**
1. Import from the same form again
2. Check the results

**Expected Results:**
- First import: "Imported: X, Skipped: 0" ✅
- Second import: "Imported: 0, Skipped: X" ✅ (all duplicates)
- No duplicate entries in CRM ✅

---

### 8. Test Manual Integration Override

**Setup:**
1. Create a form titled "Book a Service" (no keywords match)
2. Add all required fields
3. Add CSS class: `crm-integration`
4. Save the form

**Test:**
1. Submit the form
2. Check Enquiries dashboard

**Expected Results:**
- Enquiry should be created ✅ (manual override works)

---

### 9. Test Field Mapping with Name Field

**Setup:**
1. Create a form with Gravity Forms Name field (Advanced Field)
2. Label: "Your Name"
3. Format: First and Last
4. Add other required fields

**Test:**
1. Submit form with name "John Smith"
2. Check enquiry in CRM

**Expected Results:**
- First Name: "John" ✅
- Last Name: "Smith" ✅
- Name field correctly split

---

### 10. Test Field Mapping with Address Field

**Setup:**
1. Create a form with Gravity Forms Address field (Advanced Field)
2. Label: "Current Address"
3. Add other required fields

**Test:**
1. Submit form with address:
   - Street: "123 Main St"
   - City: "Auckland"
   - State: "Auckland"
   - ZIP: "1010"
2. Check enquiry in CRM

**Expected Results:**
- Address: "123 Main St, Auckland, Auckland, 1010" ✅
- Suburb: "Auckland" ✅ (extracted from city field)
- Address field correctly combined and suburb extracted

---

## Validation Checklist

After running all tests, verify:

- [ ] General contact form does NOT create enquiries
- [ ] Pickup/delivery forms DO create enquiries automatically
- [ ] "Contact" keyword forms do NOT auto-integrate
- [ ] Historical import works and shows progress
- [ ] Duplicate prevention works in import
- [ ] Menu shows "Enquiries" with truck icon
- [ ] Manual override with `crm-integration` class works
- [ ] Exclusion with `no-crm-integration` class works
- [ ] Name field mapping works correctly
- [ ] Address field mapping works correctly

---

## Regression Testing

Test that existing functionality still works:

- [ ] "Moving" keyword forms still auto-integrate
- [ ] "Enquiry" keyword forms still auto-integrate
- [ ] "Furniture" keyword forms still auto-integrate
- [ ] "Quote" keyword forms still auto-integrate
- [ ] Forms with `crm-integration` class still integrate
- [ ] Email notifications still sent to admin
- [ ] Required field validation still works
- [ ] Optional fields (suburb, move_date, move_time) still work

---

## Browser/Environment Testing

Test in multiple environments:

- [ ] Chrome browser
- [ ] Firefox browser
- [ ] Safari browser (if available)
- [ ] WordPress 5.x
- [ ] WordPress 6.x
- [ ] Gravity Forms latest version

---

## Documentation Verification

Verify documentation is accurate:

- [ ] GRAVITY_FORMS_INTEGRATION.md reflects all changes
- [ ] GRAVITY_FORMS_MAPPING_SUMMARY.md updated with new keywords
- [ ] GRAVITY_FORMS_CONFIGURATION_GUIDE.md has step-by-step instructions
- [ ] All code examples in docs are correct
- [ ] All screenshots (if any) are up to date

---

## Security Validation

Verify security measures:

- [ ] Nonce verification in AJAX import
- [ ] Permission checks for import functionality
- [ ] All user input sanitized
- [ ] SQL injection prevention (using $wpdb->prepare)
- [ ] XSS prevention (using esc_html, esc_attr, etc.)
- [ ] No direct user input in SQL queries

---

## Performance Testing

Test import performance:

- [ ] Import 10 entries - should complete in < 5 seconds
- [ ] Import 50 entries - should complete in < 15 seconds
- [ ] Import 100 entries - should complete in < 30 seconds
- [ ] Import 1000 entries - should complete in < 5 minutes
- [ ] No memory errors during large imports
- [ ] No timeout errors during large imports

---

## Error Handling

Test error scenarios:

- [ ] Import with invalid form ID - shows error
- [ ] Import with no Gravity Forms installed - shows error
- [ ] Import with form that has no entries - shows appropriate message
- [ ] Import without permission - shows error
- [ ] Import with invalid nonce - shows error
- [ ] Submission with missing required fields - enquiry NOT created

---

## Summary

All tests should pass without errors. Any failures should be investigated and fixed before deployment.

**Test Status:** ⬜ Not Started | ⏳ In Progress | ✅ Complete | ❌ Failed

Overall Status: ⬜ Not Started
