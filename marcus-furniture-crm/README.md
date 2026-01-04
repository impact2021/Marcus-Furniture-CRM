# Marcus Furniture CRM

A WordPress CRM plugin for managing furniture moving enquiries with contact form, admin dashboard, and truck scheduling system.

## Features

### Customer Enquiry Management
- **Contact Form**: Public-facing form with move date field
- **Automatic Entries**: Auto-create enquiries from website form submissions
- **Manual Entry Creation**: Add enquiries from WhatsApp, phone calls, direct emails, etc.
- **Sortable Lists**: Sort by contact date or requested move date (defaults to move date)
- **Status Workflow**: Track enquiries through stages:
  - First Contact
  - Quote Sent
  - Booking Confirmed
  - Deposit Paid
  - Completed
  - Archived

### Truck Scheduling System
- **Visual Calendar**: Month view showing all truck bookings
- **Truck Management**: Add/edit/remove trucks with details (name, registration, capacity)
- **Booking Management**: 
  - Add bookings with date and time ranges
  - Link bookings to customer enquiries
  - Add notes for each booking
  - Click calendar cells to quick-add bookings
  - Color-coded display for easy viewing

### Communication Tools
- **Email Templates**: Send quotes, invoices, and receipts
- **Quote Builder**: Create itemized quotes with GST calculations
- **Automated Notifications**: Customer confirmation and admin notification emails
- **Notes System**: Add timestamped notes to each enquiry

### Admin Features
- **Dashboard**: View and filter enquiries by status
- **Quick Actions**: Change status, send emails, edit details
- **Multi-source Tracking**: Know if enquiry came from form, WhatsApp, phone, or email
- **New Zealand Focus**: Google Maps autocomplete restricted to NZ addresses
- **Timezone Support**: Display dates/times in NZ timezone

## Installation

1. Download the `marcus-furniture-crm` folder
2. Upload to your WordPress site's `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Go to **MF Enquiries > Settings** to configure:
   - Admin email address
   - Google Maps API key (optional, for address autocomplete)
   - Timezone

## Usage

### Displaying the Contact Form

Add the following shortcode to any page or post:

```
[hs_contact_form]
```

### Managing Enquiries

1. Go to **MF Enquiries** in the WordPress admin menu
2. View enquiries filtered by status (Active leads, All, First Contact, etc.)
3. Click **+ Add New Enquiry** to manually create entries
4. Use the **Status Change** dropdown to update workflow stage
5. Use the **Action** dropdown to send quotes/invoices or edit details
6. Add notes to track communication and progress

### Truck Scheduling

1. Go to **MF Enquiries > Truck Scheduler**
2. Click **+ Add Truck** to add your vehicles
3. Click **+ Add Booking** or click any calendar cell to create bookings
4. Link bookings to customer enquiries for integrated tracking
5. Navigate months using Previous/Next buttons
6. Click on bookings to edit or delete them

## Requirements

- WordPress 5.0 or higher
- PHP 7.0 or higher
- MySQL 5.6 or higher

## Optional Configuration

### Google Maps API Key

For address autocomplete functionality (restricted to New Zealand):

1. Get an API key from [Google Maps Platform](https://developers.google.com/maps/documentation/javascript/get-api-key)
2. Enable **Places API** and **Maps JavaScript API**
3. Add the key in **MF Enquiries > Settings**

## Database Tables

The plugin creates the following tables:

- `wp_hs_enquiries` - Customer enquiries
- `wp_hs_enquiry_notes` - Notes attached to enquiries  
- `wp_hs_trucks` - Truck information
- `wp_hs_truck_bookings` - Truck booking schedule

## Support

For issues or feature requests, please contact Impact Websites.

## License

GPL v2 or later

## Credits

Developed by Impact Websites for Marcus Furniture
