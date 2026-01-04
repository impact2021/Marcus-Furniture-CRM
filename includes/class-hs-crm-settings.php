<?php
/**
 * Settings page class
 */

if (!defined('ABSPATH')) {
    exit;
}

class HS_CRM_Settings {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_settings_page'));
        add_action('admin_init', array($this, 'register_settings'));
    }
    
    /**
     * Add settings page to admin menu
     */
    public function add_settings_page() {
        add_submenu_page(
            'hs-crm-enquiries',
            'Settings',
            'Settings',
            'manage_options',
            'hs-crm-settings',
            array($this, 'render_settings_page')
        );
    }
    
    /**
     * Register plugin settings
     */
    public function register_settings() {
        register_setting('hs_crm_settings', 'hs_crm_google_api_key');
        register_setting('hs_crm_settings', 'hs_crm_timezone');
        register_setting('hs_crm_settings', 'hs_crm_admin_email', array(
            'sanitize_callback' => array($this, 'sanitize_admin_email')
        ));
        register_setting('hs_crm_settings', 'hs_crm_default_booking_duration', array(
            'sanitize_callback' => array($this, 'sanitize_booking_duration')
        ));
    }
    
    /**
     * Sanitize admin email setting
     * 
     * @param string $email Email address to sanitize
     * @return string Sanitized email address or empty string
     */
    public function sanitize_admin_email($email) {
        // If empty, return empty string (will use default admin email)
        if (empty($email)) {
            return '';
        }
        
        // Validate email format
        $email = sanitize_email($email);
        if (!is_email($email)) {
            add_settings_error(
                'hs_crm_admin_email',
                'invalid_email',
                'Please enter a valid email address for the admin email setting.',
                'error'
            );
            // Return the current saved value instead of the invalid one
            return get_option('hs_crm_admin_email', '');
        }
        
        return $email;
    }
    
    /**
     * Sanitize booking duration setting
     * 
     * @param string $duration Duration in hours
     * @return float Sanitized duration or default value
     */
    public function sanitize_booking_duration($duration) {
        // If empty, return default value (3 hours)
        if (empty($duration)) {
            return 3;
        }
        
        // Convert to float and validate
        $duration = floatval($duration);
        
        // Ensure it's a positive number between 0.5 and 24 hours
        if ($duration < 0.5 || $duration > 24) {
            add_settings_error(
                'hs_crm_default_booking_duration',
                'invalid_duration',
                'Default booking duration must be between 0.5 and 24 hours.',
                'error'
            );
            // Return the current saved value instead of the invalid one
            return get_option('hs_crm_default_booking_duration', 3);
        }
        
        return $duration;
    }
    
    /**
     * Render settings page
     */
    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Save settings message
        if (isset($_GET['settings-updated'])) {
            add_settings_error(
                'hs_crm_messages',
                'hs_crm_message',
                'Settings saved successfully.',
                'updated'
            );
        }
        
        settings_errors('hs_crm_messages');
        ?>
        <div class="wrap">
            <h1>Marcus Furniture CRM Settings</h1>
            
            <form method="post" action="options.php">
                <?php
                settings_fields('hs_crm_settings');
                do_settings_sections('hs_crm_settings');
                ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="hs_crm_admin_email">Admin Email Address</label>
                        </th>
                        <td>
                            <input type="email" 
                                   id="hs_crm_admin_email" 
                                   name="hs_crm_admin_email" 
                                   value="<?php echo esc_attr(get_option('hs_crm_admin_email', get_option('admin_email'))); ?>" 
                                   class="regular-text">
                            <p class="description">
                                Email address to receive form submissions. Defaults to WordPress admin email if not set.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="hs_crm_default_booking_duration">Default Booking Duration (Hours)</label>
                        </th>
                        <td>
                            <input type="number" 
                                   id="hs_crm_default_booking_duration" 
                                   name="hs_crm_default_booking_duration" 
                                   value="<?php echo esc_attr(get_option('hs_crm_default_booking_duration', 3)); ?>" 
                                   step="0.5" 
                                   min="0.5" 
                                   max="24" 
                                   class="small-text">
                            <p class="description">
                                Default duration for truck bookings in hours. When a start time is entered, the end time will automatically be set to start time + this duration. Default is 3 hours.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="hs_crm_google_api_key">Google Maps API Key</label>
                        </th>
                        <td>
                            <input type="text" 
                                   id="hs_crm_google_api_key" 
                                   name="hs_crm_google_api_key" 
                                   value="<?php echo esc_attr(get_option('hs_crm_google_api_key', '')); ?>" 
                                   class="regular-text">
                            <p class="description">
                                Enter your Google Maps API key to enable address autocomplete (restricted to New Zealand).<br>
                                Get your API key from: <a href="https://developers.google.com/maps/documentation/javascript/get-api-key" target="_blank">Google Maps Platform</a><br>
                                <strong>Required APIs:</strong> Places API, Maps JavaScript API
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="hs_crm_timezone">Timezone</label>
                        </th>
                        <td>
                            <select id="hs_crm_timezone" name="hs_crm_timezone" class="regular-text">
                                <?php
                                $saved_timezone = get_option('hs_crm_timezone', 'Pacific/Auckland');
                                $timezones = array(
                                    'Pacific/Auckland' => 'Pacific/Auckland (NZDT/NZST)',
                                    'Pacific/Chatham' => 'Pacific/Chatham',
                                    'Australia/Sydney' => 'Australia/Sydney (AEDT/AEST)',
                                    'Australia/Melbourne' => 'Australia/Melbourne (AEDT/AEST)',
                                    'Australia/Brisbane' => 'Australia/Brisbane (AEST)',
                                    'Australia/Perth' => 'Australia/Perth (AWST)',
                                    'UTC' => 'UTC',
                                );
                                foreach ($timezones as $value => $label) {
                                    printf(
                                        '<option value="%s" %s>%s</option>',
                                        esc_attr($value),
                                        selected($saved_timezone, $value, false),
                                        esc_html($label)
                                    );
                                }
                                ?>
                            </select>
                            <p class="description">
                                Select the timezone to use for displaying dates and times in the admin dashboard.<br>
                                This setting overrides the WordPress timezone setting for this plugin only.
                            </p>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button('Save Settings'); ?>
            </form>
            
            <hr>
            
            <h2>Shortcode Usage</h2>
            <p>Use the following shortcode to display the contact form on any page or post:</p>
            <code>[hs_contact_form]</code>
        </div>
        <?php
    }
}
