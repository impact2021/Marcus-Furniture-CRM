# Deployment Checklist for Marcus Furniture CRM

## Pre-Deployment Verification ✅

- [x] All PHP files syntax validated
- [x] Code review completed
- [x] Security best practices verified
- [x] Documentation complete
- [x] Repository clean (no unnecessary files)
- [x] .gitignore configured
- [x] All requirements met

## Installation Steps

### 1. Download Plugin
```bash
# Download the marcus-furniture-crm folder from this repository
```

### 2. Upload to WordPress
```bash
# Method 1: FTP/SFTP Upload
# Upload to: /wp-content/plugins/marcus-furniture-crm/

# Method 2: WordPress Admin Upload
# 1. Create a zip file of the marcus-furniture-crm folder
# 2. Go to WordPress Admin > Plugins > Add New > Upload Plugin
# 3. Choose the zip file and click "Install Now"
# 4. Click "Activate Plugin"
```

**Note:** The plugin now includes a `readme.txt` file which allows installation via the WordPress admin uploader without errors.

### 3. Activate Plugin
- Go to WordPress Admin > Plugins
- Find "Marcus Furniture CRM"
- Click "Activate"
- Database tables will be created automatically

### 4. Configure Settings
Navigate to: **MF Enquiries > Settings**

**Required:**
- Admin Email Address (defaults to WordPress admin email)
- Timezone (defaults to Pacific/Auckland)

**Optional:**
- Google Maps API Key (for address autocomplete in NZ)
  - Get from: https://developers.google.com/maps/documentation/javascript/get-api-key
  - Enable: Places API, Maps JavaScript API

### 5. Add Contact Form to Pages
Add this shortcode to any page or post:
```
[hs_contact_form]
```

Recommended pages:
- Contact Us
- Get a Quote
- Request Moving Service

**Alternative: Using Gravity Forms**

If you have Gravity Forms installed, the plugin will automatically integrate with it:

1. Create a new Gravity Form with fields for customer information
2. Include fields with labels like:
   - "First Name" or "First"
   - "Last Name" or "Last"
   - "Email" or "Email Address"
   - "Phone" or "Phone Number"
   - "Address" or "Street Address"
   - "Move Date" or "Moving Date" (optional)
3. Either:
   - Include one of these keywords in the form title: "moving", "enquiry", "contact", "furniture", or "quote"
   - OR add the CSS class `crm-integration` to the form settings

When a Gravity Form is submitted, an enquiry will automatically be created in the CRM.

### 6. Set Up Trucks
- Contact Us
- Get a Quote
- Request Moving Service

### 6. Set Up Trucks
Navigate to: **MF Enquiries > Truck Scheduler**

1. Click "+ Add Truck"
2. Enter truck details:
   - Name (e.g., "Truck 1", "Large Van")
   - Registration (e.g., "ABC123")
   - Capacity (e.g., "3-bedroom house", "Small apartment")
3. Save

Repeat for all trucks in your fleet.

### 7. Test the System

**Test Contact Form:**
1. Visit the page with the contact form
2. Fill in all fields including move date
3. Submit form
4. Verify:
   - Success message appears
   - Customer receives confirmation email
   - Admin receives notification email
   - Enquiry appears in MF Enquiries dashboard

**Test Manual Entry:**
1. Go to MF Enquiries
2. Click "+ Add New Enquiry"
3. Fill in customer details
4. Select contact source (e.g., "WhatsApp")
5. Save
6. Verify enquiry appears with correct source badge

**Test Truck Booking:**
1. Go to MF Enquiries > Truck Scheduler
2. Click any calendar cell
3. Fill in booking details
4. Link to an existing enquiry (optional)
5. Save
6. Verify booking appears in calendar

**Test Status Workflow:**
1. Go to MF Enquiries
2. Select an enquiry
3. Use "Status Change" dropdown
4. Change status (e.g., First Contact → Quote Sent)
5. Verify:
   - Status badge updates
   - Automatic note is created
   - Status count updates

**Test Quote/Invoice Sending:**
1. Go to MF Enquiries
2. Use "Action" dropdown > "Send Quote"
3. Fill in quote items and amounts
4. Send email
5. Verify customer receives email with quote table

## Post-Deployment Configuration

### User Training
Train staff on:
- Adding manual enquiries from phone/WhatsApp
- Updating status workflow stages
- Using truck scheduler
- Sending quotes and invoices
- Adding notes to track communication

### Data Import (if migrating)
If migrating from Home Shield Painters CRM:
1. Existing data will be preserved
2. Old status values will still work
3. Update old statuses manually:
   - "Not Actioned" → "First Contact"
   - "Emailed" → "Quote Sent"
4. New fields (move_date, contact_source) will be NULL for old records

### Customization Options

**Email Templates:**
- Edit: `includes/class-hs-crm-email.php`
- Edit: `includes/class-hs-crm-form.php`
- Customize subject lines and message content

**Workflow Stages:**
- Edit status options in `includes/class-hs-crm-admin.php`
- Update status counts in `includes/class-hs-crm-database.php`

**Branding:**
- Company name appears in:
  - Email templates
  - Settings page title
  - Admin page headers

## Troubleshooting

### Plugin Upload Error: "The package could not be installed"

**Solution:** This error occurred in earlier versions. The plugin now includes a `readme.txt` file which is required by WordPress for proper plugin installation.

If you still see this error:
1. Verify the `readme.txt` file exists in the plugin folder
2. Ensure the plugin folder is named `marcus-furniture-crm` (not `marcus-furniture-crm-main` or similar)
3. Make sure all plugin files are present in the correct structure
4. Try uploading via FTP instead of the WordPress admin interface

### Contact Form Not Appearing
- Verify shortcode is `[hs_contact_form]` exactly
- Check that plugin is activated
- View page source to check for JavaScript errors

### Emails Not Sending
- Check Settings > Admin Email is set correctly
- Verify WordPress email function works
- Check spam folder
- Install SMTP plugin if needed (WP Mail SMTP)

### Google Maps Autocomplete Not Working
- Verify API key is entered in Settings
- Check API key has Places API enabled
- Check browser console for errors
- Verify API key restrictions allow your domain

### Database Tables Not Created
- Deactivate and reactivate plugin
- Check PHP error logs
- Verify MySQL user has CREATE TABLE permission

### Truck Scheduler Not Loading
- Check browser console for JavaScript errors
- Verify nonce is being generated
- Clear browser cache

### Gravity Forms Not Creating Enquiries

If Gravity Forms submissions are not creating enquiries in the CRM:
1. **Check Form Title or CSS Class:**
   - Form title must contain: "moving", "enquiry", "contact", "furniture", or "quote"
   - OR add CSS class `crm-integration` to the form settings (Form Settings > Advanced)

2. **Verify Field Labels:**
   - Field labels must match expected names (see Gravity Forms Integration section)
   - The plugin looks for field labels like "First Name", "Email", "Phone", etc.

3. **Check Required Fields:**
   - Gravity Form must have all required fields: First Name, Last Name, Email, Phone, Address
   - If any required field is missing, the enquiry won't be created

4. **Review Admin Email:**
   - Check if admin notification emails are being sent from CRM
   - If emails arrive, integration is working

5. **Check WordPress Debug Log:**
   - Enable WP_DEBUG to see if there are any errors during form submission
   - Look for database insertion errors

## Security Recommendations

1. **Keep WordPress Updated**
   - Update WordPress core regularly
   - Update all plugins and themes

2. **Secure Admin Access**
   - Use strong passwords
   - Enable two-factor authentication
   - Limit `manage_options` capability to trusted users

3. **Regular Backups**
   - Backup database regularly
   - Include wp-content/plugins/ folder
   - Store backups offsite

4. **SSL Certificate**
   - Use HTTPS for admin area
   - Protect customer data in transit

5. **Monitor Activity**
   - Review enquiries regularly
   - Check for spam submissions
   - Monitor email delivery

## Maintenance

### Regular Tasks
- Review and archive completed jobs
- Clean up old archived enquiries (optional)
- Update truck information as fleet changes
- Review booking calendar weekly

### Database Cleanup (Optional)
```sql
-- Archive old completed enquiries (older than 1 year)
UPDATE wp_hs_enquiries 
SET status = 'Archived' 
WHERE status = 'Completed' 
AND created_at < DATE_SUB(NOW(), INTERVAL 1 YEAR);
```

## Support

### Documentation
- Plugin User Guide: `USER_GUIDE.md`
- Changes Log: `CHANGES.md`
- Project Summary: `PROJECT_SUMMARY.md`

### WordPress Resources
- WordPress Codex: https://codex.wordpress.org/
- WordPress Support: https://wordpress.org/support/

### Developer Resources
- Google Maps API: https://developers.google.com/maps/
- WordPress Plugin Handbook: https://developer.wordpress.org/plugins/

## Success Indicators

Your installation is successful when:
- ✅ Contact form accepts and processes submissions
- ✅ Emails are sent to customers and admin
- ✅ Enquiries appear in admin dashboard
- ✅ Manual entries can be created
- ✅ Status workflow functions correctly
- ✅ Truck scheduler displays and accepts bookings
- ✅ Sort and filter functions work
- ✅ No PHP errors in error log
- ✅ No JavaScript console errors

## Next Steps After Deployment

1. Add contact form to website pages
2. Import existing customer data (if any)
3. Configure email templates for your brand
4. Add all trucks to scheduler
5. Train team on the system
6. Set up regular backup schedule
7. Monitor for the first week
8. Collect user feedback
9. Optimize workflow as needed

---

**Plugin Version:** 1.0  
**WordPress Compatibility:** 5.0+  
**PHP Compatibility:** 7.0+  
**Database:** MySQL 5.6+

**Status:** ✅ Ready for Production Deployment
