# Version 2.14 Testing Guide - Mobile Edit Button

## Overview
This guide provides step-by-step instructions for testing the new "Edit Details" button in the mobile enquiry detail modal.

## Prerequisites
- WordPress admin access to the CRM
- At least one enquiry in the system
- Mobile device OR browser with responsive design mode

## Test Environment Setup

### Option 1: Real Mobile Device
1. Open browser on your mobile phone or tablet
2. Navigate to your WordPress admin URL
3. Log in with admin credentials
4. Go to MF Enquiries page

### Option 2: Browser Responsive Mode (Recommended for thorough testing)
1. Open your WordPress admin in Chrome, Firefox, or Edge
2. Open Developer Tools (F12 or Right-click → Inspect)
3. Click the "Toggle device toolbar" icon (or press Ctrl+Shift+M / Cmd+Shift+M on Mac)
4. Select a mobile device from the dropdown (e.g., "iPhone 12 Pro" or "Pixel 5")
5. Navigate to MF Enquiries page

**Tip**: Test with various screen widths - the mobile view activates at screen widths < 768px

## Test Cases

### Test 1: Basic Functionality - Open Edit Modal
**Objective**: Verify the Edit button appears and opens the edit modal

**Steps**:
1. Navigate to MF Enquiries page in mobile view
2. Locate any enquiry card (colored box with customer name)
3. Tap/click on the enquiry card
4. Observe the mobile detail modal opens
5. Scroll to the bottom of the modal
6. Verify the "Edit Details" button is visible
7. Tap/click the "Edit Details" button

**Expected Results**:
- ✅ Mobile detail modal closes smoothly
- ✅ Edit enquiry modal opens
- ✅ Edit modal shows "Edit Enquiry Details" as the title
- ✅ All form fields are pre-populated with enquiry data
- ✅ No JavaScript errors in console (check Developer Tools → Console tab)

### Test 2: Edit and Save Changes
**Objective**: Verify that changes made through the mobile edit flow are saved correctly

**Steps**:
1. Follow Test 1 steps to open the edit modal
2. Make a simple change (e.g., modify phone number or add a note to "Property Notes")
3. Scroll to bottom of edit modal
4. Click "Save Changes" button
5. Wait for success message
6. Close the edit modal
7. Tap the same enquiry card again to view details
8. Verify your changes are reflected

**Expected Results**:
- ✅ Changes save successfully
- ✅ Success message displays
- ✅ Changes persist when viewing enquiry again
- ✅ No data loss or corruption

### Test 3: Different Enquiry Types
**Objective**: Verify the edit button works for both Moving House and Pickup/Delivery enquiries

**Steps for Moving House Enquiry**:
1. Find a "Moving House" enquiry (blue card)
2. Tap the card to open detail modal
3. Tap "Edit Details"
4. Verify Moving House specific fields are shown (Moving From, Moving To, Stairs From/To, etc.)
5. Close without saving

**Steps for Pickup/Delivery Enquiry**:
1. Find a "Pickup/Delivery" enquiry (orange card)
2. Tap the card to open detail modal
3. Tap "Edit Details"
4. Verify Pickup/Delivery specific fields are shown (Items Being Collected, Assembly Help, etc.)
5. Close without saving

**Expected Results**:
- ✅ Both enquiry types open correct edit form
- ✅ Appropriate fields shown for each type
- ✅ No errors or field mismatches

### Test 4: Edit Modal Close Behavior
**Objective**: Verify users can cancel editing and return to normal flow

**Steps**:
1. Open enquiry detail modal
2. Tap "Edit Details"
3. Make some changes (don't save)
4. Click the × (close) button in the edit modal OR click outside the modal
5. Observe behavior

**Expected Results**:
- ✅ Edit modal closes
- ✅ Changes are not saved (as expected)
- ✅ User returns to enquiries list
- ✅ Can view details again if needed

### Test 5: Multiple Enquiries in Sequence
**Objective**: Verify the edit button works correctly when viewing multiple enquiries

**Steps**:
1. Open first enquiry detail modal
2. Note the customer name
3. Tap "Edit Details"
4. Verify correct customer name in edit form
5. Close edit modal without saving
6. Open a different enquiry detail modal
7. Note the different customer name
8. Tap "Edit Details"
9. Verify the NEW customer's data is shown (not the previous one)

**Expected Results**:
- ✅ Each enquiry opens with its own correct data
- ✅ No data mixing between enquiries
- ✅ Enquiry ID is correctly stored and retrieved

### Test 6: Error Handling
**Objective**: Verify graceful error handling if something goes wrong

**Steps**:
1. Open browser Developer Tools (F12)
2. Go to Console tab
3. In console, type: `$('#hs-crm-mobile-enquiry-detail-modal').data('enquiry-id', null);`
4. Press Enter (this simulates missing enquiry ID)
5. In the mobile detail modal, tap "Edit Details"

**Expected Results**:
- ✅ User sees alert: "Unable to load enquiry details for editing. Please try again."
- ✅ Modal does not close
- ✅ No JavaScript crashes or freeze
- ✅ Error is also logged to console for debugging

### Test 7: Visual Inspection
**Objective**: Verify the button looks good on various screen sizes

**Test on different mobile screen sizes**:
- iPhone SE (375px)
- iPhone 12 Pro (390px)
- Samsung Galaxy S20 (360px)
- iPad Mini (768px)
- Very small screens (320px)

**Checks**:
- ✅ Button is visible and not cut off
- ✅ Icon (pencil) is displayed correctly
- ✅ Text "Edit Details" is readable
- ✅ Button is centered
- ✅ Border separator above button is visible
- ✅ Button has enough padding for easy tapping (at least 44px height for accessibility)
- ✅ Button follows WordPress admin styling

### Test 8: Button Styling and Accessibility
**Objective**: Verify button meets accessibility standards

**Steps**:
1. Open enquiry detail modal
2. Inspect the button visually
3. Use browser accessibility tools (if available)
4. Try tapping with finger (on real device) or clicking with mouse

**Checks**:
- ✅ Button has sufficient color contrast
- ✅ Button has clear focus state when tabbed to (keyboard accessibility)
- ✅ Button is large enough for touch (minimum 44x44px)
- ✅ Icon and text are aligned properly
- ✅ Button responds to hover (on desktop)
- ✅ Button responds to tap/click immediately

### Test 9: Rapid Clicking
**Objective**: Verify no issues with rapid or accidental double-clicks

**Steps**:
1. Open enquiry detail modal
2. Quickly double-click (or double-tap) the "Edit Details" button
3. Observe behavior

**Expected Results**:
- ✅ Edit modal opens once (not twice)
- ✅ No JavaScript errors
- ✅ No duplicate forms or modals
- ✅ System remains stable

### Test 10: Browser Compatibility
**Objective**: Verify functionality across different mobile browsers

**Test on**:
- iOS Safari (iPhone)
- Chrome Mobile (Android)
- Firefox Mobile
- Samsung Internet
- Opera Mobile (if available)

**Expected Results**:
- ✅ Button appears correctly on all browsers
- ✅ Edit modal opens on all browsers
- ✅ Data saves correctly on all browsers
- ✅ No browser-specific errors

## Regression Testing
**Important**: Verify existing functionality still works

### Desktop Mode Tests
1. Switch to desktop view (screen width > 768px)
2. Verify the existing "Edit" button in the Actions column still works
3. Verify the existing "Edit Details" dropdown option still works
4. Verify editing works the same as before
5. Verify mobile enquiry cards are hidden in desktop view

**Expected Results**:
- ✅ All existing desktop functionality unchanged
- ✅ No interference between mobile and desktop edit buttons

## Known Limitations
- The edit button currently uses a temporary DOM element to trigger editing (a workaround)
- This is intentional to minimize code changes and maintain compatibility
- Future enhancement: Extract edit logic into a shared function

## Troubleshooting

### Issue: Edit button not visible
**Solutions**:
- Ensure screen width is < 768px (check responsive mode settings)
- Clear browser cache
- Verify plugin updated to version 2.14
- Check browser console for JavaScript errors

### Issue: Edit button doesn't respond
**Solutions**:
- Check JavaScript console for errors
- Verify enquiry has valid ID (check data-enquiry-id attribute)
- Try refreshing the page
- Clear browser cache and cookies

### Issue: Edit modal shows wrong data
**Solutions**:
- This shouldn't happen - if it does, it's a bug
- Check console for errors
- Report issue with details:
  - Which enquiry was clicked
  - What data was shown
  - What data was expected

### Issue: Alert appears "Unable to load enquiry details"
**Causes**:
- Enquiry data corrupted
- JavaScript error before modal opens
- Browser cache issue

**Solutions**:
- Refresh the page
- Clear browser cache
- Check console for underlying errors
- Try a different enquiry

## Browser Console Checks
When testing, keep browser console open to monitor for:
- ❌ Red errors
- ⚠️ Yellow warnings
- ℹ️ Info messages (these are OK if they're just logs)

**What to look for**:
- "Failed to parse enquiry data" - indicates data corruption
- "No enquiry ID found for editing" - indicates ID storage issue
- Any uncaught exceptions or promise rejections
- Network errors during AJAX calls

## Success Criteria
All tests pass with:
- ✅ No JavaScript errors in console
- ✅ Button appears and functions correctly
- ✅ Edit modal opens with correct data
- ✅ Changes save successfully
- ✅ Good visual appearance on all screen sizes
- ✅ Existing functionality unaffected

## Reporting Issues
If you find bugs, please report with:
1. Test case number (e.g., "Test 5 failed")
2. Device/browser being used
3. Screen width
4. Steps to reproduce
5. Screenshot (if visual issue)
6. Console errors (copy/paste from browser console)
7. Expected vs actual behavior

## Performance Notes
- Modal should open within 500ms on modern devices
- Edit modal should open within 500ms after button click
- AJAX call to fetch enquiry data should complete within 1-2 seconds
- No lag or freeze when scrolling in the detail modal

## Test Completion Checklist
After completing all tests, verify:
- [ ] All 10 test cases passed
- [ ] Tested on at least 2 different screen sizes
- [ ] Tested on at least 1 real mobile device (optional but recommended)
- [ ] No JavaScript errors in console
- [ ] Existing desktop functionality still works
- [ ] Button looks good and is easy to use
- [ ] Changes save correctly
- [ ] No performance issues or lag

## Next Steps After Testing
1. If all tests pass: Feature is ready for production ✅
2. If some tests fail: Review failures and determine if they're critical
3. If critical bugs found: Report to development team
4. If minor UI issues: Note for future improvements

## Additional Resources
- Version 2.14 Implementation Documentation: `VERSION_2.14_IMPLEMENTATION.md`
- Plugin Changelog: `CHANGELOG.md`
- User Guide: `USER_GUIDE.md`
