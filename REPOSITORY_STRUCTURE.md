# Repository Structure

## Overview

This repository contains the Marcus Furniture CRM WordPress plugin. The plugin files are located at the **root level** of the repository for easy access and development.

## Directory Layout

```
/                                   # Repository root
├── marcus-furniture-crm.php       # Main plugin file
├── readme.txt                     # WordPress standard readme
├── assets/                        # CSS and JavaScript assets
│   ├── css/styles.css
│   └── js/scripts.js
├── includes/                      # PHP class files
│   ├── class-hs-crm-admin.php
│   ├── class-hs-crm-database.php
│   ├── class-hs-crm-email.php
│   ├── class-hs-crm-form.php
│   ├── class-hs-crm-settings.php
│   └── class-hs-crm-truck-scheduler.php
├── marcus-furniture-crm.zip       # Pre-built distribution package
├── README.md                      # Repository readme (you are here)
├── USER_GUIDE.md                  # Plugin user guide
├── INSTALL.md                     # Installation instructions
├── DEPLOYMENT.md                  # Deployment guide
├── GRAVITY_FORMS_INTEGRATION.md   # Gravity Forms integration guide
├── PACKAGE_INSTALLATION_FIX.md    # Fix documentation
└── Other documentation files...
```

## Key Files

### Plugin Files (WordPress)
- **marcus-furniture-crm.php** - Main plugin file with header
- **readme.txt** - WordPress plugin repository standard readme
- **assets/** - Stylesheets and JavaScript
- **includes/** - PHP class files for plugin functionality

### Distribution Package
- **marcus-furniture-crm.zip** - Ready-to-install WordPress plugin package
  - Contains all plugin files in a `marcus-furniture-crm/` folder
  - Can be uploaded directly to WordPress via Admin → Plugins → Upload

### Documentation Files
- **README.md** - Repository overview and quick start (this is NOT the plugin readme)
- **USER_GUIDE.md** - Detailed plugin usage guide
- **INSTALL.md** - Complete installation instructions
- **DEPLOYMENT.md** - Production deployment guide
- **GRAVITY_FORMS_INTEGRATION.md** - Gravity Forms setup
- **PACKAGE_INSTALLATION_FIX.md** - Explains the installation error fix

## Why This Structure?

### Plugin Files at Root
✅ **Easier Development** - Direct access to all plugin files
✅ **No Nested Folders** - Cleaner repository structure
✅ **Single Source** - Plugin files aren't duplicated in multiple locations

### Distribution ZIP Included
✅ **Pre-tested** - The ZIP has been validated for WordPress compatibility
✅ **No Build Required** - Users can download and install immediately
✅ **Proper Structure** - ZIP contains `marcus-furniture-crm/` folder as WordPress expects

## For Developers

### Making Changes
1. Edit plugin files at the repository root
2. Test your changes locally
3. Rebuild the ZIP file:
   ```bash
   # From repository root:
   mkdir -p /tmp/build/marcus-furniture-crm
   cp -r assets includes marcus-furniture-crm.php readme.txt \
         GRAVITY_FORMS_INTEGRATION.md USER_GUIDE.md \
         /tmp/build/marcus-furniture-crm/
   cd /tmp/build
   zip -r marcus-furniture-crm.zip marcus-furniture-crm/
   mv marcus-furniture-crm.zip /path/to/repo/
   ```

### Testing
- Validate PHP syntax: `php -l marcus-furniture-crm.php`
- Test ZIP structure: `unzip -t marcus-furniture-crm.zip`
- Check all includes: `for f in includes/*.php; do php -l "$f"; done`

## For Users

### Installation
1. Download `marcus-furniture-crm.zip` from this repository
2. WordPress Admin → Plugins → Add New → Upload Plugin
3. Select the ZIP file and click Install Now
4. Activate and configure

See **[INSTALL.md](INSTALL.md)** for detailed instructions.

## Migration from Old Structure

If you're working with an old version that had `marcus-furniture-crm/` as a subfolder:

**Old Structure:**
```
/
└── marcus-furniture-crm/
    ├── marcus-furniture-crm.php
    ├── assets/
    └── includes/
```

**New Structure:**
```
/
├── marcus-furniture-crm.php
├── assets/
└── includes/
```

All file paths in documentation have been updated to reflect the new structure.

---

**Last Updated**: January 4, 2026  
**Plugin Version**: 1.0  
**WordPress**: 5.0+  
**PHP**: 7.0+
