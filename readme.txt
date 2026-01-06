=== Marcus Furniture CRM ===
Contributors: impactwebsites
Tags: crm, enquiry management, contact form, truck scheduling, furniture moving
Requires at least: 5.0
Tested up to: 6.8
Stable tag: 2.7
Requires PHP: 7.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A CRM system for managing furniture moving enquiries with contact form, admin dashboard, and truck scheduling system.

== Description ==

Marcus Furniture CRM is a comprehensive WordPress plugin designed specifically for furniture moving and relocation companies. It provides a complete customer relationship management system with contact form, enquiry tracking, and truck scheduling capabilities.

= Key Features =

**Customer Enquiry Management**

* Public-facing contact form with move date field
* Automatic entry creation from website form submissions
* Manual entry creation for phone, WhatsApp, email enquiries
* Sortable lists by contact date or requested move date
* Status workflow tracking through multiple stages

**Truck Scheduling System**

* Visual calendar with month view
* Add/edit/remove trucks with details
* Link bookings to customer enquiries
* Time range management for bookings
* Color-coded display for easy viewing

**Communication Tools**

* Email templates for quotes, invoices, and receipts
* Quote builder with itemized lists and GST calculations
* Automated customer confirmation and admin notification emails
* Notes system with timestamps for tracking communication

**Admin Features**

* Dashboard with enquiry filtering by status
* Quick actions for status changes and email sending
* Multi-source tracking (form, WhatsApp, phone, email)
* Google Maps autocomplete for New Zealand addresses
* Timezone support for accurate date/time display

= Workflow Stages =

Track enquiries through your complete sales process:

1. First Contact
2. Quote Sent
3. Booking Confirmed
4. Deposit Paid
5. Completed
6. Archived

= New Zealand Focused =

* Google Maps autocomplete restricted to NZ addresses
* Timezone options including Pacific/Auckland
* GST calculation at 15%
* Date format: d/m/Y (NZ/AU standard)

= Gravity Forms Compatible =

Works alongside Gravity Forms - you can use either the built-in contact form or integrate with Gravity Forms for more advanced form features.

== Installation ==

1. Upload the `marcus-furniture-crm` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to **MF Enquiries > Settings** to configure your admin email and timezone
4. (Optional) Add your Google Maps API key for address autocomplete
5. Add the shortcode `[hs_contact_form]` to any page to display the contact form

= Minimum Requirements =

* WordPress 5.0 or higher
* PHP 7.0 or higher
* MySQL 5.6 or higher

== Frequently Asked Questions ==

= How do I display the contact form? =

Add the shortcode `[hs_contact_form]` to any page or post where you want the contact form to appear.

= Can I create enquiries manually? =

Yes! Click the "+ Add New Enquiry" button in the MF Enquiries dashboard to manually create entries from phone calls, WhatsApp messages, or direct emails.

= How do I set up truck scheduling? =

1. Go to MF Enquiries > Truck Scheduler
2. Click "+ Add Truck" to add your vehicles
3. Click any calendar cell or use "+ Add Booking" to create bookings
4. Link bookings to customer enquiries for integrated tracking

= Does this work with Gravity Forms? =

Yes, the plugin is compatible with Gravity Forms. You can use the built-in contact form or integrate with Gravity Forms for more advanced features.

= How do I configure Google Maps autocomplete? =

1. Get an API key from Google Maps Platform
2. Enable Places API and Maps JavaScript API
3. Add the key in MF Enquiries > Settings
4. The autocomplete will work in the address field, restricted to New Zealand

= What emails are sent automatically? =

When a customer submits the contact form, two emails are sent:
1. Confirmation email to the customer
2. Notification email to the admin (configured in Settings)

= Can I customize the email templates? =

Yes, the email templates can be customized by editing the PHP files in the includes folder. Look for `class-hs-crm-email.php` and `class-hs-crm-form.php`.

= What database tables are created? =

The plugin creates four tables:
* wp_hs_enquiries - Customer enquiries
* wp_hs_enquiry_notes - Notes attached to enquiries
* wp_hs_trucks - Truck information
* wp_hs_truck_bookings - Truck booking schedule

== Screenshots ==

1. Main enquiries dashboard with status filtering
2. Contact form with move date picker
3. Truck scheduler calendar view
4. Manual enquiry creation form
5. Quote builder interface
6. Settings page

== Changelog ==

= 2.7 =
* Updated plugin version to 2.7
* Updated WordPress compatibility - tested up to version 6.8

= 2.5 =
* Added Gravity Form name display - enquiries now show which specific form they came from
* Added source_form_name database column to track the originating Gravity Form
* Form name displays in blue above the job type in the admin enquiries table
* Enhanced move date visibility with larger fonts, color coding, and highlighted box
* Move dates now stand out with red/pink highlighting and 15px bold font
* Added complete uninstall script to remove all data when plugin is deleted
* Uninstall removes all database tables, options, and custom user role
* Updated plugin version to 2.5

= 2.2 =
* Added comprehensive debug mode for Gravity Forms import to identify field mapping issues
* Debug mode shows detailed information about each entry including:
  - All form fields found and their types
  - Name and address field subfield values
  - Extracted data for each required field
  - Reasons why entries were skipped (missing fields or duplicates)
  - All available entry keys for troubleshooting
* Enhanced Gravity Forms import error reporting
* Updated plugin version to 2.2

= 1.9 =
* Fixed critical bug in update_enquiry() that caused "Failed to update enquiry" errors
* Resolved duplicate format specifier issue when updating delivery addresses
* The bug occurred when the address field auto-sync logic added duplicate format strings
* This fix ensures enquiry updates work correctly in all scenarios

= 1.8 =
* Fixed critical enquiry update bug where delivery address changes weren't syncing to the legacy address field
* Improved data consistency when editing enquiries to ensure all related fields update correctly
* Enhanced update_enquiry() method to auto-update the address field when delivery addresses change
* This fix resolves the "Failed to update registry" issue reported when editing enquiries

= 1.7 =
* Removed generic "Address" field - now only uses "From Address" and "To Address"
* Made from/to address fields required when creating enquiries
* Removed "House size" field from the user interface
* Cleaned up stairs fields - now only "Stairs Involved (From Address)" and "Stairs Involved (To Address)"
* Fixed "Failed to create enquiry" error that occurred when editing or creating enquiries
* Improved admin table display to show from/to addresses more prominently
* Updated field validation and error handling for better user experience

= 1.6 =
* Simplified address fields to only "From Address" and "To Address" (removed separate pickup/dropoff fields)
* Added dedicated "Edit" button to enquiry table for easier access (removed from action dropdown)
* Improved edit details modal layout with better column organization for desktop viewing
* Enhanced user experience with clearer field labels and better form organization

= 1.4 =
* Added separated booking time fields (booking start and finish) for admin to enter actual booking times
* Simplified address fields to pickup and dropoff addresses
* Changed house size to number of bedrooms dropdown (1-6)
* Changed number of rooms to total number of rooms dropdown (1-12)
* Added property notes field for additional property information
* Enhanced booking management with more detailed property information

= 1.3 =
* Updated plugin to version 1.3
* Added delivery from address field to track pick-up locations
* Added delivery to address field to track drop-off locations
* Enhanced enquiry editing capability with delivery address management
* Improved database schema with new delivery address columns

= 1.2 =
* Added documentation page explaining Gravity Forms integration
* Added truck assignment functionality to enquiries
* Added house details fields: house size, number of rooms, stairs
* Reorganized enquiries table columns for better space management
* Combined source, contact date, and move date into one column
* Combined contact info and address into another column
* Made notes collapsible with toggle icon for better UI

= 1.0 =
* Initial release
* Customer enquiry management system
* Contact form with move date field
* Manual entry creation from multiple sources
* Status workflow tracking
* Truck scheduling system
* Visual calendar for truck bookings
* Email templates for quotes and invoices
* Notes system
* Google Maps integration for NZ addresses
* Timezone support

== Upgrade Notice ==

= 1.3 =
Version 1.3 adds delivery address fields for better tracking of pick-up and drop-off locations, plus enhanced edit functionality.

= 1.2 =
Enhanced enquiries management with truck assignments, house details, and improved UI layout.

= 1.0 =
Initial release of Marcus Furniture CRM.

== Additional Information ==

= Support =

For support and feature requests, please contact Impact Websites.

= Credits =

Developed by Impact Websites for Marcus Furniture.

= Privacy Policy =

This plugin stores customer enquiry data in your WordPress database. No data is sent to external services except:
* Email notifications sent through your WordPress mail system
* Google Maps API calls (only if API key is configured)

All customer data is stored locally on your WordPress installation and is subject to your site's privacy policy.
