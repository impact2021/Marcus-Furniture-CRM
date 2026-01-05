<?php
/**
 * Uninstall script for Marcus Furniture CRM
 * 
 * This file is executed when the plugin is uninstalled via WordPress admin.
 * It removes all data created by the plugin, including:
 * - Database tables
 * - Plugin options
 * - Custom user role
 */

// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Check if user has opted to delete all data on uninstall
$delete_on_uninstall = get_option('hs_crm_delete_on_uninstall', false);

if (!$delete_on_uninstall) {
    // User has not opted to delete data - preserve everything
    return;
}

global $wpdb;

// Remove database tables
// Note: $wpdb->prefix is sanitized by WordPress core, so these table names are safe
// We're only using hardcoded table names that we created, not user input
$tables = array(
    $wpdb->prefix . 'hs_enquiries',
    $wpdb->prefix . 'hs_enquiry_notes',
    $wpdb->prefix . 'hs_trucks',
    $wpdb->prefix . 'hs_truck_bookings'
);

foreach ($tables as $table) {
    // Table names use $wpdb->prefix which is sanitized by WordPress
    // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
    $wpdb->query("DROP TABLE IF EXISTS {$table}");
}

// Remove plugin options
$options = array(
    'hs_crm_db_version',
    'hs_crm_admin_email',
    'hs_crm_google_api_key',
    'hs_crm_timezone',
    'hs_crm_default_booking_duration',
    'hs_crm_delete_on_uninstall'
);

foreach ($options as $option) {
    delete_option($option);
}

// Remove custom user role
remove_role('crm_manager');

// Remove capabilities from administrator role
$admin_role = get_role('administrator');
if ($admin_role) {
    $admin_role->remove_cap('manage_crm_enquiries');
    $admin_role->remove_cap('view_crm_dashboard');
    $admin_role->remove_cap('manage_crm_settings');
}

// Note: User data (users assigned to crm_manager role) is preserved
// Those users will need to be reassigned to a different role manually
