# Gravity Forms to CRM Mapping - Quick Reference

## How It Works

When a Gravity Form is submitted, the plugin automatically:
1. Checks if the form should integrate (by title keywords or CSS class)
2. Maps form fields to CRM fields based on field labels
3. Creates an enquiry in the CRM database
4. Sends admin notification email

**No manual configuration required** - it uses intelligent field label matching!

## Field Mapping Rules

### Required Fields (Must be present for enquiry creation)

| CRM Field | Gravity Form Field Labels (case-insensitive) |
|-----------|----------------------------------------------|
| First Name | Contains: "first name", "first", "fname" |
| Last Name | Contains: "last name", "last", "surname", "lname" |
| Email | Contains: "email", "e-mail", "email address" |
| Phone | Contains: "phone", "telephone", "mobile", "phone number" |
| Address | Contains: "address", "street address", "location" |

### Optional Fields

| CRM Field | Gravity Form Field Labels (case-insensitive) |
|-----------|----------------------------------------------|
| Suburb | Contains: "suburb", "city", "town" |
| Move Date | Contains: "move date", "moving date", "preferred date", "date" |
| Move Time | Contains: "move time", "moving time", "preferred time", "time" |

## Special Field Type Handling

### Name Field (Gravity Forms Advanced Field)
```
Field Type: Name
Label: "Your Name"
→ Automatically splits into first_name and last_name
```

### Address Field (Gravity Forms Advanced Field)
```
Field Type: Address
Label: "Current Address"
→ Street, Line 2, City, State, ZIP combined into address
→ City component extracted separately into suburb
```

### Date Field (Gravity Forms Advanced Field)
```
Field Type: Date
Label: "Preferred Move Date"
→ Automatically formatted and stored as move_date
```

### Time Field (Gravity Forms Advanced Field)
```
Field Type: Time
Label: "Preferred Move Time"
→ Automatically formatted and stored as move_time
```

## Integration Activation

### Method 1: Automatic (by form title)
Form title contains any of these keywords:
- moving
- enquiry
- contact
- furniture
- quote

**Example:** "Moving Quote Request" → Auto-integrated ✅

### Method 2: Manual (by CSS class)
Add `crm-integration` to Form Settings → Advanced → CSS Class Name

**Example:** Generic "Get Started" form → Add CSS class ✅

## Example Form Setup

### Recommended Form Structure

```
Form Title: "Moving Enquiry Form"

Fields:
1. Name (Advanced)                 → first_name, last_name
2. Email (Standard)                → email
3. Phone (Standard)                → phone
4. Address (Advanced)              → address, suburb (auto-extracted)
5. Date (Advanced)                 → move_date
   Label: "Preferred Move Date"
6. Time (Advanced)                 → move_time
   Label: "Preferred Move Time"
7. Paragraph (Standard)            → (stored in Gravity Forms only)
   Label: "Additional Details"
```

## Alternative Labeling Examples

These will ALL work (case-insensitive, partial matching):

### First Name Field
- ✅ "First Name"
- ✅ "Your First Name"
- ✅ "Customer First Name"
- ✅ "fname"

### Email Field
- ✅ "Email"
- ✅ "Email Address"
- ✅ "Your Email"
- ✅ "Customer E-mail"

### Move Date Field
- ✅ "Move Date"
- ✅ "Preferred Moving Date"
- ✅ "When do you want to move?"
- ✅ "Date of Move"

### Move Time Field
- ✅ "Move Time"
- ✅ "Preferred Moving Time"
- ✅ "What time works for you?"
- ✅ "Time"

### Suburb Field
- ✅ "Suburb"
- ✅ "City"
- ✅ "Town"
- ✅ "What suburb are you in?"

## Verification Steps

After setting up a form:

1. **Submit a test entry**
2. **Check Gravity Forms → Entries** - Should see the submission
3. **Check MF Enquiries dashboard** - Should see new enquiry
4. **Check the notes** - Should say "Enquiry created from Gravity Forms: [Form Title]"
5. **Check emails** - Admin should receive notification

## Troubleshooting

### No enquiry created?

**Check 1:** Form title or CSS class
- Does title contain keyword? OR
- Does form have `crm-integration` CSS class?

**Check 2:** Required fields
- All 5 required fields must be present
- Field labels must match expected patterns
- Fields must have values (not empty)

**Check 3:** Field labels
- Go to form editor
- Check exact field label text
- Label just needs to *contain* the keyword (partial match)

### Specific field not mapping?

Example: Email not capturing

1. Edit form
2. Click on email field
3. Check "Field Label" value
4. Ensure it contains one of: "email", "e-mail", "email address"
5. Label "What's your email address?" → Contains "email" → ✅ Works

## Common Mistakes

❌ **Wrong:** Form title "Book a Service" + No CSS class
✅ **Right:** Form title "Book a Service" + CSS class `crm-integration`

❌ **Wrong:** Name field labeled "Name"
✅ **Right:** Use Gravity Forms Name field type OR separate fields labeled "First Name" and "Last Name"

❌ **Wrong:** Date field labeled "Schedule"
✅ **Right:** Date field labeled "Move Date" or "Preferred Moving Date"

## Code Location

The mapping logic is in `marcus-furniture-crm.php`:
- Function: `hs_crm_gravity_forms_integration()`
- Lines: ~321-510
- Field mapping array: Line ~353

To customize, edit the `$field_mapping` array.

## Benefits of This Approach

✅ **Flexible:** Works with various field label styles
✅ **No manual config:** Automatic field detection
✅ **Forgiving:** Partial matching allows natural language labels
✅ **Future-proof:** Easy to add new field mappings
✅ **User-friendly:** Non-technical users can set up forms

## Summary

**You don't need to configure field mapping manually!**

Just:
1. Ensure form integrates (title keyword or CSS class)
2. Use clear, descriptive field labels
3. Include all required fields
4. The plugin handles the rest automatically

The intelligent label matching means you can use natural language labels like "What's your email address?" and it will still work perfectly.
