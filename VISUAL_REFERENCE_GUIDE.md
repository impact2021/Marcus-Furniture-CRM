# Visual Reference Guide - Table Layout

## Header Color Coding

### Pickup/Delivery Enquiries
```
┌──────────────────────────────────────────────────────────────────────────────────┐
│ ORANGE HEADER (#FF8C00) with WHITE TEXT                                          │
├──────────┬─────────────┬──────────────┬─────────────┬────────┬──────┬───────────┬──────┤
│ Source & │  Contact &  │    House     │   Items &   │ Status │Truck │Status/    │Edit/ │
│  Dates   │   Address   │   Details    │Instructions │        │      │Action     │Delete│
├──────────┼─────────────┼──────────────┼─────────────┼────────┼──────┼───────────┼──────┤
│ Form     │ John Doe    │ Stairs: Yes  │ Items: Sofa,│ First  │ No   │[Status ▼] │[Edit]│
│ Pickup/  │ 021-XXX-XXX │              │ Table       │Contact │Truck │[Action ▼] │[Del] │
│ Delivery │ john@...    │              │ Furniture   │        │      │           │      │
│ Contact: │ From: ABC   │              │ moved: Yes  │        │      │           │      │
│ 5/1/26   │ To: XYZ     │              │             │        │      │           │      │
│ Move:    │             │              │             │        │      │           │      │
│ 15/1/26  │             │              │             │        │      │           │      │
└──────────┴─────────────┴──────────────┴─────────────┴────────┴──────┴───────────┴──────┘
```

### Moving House Enquiries
```
┌──────────────────────────────────────────────────────────────────────────────────┐
│ BLUE HEADER (#061257) with WHITE TEXT                                            │
├──────────┬─────────────┬──────────────┬─────────────┬────────┬──────┬───────────┬──────┤
│ Source & │  Contact &  │    House     │   Items &   │ Status │Truck │Status/    │Edit/ │
│  Dates   │   Address   │   Details    │Instructions │        │      │Action     │Delete│
├──────────┼─────────────┼──────────────┼─────────────┼────────┼──────┼───────────┼──────┤
│ Form     │ Jane Smith  │ 3 bedrooms   │      -      │ Quote  │Truck │[Status ▼] │[Edit]│
│ Moving   │ 021-YYY-YYY │ 8 total rooms│             │ Sent   │ #1   │[Action ▼] │[Del] │
│ House    │ jane@...    │ Stairs: No   │             │        │      │           │      │
│ Contact: │ From: ABC   │ Notes:...    │             │        │      │           │      │
│ 5/1/26   │ To: XYZ     │              │             │        │      │           │      │
│ Move:    │             │              │             │        │      │           │      │
│ 15/1/26  │             │              │             │        │      │           │      │
└──────────┴─────────────┴──────────────┴─────────────┴────────┴──────┴───────────┴──────┘
```

## Column Details

### Column 1: Source & Dates (14%)
- Form source badge (Form, Website, etc.)
- **Form type: "Pickup/Delivery" or "Moving House"** (NEW - in bold)
- Contact date
- Move date (if set)

### Column 2: Contact & Address (18%)
- Customer name (editable inline)
- Phone | Email
- From/To addresses (if applicable)
- Suburb (if set)

### Column 3: House Details (14%)
**Content varies by type:**

**For Pickup/Delivery:**
- Stairs: Yes/No

**For Moving House:**
- X bedrooms
- Y total rooms
- Stairs: Yes/No
- Notes: Property notes preview

### Column 4: Items & Instructions (16%) - NEW
**Only populated for Pickup/Delivery:**
- **Items:** List of items being collected
- **Furniture moved?:** Yes/No

**For Moving House:**
- Shows "-" (dash)

### Column 5: Status (8%)
- Status badge with color coding:
  - First Contact (gray)
  - Quote Sent (blue)
  - Booking Confirmed (orange)
  - Deposit Paid (light blue)
  - Completed (green)
  - Archived (red)

### Column 6: Truck (10%)
- Truck assignment dropdown
- Options: No Truck, Truck #1, Truck #2, etc.

### Column 7: Status / Action (12%) - RESTRUCTURED
**Two dropdowns stacked vertically:**
1. Change Status dropdown
   - First Contact
   - Quote Sent
   - Booking Confirmed
   - Deposit Paid
   - Completed
   - Archived
   
2. Action dropdown
   - Send Quote
   - Send Invoice
   - Send Receipt

### Column 8: Edit / Delete (8%) - RESTRUCTURED
**Two buttons stacked vertically:**
1. Edit button - Opens modal
2. Delete button - Archives enquiry (with confirmation)

## Modal Layout

### Edit Enquiry Modal
**Two-column grid layout on desktop:**

```
┌─────────────────────────────────────────────────────────────┐
│                     Edit Enquiry Details                    │
├──────────────────────────────┬──────────────────────────────┤
│ First Name *                 │ Last Name *                  │
│ [                ]           │ [                ]           │
├──────────────────────────────┼──────────────────────────────┤
│ From Address                 │ To Address                   │
│ [                ]           │ [                ]           │
│ [                ]           │ [                ]           │
├──────────────────────────────┼──────────────────────────────┤
│ Number of Bedrooms           │ Total Number of Rooms        │
│ [Select... ▼     ]           │ [Select... ▼     ]           │
├──────────────────────────────┼──────────────────────────────┤
│ Stairs                       │ Do you need any existing     │
│ [Select... ▼     ]           │ furniture moved?             │
│                              │ [Select... ▼     ]           │
├──────────────────────────────┴──────────────────────────────┤
│ What item(s) are being collected?                           │
│ [                                                  ]         │
│ [                                                  ]         │
├─────────────────────────────────────────────────────────────┤
│ Property Notes                                              │
│ [                                                  ]         │
│ [                                                  ]         │
│ [                                                  ]         │
├─────────────────────────────────────────────────────────────┤
│                [Save Enquiry]  [Cancel]                     │
└─────────────────────────────────────────────────────────────┘
```

**Fields removed from modal:**
- ~~From Suburb~~
- ~~To Suburb~~

## Gravity Forms Field Mapping

### Required Fields (for all enquiries)
- First Name
- Last Name
- Email
- Phone
- Address

### Pickup/Delivery Specific Fields
- **Stairs** → Recognized labels: "stairs", "stairs involved", "are there stairs"
- **Items being collected** → Recognized labels: "items being delivered", "items being collected", "what items", "items to collect", "what item(s) are being collected"
- **Furniture moved** → Recognized labels: "existing furniture moved", "furniture moved", "do you need any existing furniture moved"
- **Special instructions** → Recognized labels: "special instructions", "additional instructions", "instructions", "special requests"

### Note About Special Instructions
Special instructions from Gravity Forms are automatically added as a **note** on the enquiry, not as an editable field. They will appear in the notes section when expanded.

## User Experience Flow

### Viewing Enquiries
1. Open "Enquiries" page
2. See color-coded headers:
   - Orange = Pickup/Delivery
   - Blue = Moving House
3. Quickly identify form type in "Source & Dates" column
4. View relevant details in appropriately populated columns

### Editing an Enquiry
1. Click "Edit" button
2. Modal opens with all fields
3. Edit any field (including new stairs, items, furniture moved fields)
4. Click "Save Enquiry"
5. Changes reflected immediately in table

### Deleting (Archiving) an Enquiry
1. Click "Delete" button
2. Confirm deletion in dialog
3. Enquiry is archived (status set to "Archived")
4. Table entry fades out and is removed from current view
5. Enquiry can still be accessed in "Archived" tab

## Browser Compatibility
- Tested on modern browsers (Chrome, Firefox, Safari, Edge)
- Responsive design for mobile/tablet
- Modal is mobile-friendly

## Accessibility
- Color coding supplemented with text labels
- Keyboard navigation supported
- ARIA labels on interactive elements
- Confirmation dialogs for destructive actions
