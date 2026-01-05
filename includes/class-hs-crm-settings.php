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
        add_action('wp_ajax_hs_crm_import_gravity_forms', array($this, 'ajax_import_gravity_forms'));
    }
    
    /**
     * Add settings page to admin menu
     */
    public function add_settings_page() {
        add_submenu_page(
            'hs-crm-enquiries',
            'Settings',
            'Settings',
            'manage_crm_settings',
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
        // If empty, return default value
        if (empty($duration)) {
            return HS_CRM_DEFAULT_BOOKING_DURATION;
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
            return get_option('hs_crm_default_booking_duration', HS_CRM_DEFAULT_BOOKING_DURATION);
        }
        
        return $duration;
    }
    
    /**
     * Render settings page
     */
    public function render_settings_page() {
        if (!current_user_can('manage_crm_settings')) {
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
                                   value="<?php echo esc_attr(get_option('hs_crm_default_booking_duration', HS_CRM_DEFAULT_BOOKING_DURATION)); ?>" 
                                   step="0.5" 
                                   min="0.5" 
                                   max="24" 
                                   class="small-text">
                            <p class="description">
                                Default duration for truck bookings in hours. When a start time is entered, the end time will automatically be set to start time + this duration. Default is <?php echo HS_CRM_DEFAULT_BOOKING_DURATION; ?> hours.
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
            
            <h2>Gravity Forms Import</h2>
            <?php if (class_exists('GFForms')) : ?>
                <p>Import historical entries from Gravity Forms into the CRM system.</p>
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="hs_crm_gf_form_id">Select Form to Import</label>
                        </th>
                        <td>
                            <select id="hs_crm_gf_form_id" class="regular-text">
                                <option value="">-- Select a Form --</option>
                                <?php
                                if (class_exists('GFAPI')) {
                                    $forms = GFAPI::get_forms();
                                    foreach ($forms as $gf_form) {
                                        printf(
                                            '<option value="%d">%s (ID: %d)</option>',
                                            esc_attr($gf_form['id']),
                                            esc_html($gf_form['title']),
                                            esc_attr($gf_form['id'])
                                        );
                                    }
                                }
                                ?>
                            </select>
                            <p class="description">
                                Select a Gravity Form to import all entries from that form into the CRM.<br>
                                Only entries that match the field mapping requirements (first name, last name, email, phone, address) will be imported.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="hs_crm_gf_limit">Number of Entries</label>
                        </th>
                        <td>
                            <input type="number" id="hs_crm_gf_limit" value="50" min="1" max="1000" class="small-text">
                            <p class="description">
                                Maximum number of recent entries to import (default: 50, max: 1000).
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="hs_crm_gf_debug">Debug Mode</label>
                        </th>
                        <td>
                            <label>
                                <input type="checkbox" id="hs_crm_gf_debug" value="1">
                                Enable detailed debugging (shows why entries were skipped)
                            </label>
                            <p class="description">
                                When enabled, you'll see detailed information about each entry including field mapping, extracted data, and reasons for skipping.
                            </p>
                        </td>
                    </tr>
                </table>
                <button type="button" id="hs-crm-import-gf-btn" class="button button-secondary">Import Entries</button>
                <div id="hs-crm-import-gf-result" style="margin-top: 15px;"></div>
                
                <script type="text/javascript">
                jQuery(document).ready(function($) {
                    $('#hs-crm-import-gf-btn').on('click', function() {
                        var formId = $('#hs_crm_gf_form_id').val();
                        var limit = $('#hs_crm_gf_limit').val();
                        var debug = $('#hs_crm_gf_debug').is(':checked');
                        var $button = $(this);
                        var $result = $('#hs-crm-import-gf-result');
                        
                        if (!formId) {
                            $result.html('<div class="notice notice-error"><p>Please select a form to import.</p></div>');
                            return;
                        }
                        
                        $button.prop('disabled', true).text('Importing...');
                        $result.html('<div class="notice notice-info"><p>Importing entries, please wait...</p></div>');
                        
                        $.ajax({
                            url: ajaxurl,
                            type: 'POST',
                            data: {
                                action: 'hs_crm_import_gravity_forms',
                                form_id: formId,
                                limit: limit,
                                debug: debug ? 'true' : 'false',
                                nonce: <?php echo wp_json_encode(wp_create_nonce('hs_crm_import_gf')); ?>
                            },
                            success: function(response) {
                                if (response.success) {
                                    var html = '<div class="notice notice-success"><p>' + response.data.message + '</p></div>';
                                    
                                    // Display debug information if available
                                    if (response.data.debug && response.data.debug.length > 0) {
                                        html += '<div style="margin-top: 20px;"><h3>Debug Information</h3>';
                                        html += '<div style="background: #f5f5f5; padding: 15px; border: 1px solid #ddd; max-height: 600px; overflow-y: auto;">';
                                        
                                        response.data.debug.forEach(function(entry, index) {
                                            var statusClass = entry.skip_reason.indexOf('SUCCESS') !== -1 ? 'success' : 'error';
                                            html += '<div style="margin-bottom: 20px; padding: 10px; background: white; border-left: 4px solid ' + (statusClass === 'success' ? '#46b450' : '#dc3232') + ';">';
                                            html += '<h4 style="margin-top: 0;">Entry #' + (index + 1) + ' (ID: ' + entry.entry_id + ') - ' + entry.date_created + '</h4>';
                                            html += '<p><strong>Status:</strong> ' + entry.skip_reason + '</p>';
                                            
                                            if (entry.fields_found && entry.fields_found.length > 0) {
                                                html += '<p><strong>Form Fields Found:</strong></p><ul>';
                                                entry.fields_found.forEach(function(field) {
                                                    html += '<li>ID: ' + field.id + ', Label: "' + field.label + '", Type: ' + field.type + '</li>';
                                                });
                                                html += '</ul>';
                                            }
                                            
                                            if (entry.name_field_debug) {
                                                html += '<details style="margin-top: 10px;"><summary style="cursor: pointer; font-weight: bold;">Name Field Debug</summary>';
                                                html += '<pre style="background: #f9f9f9; padding: 10px; overflow-x: auto;">' + JSON.stringify(entry.name_field_debug, null, 2) + '</pre>';
                                                html += '</details>';
                                            }
                                            
                                            if (entry.address_field_debug) {
                                                html += '<details style="margin-top: 10px;"><summary style="cursor: pointer; font-weight: bold;">Address Field Debug</summary>';
                                                html += '<pre style="background: #f9f9f9; padding: 10px; overflow-x: auto;">' + JSON.stringify(entry.address_field_debug, null, 2) + '</pre>';
                                                html += '</details>';
                                            }
                                            
                                            if (entry.data_extracted) {
                                                html += '<details style="margin-top: 10px;"><summary style="cursor: pointer; font-weight: bold;">Data Extracted</summary>';
                                                html += '<pre style="background: #f9f9f9; padding: 10px; overflow-x: auto;">' + JSON.stringify(entry.data_extracted, null, 2) + '</pre>';
                                                html += '</details>';
                                            }
                                            
                                            if (entry.missing_required && entry.missing_required.length > 0) {
                                                html += '<p style="color: #dc3232;"><strong>Missing Required Fields:</strong> ' + entry.missing_required.join(', ') + '</p>';
                                            }
                                            
                                            if (entry.all_entry_keys && entry.all_entry_keys.length > 0) {
                                                html += '<details style="margin-top: 10px;"><summary style="cursor: pointer; font-weight: bold;">All Entry Keys Available</summary>';
                                                html += '<pre style="background: #f9f9f9; padding: 10px; overflow-x: auto;">' + JSON.stringify(entry.all_entry_keys, null, 2) + '</pre>';
                                                html += '</details>';
                                            }
                                            
                                            html += '</div>';
                                        });
                                        
                                        html += '</div></div>';
                                    }
                                    
                                    $result.html(html);
                                } else {
                                    $result.html('<div class="notice notice-error"><p>' + response.data.message + '</p></div>');
                                }
                            },
                            error: function(xhr, status, error) {
                                var errorMsg = 'An error occurred during import. ';
                                if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                                    errorMsg += xhr.responseJSON.data.message;
                                } else {
                                    errorMsg += 'Status: ' + status + ', Error: ' + error;
                                }
                                $result.html('<div class="notice notice-error"><p>' + errorMsg + '</p></div>');
                            },
                            complete: function() {
                                $button.prop('disabled', false).text('Import Entries');
                            }
                        });
                    });
                });
                </script>
            <?php else : ?>
                <p class="description">
                    Gravity Forms plugin is not active. Install and activate Gravity Forms to import historical entries.
                </p>
            <?php endif; ?>
            
            <hr>
            
            <h2>Shortcode Usage</h2>
            <p>Use the following shortcode to display the contact form on any page or post:</p>
            <code>[hs_contact_form]</code>
        </div>
        <?php
    }
    
    /**
     * AJAX handler for importing Gravity Forms entries
     */
    public function ajax_import_gravity_forms() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'hs_crm_import_gf')) {
            wp_send_json_error(array('message' => 'Security verification failed.'));
        }
        
        // Check user permissions
        if (!current_user_can('manage_crm_settings')) {
            wp_send_json_error(array('message' => 'You do not have permission to perform this action.'));
        }
        
        // Check if Gravity Forms is active
        if (!class_exists('GFAPI')) {
            wp_send_json_error(array('message' => 'Gravity Forms is not active.'));
        }
        
        // Get form ID and limit
        $form_id = isset($_POST['form_id']) ? intval($_POST['form_id']) : 0;
        $limit = isset($_POST['limit']) ? intval($_POST['limit']) : 50;
        $debug_mode = isset($_POST['debug']) && $_POST['debug'] === 'true';
        
        if ($form_id <= 0) {
            wp_send_json_error(array('message' => 'Invalid form ID.'));
        }
        
        // Limit to max 1000 entries
        $limit = min($limit, 1000);
        
        // Get form
        $form = GFAPI::get_form($form_id);
        if (!$form) {
            wp_send_json_error(array('message' => 'Form not found.'));
        }
        
        // Get entries
        $search_criteria = array('status' => 'active');
        $sorting = array('key' => 'date_created', 'direction' => 'DESC');
        $paging = array('offset' => 0, 'page_size' => $limit);
        
        $entries = GFAPI::get_entries($form_id, $search_criteria, $sorting, $paging);
        
        if (!is_array($entries) || empty($entries)) {
            wp_send_json_error(array('message' => 'No entries found for this form.'));
        }
        
        // Import entries
        $imported_count = 0;
        $skipped_count = 0;
        $debug_log = array();
        
        foreach ($entries as $entry) {
            $result = $this->import_single_gravity_form_entry($entry, $form, $debug_mode, $debug_log);
            if ($result) {
                $imported_count++;
            } else {
                $skipped_count++;
            }
        }
        
        $message = sprintf(
            'Import complete! Imported: %d, Skipped: %d (missing required fields or duplicates)',
            $imported_count,
            $skipped_count
        );
        
        $response = array('message' => $message);
        if ($debug_mode && !empty($debug_log)) {
            $response['debug'] = $debug_log;
        }
        
        wp_send_json_success($response);
    }
    
    /**
     * Import a single Gravity Forms entry into the CRM
     * This uses the same logic as the live integration
     */
    private function import_single_gravity_form_entry($entry, $form, $debug_mode = false, &$debug_log = array()) {
        $entry_debug = array(
            'entry_id' => $entry['id'],
            'date_created' => $entry['date_created'],
            'fields_found' => array(),
            'data_extracted' => array(),
            'missing_required' => array(),
            'skip_reason' => '',
            'all_entry_keys' => $debug_mode ? array_keys($entry) : array()
        );
        
        // Map Gravity Forms fields to CRM fields
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
            
            if ($debug_mode) {
                $entry_debug['fields_found'][] = array(
                    'id' => $field->id,
                    'label' => $field->label,
                    'type' => $field->type
                );
            }
            
            // Get field value from entry
            if (isset($entry[$field->id])) {
                $field_value = $entry[$field->id];
            }
            
            // Skip empty values
            if (empty($field_value)) {
                continue;
            }
            
            // Handle special field types FIRST (name, address, email, phone)
            // This ensures we catch fields regardless of their label
            if ($field->type === 'name') {
                // Gravity Forms name field uses subfield keys: field_id.3 = First Name, field_id.6 = Last Name
                $first_name_key = $field->id . '.3';
                $last_name_key = $field->id . '.6';
                
                if ($debug_mode) {
                    $entry_debug['name_field_debug'] = array(
                        'field_id' => $field->id,
                        'first_name_key' => $first_name_key,
                        'last_name_key' => $last_name_key,
                        'first_name_value' => isset($entry[$first_name_key]) ? $entry[$first_name_key] : 'NOT SET',
                        'last_name_value' => isset($entry[$last_name_key]) ? $entry[$last_name_key] : 'NOT SET',
                        'combined_value' => $field_value
                    );
                }
                
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
                
                if ($debug_mode) {
                    $entry_debug['address_field_debug'] = array(
                        'field_id' => $field->id,
                        'street_value' => isset($entry[$street_key]) ? $entry[$street_key] : 'NOT SET',
                        'street2_value' => isset($entry[$street2_key]) ? $entry[$street2_key] : 'NOT SET',
                        'city_value' => isset($entry[$city_key]) ? $entry[$city_key] : 'NOT SET',
                        'state_value' => isset($entry[$state_key]) ? $entry[$state_key] : 'NOT SET',
                        'zip_value' => isset($entry[$zip_key]) ? $entry[$zip_key] : 'NOT SET',
                        'combined_value' => $field_value
                    );
                }
                
                // Extract suburb/city if available
                if (isset($entry[$city_key]) && !empty($entry[$city_key])) {
                    $data['suburb'] = sanitize_text_field($entry[$city_key]);
                }
                
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
                
                if (!empty($address_parts)) {
                    $data['address'] = sanitize_textarea_field(implode(', ', $address_parts));
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
            foreach ($field_mapping as $crm_field => $possible_labels) {
                foreach ($possible_labels as $label) {
                    if (strpos($field_label, $label) !== false) {
                        if ($field->type === 'date') {
                            $data[$crm_field] = sanitize_text_field($field_value);
                        } elseif ($field->type === 'time') {
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
        
        if ($debug_mode) {
            $entry_debug['data_extracted'] = $data;
        }
        
        // Validate required fields
        $required_fields = array('first_name', 'last_name', 'email', 'phone', 'address');
        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                if ($debug_mode) {
                    $entry_debug['missing_required'][] = $field;
                }
            }
        }
        
        if (!empty($entry_debug['missing_required'])) {
            $entry_debug['skip_reason'] = 'Missing required fields: ' . implode(', ', $entry_debug['missing_required']);
            if ($debug_mode) {
                $debug_log[] = $entry_debug;
            }
            return false; // Skip this entry
        }
        
        // Check if this entry already exists (by email and phone)
        global $wpdb;
        $table_name = $wpdb->prefix . 'hs_enquiries';
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $table_name WHERE email = %s AND phone = %s",
            $data['email'],
            $data['phone']
        ));
        
        if ($existing) {
            $entry_debug['skip_reason'] = 'Duplicate entry (email and phone already exist in database)';
            if ($debug_mode) {
                $debug_log[] = $entry_debug;
            }
            return false; // Skip duplicate
        }
        
        // Insert into database
        $enquiry_id = HS_CRM_Database::insert_enquiry($data);
        
        // Add a note indicating this was imported from Gravity Forms
        if ($enquiry_id) {
            $notes_table = $wpdb->prefix . 'hs_enquiry_notes';
            $wpdb->insert(
                $notes_table,
                array(
                    'enquiry_id' => $enquiry_id,
                    'note' => sprintf(
                        'Imported from Gravity Forms: %s (Entry ID: %d, Submitted: %s)',
                        esc_html($form['title']),
                        intval($entry['id']),
                        sanitize_text_field($entry['date_created'])
                    )
                ),
                array('%d', '%s')
            );
            $entry_debug['skip_reason'] = 'SUCCESS - Imported as enquiry ID: ' . $enquiry_id;
            if ($debug_mode) {
                $debug_log[] = $entry_debug;
            }
            return true;
        }
        
        $entry_debug['skip_reason'] = 'Failed to insert into database';
        if ($debug_mode) {
            $debug_log[] = $entry_debug;
        }
        return false;
    }
}
