# Gravity Forms Debug Output - Example

## What You'll See When Using Debug Mode

When you enable debug mode and import entries, you'll see output like this:

---

### ✅ SUCCESS Example

```
Entry #1 (ID: 42) - 2024-01-05 14:32:15

Status: SUCCESS - Imported as enquiry ID: 123

Form Fields Found:
• ID: 1, Label: "Name", Type: name
• ID: 2, Label: "Email Address", Type: email
• ID: 3, Label: "Phone Number", Type: phone
• ID: 4, Label: "Property Address", Type: address
• ID: 5, Label: "Moving Date", Type: date

▼ Name Field Debug
{
  "field_id": "1",
  "first_name_key": "1.3",
  "last_name_key": "1.6",
  "first_name_value": "John",
  "last_name_value": "Doe",
  "combined_value": "John Doe"
}

▼ Address Field Debug
{
  "field_id": "4",
  "street_value": "123 Queen Street",
  "street2_value": "",
  "city_value": "Auckland",
  "state_value": "Auckland Region",
  "zip_value": "1010",
  "combined_value": "123 Queen Street\nAuckland Auckland Region 1010"
}

▼ Data Extracted
{
  "contact_source": "form",
  "first_name": "John",
  "last_name": "Doe",
  "email": "john.doe@example.com",
  "phone": "021234567",
  "address": "123 Queen Street, Auckland, Auckland Region, 1010",
  "suburb": "Auckland",
  "move_date": "2024-02-15"
}
```

---

### ❌ SKIPPED Example - Missing Required Fields

```
Entry #2 (ID: 43) - 2024-01-05 14:35:22

Status: Missing required fields: phone, address

Form Fields Found:
• ID: 1, Label: "Full Name", Type: text
• ID: 2, Label: "Email Address", Type: email
• ID: 3, Label: "Comments", Type: textarea

▼ Data Extracted
{
  "contact_source": "form",
  "email": "jane.smith@example.com"
}

Missing Required Fields: phone, address

⚠️ Problem: This form doesn't have phone and address fields!
Solutions:
1. Add a "Phone" field (type: phone) to your form
2. Add an "Address" field (type: address) to your form
3. Make sure these fields are required in Gravity Forms
```

---

### ❌ SKIPPED Example - Wrong Field Types

```
Entry #3 (ID: 44) - 2024-01-05 14:38:45

Status: Missing required fields: first_name, last_name

Form Fields Found:
• ID: 1, Label: "Full Name", Type: text
• ID: 2, Label: "Email Address", Type: email
• ID: 3, Label: "Phone Number", Type: phone
• ID: 4, Label: "Property Address", Type: address

▼ Data Extracted
{
  "contact_source": "form",
  "email": "bob.jones@example.com",
  "phone": "021555666",
  "address": "456 Main Road, Wellington, Wellington Region, 6011",
  "suburb": "Wellington"
}

Missing Required Fields: first_name, last_name

⚠️ Problem: Form has "Full Name" as a text field instead of Name field type!
Solutions:
1. Change field type from "Single Line Text" to "Name" in Gravity Forms
2. OR split into two fields: "First Name" and "Last Name" (as text fields)
```

---

### ❌ SKIPPED Example - Duplicate Entry

```
Entry #4 (ID: 45) - 2024-01-05 14:40:12

Status: Duplicate entry (email and phone already exist in database)

Form Fields Found:
• ID: 1, Label: "Name", Type: name
• ID: 2, Label: "Email Address", Type: email
• ID: 3, Label: "Phone Number", Type: phone
• ID: 4, Label: "Property Address", Type: address

▼ Data Extracted
{
  "contact_source": "form",
  "first_name": "John",
  "last_name": "Doe",
  "email": "john.doe@example.com",
  "phone": "021234567",
  "address": "123 Queen Street, Auckland, Auckland Region, 1010",
  "suburb": "Auckland"
}

⚠️ Problem: An enquiry with this email (john.doe@example.com) AND phone (021234567) already exists!
Solutions:
1. Check your CRM to see if this customer is already in the system
2. This is expected behavior to prevent duplicate entries
3. If needed, manually update the existing entry instead of creating a new one
```

---

### ❌ SKIPPED Example - Field Label Mismatch

```
Entry #5 (ID: 46) - 2024-01-05 14:42:30

Status: Missing required fields: address

Form Fields Found:
• ID: 1, Label: "Name", Type: name
• ID: 2, Label: "Email Address", Type: email
• ID: 3, Label: "Contact Number", Type: phone
• ID: 4, Label: "Current Location", Type: text

▼ Data Extracted
{
  "contact_source": "form",
  "first_name": "Alice",
  "last_name": "Williams",
  "email": "alice.w@example.com",
  "phone": "021777888"
}

Missing Required Fields: address

⚠️ Problem: Field labeled "Current Location" isn't recognized as an address!
Solutions:
1. Change field type to "Address" (recommended)
2. OR change field label to include the word "address" (e.g., "Current Address")
3. The importer looks for these keywords in labels: "address", "street address", "location"
```

---

## Understanding the Debug Output

### Green Border = Success ✅
- Entry was successfully imported
- All required fields were found
- No duplicate detected
- New enquiry created in CRM

### Red Border = Skipped ❌
- Entry was NOT imported
- Check the "Status" line for the reason
- Review "Missing Required Fields" if shown
- Look at "Data Extracted" to see what WAS found

### Key Sections to Check

1. **Form Fields Found**
   - Shows every field in your form
   - Check that you have fields for: name, email, phone, address
   - Verify field types match expectations

2. **Name Field Debug**
   - Only shown if you have a Name field type
   - Shows the subfield values (.3 = first, .6 = last)
   - If values show "NOT SET", the name wasn't entered

3. **Address Field Debug**
   - Only shown if you have an Address field type
   - Shows all address components
   - Street, City, State, ZIP should have values

4. **Data Extracted**
   - This is the final data that would be imported
   - Required fields: first_name, last_name, email, phone, address
   - Optional fields: suburb, move_date, move_time

5. **Missing Required Fields**
   - Lists which required fields are empty or missing
   - This is the most important section for troubleshooting

6. **All Entry Keys Available**
   - Technical section showing the raw entry structure
   - Use this if you suspect a field mapping issue
   - Look for patterns like "1.3", "2.1", etc.

## Quick Troubleshooting Checklist

- [ ] Does my form have a **Name field** (type: Name)?
- [ ] Does my form have an **Email field** (type: Email)?
- [ ] Does my form have a **Phone field** (type: Phone)?
- [ ] Does my form have an **Address field** (type: Address)?
- [ ] Are all these fields **required** in Gravity Forms?
- [ ] Did customers actually fill out all required fields?
- [ ] Are there any duplicates (same email + phone)?

## Next Steps

1. **If entries are being skipped**: Read the "Status" and "Missing Required Fields" for each entry
2. **If field types are wrong**: Update your Gravity Forms form to use the correct field types
3. **If labels don't match**: Either change the labels or add the proper field types
4. **If you see duplicates**: Check your CRM for existing entries with the same email/phone
5. **If you're still stuck**: Copy the entire debug output and contact support

## Need More Help?

Read the full guide: `GRAVITY_FORMS_DEBUG_GUIDE.md`
