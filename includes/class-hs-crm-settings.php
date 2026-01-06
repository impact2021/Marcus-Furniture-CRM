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
        register_setting('hs_crm_settings', 'hs_crm_delete_on_uninstall', array(
            'type' => 'boolean',
            'default' => false
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
                    <tr>
                        <th scope="row">
                            <label for="hs_crm_delete_on_uninstall">Delete All Data on Uninstall</label>
                        </th>
                        <td>
                            <label>
                                <input type="checkbox" 
                                       id="hs_crm_delete_on_uninstall" 
                                       name="hs_crm_delete_on_uninstall" 
                                       value="1" 
                                       <?php checked(get_option('hs_crm_delete_on_uninstall', false), true); ?>>
                                Remove all plugin data when the plugin is uninstalled
                            </label>
                            <p class="description">
                                <strong>Warning:</strong> If checked, all CRM data (enquiries, notes, trucks, bookings) and settings will be permanently deleted when you uninstall this plugin. This action cannot be undone. Leave unchecked to preserve your data.
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
                                    var $successDiv = $('<div>').addClass('notice notice-success');
                                    $successDiv.append($('<p>').text(response.data.message));
                                    
                                    // Display debug information if available
                                    if (response.data.debug && response.data.debug.length > 0) {
                                        var $debugContainer = $('<div>').css('margin-top', '20px');
                                        $debugContainer.append($('<h3>').text('Debug Information'));
                                        
                                        var $debugContent = $('<div>').css({
                                            'background': '#f5f5f5',
                                            'padding': '15px',
                                            'border': '1px solid #ddd',
                                            'max-height': '600px',
                                            'overflow-y': 'auto'
                                        });
                                        
                                        response.data.debug.forEach(function(entry, index) {
                                            var statusClass = entry.status === 'success' ? 'success' : 'error';
                                            var borderColor = statusClass === 'success' ? '#46b450' : '#dc3232';
                                            
                                            var $entryDiv = $('<div>').css({
                                                'margin-bottom': '20px',
                                                'padding': '10px',
                                                'background': 'white',
                                                'border-left': '4px solid ' + borderColor
                                            });
                                            
                                            $entryDiv.append(
                                                $('<h4>').css('margin-top', '0').text(
                                                    'Entry #' + (index + 1) + ' (ID: ' + entry.entry_id + ') - ' + entry.date_created
                                                )
                                            );
                                            
                                            $entryDiv.append(
                                                $('<p>').append(
                                                    $('<strong>').text('Status: '),
                                                    document.createTextNode(entry.skip_reason)
                                                )
                                            );
                                            
                                            if (entry.fields_found && entry.fields_found.length > 0) {
                                                var $fieldsP = $('<p>').append($('<strong>').text('Form Fields Found:'));
                                                var $fieldsList = $('<ul>');
                                                entry.fields_found.forEach(function(field) {
                                                    $fieldsList.append(
                                                        $('<li>').text('ID: ' + field.id + ', Label: "' + field.label + '", Type: ' + field.type)
                                                    );
                                                });
                                                $entryDiv.append($fieldsP, $fieldsList);
                                            }
                                            
                                            if (entry.name_field_debug) {
                                                var $nameDetails = $('<details>').css('margin-top', '10px');
                                                $nameDetails.append($('<summary>').css({'cursor': 'pointer', 'font-weight': 'bold'}).text('Name Field Debug'));
                                                $nameDetails.append(
                                                    $('<pre>').css({'background': '#f9f9f9', 'padding': '10px', 'overflow-x': 'auto'})
                                                        .text(JSON.stringify(entry.name_field_debug, null, 2))
                                                );
                                                $entryDiv.append($nameDetails);
                                            }
                                            
                                            if (entry.address_field_debug) {
                                                var $addrDetails = $('<details>').css('margin-top', '10px');
                                                $addrDetails.append($('<summary>').css({'cursor': 'pointer', 'font-weight': 'bold'}).text('Address Field Debug'));
                                                $addrDetails.append(
                                                    $('<pre>').css({'background': '#f9f9f9', 'padding': '10px', 'overflow-x': 'auto'})
                                                        .text(JSON.stringify(entry.address_field_debug, null, 2))
                                                );
                                                $entryDiv.append($addrDetails);
                                            }
                                            
                                            if (entry.data_extracted) {
                                                var $dataDetails = $('<details>').css('margin-top', '10px');
                                                $dataDetails.append($('<summary>').css({'cursor': 'pointer', 'font-weight': 'bold'}).text('Data Extracted'));
                                                $dataDetails.append(
                                                    $('<pre>').css({'background': '#f9f9f9', 'padding': '10px', 'overflow-x': 'auto'})
                                                        .text(JSON.stringify(entry.data_extracted, null, 2))
                                                );
                                                $entryDiv.append($dataDetails);
                                            }
                                            
                                            if (entry.missing_required && entry.missing_required.length > 0) {
                                                $entryDiv.append(
                                                    $('<p>').css('color', '#dc3232').append(
                                                        $('<strong>').text('Missing Required Fields: '),
                                                        document.createTextNode(entry.missing_required.join(', '))
                                                    )
                                                );
                                            }
                                            
                                            if (entry.all_entry_keys && entry.all_entry_keys.length > 0) {
                                                var $keysDetails = $('<details>').css('margin-top', '10px');
                                                $keysDetails.append($('<summary>').css({'cursor': 'pointer', 'font-weight': 'bold'}).text('All Entry Keys Available'));
                                                $keysDetails.append(
                                                    $('<pre>').css({'background': '#f9f9f9', 'padding': '10px', 'overflow-x': 'auto'})
                                                        .text(JSON.stringify(entry.all_entry_keys, null, 2))
                                                );
                                                $entryDiv.append($keysDetails);
                                            }
                                            
                                            $debugContent.append($entryDiv);
                                        });
                                        
                                        $debugContainer.append($debugContent);
                                        $result.empty().append($successDiv, $debugContainer);
                                    } else {
                                        $result.empty().append($successDiv);
                                    }
                                } else {
                                    var $errorDiv = $('<div>').addClass('notice notice-error');
                                    $errorDiv.append($('<p>').text(response.data.message));
                                    $result.empty().append($errorDiv);
                                }
                            },
                            error: function(xhr, status, error) {
                                var $errorDiv = $('<div>').addClass('notice notice-error');
                                var errorMsg = 'An error occurred during import. ';
                                if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                                    errorMsg += xhr.responseJSON.data.message;
                                } else {
                                    errorMsg += 'Status: ' + status + ', Error: ' + error;
                                }
                                $errorDiv.append($('<p>').text(errorMsg));
                                $result.empty().append($errorDiv);
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
            'status' => 'processing', // Will be updated: 'success', 'missing_fields', 'duplicate', or 'error'
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
            'name' => array('name'), // Single name field to be split
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
        
        // Determine job type based on form CSS class (highest priority), then form title
        // Check CSS class first - allows users to explicitly set the form type
        $form_title = isset($form['title']) ? $form['title'] : '';
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
                        'field_label' => $field_label,
                        'street_value' => isset($entry[$street_key]) ? $entry[$street_key] : 'NOT SET',
                        'street2_value' => isset($entry[$street2_key]) ? $entry[$street2_key] : 'NOT SET',
                        'city_value' => isset($entry[$city_key]) ? $entry[$city_key] : 'NOT SET',
                        'state_value' => isset($entry[$state_key]) ? $entry[$state_key] : 'NOT SET',
                        'zip_value' => isset($entry[$zip_key]) ? $entry[$zip_key] : 'NOT SET',
                        'combined_value' => $field_value
                    );
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
            // Note: field_label is already lowercased at line 573, so exact match using === is effectively case-insensitive
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
        }
        
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
            
            if ($debug_mode) {
                $entry_debug['name_split_debug'] = array(
                    'original_name' => $name_parts,
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name']
                );
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
            $entry_debug['status'] = 'missing_fields';
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
            $entry_debug['status'] = 'duplicate';
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
            $entry_debug['status'] = 'success';
            $entry_debug['skip_reason'] = 'SUCCESS - Imported as enquiry ID: ' . $enquiry_id;
            if ($debug_mode) {
                $debug_log[] = $entry_debug;
            }
            return true;
        }
        
        $entry_debug['status'] = 'error';
        $entry_debug['skip_reason'] = 'Failed to insert into database';
        if ($debug_mode) {
            $debug_log[] = $entry_debug;
        }
        return false;
    }
}
