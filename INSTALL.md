# Installation Instructions

## WordPress Installation Error - FIXED ✅

The error "An error occurred: The package could not be installed" has been resolved.

## Quick Start

### Option 1: WordPress Admin Upload (Recommended)

1. **Download the Plugin**
   - Download or clone this repository
   - Use the pre-built `marcus-furniture-crm.zip` file from the repository root
   - **OR** create your own ZIP file:
     ```bash
     # From the repository root:
     zip -r marcus-furniture-crm.zip marcus-furniture-crm/
     ```
   - ⚠️ **Important**: The ZIP must contain the `marcus-furniture-crm` folder, not just the files inside it

2. **Upload to WordPress**
   - Log into WordPress Admin
   - Go to **Plugins** > **Add New** > **Upload Plugin**
   - Click **Choose File** and select `marcus-furniture-crm.zip`
   - Click **Install Now**
   - Click **Activate Plugin**

3. **Configure Settings**
   - Go to **MF Enquiries** > **Settings**
   - Set your admin email address
   - Configure timezone (defaults to Pacific/Auckland)
   - (Optional) Add Google Maps API key

4. **Add Contact Form**
   - Edit any page or post
   - Add the shortcode: `[hs_contact_form]`
   - Publish the page

### Option 2: FTP Upload

1. **Download the Plugin**
   - Download the `marcus-furniture-crm` folder from this repository

2. **Upload via FTP**
   - Connect to your WordPress site via FTP
   - Navigate to `/wp-content/plugins/`
   - Upload the entire `marcus-furniture-crm` folder

3. **Activate**
   - Log into WordPress Admin
   - Go to **Plugins**
   - Find "Marcus Furniture CRM"
   - Click **Activate**

4. **Configure** (same as Option 1, steps 4-5)

## Gravity Forms Integration (Optional)

If you have Gravity Forms installed, the plugin will automatically integrate!

### Setup:

1. **Create a Gravity Form** with fields:
   - First Name
   - Last Name
   - Email
   - Phone
   - Address
   - Move Date (optional)

2. **Enable Integration** (choose one):
   - **Option A**: Include a keyword in form title: "moving", "enquiry", "contact", "furniture", or "quote"
   - **Option B**: Add CSS class `crm-integration` to Form Settings > Advanced

3. **Test**:
   - Submit the form
   - Check **MF Enquiries** dashboard
   - Verify enquiry was created

For detailed instructions, see [GRAVITY_FORMS_INTEGRATION.md](marcus-furniture-crm/GRAVITY_FORMS_INTEGRATION.md)

## What's Fixed

### Problem 1: Installation Error ✅
**Before**: "The package could not be installed."
**After**: Plugin installs successfully via WordPress admin

**Solution**: 
1. Added proper `readme.txt` file in WordPress standard format
2. Included pre-built, tested `marcus-furniture-crm.zip` in the repository
3. Updated .gitignore to allow distribution zip file

### Problem 2: No Gravity Forms Support ✅
**Before**: No integration with Gravity Forms
**After**: Full automatic integration with intelligent field mapping

**Solution**: Implemented comprehensive Gravity Forms integration

## File Locations

- **Plugin Folder**: `marcus-furniture-crm/`
- **Main Plugin File**: `marcus-furniture-crm/marcus-furniture-crm.php`
- **WordPress Readme**: `marcus-furniture-crm/readme.txt`
- **User Documentation**: `marcus-furniture-crm/README.md`
- **GF Integration Guide**: `marcus-furniture-crm/GRAVITY_FORMS_INTEGRATION.md`

## Verification

The plugin has been verified for:

- ✅ WordPress admin upload capability
- ✅ All PHP files syntax valid
- ✅ Proper readme.txt format
- ✅ Gravity Forms integration functional
- ✅ All documentation complete
- ✅ All required includes present
- ✅ Security best practices implemented
- ✅ Code review completed

## Support

- **User Guide**: `marcus-furniture-crm/README.md`
- **Gravity Forms Guide**: `marcus-furniture-crm/GRAVITY_FORMS_INTEGRATION.md`
- **Deployment Guide**: `DEPLOYMENT.md`
- **Solution Summary**: `SOLUTION_SUMMARY.md`

## Next Steps

1. ✅ Install the plugin (error-free!)
2. ✅ Configure settings
3. ✅ Add contact form to your pages
4. ✅ (Optional) Set up Gravity Forms integration
5. ✅ Add trucks to the scheduler
6. ✅ Start managing enquiries!

---

**Status**: ✅ Ready for Production
**Version**: 1.0
**WordPress Compatibility**: 5.0+
**PHP Compatibility**: 7.0+
