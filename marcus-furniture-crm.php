<?php
/**
 * Plugin Name: Marcus Furniture CRM
 * Plugin URI: https://github.com/impact2021/Marcus-Furniture-CRM
 * Description: A CRM system for managing furniture moving enquiries with contact form and admin dashboard
 * Version: 2.14
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
define('HS_CRM_VERSION', '2.14');
define('HS_CRM_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('HS_CRM_PLUGIN_URL', plugin_dir_url(__FILE__));
define('HS_CRM_DEFAULT_BOOKING_DURATION', 3); // Default booking duration in hours

// Include required files
require_once HS_CRM_PLUGIN_DIR . 'includes/class-hs-crm-database.php';
require_once HS_CRM_PLUGIN_DIR . 'includes/class-hs-crm-form.php';
require_once HS_CRM_PLUGIN_DIR . 'includes/class-hs-crm-admin.php';
require_once HS_CRM_PLUGIN_DIR . 'includes/class-hs-crm-email.php';
require_once HS_CRM_PLUGIN_DIR . 'includes/class-hs-crm-settings.php';
require_once HS_CRM_PLUGIN_DIR . 'includes/class-hs-crm-truck-scheduler.php';
require_once HS_CRM_PLUGIN_DIR . 'includes/class-hs-crm-docs.php';

/**
 * Activation hook - Create database tables and custom role
 */
function hs_crm_activate() {
    HS_CRM_Database::create_tables();
    // Ensure migration runs on activation
    delete_option('hs_crm_db_version');
    hs_crm_check_db_version();
    
    // Create custom user role for CRM-only access
    hs_crm_create_custom_role();
    
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'hs_crm_activate');

/**
 * Create custom CRM Manager role
 */
function hs_crm_create_custom_role() {
    // Remove the role first if it exists to ensure clean slate
    remove_role('crm_manager');
    
    // Add the CRM Manager role with only CRM access capabilities
    add_role(
        'crm_manager',
        'CRM Manager',
        array(
            'read' => true, // Required for backend access
            'manage_crm_enquiries' => true,
            'view_crm_dashboard' => true,
            'gravityforms_view_entries' => true, // Allow viewing Gravity Forms entries
        )
    );
    
    // Also ensure admin has these capabilities
    // Admins already have manage_options which gives them full access
    $admin_role = get_role('administrator');
    if ($admin_role) {
        $admin_role->add_cap('manage_crm_enquiries');
        $admin_role->add_cap('view_crm_dashboard');
        $admin_role->add_cap('manage_crm_settings');
    }
}

/**
 * Ensure CRM Manager role is visible in user role dropdown
 */
function hs_crm_make_role_editable($roles) {
    // Ensure CRM Manager role is available for assignment
    // This makes the role visible in the user creation/editing interface
    // WordPress editable_roles filter expects array('role_slug' => 'Display Name')
    if (!isset($roles['crm_manager'])) {
        $crm_role = get_role('crm_manager');
        if ($crm_role) {
            $roles['crm_manager'] = translate_user_role('CRM Manager');
        }
    }
    return $roles;
}
add_filter('editable_roles', 'hs_crm_make_role_editable');

/**
 * Deactivation hook
 */
function hs_crm_deactivate() {
    flush_rewrite_rules();
    // Note: We don't remove the role on deactivation to preserve user assignments
    // Role will be removed on plugin uninstall if uninstall.php is implemented
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
        $docs = new HS_CRM_Docs();
        
        // Hide other admin menu items for CRM Managers
        add_action('admin_menu', 'hs_crm_restrict_admin_menu', 999);
        
        // Redirect CRM Managers to CRM dashboard on login
        add_action('admin_init', 'hs_crm_redirect_crm_manager');
        
        // Hide admin bar items for CRM Managers
        add_action('wp_before_admin_bar_render', 'hs_crm_customize_admin_bar');
    }
    
    // Add login redirect filter for CRM Managers
    // Login redirects occur before admin context is established, so this filter must be registered outside is_admin()
    add_filter('login_redirect', 'hs_crm_login_redirect', 10, 3);
    
    // Initialize email handler
    $email = new HS_CRM_Email();
}
add_action('plugins_loaded', 'hs_crm_init');

/**
 * Restrict admin menu for CRM Managers - hide everything except CRM pages
 */
function hs_crm_restrict_admin_menu() {
    $user = wp_get_current_user();
    
    // Only restrict for CRM Managers (not admins)
    if (in_array('crm_manager', $user->roles) && !in_array('administrator', $user->roles)) {
        // Remove default WordPress menu items
        remove_menu_page('index.php');                  // Dashboard
        remove_menu_page('edit.php');                   // Posts
        remove_menu_page('upload.php');                 // Media
        remove_menu_page('edit.php?post_type=page');   // Pages
        remove_menu_page('edit-comments.php');          // Comments
        remove_menu_page('themes.php');                 // Appearance
        remove_menu_page('plugins.php');                // Plugins
        remove_menu_page('users.php');                  // Users
        remove_menu_page('tools.php');                  // Tools
        remove_menu_page('options-general.php');        // Settings
        
        // Remove other common plugin menu items
        remove_menu_page('wpcf7');                      // Contact Form 7
        // Note: Gravity Forms is intentionally NOT removed so CRM Managers can view entries
        // remove_menu_page('gf_edit_forms');           // Gravity Forms
        remove_menu_page('jetpack');                    // Jetpack
        remove_menu_page('wpseo_dashboard');            // Yoast SEO
        
        // Remove profile submenu (they can't edit their profile)
        remove_submenu_page('users.php', 'profile.php');
    }
}

/**
 * Redirect CRM Managers to enquiries page on login
 * 
 * @param string $redirect_to The redirect destination URL
 * @param string $request The requested redirect destination URL passed as a parameter
 * @param WP_User|WP_Error $user The user object or WP_Error on login failure
 * @return string The redirect URL
 */
function hs_crm_login_redirect($redirect_to, $request, $user) {
    // Check if user is valid and is a CRM Manager (and not an administrator)
    if (is_a($user, 'WP_User') && isset($user->roles) && is_array($user->roles)) {
        if (in_array('crm_manager', $user->roles) && !in_array('administrator', $user->roles)) {
            return admin_url('admin.php?page=hs-crm-enquiries');
        }
    }
    
    return $redirect_to;
}

/**
 * Redirect CRM Managers to enquiries page after admin navigation (fallback)
 * This handles cases where CRM Managers navigate to non-CRM admin pages
 */
function hs_crm_redirect_crm_manager() {
    $user = wp_get_current_user();
    
    // Only redirect CRM Managers on non-CRM admin pages
    if (in_array('crm_manager', $user->roles) && !in_array('administrator', $user->roles)) {
        // Check if we're on a non-CRM admin page
        global $pagenow;
        if ($pagenow === 'index.php' || $pagenow === 'profile.php' || ($pagenow === 'admin.php' && !isset($_GET['page']))) {
            wp_safe_redirect(admin_url('admin.php?page=hs-crm-enquiries'));
            exit;
        }
    }
}

/**
 * Customize admin bar for CRM Managers
 */
function hs_crm_customize_admin_bar() {
    global $wp_admin_bar;
    $user = wp_get_current_user();
    
    if (in_array('crm_manager', $user->roles) && !in_array('administrator', $user->roles)) {
        // Remove WordPress logo and menu
        $wp_admin_bar->remove_menu('wp-logo');
        
        // Remove other non-essential items
        $wp_admin_bar->remove_menu('comments');
        $wp_admin_bar->remove_menu('new-content');
        $wp_admin_bar->remove_menu('updates');
    }
}

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
    
    if (version_compare($db_version, '1.5.0', '<')) {
        // Run migration for version 1.5.0 - Add truck_id, house_size, number_of_rooms, stairs columns
        hs_crm_migrate_to_1_5_0();
        update_option('hs_crm_db_version', '1.5.0');
    }
    
    if (version_compare($db_version, '1.6.0', '<')) {
        // Run migration for version 1.6.0 - Add delivery_from_address and delivery_to_address columns
        hs_crm_migrate_to_1_6_0();
        update_option('hs_crm_db_version', '1.6.0');
    }
    
    if (version_compare($db_version, '1.7.0', '<')) {
        // Run migration for version 1.7.0 - No database changes, just update version
        // Version 1.7 focuses on UI improvements: removed address field, removed house_size from UI
        update_option('hs_crm_db_version', '1.7.0');
    }
    
    if (version_compare($db_version, '1.10.0', '<')) {
        // Run migration for version 1.10.0 - Add number_of_bedrooms column
        hs_crm_migrate_to_1_10_0();
        update_option('hs_crm_db_version', '1.10.0');
    }
    
    if (version_compare($db_version, '2.3.0', '<')) {
        // Run migration for version 2.3.0 - Add pickup/delivery specific columns
        hs_crm_migrate_to_2_3_0();
        update_option('hs_crm_db_version', '2.3.0');
    }
    
    if (version_compare($db_version, '2.5.0', '<')) {
        // Run migration for version 2.5.0 - Add source_form_name column
        hs_crm_migrate_to_2_5_0();
        update_option('hs_crm_db_version', '2.5.0');
    }
    
    if (version_compare($db_version, '2.6.0', '<')) {
        // Run migration for version 2.6.0 - Add new enquiry form fields
        hs_crm_migrate_to_2_6_0();
        update_option('hs_crm_db_version', '2.6.0');
    }
    
    if (version_compare($db_version, '2.6.1', '<')) {
        // Run migration for version 2.6.1 - Add Gravity Forms entry ID tracking
        hs_crm_migrate_to_2_6_1();
        update_option('hs_crm_db_version', '2.6.1');
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
 * Migrate database to version 1.5.0
 * Adds truck_id, house_size, number_of_rooms, and stairs columns
 */
function hs_crm_migrate_to_1_5_0() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'hs_enquiries';
    
    // Check if columns exist before adding them
    $columns = $wpdb->get_results("SHOW COLUMNS FROM {$table_name}");
    $column_names = array_column($columns, 'Field');
    
    // Add truck_id column if it doesn't exist
    if (!in_array('truck_id', $column_names)) {
        $wpdb->query("ALTER TABLE {$table_name} ADD COLUMN truck_id mediumint(9) DEFAULT NULL AFTER status");
    }
    
    // Add house_size column if it doesn't exist
    if (!in_array('house_size', $column_names)) {
        $wpdb->query("ALTER TABLE {$table_name} ADD COLUMN house_size varchar(100) DEFAULT '' NOT NULL AFTER suburb");
    }
    
    // Add number_of_rooms column if it doesn't exist
    if (!in_array('number_of_rooms', $column_names)) {
        $wpdb->query("ALTER TABLE {$table_name} ADD COLUMN number_of_rooms varchar(50) DEFAULT '' NOT NULL AFTER house_size");
    }
    
    // Add stairs column if it doesn't exist
    if (!in_array('stairs', $column_names)) {
        $wpdb->query("ALTER TABLE {$table_name} ADD COLUMN stairs varchar(50) DEFAULT '' NOT NULL AFTER number_of_rooms");
    }
}

/**
 * Migrate database to version 1.6.0
 * Adds delivery_from_address and delivery_to_address columns
 */
function hs_crm_migrate_to_1_6_0() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'hs_enquiries';
    
    // Check if columns exist before adding them
    $columns = $wpdb->get_results("SHOW COLUMNS FROM {$table_name}");
    $column_names = array_column($columns, 'Field');
    
    // Add delivery_from_address column if it doesn't exist
    if (!in_array('delivery_from_address', $column_names)) {
        $wpdb->query("ALTER TABLE {$table_name} ADD COLUMN delivery_from_address text DEFAULT '' NOT NULL AFTER address");
    }
    
    // Add delivery_to_address column if it doesn't exist
    if (!in_array('delivery_to_address', $column_names)) {
        $wpdb->query("ALTER TABLE {$table_name} ADD COLUMN delivery_to_address text DEFAULT '' NOT NULL AFTER delivery_from_address");
    }
}

/**
 * Migrate database to version 1.10.0
 * Adds number_of_bedrooms column (was missing from 1.5.0 migration)
 */
function hs_crm_migrate_to_1_10_0() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'hs_enquiries';
    
    // Check if columns exist before adding them
    $columns = $wpdb->get_results("SHOW COLUMNS FROM {$table_name}");
    $column_names = array_column($columns, 'Field');
    
    // Add number_of_bedrooms column if it doesn't exist
    if (!in_array('number_of_bedrooms', $column_names)) {
        $wpdb->query("ALTER TABLE {$table_name} ADD COLUMN number_of_bedrooms varchar(50) DEFAULT '' NOT NULL AFTER house_size");
    }
    
    // Add total_rooms column if it doesn't exist (also missing from original schema)
    if (!in_array('total_rooms', $column_names)) {
        $wpdb->query("ALTER TABLE {$table_name} ADD COLUMN total_rooms varchar(50) DEFAULT '' NOT NULL AFTER number_of_rooms");
    }
    
    // Add stairs_from column if it doesn't exist
    if (!in_array('stairs_from', $column_names)) {
        $wpdb->query("ALTER TABLE {$table_name} ADD COLUMN stairs_from varchar(50) DEFAULT '' NOT NULL AFTER stairs");
    }
    
    // Add stairs_to column if it doesn't exist
    if (!in_array('stairs_to', $column_names)) {
        $wpdb->query("ALTER TABLE {$table_name} ADD COLUMN stairs_to varchar(50) DEFAULT '' NOT NULL AFTER stairs_from");
    }
    
    // Add property_notes column if it doesn't exist
    if (!in_array('property_notes', $column_names)) {
        $wpdb->query("ALTER TABLE {$table_name} ADD COLUMN property_notes text DEFAULT '' NOT NULL AFTER total_rooms");
    }
    
    // Add pickup_address column if it doesn't exist
    if (!in_array('pickup_address', $column_names)) {
        $wpdb->query("ALTER TABLE {$table_name} ADD COLUMN pickup_address text DEFAULT '' NOT NULL AFTER address");
    }
    
    // Add dropoff_address column if it doesn't exist
    if (!in_array('dropoff_address', $column_names)) {
        $wpdb->query("ALTER TABLE {$table_name} ADD COLUMN dropoff_address text DEFAULT '' NOT NULL AFTER pickup_address");
    }
    
    // Add from_suburb column if it doesn't exist
    if (!in_array('from_suburb', $column_names)) {
        $wpdb->query("ALTER TABLE {$table_name} ADD COLUMN from_suburb varchar(255) DEFAULT '' NOT NULL AFTER delivery_from_address");
    }
    
    // Add to_suburb column if it doesn't exist
    if (!in_array('to_suburb', $column_names)) {
        $wpdb->query("ALTER TABLE {$table_name} ADD COLUMN to_suburb varchar(255) DEFAULT '' NOT NULL AFTER delivery_to_address");
    }
}

/**
 * Migrate database to version 2.3.0
 * Adds columns for pickup/delivery form enhancements
 */
function hs_crm_migrate_to_2_3_0() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'hs_enquiries';
    
    // Check if columns exist before adding them
    $columns = $wpdb->get_results("SHOW COLUMNS FROM {$table_name}");
    $column_names = array_column($columns, 'Field');
    
    // Add items_being_collected column if it doesn't exist
    if (!in_array('items_being_collected', $column_names)) {
        $wpdb->query("ALTER TABLE {$table_name} ADD COLUMN items_being_collected text DEFAULT '' NOT NULL AFTER property_notes");
    }
    
    // Add furniture_moved_question column if it doesn't exist
    if (!in_array('furniture_moved_question', $column_names)) {
        $wpdb->query("ALTER TABLE {$table_name} ADD COLUMN furniture_moved_question varchar(50) DEFAULT '' NOT NULL AFTER items_being_collected");
    }
    
    // Add special_instructions column if it doesn't exist
    if (!in_array('special_instructions', $column_names)) {
        $wpdb->query("ALTER TABLE {$table_name} ADD COLUMN special_instructions text DEFAULT '' NOT NULL AFTER furniture_moved_question");
    }
}

/**
 * Migrate database to version 2.5.0
 * Adds source_form_name column to track which Gravity Form created the enquiry
 */
function hs_crm_migrate_to_2_5_0() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'hs_enquiries';
    
    // Check if columns exist before adding them
    // Table name is safe: uses $wpdb->prefix (sanitized by WordPress) + hardcoded string
    // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
    $columns = $wpdb->get_results("SHOW COLUMNS FROM {$table_name}");
    $column_names = array_column($columns, 'Field');
    
    // Add source_form_name column if it doesn't exist
    if (!in_array('source_form_name', $column_names)) {
        // Table name is safe: uses $wpdb->prefix (sanitized by WordPress) + hardcoded string
        // Column definition uses only hardcoded SQL (no user input)
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $wpdb->query("ALTER TABLE {$table_name} ADD COLUMN source_form_name varchar(255) DEFAULT '' NOT NULL AFTER contact_source");
    }
}

/**
 * Migrate database to version 2.6.0
 * Adds columns for enhanced enquiry modal fields
 */
function hs_crm_migrate_to_2_6_0() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'hs_enquiries';
    
    // Check if columns exist before adding them
    // Table name is safe: uses $wpdb->prefix (sanitized by WordPress) + hardcoded string
    // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
    $columns = $wpdb->get_results("SHOW COLUMNS FROM {$table_name}");
    $column_names = array_column($columns, 'Field');
    
    // Add move_type column if it doesn't exist
    if (!in_array('move_type', $column_names)) {
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $wpdb->query("ALTER TABLE {$table_name} ADD COLUMN move_type varchar(100) DEFAULT '' NOT NULL AFTER job_type");
    }
    
    // Add outdoor_plants column if it doesn't exist
    if (!in_array('outdoor_plants', $column_names)) {
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $wpdb->query("ALTER TABLE {$table_name} ADD COLUMN outdoor_plants varchar(50) DEFAULT '' NOT NULL AFTER property_notes");
    }
    
    // Add oversize_items column if it doesn't exist
    if (!in_array('oversize_items', $column_names)) {
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $wpdb->query("ALTER TABLE {$table_name} ADD COLUMN oversize_items varchar(50) DEFAULT '' NOT NULL AFTER outdoor_plants");
    }
    
    // Add driveway_concerns column if it doesn't exist
    if (!in_array('driveway_concerns', $column_names)) {
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $wpdb->query("ALTER TABLE {$table_name} ADD COLUMN driveway_concerns varchar(50) DEFAULT '' NOT NULL AFTER oversize_items");
    }
    
    // Add assembly_help column if it doesn't exist
    if (!in_array('assembly_help', $column_names)) {
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $wpdb->query("ALTER TABLE {$table_name} ADD COLUMN assembly_help varchar(50) DEFAULT '' NOT NULL AFTER special_instructions");
    }
    
    // Add alternate_date column if it doesn't exist
    if (!in_array('alternate_date', $column_names)) {
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $wpdb->query("ALTER TABLE {$table_name} ADD COLUMN alternate_date date DEFAULT NULL AFTER move_time");
    }
}

/**
 * Migrate database to version 2.6.1
 * Adds gravity_forms_entry_id and gravity_forms_form_id columns to track source
 */
function hs_crm_migrate_to_2_6_1() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'hs_enquiries';
    
    // Check if columns exist before adding them
    // Table name is safe: uses $wpdb->prefix (sanitized by WordPress) + hardcoded string
    // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
    $columns = $wpdb->get_results("SHOW COLUMNS FROM {$table_name}");
    $column_names = array_column($columns, 'Field');
    
    // Add gravity_forms_entry_id column if it doesn't exist
    if (!in_array('gravity_forms_entry_id', $column_names)) {
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $wpdb->query("ALTER TABLE {$table_name} ADD COLUMN gravity_forms_entry_id int(11) DEFAULT NULL AFTER source_form_name");
    }
    
    // Add gravity_forms_form_id column if it doesn't exist
    if (!in_array('gravity_forms_form_id', $column_names)) {
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $wpdb->query("ALTER TABLE {$table_name} ADD COLUMN gravity_forms_form_id int(11) DEFAULT NULL AFTER gravity_forms_entry_id");
    }
}

/**
 * Enqueue styles and scripts
 */
function hs_crm_enqueue_assets() {
    // Ensure dashicons are loaded in admin (should be by default, but explicitly load just in case)
    if (is_admin()) {
        wp_enqueue_style('dashicons');
    }
    
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
        'thankYouUrl' => home_url('/thank-you/'),
        'defaultBookingDuration' => floatval(get_option('hs_crm_default_booking_duration', HS_CRM_DEFAULT_BOOKING_DURATION))
    ));
}
add_action('wp_enqueue_scripts', 'hs_crm_enqueue_assets');
add_action('admin_enqueue_scripts', 'hs_crm_enqueue_assets');

/**
 * Normalize dropdown values to match exact option values in HTML select elements
 * This fixes the issue where Gravity Forms imports values with different casing or variations
 */
function hs_crm_normalize_dropdown_values($data) {
    // Define the exact dropdown values that match the HTML select options
    // These must match EXACTLY what's in includes/class-hs-crm-admin.php dropdown options
    
    // Yes/No dropdowns - normalize variations to "Yes" or "No"
    $yes_no_fields = array('stairs', 'stairs_from', 'stairs_to', 'furniture_moved_question', 
                           'assembly_help', 'outdoor_plants', 'oversize_items', 'driveway_concerns');
    
    foreach ($yes_no_fields as $field) {
        if (!empty($data[$field])) {
            $value = strtolower(trim($data[$field]));
            
            // Normalize Yes variations
            if (in_array($value, array('yes', 'y', 'true', '1', 'yeah', 'yep'))) {
                $data[$field] = 'Yes';
            }
            // Normalize No variations
            elseif (in_array($value, array('no', 'n', 'false', '0', 'nope', 'nah'))) {
                $data[$field] = 'No';
            }
        }
    }
    
    // Move type dropdown - normalize to "Residential" or "Office"
    if (!empty($data['move_type'])) {
        $value = strtolower(trim($data['move_type']));
        
        if (in_array($value, array('residential', 'home', 'house', 'residential move'))) {
            $data['move_type'] = 'Residential';
        } elseif (in_array($value, array('office', 'commercial', 'business', 'office move'))) {
            $data['move_type'] = 'Office';
        }
    }
    
    // House size dropdown - normalize to exact option values
    // Options: "1 Room Worth of Items Only", "1 BR House - Big Items Only", etc.
    if (!empty($data['house_size'])) {
        $value = strtolower(trim($data['house_size']));
        
        // Create a mapping of common variations to exact values
        $size_mapping = array(
            '1 room' => '1 Room Worth of Items Only',
            '1 room worth' => '1 Room Worth of Items Only',
            'one room' => '1 Room Worth of Items Only',
            '1 br house - big items only' => '1 BR House - Big Items Only',
            '1 bedroom - big items only' => '1 BR House - Big Items Only',
            '1 bedroom big items' => '1 BR House - Big Items Only',
            '1 br big items' => '1 BR House - Big Items Only',
            '1 br house - big items and boxes' => '1 BR House - Big Items and Boxes',
            '1 bedroom - big items and boxes' => '1 BR House - Big Items and Boxes',
            '1 bedroom big items and boxes' => '1 BR House - Big Items and Boxes',
            '1 br big items and boxes' => '1 BR House - Big Items and Boxes',
            '2 br house - big items only' => '2 BR House - Big Items Only',
            '2 bedroom - big items only' => '2 BR House - Big Items Only',
            '2 bedroom big items' => '2 BR House - Big Items Only',
            '2 br big items' => '2 BR House - Big Items Only',
            '2 br house - big items and boxes' => '2 BR House - Big Items and Boxes',
            '2 bedroom - big items and boxes' => '2 BR House - Big Items and Boxes',
            '2 bedroom big items and boxes' => '2 BR House - Big Items and Boxes',
            '2 br big items and boxes' => '2 BR House - Big Items and Boxes',
            '3 br house - big items only' => '3 BR House - Big Items Only',
            '3 bedroom - big items only' => '3 BR House - Big Items Only',
            '3 bedroom big items' => '3 BR House - Big Items Only',
            '3 br big items' => '3 BR House - Big Items Only',
            '3 br house - big items and boxes' => '3 BR House - Big Items and Boxes',
            '3 bedroom - big items and boxes' => '3 BR House - Big Items and Boxes',
            '3 bedroom big items and boxes' => '3 BR House - Big Items and Boxes',
            '3 br big items and boxes' => '3 BR House - Big Items and Boxes',
            '4 br houses or above' => '4 BR Houses or above',
            '4 bedroom or above' => '4 BR Houses or above',
            '4+ bedrooms' => '4 BR Houses or above',
            '4 or more bedrooms' => '4 BR Houses or above'
        );
        
        // Check for exact match first (case-insensitive)
        $matched = false;
        foreach ($size_mapping as $pattern => $exact_value) {
            if ($value === $pattern) {
                $data['house_size'] = $exact_value;
                $matched = true;
                break;
            }
        }
        
        // If no exact match, try word-boundary matching for flexibility
        // This avoids false positives like "1 bedroom apartment" matching "1 bedroom"
        if (!$matched) {
            foreach ($size_mapping as $pattern => $exact_value) {
                $pattern_regex = '/\b' . preg_quote($pattern, '/') . '\b/i';
                if (preg_match($pattern_regex, $value)) {
                    $data['house_size'] = $exact_value;
                    break;
                }
            }
        }
    }
    
    return $data;
}

/**
 * Gravity Forms Integration
 * Hook into Gravity Forms submission to create enquiries
 */
function hs_crm_gravity_forms_integration($entry, $form) {
    // Verify that required classes are available
    if (!class_exists('HS_CRM_Database')) {
        return;
    }
    
    // Check if this form should be excluded (has no-crm-integration CSS class)
    if (isset($form['cssClass']) && strpos($form['cssClass'], 'no-crm-integration') !== false) {
        return; // Skip integration for this form
    }
    
    // Check if this form should be integrated (you can customize this based on form ID or form title)
    // For example, only integrate forms with specific IDs or containing "moving" or "enquiry" in the title
    $form_title = strtolower($form['title']);
    $integrate_keywords = array('moving', 'enquiry', 'pickup', 'delivery', 'furniture', 'quote');
    
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
        'name' => array('name'), // Single name field to be split
        'email' => array('email', 'e-mail', 'email address'),
        'phone' => array('phone', 'telephone', 'mobile', 'phone number'),
        'address' => array('address', 'street address', 'location'),
        'suburb' => array('suburb', 'city', 'town'),
        'move_date' => array('move date', 'moving date', 'preferred date', 'date', 'delivery date'),
        'move_time' => array('move time', 'moving time', 'preferred time', 'preferred delivery time', 'time'),
        'alternate_date' => array('alternate date', 'alternate delivery date', 'alternative date'),
        'stairs' => array('stairs', 'stairs involved', 'are stairs involved', 'are there stairs'),
        'stairs_from' => array('stairs from', 'stairs involved? (from)', 'stairs (from)', 'stairs at pickup', 'stairs involved from', 'stairs involved? (pickup)'),
        'stairs_to' => array('stairs to', 'stairs involved? (to)', 'stairs (to)', 'stairs at delivery', 'stairs involved to', 'stairs involved? (delivery)'),
        'items_being_collected' => array('items being delivered', 'items being collected', 'what items', 'items to collect', 'what item(s) are being collected', 'what item(s)'),
        'furniture_moved_question' => array('existing furniture moved', 'furniture moved', 'do you need any existing furniture moved', 'furniture', 'need any existing furniture'),
        'special_instructions' => array('special instructions', 'additional instructions', 'instructions', 'special requests', 'any special instructions'),
        'move_type' => array('move type', 'type of move', 'what\'s the type of your move', 'type of your move'),
        'house_size' => array('house size', 'size of move', 'what\'s the size of your move', 'move size', 'size of your move'),
        'property_notes' => array('property notes', 'additional info', 'additional information', 'notes'),
        'outdoor_plants' => array('outdoor plants', 'any outdoor plants', 'plants', 'outdoor'),
        'oversize_items' => array('oversize items', 'any oversize items', 'piano', 'spa', 'large items', 'oversize'),
        'driveway_concerns' => array('driveway concerns', 'driveway', 'anything that could be a concern with the driveway', 'concern with the driveway'),
        'assembly_help' => array('assembly help', 'help assembling', 'do you need help assembling', 'assembly', 'assembling', 'help assembling the item')
    );
    
    $data = array(
        'contact_source' => 'form',
        'source_form_name' => $form['title'],
        'gravity_forms_entry_id' => intval($entry['id']),
        'gravity_forms_form_id' => intval($form['id'])
    );
    
    // Determine job type based on form CSS class (highest priority), then form title
    // Check CSS class first - allows users to explicitly set the form type
    $form_css_class = isset($form['cssClass']) ? strtolower($form['cssClass']) : '';
    
    if (strpos($form_css_class, 'moving-house') !== false || strpos($form_css_class, 'house-move') !== false) {
        $data['job_type'] = 'Moving House';
    } elseif (strpos($form_css_class, 'pickup-delivery') !== false || strpos($form_css_class, 'delivery') !== false) {
        $data['job_type'] = 'Pickup/Delivery';
    } elseif (preg_match('/\b(moving(\s+house)?|move(\s+house)?)\b/i', $form_title)) {
        // Fallback to form title - Check for moving keywords FIRST (higher priority) since "delivery" often appears in moving forms
        // Use word boundaries to avoid false positives (e.g., "remove" shouldn't match "move")
        $data['job_type'] = 'Moving House';
    } elseif (preg_match('/\b(pickup|pick\s+up|delivery)\b/i', $form_title)) {
        $data['job_type'] = 'Pickup/Delivery';
    } else {
        // Default - try to determine from fields later
        $data['job_type'] = '';
    }
    
    // Extract data from Gravity Forms entry
    foreach ($form['fields'] as $field) {
        $field_label = strtolower(trim($field->label));
        $field_value = '';
        
        // Get field value from entry
        if (isset($entry[$field->id])) {
            $field_value = $entry[$field->id];
        }
        
        // Skip empty values, but NOT for name and address fields
        // Name and address fields are compound fields that store data in subfields,
        // so we need to check the subfields even if the combined value appears empty
        if (empty($field_value) && $field->type !== 'name' && $field->type !== 'address') {
            continue;
        }
        
        // Handle special field types FIRST (name, address, email, phone)
        // This ensures we catch fields regardless of their label
        if ($field->type === 'name') {
            // Gravity Forms name field uses subfield keys: field_id.3 = First Name, field_id.6 = Last Name
            $first_name_key = $field->id . '.3';
            $last_name_key = $field->id . '.6';
            
            if (isset($entry[$first_name_key]) && !empty($entry[$first_name_key])) {
                $data['first_name'] = sanitize_text_field($entry[$first_name_key]);
            }
            if (isset($entry[$last_name_key]) && !empty($entry[$last_name_key])) {
                $data['last_name'] = sanitize_text_field($entry[$last_name_key]);
            }
            continue; // Move to next field
        }
        
        if ($field->type === 'address') {
            // Gravity Forms address field uses subfield keys:
            // field_id.1 = Street Address, field_id.2 = Address Line 2, field_id.3 = City/Suburb
            // field_id.4 = State/Province, field_id.5 = ZIP/Postal Code, field_id.6 = Country
            
            $street_key = $field->id . '.1';
            $street2_key = $field->id . '.2';
            $city_key = $field->id . '.3';
            $state_key = $field->id . '.4';
            $zip_key = $field->id . '.5';
            
            // Combine address parts
            $address_parts = array();
            if (isset($entry[$street_key]) && !empty($entry[$street_key])) {
                $address_parts[] = $entry[$street_key];
            }
            if (isset($entry[$street2_key]) && !empty($entry[$street2_key])) {
                $address_parts[] = $entry[$street2_key];
            }
            if (isset($entry[$city_key]) && !empty($entry[$city_key])) {
                $address_parts[] = $entry[$city_key];
            }
            if (isset($entry[$state_key]) && !empty($entry[$state_key])) {
                $address_parts[] = $entry[$state_key];
            }
            if (isset($entry[$zip_key]) && !empty($entry[$zip_key])) {
                $address_parts[] = $entry[$zip_key];
            }
            
            $combined_address = '';
            if (!empty($address_parts)) {
                $combined_address = sanitize_textarea_field(implode(', ', $address_parts));
            }
            
            // Determine which address field to populate based on label
            if (stripos($field_label, 'from') !== false || stripos($field_label, 'pickup') !== false) {
                $data['delivery_from_address'] = $combined_address;
                // Extract suburb/city for from location
                if (isset($entry[$city_key]) && !empty($entry[$city_key])) {
                    $data['from_suburb'] = sanitize_text_field($entry[$city_key]);
                }
                // Use first address as the main address field if not set
                if (empty($data['address'])) {
                    $data['address'] = $combined_address;
                    // Also set suburb if not set
                    if (empty($data['suburb']) && isset($entry[$city_key]) && !empty($entry[$city_key])) {
                        $data['suburb'] = sanitize_text_field($entry[$city_key]);
                    }
                }
            } elseif (stripos($field_label, 'to') !== false || stripos($field_label, 'dropoff') !== false || stripos($field_label, 'delivery') !== false) {
                $data['delivery_to_address'] = $combined_address;
                // Extract suburb/city for to location
                if (isset($entry[$city_key]) && !empty($entry[$city_key])) {
                    $data['to_suburb'] = sanitize_text_field($entry[$city_key]);
                }
                // Use as main address if no from address exists
                if (empty($data['address'])) {
                    $data['address'] = $combined_address;
                    // Also set suburb if not set
                    if (empty($data['suburb']) && isset($entry[$city_key]) && !empty($entry[$city_key])) {
                        $data['suburb'] = sanitize_text_field($entry[$city_key]);
                    }
                }
            } else {
                // Generic address field - use as main address
                $data['address'] = $combined_address;
                // Extract suburb/city if available
                if (isset($entry[$city_key]) && !empty($entry[$city_key])) {
                    $data['suburb'] = sanitize_text_field($entry[$city_key]);
                }
            }
            continue; // Move to next field
        }
        
        if ($field->type === 'email') {
            $data['email'] = sanitize_email($field_value);
            continue; // Move to next field
        }
        
        if ($field->type === 'phone') {
            $data['phone'] = sanitize_text_field($field_value);
            continue; // Move to next field
        }
        
        // For other field types, match by label
        // Note: field_label is already lowercased at line 651, so exact match using === is effectively case-insensitive
        // Use exact matching first, then partial matching for better accuracy
        $matched = false;
        
        foreach ($field_mapping as $crm_field => $possible_labels) {
            if ($matched) break;
            
            foreach ($possible_labels as $label) {
                // Check for exact match first
                if ($field_label === $label) {
                    if ($field->type === 'date') {
                        $data[$crm_field] = sanitize_text_field($field_value);
                    } elseif ($field->type === 'time') {
                        $data[$crm_field] = sanitize_text_field($field_value);
                    } else {
                        // Standard text field - sanitize based on field type
                        if ($crm_field === 'email') {
                            $data[$crm_field] = sanitize_email($field_value);
                        } elseif ($crm_field === 'address') {
                            $data[$crm_field] = sanitize_textarea_field($field_value);
                        } else {
                            // All other fields including 'name' which will be split later
                            $data[$crm_field] = sanitize_text_field($field_value);
                        }
                    }
                    $matched = true;
                    break;
                }
            }
        }
        
        // If no exact match, try partial matching
        if (!$matched) {
            foreach ($field_mapping as $crm_field => $possible_labels) {
                if ($matched) break;
                
                foreach ($possible_labels as $label) {
                    if (strpos($field_label, $label) !== false) {
                        if ($field->type === 'date') {
                            $data[$crm_field] = sanitize_text_field($field_value);
                        } elseif ($field->type === 'time') {
                            $data[$crm_field] = sanitize_text_field($field_value);
                        } else {
                            // Standard text field - sanitize based on field type
                            if ($crm_field === 'email') {
                                $data[$crm_field] = sanitize_email($field_value);
                            } elseif ($crm_field === 'address') {
                                $data[$crm_field] = sanitize_textarea_field($field_value);
                            } else {
                                // All other fields including 'name' which will be split later
                                $data[$crm_field] = sanitize_text_field($field_value);
                            }
                        }
                        $matched = true;
                        break;
                    }
                }
            }
        }
        
        // Add debug logging for unmatched select/radio fields to help troubleshoot import issues
        // This helps identify when dropdown field labels don't match the expected mappings
        if (!$matched && ($field->type === 'select' || $field->type === 'radio')) {
            // Log unmatched dropdown/select fields to WordPress error log for debugging
            // Sanitize all values to prevent log injection attacks
            error_log(sprintf(
                'Marcus Furniture CRM: Unmatched dropdown field in Gravity Forms import - Label: "%s", Type: %s, Value: "%s", Form: "%s" (ID: %d)',
                esc_html($field->label),
                esc_html($field->type),
                esc_html($field_value),
                esc_html($form['title']),
                intval($form['id'])
            ));
        }
    }
    
    // Normalize dropdown values to match the exact option values in HTML select elements
    $data = hs_crm_normalize_dropdown_values($data);
    
    // Handle single 'name' field - split into first_name and last_name if needed
    // Only process if we have a name field AND both first_name and last_name are missing
    // Note: This assumes Western naming conventions (first name, then last name separated by space)
    // For international names with different formats, users should use separate first/last name fields
    if (!empty($data['name']) && empty($data['first_name']) && empty($data['last_name'])) {
        $name_parts = explode(' ', trim($data['name']), 2);
        if (isset($name_parts[0])) {
            $data['first_name'] = sanitize_text_field($name_parts[0]);
        }
        if (isset($name_parts[1])) {
            $data['last_name'] = sanitize_text_field($name_parts[1]);
        } else {
            // If only one word provided, use it for both first and last name
            // This ensures both required fields are populated and the contact can be created
            $data['last_name'] = sanitize_text_field($name_parts[0]);
        }
        // Remove the temporary 'name' field
        unset($data['name']);
    }
    
    // Convert move_time to 24-hour format if it's in 12-hour format (e.g., "9:00am" -> "09:00:00")
    // This is needed for SELECT fields that store time values in 12-hour format
    // which need to be converted for the MySQL TIME column
    if (!empty($data['move_time'])) {
        $time_value = trim($data['move_time']);
        
        // Check if the time contains 'am' or 'pm' (case-insensitive)
        // Handles both "9:00am" and "9am" formats
        if (preg_match('/^(\d{1,2})(?::(\d{2}))?\s*(am|pm)$/i', $time_value, $matches)) {
            $hour = intval($matches[1]);
            $minute = isset($matches[2]) ? intval($matches[2]) : 0;
            $meridiem = strtolower($matches[3]);
            
            // Convert to 24-hour format
            if ($meridiem === 'pm' && $hour !== 12) {
                $hour += 12;
            } elseif ($meridiem === 'am' && $hour === 12) {
                $hour = 0;
            }
            
            // Format as HH:MM:SS for MySQL TIME column
            $data['move_time'] = sprintf('%02d:%02d:00', $hour, $minute);
        }
        // If it's already in 24-hour format (HH:MM or HH:MM:SS), leave it as-is
        // The database will handle it correctly
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
            // Auto-archive if the delivery/move date is in the past
            // This ensures Gravity Forms imports with past dates don't appear in active leads
            HS_CRM_Database::auto_archive_if_past_date($enquiry_id, isset($data['move_date']) ? $data['move_date'] : '');
            
            // Add form source note
            HS_CRM_Database::add_note($enquiry_id, 'Enquiry created from Gravity Forms: ' . $form['title'] . ' (Form ID: ' . $form['id'] . ')');
            
            // Add special instructions as a note if provided
            if (!empty($data['special_instructions'])) {
                HS_CRM_Database::add_note($enquiry_id, 'Special Instructions: ' . $data['special_instructions']);
            }
            
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
