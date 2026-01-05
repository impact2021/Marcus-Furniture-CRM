# CRM Manager Role Guide

## Overview

The Marcus Furniture CRM plugin includes a custom **CRM Manager** user role that allows you to create users with access only to the CRM functionality, without giving them full WordPress administrator access.

## Version 2.1 Verification

✅ **Confirmed**: The CRM Manager role is fully functional and ready to use.

## Role Capabilities

The CRM Manager role has the following capabilities:

| Capability | Status | Description |
|------------|--------|-------------|
| `read` | ✓ Enabled | Required for WordPress backend access |
| `manage_crm_enquiries` | ✓ Enabled | Can create, edit, and manage customer enquiries |
| `view_crm_dashboard` | ✓ Enabled | Can view the CRM dashboard and enquiry lists |
| `manage_crm_settings` | ✗ Disabled | Cannot access CRM plugin settings (admin-only) |

### What CRM Managers CAN Do:

- Access the WordPress admin dashboard
- View the "MF Enquiries" menu
- Create new enquiries
- Edit existing enquiries
- Update enquiry status
- Add and manage notes on enquiries
- Send emails to customers
- View and manage truck scheduling
- Filter and sort enquiries by status

### What CRM Managers CANNOT Do:

- Access other WordPress admin areas (posts, pages, media, etc.)
- Install or manage plugins
- Manage WordPress themes
- Create or manage other users
- Access CRM plugin settings
- Access WordPress general settings

## Creating a User with CRM Manager Role

### Method 1: Through WordPress Admin

1. Log in to WordPress as an Administrator
2. Navigate to **Users → Add New**
3. Fill in the new user details:
   - Username (required)
   - Email (required)
   - First Name (optional)
   - Last Name (optional)
   - Password (required - or "Send User Notification" to let them set it)
4. In the **Role** dropdown, select **CRM Manager**
5. Click **Add New User**

### Method 2: Programmatically

```php
// Create a new user with CRM Manager role
$user_id = wp_create_user(
    'username',           // Username
    'password',           // Password
    'email@example.com'   // Email
);

if (!is_wp_error($user_id)) {
    // Assign the CRM Manager role
    $user = new WP_User($user_id);
    $user->set_role('crm_manager');
}
```

## Role Availability

The CRM Manager role becomes available:

1. **On Plugin Activation**: The role is automatically created when the plugin is activated
2. **Visible in User Interface**: The role appears in the WordPress user creation/editing interface
3. **Persistent**: The role remains available even after plugin deactivation (only removed on plugin uninstall)

## Technical Implementation

### Role Creation

The role is created in the `hs_crm_create_custom_role()` function, which is called during plugin activation:

```php
add_role(
    'crm_manager',
    'CRM Manager',
    array(
        'read' => true,
        'manage_crm_enquiries' => true,
        'view_crm_dashboard' => true,
    )
);
```

Note: The `manage_crm_settings` capability is intentionally not included, which means CRM Managers will not have this permission (only administrators do).

### Making Role Editable

The role is made visible in the user interface through the `editable_roles` filter:

```php
add_filter('editable_roles', 'hs_crm_make_role_editable');
```

### Permission Checks

Access to CRM features is controlled using `current_user_can()` checks:

```php
// In admin menu
add_menu_page(
    'Marcus Furniture Enquiries',
    'MF Enquiries',
    'view_crm_dashboard',  // Capability check
    'hs-crm-enquiries',
    array($this, 'render_admin_page'),
    'dashicons-move',
    26
);
```

## Troubleshooting

### Role Not Appearing in Dropdown

If the CRM Manager role doesn't appear in the user role dropdown:

1. Deactivate the plugin
2. Re-activate the plugin (this will recreate the role)
3. Refresh the user creation page

### User Cannot Access CRM Dashboard

If a user with CRM Manager role cannot access the CRM:

1. Verify the user has the CRM Manager role:
   - Go to **Users → All Users**
   - Check the user's role in the user list
2. Ensure the plugin is activated
3. Try logging out and logging back in

### Removing the Role

The CRM Manager role can be removed:

1. **Manually** via code:
   ```php
   remove_role('crm_manager');
   ```
2. **Automatically** when the plugin is uninstalled (if uninstall.php is implemented)

## Security Considerations

The CRM Manager role is designed with security in mind:

- **Limited Scope**: Only has access to CRM-specific functionality
- **No Settings Access**: Cannot modify plugin settings
- **No User Management**: Cannot create or modify other users
- **Read-Only WordPress**: Cannot access other WordPress admin areas
- **Backend Only**: Role is only for backend access, not frontend

## Use Cases

The CRM Manager role is ideal for:

1. **Customer Service Representatives**: Staff who need to manage customer enquiries but don't need full site access
2. **Sales Team Members**: Team members who track leads and manage customer communications
3. **Office Administrators**: Staff who handle scheduling and enquiry management
4. **Remote Workers**: Team members who need CRM access but shouldn't have full WordPress access
5. **Temporary Staff**: Contractors or temporary workers who need limited access

## Summary

✅ **Version 2.1 Confirmation**: The CRM Manager role is fully implemented and tested
✅ **User Creation**: You can create new users with the CRM Manager role through the WordPress admin interface
✅ **Proper Permissions**: The role has appropriate permissions for CRM access without full admin rights
✅ **Production Ready**: The role is ready for use in production environments
