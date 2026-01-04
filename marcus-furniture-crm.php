<?php
/**
 * Plugin Name: Marcus Furniture CRM
 * Plugin URI: https://github.com/impact2021/Marcus-Furniture-CRM
 * Description: A CRM system for managing furniture moving enquiries with contact form and admin dashboard
 * Version: 1.1
 * Author: Impact Websites
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: marcus-furniture-crm
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
// Note: Using HS_CRM prefix for backward compatibility with existing database tables
// and class structure from the original Home Shield CRM plugin
define('HS_CRM_VERSION', '1.1');
define('HS_CRM_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('HS_CRM_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include required files
require_once HS_CRM_PLUGIN_DIR . 'includes/class-hs-crm-database.php';
require_once HS_CRM_PLUGIN_DIR . 'includes/class-hs-crm-form.php';
require_once HS_CRM_PLUGIN_DIR . 'includes/class-hs-crm-admin.php';
require_once HS_CRM_PLUGIN_DIR . 'includes/class-hs-crm-email.php';
require_once HS_CRM_PLUGIN_DIR . 'includes/class-hs-crm-settings.php';
require_once HS_CRM_PLUGIN_DIR . 'includes/class-hs-crm-truck-scheduler.php';

/**
 * Activation hook - Create database tables
 */
function hs_crm_activate() {
    HS_CRM_Database::create_tables();
    // Ensure migration runs on activation
    delete_option('hs_crm_db_version');
    hs_crm_check_db_version();
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'hs_crm_activate');

/**
 * Deactivation hook
 */
function hs_crm_deactivate() {
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'hs_crm_deactivate');

/**
 * Initialize plugin
 */
function hs_crm_init() {
    // Check for database updates
    hs_crm_check_db_version();
    
    // Initialize form shortcode
    $form = new HS_CRM_Form();
    
    // Initialize admin interface
    if (is_admin()) {
        $admin = new HS_CRM_Admin();
        $settings = new HS_CRM_Settings();
        $truck_scheduler = new HS_CRM_Truck_Scheduler();
    }
    
    // Initialize email handler
    $email = new HS_CRM_Email();
}
add_action('plugins_loaded', 'hs_crm_init');

/**
 * Format date with plugin timezone setting
 * 
 * @param string $mysql_date Date in MySQL format (Y-m-d H:i:s)
 * @param string $format Date format (default: 'd/m/Y H:i')
 * @return string Formatted date in plugin timezone
 */
function hs_crm_format_date($mysql_date, $format = 'd/m/Y H:i') {
    if (empty($mysql_date)) {
        return '';
    }
    
    // Get the plugin timezone setting
    $timezone_string = get_option('hs_crm_timezone', 'Pacific/Auckland');
    
    try {
        // Create DateTime object from MySQL date
        // MySQL CURRENT_TIMESTAMP uses the database server's timezone (usually UTC)
        // But we need to treat it as the WordPress site timezone for proper conversion
        // For WordPress < 5.3 compatibility, check if wp_timezone_string exists
        if (function_exists('wp_timezone_string')) {
            $wp_timezone = wp_timezone_string();
        } else {
            $wp_timezone = get_option('timezone_string') ?: 'UTC';
        }
        $date = new DateTime($mysql_date, new DateTimeZone($wp_timezone));
        
        // Convert to plugin timezone
        $date->setTimezone(new DateTimeZone($timezone_string));
        
        // Format and return
        return $date->format($format);
    } catch (Exception $e) {
        // Fallback to mysql2date for consistent formatting
        return mysql2date($format, $mysql_date);
    }
}

/**
 * Get current time formatted in plugin timezone
 * 
 * @param string $format Date format (default: 'd/m/Y H:i')
 * @return string Formatted current time in plugin timezone
 */
function hs_crm_current_time_formatted($format = 'd/m/Y H:i') {
    $timezone_string = get_option('hs_crm_timezone', 'Pacific/Auckland');
    
    try {
        $date = new DateTime('now', new DateTimeZone($timezone_string));
        return $date->format($format);
    } catch (Exception $e) {
        // Fallback to wp_date
        return wp_date($format);
    }
}

/**
 * Check and update database if needed
 */
function hs_crm_check_db_version() {
    $db_version = get_option('hs_crm_db_version', '1.0.0');
    
    if (version_compare($db_version, '1.1.0', '<')) {
        // Run migration for version 1.1.0
        hs_crm_migrate_to_1_1_0();
        update_option('hs_crm_db_version', '1.1.0');
    }
    
    // Note: Migrations 1.2.0 through 1.4.0 are included in version 1.1 release
    // These migrations are kept for backward compatibility with existing installations
    if (version_compare($db_version, '1.2.0', '<')) {
        // Run migration for version 1.2.0 - Create notes table
        hs_crm_migrate_to_1_2_0();
        update_option('hs_crm_db_version', '1.2.0');
    }
    
    if (version_compare($db_version, '1.3.0', '<')) {
        // Run migration for version 1.3.0 - Add first_email_sent_at column
        hs_crm_migrate_to_1_3_0();
        update_option('hs_crm_db_version', '1.3.0');
    }
    
    if (version_compare($db_version, '1.4.0', '<')) {
        // Run migration for version 1.4.0 - Add suburb and move_time columns
        hs_crm_migrate_to_1_4_0();
        update_option('hs_crm_db_version', '1.4.0');
    }
}

/**
 * Migrate database to version 1.1.0
 * Adds first_name, last_name, email_sent, and admin_notes columns
 */
function hs_crm_migrate_to_1_1_0() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'hs_enquiries';
    
    // Check if columns exist before adding them
    $columns = $wpdb->get_results("SHOW COLUMNS FROM $table_name");
    $column_names = array();
    foreach ($columns as $column) {
        $column_names[] = $column->Field;
    }
    
    // Add first_name column if it doesn't exist
    if (!in_array('first_name', $column_names)) {
        $wpdb->query("ALTER TABLE $table_name ADD COLUMN first_name varchar(255) NOT NULL DEFAULT '' AFTER id");
    }
    
    // Add last_name column if it doesn't exist
    if (!in_array('last_name', $column_names)) {
        $wpdb->query("ALTER TABLE $table_name ADD COLUMN last_name varchar(255) NOT NULL DEFAULT '' AFTER first_name");
    }
    
    // Add email_sent column if it doesn't exist
    if (!in_array('email_sent', $column_names)) {
        $wpdb->query("ALTER TABLE $table_name ADD COLUMN email_sent tinyint(1) NOT NULL DEFAULT 0 AFTER status");
    }
    
    // Add admin_notes column if it doesn't exist
    if (!in_array('admin_notes', $column_names)) {
        $wpdb->query("ALTER TABLE $table_name ADD COLUMN admin_notes text NOT NULL DEFAULT '' AFTER email_sent");
    }
    
    // Migrate existing name data to first_name and last_name
    $wpdb->query("
        UPDATE $table_name 
        SET first_name = SUBSTRING_INDEX(name, ' ', 1),
            last_name = IF(
                SUBSTRING_INDEX(name, ' ', 1) = SUBSTRING_INDEX(name, ' ', -1),
                '',
                TRIM(SUBSTRING(name, LOCATE(' ', name) + 1))
            )
        WHERE first_name = '' AND name != ''
    ");
}

/**
 * Migrate database to version 1.2.0
 * Creates notes table and migrates existing admin_notes
 */
function hs_crm_migrate_to_1_2_0() {
    global $wpdb;
    
    $charset_collate = $wpdb->get_charset_collate();
    $notes_table = $wpdb->prefix . 'hs_enquiry_notes';
    
    // Create notes table
    $sql = "CREATE TABLE IF NOT EXISTS $notes_table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        enquiry_id mediumint(9) NOT NULL,
        note text NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id),
        KEY enquiry_id (enquiry_id)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
    
    // Migrate existing admin_notes to the new notes table
    $enquiries_table = $wpdb->prefix . 'hs_enquiries';
    $enquiries = $wpdb->get_results("SELECT id, admin_notes FROM $enquiries_table WHERE admin_notes != ''");
    
    foreach ($enquiries as $enquiry) {
        if (!empty($enquiry->admin_notes)) {
            $wpdb->insert(
                $notes_table,
                array(
                    'enquiry_id' => $enquiry->id,
                    'note' => $enquiry->admin_notes
                ),
                array('%d', '%s')
            );
        }
    }
}

/**
 * Migrate database to version 1.3.0
 * Adds first_email_sent_at column to track when first message was sent
 */
function hs_crm_migrate_to_1_3_0() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'hs_enquiries';
    
    // Check if column exists before adding it
    $columns = $wpdb->get_results("SHOW COLUMNS FROM {$table_name}");
    $column_names = array_column($columns, 'Field');
    
    // Add first_email_sent_at column if it doesn't exist
    if (!in_array('first_email_sent_at', $column_names)) {
        // Table name is safe as it uses $wpdb->prefix which is sanitized by WordPress
        $wpdb->query("ALTER TABLE {$table_name} ADD COLUMN first_email_sent_at datetime DEFAULT NULL AFTER email_sent");
    }
}

/**
 * Migrate database to version 1.4.0
 * Adds suburb and move_time columns
 */
function hs_crm_migrate_to_1_4_0() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'hs_enquiries';
    
    // Check if columns exist before adding them
    $columns = $wpdb->get_results("SHOW COLUMNS FROM {$table_name}");
    $column_names = array_column($columns, 'Field');
    
    // Add suburb column if it doesn't exist
    if (!in_array('suburb', $column_names)) {
        $wpdb->query("ALTER TABLE {$table_name} ADD COLUMN suburb varchar(255) DEFAULT '' NOT NULL AFTER address");
    }
    
    // Add move_time column if it doesn't exist
    if (!in_array('move_time', $column_names)) {
        $wpdb->query("ALTER TABLE {$table_name} ADD COLUMN move_time time DEFAULT NULL AFTER move_date");
    }
}

/**
 * Enqueue styles and scripts
 */
function hs_crm_enqueue_assets() {
    wp_enqueue_style('hs-crm-styles', HS_CRM_PLUGIN_URL . 'assets/css/styles.css', array(), HS_CRM_VERSION);
    
    // Enqueue Google Places API
    $google_api_key = get_option('hs_crm_google_api_key', '');
    if (!empty($google_api_key)) {
        wp_enqueue_script('google-maps-places', 'https://maps.googleapis.com/maps/api/js?key=' . esc_attr($google_api_key) . '&libraries=places', array(), null, false);
    }
    
    wp_enqueue_script('hs-crm-scripts', HS_CRM_PLUGIN_URL . 'assets/js/scripts.js', array('jquery'), HS_CRM_VERSION, true);
    
    // Localize script for AJAX
    wp_localize_script('hs-crm-scripts', 'hsCrmAjax', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('hs_crm_nonce'),
        'thankYouUrl' => home_url('/thank-you/')
    ));
}
add_action('wp_enqueue_scripts', 'hs_crm_enqueue_assets');
add_action('admin_enqueue_scripts', 'hs_crm_enqueue_assets');

/**
 * Gravity Forms Integration
 * Hook into Gravity Forms submission to create enquiries
 */
function hs_crm_gravity_forms_integration($entry, $form) {
    // Verify that required classes are available
    if (!class_exists('HS_CRM_Database')) {
        return;
    }
    
    // Check if this form should be integrated (you can customize this based on form ID or form title)
    // For example, only integrate forms with specific IDs or containing "moving" or "enquiry" in the title
    $form_title = strtolower($form['title']);
    $integrate_keywords = array('moving', 'enquiry', 'contact', 'furniture', 'quote');
    
    // Check if form title contains any integration keywords or if a specific setting is enabled
    $should_integrate = false;
    foreach ($integrate_keywords as $keyword) {
        if (strpos($form_title, $keyword) !== false) {
            $should_integrate = true;
            break;
        }
    }
    
    // Allow manual override via form setting (if form has a CSS class "crm-integration")
    if (isset($form['cssClass']) && strpos($form['cssClass'], 'crm-integration') !== false) {
        $should_integrate = true;
    }
    
    // If form doesn't match integration criteria, skip
    if (!$should_integrate) {
        return;
    }
    
    // Map Gravity Forms fields to CRM fields
    // This is flexible - it will try to find fields by label or input name
    $field_mapping = array(
        'first_name' => array('first name', 'first', 'fname'),
        'last_name' => array('last name', 'last', 'surname', 'lname'),
        'email' => array('email', 'e-mail', 'email address'),
        'phone' => array('phone', 'telephone', 'mobile', 'phone number'),
        'address' => array('address', 'street address', 'location'),
        'suburb' => array('suburb', 'city', 'town'),
        'move_date' => array('move date', 'moving date', 'preferred date', 'date'),
        'move_time' => array('move time', 'moving time', 'preferred time', 'time')
    );
    
    $data = array(
        'contact_source' => 'form'
    );
    
    // Extract data from Gravity Forms entry
    foreach ($form['fields'] as $field) {
        $field_label = strtolower(trim($field->label));
        $field_value = '';
        
        // Get field value from entry
        if (isset($entry[$field->id])) {
            $field_value = $entry[$field->id];
        }
        
        // Skip empty values
        if (empty($field_value)) {
            continue;
        }
        
        // Match field to CRM field
        foreach ($field_mapping as $crm_field => $possible_labels) {
            foreach ($possible_labels as $label) {
                if (strpos($field_label, $label) !== false) {
                    // Handle name fields specially if using a single "Name" field
                    if ($field->type === 'name' && is_array($field_value)) {
                        if (isset($field_value[3])) { // First name
                            $data['first_name'] = sanitize_text_field($field_value[3]);
                        }
                        if (isset($field_value[6])) { // Last name
                            $data['last_name'] = sanitize_text_field($field_value[6]);
                        }
                    } elseif ($field->type === 'address' && is_array($field_value)) {
                        // Gravity Forms address field has specific indices:
                        // - field_value[1] = Street Address
                        // - field_value[2] = Address Line 2
                        // - field_value[3] = City/Suburb
                        // - field_value[4] = State/Province
                        // - field_value[5] = ZIP/Postal Code
                        // - field_value[6] = Country
                        
                        // Extract suburb/city if available
                        if (!empty($field_value[3])) {
                            $data['suburb'] = sanitize_text_field($field_value[3]);
                        }
                        
                        // Combine address parts for address field
                        $address_parts = array();
                        if (!empty($field_value[1])) $address_parts[] = $field_value[1]; // Street
                        if (!empty($field_value[2])) $address_parts[] = $field_value[2]; // Address Line 2
                        if (!empty($field_value[3])) $address_parts[] = $field_value[3]; // City
                        if (!empty($field_value[4])) $address_parts[] = $field_value[4]; // State
                        if (!empty($field_value[5])) $address_parts[] = $field_value[5]; // ZIP
                        
                        $data['address'] = sanitize_textarea_field(implode(', ', $address_parts));
                    } elseif ($field->type === 'date') {
                        // Format date properly
                        $data[$crm_field] = sanitize_text_field($field_value);
                    } elseif ($field->type === 'time') {
                        // Format time properly
                        $data[$crm_field] = sanitize_text_field($field_value);
                    } else {
                        // Standard text field
                        if ($crm_field === 'email') {
                            $data[$crm_field] = sanitize_email($field_value);
                        } elseif ($crm_field === 'address') {
                            $data[$crm_field] = sanitize_textarea_field($field_value);
                        } else {
                            $data[$crm_field] = sanitize_text_field($field_value);
                        }
                    }
                    break 2; // Exit both loops once we find a match
                }
            }
        }
    }
    
    // Validate required fields
    $required_fields = array('first_name', 'last_name', 'email', 'phone', 'address');
    $has_all_required = true;
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            $has_all_required = false;
            break;
        }
    }
    
    // Only create enquiry if we have all required fields
    if ($has_all_required) {
        // Insert into database
        $enquiry_id = HS_CRM_Database::insert_enquiry($data);
        
        // Add a note indicating this came from Gravity Forms
        if ($enquiry_id) {
            global $wpdb;
            $notes_table = $wpdb->prefix . 'hs_enquiry_notes';
            $wpdb->insert(
                $notes_table,
                array(
                    'enquiry_id' => $enquiry_id,
                    'note' => 'Enquiry created from Gravity Forms: ' . esc_html($form['title']) . ' (Form ID: ' . $form['id'] . ')'
                ),
                array('%d', '%s')
            );
            
            // Send admin notification (customer email already sent by Gravity Forms)
            $admin_email = get_option('hs_crm_admin_email', get_option('admin_email'));
            $subject = 'New Moving Enquiry from Gravity Forms - Marcus Furniture';
            $dashboard_link = admin_url('admin.php?page=hs-crm-enquiries');
            
            $message = '<!DOCTYPE html>';
            $message .= '<html>';
            $message .= '<head><meta charset="UTF-8"></head>';
            $message .= '<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">';
            $message .= '<h1>New Moving Enquiry Received</h1>';
            $message .= '<p>A new moving enquiry has been submitted via Gravity Forms.</p>';
            $message .= '<h3>Customer Details</h3>';
            $message .= '<p><strong>Name:</strong> ' . esc_html($data['first_name'] . ' ' . $data['last_name']) . '</p>';
            $message .= '<p><strong>Email:</strong> ' . esc_html($data['email']) . '</p>';
            $message .= '<p><strong>Phone:</strong> ' . esc_html($data['phone']) . '</p>';
            $message .= '<p><strong>Address:</strong> ' . esc_html($data['address']) . '</p>';
            if (!empty($data['suburb'])) {
                $message .= '<p><strong>Suburb:</strong> ' . esc_html($data['suburb']) . '</p>';
            }
            if (!empty($data['move_date'])) {
                // Validate and format the date safely
                $timestamp = strtotime($data['move_date']);
                if ($timestamp !== false) {
                    $move_date_display = date('d/m/Y', $timestamp);
                    if (!empty($data['move_time'])) {
                        $time_timestamp = strtotime($data['move_time']);
                        if ($time_timestamp !== false) {
                            $move_date_display .= ' at ' . date('g:i A', $time_timestamp);
                        }
                    }
                    $message .= '<p><strong>Requested Move Date:</strong> ' . esc_html($move_date_display) . '</p>';
                } else {
                    $message .= '<p><strong>Requested Move Date:</strong> ' . esc_html($data['move_date']) . '</p>';
                }
            }
            $message .= '<p><strong>Source:</strong> Gravity Forms - ' . esc_html($form['title']) . '</p>';
            $message .= '<p><a href="' . esc_url($dashboard_link) . '" style="display: inline-block; padding: 10px 20px; background: #0073aa; color: white; text-decoration: none; border-radius: 4px;">View in Dashboard</a></p>';
            $message .= '</body>';
            $message .= '</html>';
            
            $headers = array('Content-Type: text/html; charset=UTF-8');
            
            // Send email and log if it fails
            $mail_sent = wp_mail($admin_email, $subject, $message, $headers);
            if (!$mail_sent) {
                error_log('Marcus Furniture CRM: Failed to send admin notification email for Gravity Forms submission (Form ID: ' . $form['id'] . ')');
            }
        }
    }
}

// Hook into Gravity Forms after submission only if Gravity Forms is active
if (class_exists('GFForms') || function_exists('gform_after_submission')) {
    add_action('gform_after_submission', 'hs_crm_gravity_forms_integration', 10, 2);
}
