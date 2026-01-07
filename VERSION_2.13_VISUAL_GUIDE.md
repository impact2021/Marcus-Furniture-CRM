# Mobile Enquiry Modal - Visual Guide

## What You Should See After the Fix

### 1. Mobile Enquiry Cards (Before Tap)

```
╔════════════════════════════════════════╗
║  Marcus Furniture CRM - Enquiries     ║
╠════════════════════════════════════════╣
║                                        ║
║  ┌──────────────────────────────────┐  ║
║  │ PICKUP/DELIVERY (Orange Card)    │  ║
║  ├──────────────────────────────────┤  ║
║  │ Sarah Johnson            12/01   │  ║  ← Tap this card
║  │ ─────────────────────────────    │  ║
║  │ ⏰ 10:30AM                       │  ║
║  │                                  │  ║
║  │ From: 15 Queen St, Auckland      │  ║
║  │ To: 89 Beach Rd, Auckland        │  ║
║  └──────────────────────────────────┘  ║
║                                        ║
║  ┌──────────────────────────────────┐  ║
║  │ MOVING HOUSE (Blue Card)         │  ║
║  ├──────────────────────────────────┤  ║
║  │ Michael Chen             15/01   │  ║  ← Or tap this
║  │ ─────────────────────────────    │  ║
║  │ ⏰ 9:00AM                        │  ║
║  │                                  │  ║
║  │ From: 42 Hill Rd, Wellington     │  ║
║  │ To: 123 Park Ave, Wellington     │  ║
║  └──────────────────────────────────┘  ║
║                                        ║
╚════════════════════════════════════════╝
```

### 2. Modal Appears (After Tap)

```
╔════════════════════════════════════════╗
║ ▓▓▓▓▓▓▓▓ Dark Overlay ▓▓▓▓▓▓▓▓▓▓▓▓▓  ║
║ ▓                                  ▓  ║
║ ▓  ┏━━━━━━━━━━━━━━━━━━━━━━━━━━┓  ▓  ║
║ ▓  ┃ Enquiry Details        ✕ ┃  ▓  ║  ← Click ✕ to close
║ ▓  ┣━━━━━━━━━━━━━━━━━━━━━━━━━━┫  ▓  ║
║ ▓  ┃                          ┃  ▓  ║
║ ▓  ┃ CUSTOMER NAME            ┃  ▓  ║
║ ▓  ┃ Sarah Johnson            ┃  ▓  ║
║ ▓  ┃ ─────────────────────    ┃  ▓  ║
║ ▓  ┃                          ┃  ▓  ║
║ ▓  ┃ PHONE                    ┃  ▓  ║
║ ▓  ┃ 021 555 1234  📞         ┃  ▓  ║  ← Clickable to call
║ ▓  ┃ ─────────────────────    ┃  ▓  ║
║ ▓  ┃                          ┃  ▓  ║
║ ▓  ┃ EMAIL                    ┃  ▓  ║
║ ▓  ┃ sarah@example.com  ✉    ┃  ▓  ║  ← Clickable to email
║ ▓  ┃ ─────────────────────    ┃  ▓  ║
║ ▓  ┃                          ┃  ▓  ║
║ ▓  ┃ JOB TYPE                 ┃  ▓  ║
║ ▓  ┃ Pickup/Delivery          ┃  ▓  ║
║ ▓  ┃ ─────────────────────    ┃  ▓  ║
║ ▓  ┃                          ┃  ▓  ║
║ ▓  ┃ STATUS                   ┃  ▓  ║
║ ▓  ┃ Quote Sent               ┃  ▓  ║
║ ▓  ┃ ─────────────────────    ┃  ▓  ║
║ ▓  ┃                          ┃  ▓  ║
║ ▓  ┃ MOVE DATE                ┃  ▓  ║
║ ▓  ┃ 12/01/2026               ┃  ▓  ║
║ ▓  ┃ ─────────────────────    ┃  ▓  ║
║ ▓  ┃                          ┃  ▓  ║
║ ▓  ┃ MOVE TIME                ┃  ▓  ║
║ ▓  ┃ 10:30AM                  ┃  ▓  ║
║ ▓  ┃ ─────────────────────    ┃  ▓  ║
║ ▓  ┃                          ┃  ▓  ║
║ ▓  ┃ FROM ADDRESS             ┃  ▓  ║
║ ▓  ┃ 15 Queen St, Auckland    ┃  ▓  ║
║ ▓  ┃ ─────────────────────    ┃  ▓  ║
║ ▓  ┃                          ┃  ▓  ║
║ ▓  ┃ TO ADDRESS               ┃  ▓  ║
║ ▓  ┃ 89 Beach Rd, Auckland    ┃  ▓  ║
║ ▓  ┃ ─────────────────────    ┃  ▓  ║
║ ▓  ┃                          ┃  ▓  ║
║ ▓  ┃ ITEMS BEING COLLECTED    ┃  ▓  ║
║ ▓  ┃ 2x Sofas, 1x Dining      ┃  ▓  ║
║ ▓  ┃ Table, 1x Bed Frame      ┃  ▓  ║
║ ▓  ┃ ─────────────────────    ┃  ▓  ║
║ ▓  ┃                          ┃  ▓  ║
║ ▓  ┃ SPECIAL INSTRUCTIONS     ┃  ▓  ║
║ ▓  ┃ Please call before       ┃  ▓  ║
║ ▓  ┃ arrival. Gate code 1234  ┃  ▓  ║
║ ▓  ┃ ─────────────────────    ┃  ▓  ║
║ ▓  ┃                          ┃  ▓  ║
║ ▓  ┃ CONTACT SOURCE           ┃  ▓  ║
║ ▓  ┃ Contact form             ┃  ▓  ║
║ ▓  ┃ ─────────────────────    ┃  ▓  ║
║ ▓  ┃                          ┃  ▓  ║
║ ▓  ┃ CONTACT DATE             ┃  ▓  ║
║ ▓  ┃ 05/01/2026               ┃  ▓  ║
║ ▓  ┃                          ┃  ▓  ║
║ ▓  ┗━━━━━━━━━━━━━━━━━━━━━━━━━━┛  ▓  ║
║ ▓                                  ▓  ║  ← Tap dark area to close
║ ▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓  ║
╚════════════════════════════════════════╝
```

### 3. Moving House Example

For "Moving House" jobs, the modal will show additional fields:

```
┏━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┓
┃ Enquiry Details            ✕ ┃
┣━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┫
┃                               ┃
┃ CUSTOMER NAME                 ┃
┃ Michael Chen                  ┃
┃ ──────────────────────────    ┃
┃                               ┃
┃ PHONE                         ┃
┃ 021 987 6543  📞              ┃
┃ ──────────────────────────    ┃
┃                               ┃
┃ JOB TYPE                      ┃
┃ Moving House                  ┃
┃ ──────────────────────────    ┃
┃                               ┃
┃ NUMBER OF BEDROOMS            ┃
┃ 3                             ┃
┃ ──────────────────────────    ┃
┃                               ┃
┃ STAIRS (FROM)                 ┃
┃ Ground floor - no stairs      ┃
┃ ──────────────────────────    ┃
┃                               ┃
┃ STAIRS (TO)                   ┃
┃ 1st floor - 15 steps          ┃
┃ ──────────────────────────    ┃
┃                               ┃
┃ PROPERTY NOTES                ┃
┃ Piano needs special care.     ┃
┃ Narrow doorway on 2nd floor.  ┃
┃ ──────────────────────────    ┃
┃                               ┃
┃ ... more details ...          ┃
┗━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┛
```

## Color Coding

### Enquiry Cards
- **Orange Background** = Pickup/Delivery job
  - HEX: #FF8C00
  - White text for contrast
  
- **Dark Blue Background** = Moving House job
  - HEX: #061257
  - White text for contrast

### Modal
- **White Background** = Clean, readable content area
- **Dark Overlay** = rgba(0, 0, 0, 0.5) - 50% black
- **Labels** = Uppercase, gray (#555)
- **Values** = Regular text, dark (#333)

## Interactive Elements

### 1. Phone Number
When tapped:
```
┃ PHONE                         ┃
┃ 021 555 1234  📞              ┃  ← Tap opens phone dialer
```

### 2. Email Address
When tapped:
```
┃ EMAIL                         ┃
┃ sarah@example.com  ✉         ┃  ← Tap opens email client
```

### 3. Close Buttons
Two ways to close:
1. Tap the **✕** in top right
2. Tap anywhere on the **dark overlay**

## Responsive Behavior

### On Phone (< 480px)
- Modal width: 95% of screen
- Padding: 20px
- Font size: Readable (15px for values, 13px for labels)

### On Tablet (480px - 768px)
- Modal width: 90% of screen
- Padding: 30px
- Font size: Slightly larger

### On Desktop (> 768px)
- Mobile cards are **hidden**
- Desktop table view is shown instead
- This modal is not used on desktop

## Notes Display

If there are notes on the enquiry:
```
┃ NOTES (3)                     ┃
┃ ──────────────────────────    ┃
┃ 05/01/2026 10:30 AM           ┃
┃ Customer confirmed booking    ┃
┃                               ┃
┃ 06/01/2026 2:15 PM            ┃
┃ Deposit received              ┃
┃                               ┃
┃ 07/01/2026 9:00 AM            ┃
┃ Truck assigned - Truck #1     ┃
```

## What's Fixed

### Before (Broken)
```
User taps card
   ↓
JavaScript gets data → Returns as STRING → Type check fails
   ↓
Nothing happens ❌
```

### After (Working)
```
User taps card
   ↓
JavaScript gets data → Check if STRING → Parse to JSON → Success ✓
   ↓
Build HTML content
   ↓
Show modal ✓
```

## Testing Checklist

To verify the fix is working:

- [ ] Load enquiries page on mobile (or mobile view in browser)
- [ ] See mobile enquiry cards displayed (orange/blue boxes)
- [ ] Tap on a Pickup/Delivery card (orange)
- [ ] Modal should appear with all details
- [ ] Tap ✕ to close
- [ ] Modal should disappear
- [ ] Tap on a Moving House card (blue)
- [ ] Modal should appear with all details
- [ ] Tap outside modal (on dark area)
- [ ] Modal should disappear
- [ ] Try tapping phone number - should open dialer
- [ ] Try tapping email - should open email client
- [ ] Check browser console - no JavaScript errors

## Browser Compatibility

This fix works on:
- ✅ Chrome Mobile
- ✅ Safari iOS
- ✅ Firefox Mobile
- ✅ Samsung Internet
- ✅ Edge Mobile
- ✅ Any browser with jQuery support

The fix uses standard JavaScript features:
- `typeof` operator (ES3)
- `JSON.parse()` (ES5)
- `try...catch` (ES3)

All widely supported across modern mobile browsers.
