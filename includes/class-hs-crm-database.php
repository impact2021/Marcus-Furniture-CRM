<?php
/**
 * Database operations class
 */

if (!defined('ABSPATH')) {
    exit;
}

class HS_CRM_Database {
    
    /**
     * Create database tables on plugin activation
     */
    public static function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix . 'hs_enquiries';
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            first_name varchar(255) NOT NULL,
            last_name varchar(255) NOT NULL,
            name varchar(255) NOT NULL DEFAULT '',
            email varchar(255) NOT NULL,
            phone varchar(50) NOT NULL,
            address text NOT NULL,
            pickup_address text DEFAULT '' NOT NULL,
            dropoff_address text DEFAULT '' NOT NULL,
            delivery_from_address text DEFAULT '' NOT NULL,
            delivery_to_address text DEFAULT '' NOT NULL,
            from_suburb varchar(255) DEFAULT '' NOT NULL,
            to_suburb varchar(255) DEFAULT '' NOT NULL,
            suburb varchar(255) DEFAULT '' NOT NULL,
            house_size varchar(100) DEFAULT '' NOT NULL,
            number_of_bedrooms varchar(50) DEFAULT '' NOT NULL,
            number_of_rooms varchar(50) DEFAULT '' NOT NULL,
            total_rooms varchar(50) DEFAULT '' NOT NULL,
            property_notes text DEFAULT '' NOT NULL,
            items_being_collected text DEFAULT '' NOT NULL,
            furniture_moved_question varchar(50) DEFAULT '' NOT NULL,
            special_instructions text DEFAULT '' NOT NULL,
            stairs varchar(50) DEFAULT '' NOT NULL,
            stairs_from varchar(50) DEFAULT '' NOT NULL,
            stairs_to varchar(50) DEFAULT '' NOT NULL,
            move_date date DEFAULT NULL,
            move_time time DEFAULT NULL,
            alternate_date date DEFAULT NULL,
            booking_start_time time DEFAULT NULL,
            booking_end_time time DEFAULT NULL,
            contact_source varchar(50) DEFAULT 'form' NOT NULL,
            source_form_name varchar(255) DEFAULT '' NOT NULL,
            gravity_forms_entry_id int(11) DEFAULT NULL,
            gravity_forms_form_id int(11) DEFAULT NULL,
            job_type varchar(100) DEFAULT '' NOT NULL,
            move_type varchar(100) DEFAULT '' NOT NULL,
            outdoor_plants varchar(50) DEFAULT '' NOT NULL,
            oversize_items varchar(50) DEFAULT '' NOT NULL,
            driveway_concerns varchar(50) DEFAULT '' NOT NULL,
            assembly_help varchar(50) DEFAULT '' NOT NULL,
            status varchar(50) DEFAULT 'First Contact' NOT NULL,
            truck_id mediumint(9) DEFAULT NULL,
            email_sent tinyint(1) DEFAULT 0 NOT NULL,
            first_email_sent_at datetime DEFAULT NULL,
            admin_notes text DEFAULT '' NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            KEY status (status),
            KEY created_at (created_at),
            KEY move_date (move_date),
            KEY truck_id (truck_id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // Create notes table
        $notes_table = $wpdb->prefix . 'hs_enquiry_notes';
        $sql_notes = "CREATE TABLE IF NOT EXISTS $notes_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            enquiry_id mediumint(9) NOT NULL,
            note text NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            KEY enquiry_id (enquiry_id)
        ) $charset_collate;";
        
        dbDelta($sql_notes);
        
        // Create trucks table
        $trucks_table = $wpdb->prefix . 'hs_trucks';
        $sql_trucks = "CREATE TABLE IF NOT EXISTS $trucks_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            registration varchar(100) DEFAULT '' NOT NULL,
            capacity varchar(100) DEFAULT '' NOT NULL,
            status varchar(50) DEFAULT 'active' NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        
        dbDelta($sql_trucks);
        
        // Create truck bookings table
        $bookings_table = $wpdb->prefix . 'hs_truck_bookings';
        $sql_bookings = "CREATE TABLE IF NOT EXISTS $bookings_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            truck_id mediumint(9) NOT NULL,
            enquiry_id mediumint(9) DEFAULT NULL,
            booking_date date NOT NULL,
            start_time time DEFAULT NULL,
            end_time time DEFAULT NULL,
            notes text DEFAULT '' NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            KEY truck_id (truck_id),
            KEY enquiry_id (enquiry_id),
            KEY booking_date (booking_date)
        ) $charset_collate;";
        
        dbDelta($sql_bookings);
    }
    
    /**
     * Insert new enquiry
     */
    public static function insert_enquiry($data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'hs_enquiries';
        
        $first_name = sanitize_text_field($data['first_name']);
        $last_name = sanitize_text_field($data['last_name']);
        $full_name = trim($first_name . ' ' . $last_name);
        
        // Determine the main address field value
        // Priority: 1) from/to addresses combined, 2) provided address field, 3) empty string
        $main_address = '';
        if (isset($data['delivery_from_address']) && isset($data['delivery_to_address']) &&
            !empty($data['delivery_from_address']) && !empty($data['delivery_to_address'])) {
            // Both from/to addresses provided - combine them
            $main_address = sanitize_textarea_field($data['delivery_from_address']) . ' → ' . sanitize_textarea_field($data['delivery_to_address']);
        } elseif (isset($data['address']) && !empty($data['address'])) {
            // Generic address field provided
            $main_address = sanitize_textarea_field($data['address']);
        }
        
        $insert_data = array(
            'first_name' => $first_name,
            'last_name' => $last_name,
            'name' => $full_name,
            'email' => sanitize_email($data['email']),
            'phone' => sanitize_text_field($data['phone']),
            'address' => $main_address,
            'pickup_address' => isset($data['pickup_address']) ? sanitize_textarea_field($data['pickup_address']) : '',
            'dropoff_address' => isset($data['dropoff_address']) ? sanitize_textarea_field($data['dropoff_address']) : '',
            'delivery_from_address' => isset($data['delivery_from_address']) ? sanitize_textarea_field($data['delivery_from_address']) : '',
            'delivery_to_address' => isset($data['delivery_to_address']) ? sanitize_textarea_field($data['delivery_to_address']) : '',
            'from_suburb' => isset($data['from_suburb']) ? sanitize_text_field($data['from_suburb']) : '',
            'to_suburb' => isset($data['to_suburb']) ? sanitize_text_field($data['to_suburb']) : '',
            'suburb' => isset($data['suburb']) ? sanitize_text_field($data['suburb']) : '',
            'house_size' => isset($data['house_size']) ? sanitize_text_field($data['house_size']) : '',
            'number_of_bedrooms' => isset($data['number_of_bedrooms']) ? sanitize_text_field($data['number_of_bedrooms']) : '',
            'number_of_rooms' => isset($data['number_of_rooms']) ? sanitize_text_field($data['number_of_rooms']) : '',
            'total_rooms' => isset($data['total_rooms']) ? sanitize_text_field($data['total_rooms']) : '',
            'property_notes' => isset($data['property_notes']) ? sanitize_textarea_field($data['property_notes']) : '',
            'items_being_collected' => isset($data['items_being_collected']) ? sanitize_textarea_field($data['items_being_collected']) : '',
            'furniture_moved_question' => isset($data['furniture_moved_question']) ? sanitize_text_field($data['furniture_moved_question']) : '',
            'special_instructions' => isset($data['special_instructions']) ? sanitize_textarea_field($data['special_instructions']) : '',
            'stairs' => isset($data['stairs']) ? sanitize_text_field($data['stairs']) : '',
            'stairs_from' => isset($data['stairs_from']) ? sanitize_text_field($data['stairs_from']) : '',
            'stairs_to' => isset($data['stairs_to']) ? sanitize_text_field($data['stairs_to']) : '',
            'job_type' => isset($data['job_type']) ? sanitize_text_field($data['job_type']) : '',
            'move_type' => isset($data['move_type']) ? sanitize_text_field($data['move_type']) : '',
            'outdoor_plants' => isset($data['outdoor_plants']) ? sanitize_text_field($data['outdoor_plants']) : '',
            'oversize_items' => isset($data['oversize_items']) ? sanitize_text_field($data['oversize_items']) : '',
            'driveway_concerns' => isset($data['driveway_concerns']) ? sanitize_text_field($data['driveway_concerns']) : '',
            'assembly_help' => isset($data['assembly_help']) ? sanitize_text_field($data['assembly_help']) : '',
            'status' => 'First Contact',
            'email_sent' => 0,
            'admin_notes' => '',
            'contact_source' => isset($data['contact_source']) ? sanitize_text_field($data['contact_source']) : 'form',
            'source_form_name' => isset($data['source_form_name']) ? sanitize_text_field($data['source_form_name']) : '',
            'gravity_forms_entry_id' => isset($data['gravity_forms_entry_id']) ? intval($data['gravity_forms_entry_id']) : null,
            'gravity_forms_form_id' => isset($data['gravity_forms_form_id']) ? intval($data['gravity_forms_form_id']) : null
        );
        
        // Add move_date if provided
        if (isset($data['move_date']) && !empty($data['move_date'])) {
            $insert_data['move_date'] = sanitize_text_field($data['move_date']);
        }
        
        // Add move_time if provided
        if (isset($data['move_time']) && !empty($data['move_time'])) {
            $insert_data['move_time'] = sanitize_text_field($data['move_time']);
        }
        
        // Add alternate_date if provided
        if (isset($data['alternate_date']) && !empty($data['alternate_date'])) {
            $insert_data['alternate_date'] = sanitize_text_field($data['alternate_date']);
        }
        
        // Add booking times if provided
        if (isset($data['booking_start_time']) && !empty($data['booking_start_time'])) {
            $insert_data['booking_start_time'] = sanitize_text_field($data['booking_start_time']);
        }
        
        if (isset($data['booking_end_time']) && !empty($data['booking_end_time'])) {
            $insert_data['booking_end_time'] = sanitize_text_field($data['booking_end_time']);
        }
        
        $result = $wpdb->insert(
            $table_name,
            $insert_data
        );
        
        return $result !== false ? $wpdb->insert_id : false;
    }
    
    /**
     * Get all enquiries with optional status filter and sorting
     */
    public static function get_enquiries($status = null, $orderby = 'move_date', $order = 'ASC') {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'hs_enquiries';
        
        // Validate orderby to prevent SQL injection
        $allowed_orderby = array('move_date', 'created_at', 'status', 'name');
        if (!in_array($orderby, $allowed_orderby)) {
            $orderby = 'move_date';
        }
        
        // Validate order
        $order = strtoupper($order);
        if (!in_array($order, array('ASC', 'DESC'))) {
            $order = 'ASC';
        }
        
        // Build ORDER BY clause - move_date nulls last, then by created_at
        if ($orderby === 'move_date') {
            $order_clause = "ORDER BY CASE WHEN move_date IS NULL THEN 1 ELSE 0 END, move_date $order, created_at DESC";
        } else {
            $order_clause = "ORDER BY $orderby $order";
        }
        
        if ($status === 'active') {
            // Auto-archive enquiries with past move dates in Active tab
            // Use transient to throttle this operation to once per hour to avoid performance impact
            $last_auto_archive = get_transient('hs_crm_last_auto_archive');
            
            if ($last_auto_archive === false) {
                // Get current date in local timezone
                $current_date = current_time('Y-m-d');
                
                // Archive enquiries with move_date in the past (excluding today)
                $wpdb->query($wpdb->prepare(
                    "UPDATE $table_name 
                     SET status = 'Archived' 
                     WHERE status IN ('First Contact', 'Quote Sent', 'Booking Confirmed', 'Deposit Paid') 
                     AND move_date IS NOT NULL 
                     AND move_date < %s",
                    $current_date
                ));
                
                // Set transient to prevent running again for 1 hour (3600 seconds)
                set_transient('hs_crm_last_auto_archive', time(), 3600);
            }
            
            // Active leads: First Contact, Quote Sent, Booking Confirmed, Deposit Paid
            // Only show those with future move dates or no move date
            $sql = "SELECT * FROM $table_name WHERE status IN ('First Contact', 'Quote Sent', 'Booking Confirmed', 'Deposit Paid') $order_clause";
        } elseif ($status === 'Archived') {
            // Show both "Archived" and "Dead" (for backward compatibility)
            $sql = "SELECT * FROM $table_name WHERE status IN ('Archived', 'Dead') $order_clause";
        } elseif ($status && $status !== 'all') {
            $sql = $wpdb->prepare(
                "SELECT * FROM $table_name WHERE status = %s $order_clause",
                $status
            );
        } else {
            $sql = "SELECT * FROM $table_name $order_clause";
        }
        
        return $wpdb->get_results($sql);
    }
    
    /**
     * Get single enquiry by ID
     */
    public static function get_enquiry($id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'hs_enquiries';
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $id
        ));
    }
    
    /**
     * Update enquiry status
     */
    public static function update_status($id, $status) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'hs_enquiries';
        
        $result = $wpdb->update(
            $table_name,
            array('status' => sanitize_text_field($status)),
            array('id' => $id),
            array('%s'),
            array('%d')
        );
        
        return $result !== false;
    }
    
    /**
     * Update enquiry
     */
    public static function update_enquiry($id, $data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'hs_enquiries';
        
        $update_data = array();
        $update_format = array();
        
        // Cache for current enquiry data (to avoid multiple DB queries)
        $current_enquiry = null;
        
        if (isset($data['first_name'])) {
            $update_data['first_name'] = sanitize_text_field($data['first_name']);
            $update_format[] = '%s';
        }
        
        if (isset($data['last_name'])) {
            $update_data['last_name'] = sanitize_text_field($data['last_name']);
            $update_format[] = '%s';
        }
        
        // Update the full name field if either first_name or last_name is being updated
        if (isset($data['first_name']) || isset($data['last_name'])) {
            // If both are provided, use them directly
            if (isset($data['first_name']) && isset($data['last_name'])) {
                $first_name = sanitize_text_field($data['first_name']);
                $last_name = sanitize_text_field($data['last_name']);
            } else {
                // Get current enquiry to fetch missing name parts (fetch once and cache)
                if ($current_enquiry === null) {
                    $current_enquiry = self::get_enquiry($id);
                }
                
                if ($current_enquiry) {
                    $first_name = isset($data['first_name']) ? sanitize_text_field($data['first_name']) : $current_enquiry->first_name;
                    $last_name = isset($data['last_name']) ? sanitize_text_field($data['last_name']) : $current_enquiry->last_name;
                } else {
                    // Fallback if enquiry not found - use only the provided values
                    $first_name = isset($data['first_name']) ? sanitize_text_field($data['first_name']) : '';
                    $last_name = isset($data['last_name']) ? sanitize_text_field($data['last_name']) : '';
                }
            }
            $update_data['name'] = trim($first_name . ' ' . $last_name);
            $update_format[] = '%s';
        }
        
        if (isset($data['email'])) {
            $update_data['email'] = sanitize_email($data['email']);
            $update_format[] = '%s';
        }
        
        if (isset($data['phone'])) {
            $update_data['phone'] = sanitize_text_field($data['phone']);
            $update_format[] = '%s';
        }
        
        if (isset($data['address'])) {
            $update_data['address'] = sanitize_textarea_field($data['address']);
            $update_format[] = '%s';
        }
        
        if (isset($data['pickup_address'])) {
            $update_data['pickup_address'] = sanitize_textarea_field($data['pickup_address']);
            $update_format[] = '%s';
        }
        
        if (isset($data['dropoff_address'])) {
            $update_data['dropoff_address'] = sanitize_textarea_field($data['dropoff_address']);
            $update_format[] = '%s';
        }
        
        if (isset($data['delivery_from_address'])) {
            $update_data['delivery_from_address'] = sanitize_textarea_field($data['delivery_from_address']);
            $update_format[] = '%s';
        }
        
        if (isset($data['delivery_to_address'])) {
            $update_data['delivery_to_address'] = sanitize_textarea_field($data['delivery_to_address']);
            $update_format[] = '%s';
        }
        
        // Auto-update the legacy address field when delivery addresses change
        // This ensures data consistency with the concatenated address format used on insert
        if (isset($data['delivery_from_address']) || isset($data['delivery_to_address'])) {
            // Get current enquiry to fetch any missing address parts (reuse cached value if available)
            if ($current_enquiry === null) {
                $current_enquiry = self::get_enquiry($id);
            }
            
            if ($current_enquiry) {
                $from_address = isset($data['delivery_from_address']) 
                    ? sanitize_textarea_field($data['delivery_from_address']) 
                    : $current_enquiry->delivery_from_address;
                    
                $to_address = isset($data['delivery_to_address']) 
                    ? sanitize_textarea_field($data['delivery_to_address']) 
                    : $current_enquiry->delivery_to_address;
                
                // Update the address field with concatenated format (matching insert behavior)
                if (!empty($from_address) && !empty($to_address)) {
                    $update_data['address'] = $from_address . ' → ' . $to_address;
                } elseif (!empty($from_address)) {
                    $update_data['address'] = $from_address;
                } elseif (!empty($to_address)) {
                    $update_data['address'] = $to_address;
                } else {
                    // Both addresses are empty - clear the address field for consistency
                    $update_data['address'] = '';
                }
                
                // Only add format specifier if address wasn't already added above
                if (!isset($data['address'])) {
                    $update_format[] = '%s';
                }
            }
        }
        
        if (isset($data['suburb'])) {
            $update_data['suburb'] = sanitize_text_field($data['suburb']);
            $update_format[] = '%s';
        }
        
        if (isset($data['from_suburb'])) {
            $update_data['from_suburb'] = sanitize_text_field($data['from_suburb']);
            $update_format[] = '%s';
        }
        
        if (isset($data['to_suburb'])) {
            $update_data['to_suburb'] = sanitize_text_field($data['to_suburb']);
            $update_format[] = '%s';
        }
        
        if (isset($data['number_of_bedrooms'])) {
            $update_data['number_of_bedrooms'] = sanitize_text_field($data['number_of_bedrooms']);
            $update_format[] = '%s';
        }
        
        if (isset($data['number_of_rooms'])) {
            $update_data['number_of_rooms'] = sanitize_text_field($data['number_of_rooms']);
            $update_format[] = '%s';
        }
        
        if (isset($data['total_rooms'])) {
            $update_data['total_rooms'] = sanitize_text_field($data['total_rooms']);
            $update_format[] = '%s';
        }
        
        if (isset($data['property_notes'])) {
            $update_data['property_notes'] = sanitize_textarea_field($data['property_notes']);
            $update_format[] = '%s';
        }
        
        if (isset($data['items_being_collected'])) {
            $update_data['items_being_collected'] = sanitize_textarea_field($data['items_being_collected']);
            $update_format[] = '%s';
        }
        
        if (isset($data['furniture_moved_question'])) {
            $update_data['furniture_moved_question'] = sanitize_text_field($data['furniture_moved_question']);
            $update_format[] = '%s';
        }
        
        if (isset($data['special_instructions'])) {
            $update_data['special_instructions'] = sanitize_textarea_field($data['special_instructions']);
            $update_format[] = '%s';
        }
        
        if (isset($data['stairs'])) {
            $update_data['stairs'] = sanitize_text_field($data['stairs']);
            $update_format[] = '%s';
        }
        
        if (isset($data['stairs_from'])) {
            $update_data['stairs_from'] = sanitize_text_field($data['stairs_from']);
            $update_format[] = '%s';
        }
        
        if (isset($data['stairs_to'])) {
            $update_data['stairs_to'] = sanitize_text_field($data['stairs_to']);
            $update_format[] = '%s';
        }
        
        if (isset($data['job_type'])) {
            $update_data['job_type'] = sanitize_text_field($data['job_type']);
            $update_format[] = '%s';
        }
        
        if (isset($data['move_type'])) {
            $update_data['move_type'] = sanitize_text_field($data['move_type']);
            $update_format[] = '%s';
        }
        
        if (isset($data['house_size'])) {
            $update_data['house_size'] = sanitize_text_field($data['house_size']);
            $update_format[] = '%s';
        }
        
        if (isset($data['outdoor_plants'])) {
            $update_data['outdoor_plants'] = sanitize_text_field($data['outdoor_plants']);
            $update_format[] = '%s';
        }
        
        if (isset($data['oversize_items'])) {
            $update_data['oversize_items'] = sanitize_text_field($data['oversize_items']);
            $update_format[] = '%s';
        }
        
        if (isset($data['driveway_concerns'])) {
            $update_data['driveway_concerns'] = sanitize_text_field($data['driveway_concerns']);
            $update_format[] = '%s';
        }
        
        if (isset($data['assembly_help'])) {
            $update_data['assembly_help'] = sanitize_text_field($data['assembly_help']);
            $update_format[] = '%s';
        }
        
        if (isset($data['move_date'])) {
            $update_data['move_date'] = !empty($data['move_date']) ? sanitize_text_field($data['move_date']) : null;
            $update_format[] = '%s';
        }
        
        if (isset($data['move_time'])) {
            $update_data['move_time'] = !empty($data['move_time']) ? sanitize_text_field($data['move_time']) : null;
            $update_format[] = '%s';
        }
        
        if (isset($data['alternate_date'])) {
            $update_data['alternate_date'] = !empty($data['alternate_date']) ? sanitize_text_field($data['alternate_date']) : null;
            $update_format[] = '%s';
        }
        
        if (isset($data['booking_start_time'])) {
            $update_data['booking_start_time'] = !empty($data['booking_start_time']) ? sanitize_text_field($data['booking_start_time']) : null;
            $update_format[] = '%s';
        }
        
        if (isset($data['booking_end_time'])) {
            $update_data['booking_end_time'] = !empty($data['booking_end_time']) ? sanitize_text_field($data['booking_end_time']) : null;
            $update_format[] = '%s';
        }
        
        if (isset($data['truck_id'])) {
            $update_data['truck_id'] = ($data['truck_id'] !== null && $data['truck_id'] !== '') ? intval($data['truck_id']) : null;
            $update_format[] = '%d';
        }
        
        if (isset($data['contact_source'])) {
            $update_data['contact_source'] = sanitize_text_field($data['contact_source']);
            $update_format[] = '%s';
        }
        
        if (isset($data['status'])) {
            $update_data['status'] = sanitize_text_field($data['status']);
            $update_format[] = '%s';
        }
        
        if (empty($update_data)) {
            return false;
        }
        
        $result = $wpdb->update(
            $table_name,
            $update_data,
            array('id' => $id),
            $update_format,
            array('%d')
        );
        
        return $result !== false;
    }
    
    /**
     * Get count by status
     */
    public static function get_status_counts() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'hs_enquiries';
        
        $results = $wpdb->get_results(
            "SELECT status, COUNT(*) as count FROM $table_name GROUP BY status"
        );
        
        $counts = array(
            'all' => 0,
            'active' => 0,
            'First Contact' => 0,
            'Quote Sent' => 0,
            'Booking Confirmed' => 0,
            'Deposit Paid' => 0,
            'Completed' => 0,
            'Archived' => 0
        );
        
        foreach ($results as $row) {
            // Map old "Dead" status to "Archived" for display
            if ($row->status === 'Dead') {
                $counts['Archived'] += $row->count;
            } else {
                if (!isset($counts[$row->status])) {
                    $counts[$row->status] = 0;
                }
                $counts[$row->status] += $row->count;
            }
            $counts['all'] += $row->count;
            
            // Count active leads
            if (in_array($row->status, array('First Contact', 'Quote Sent', 'Booking Confirmed', 'Deposit Paid'))) {
                $counts['active'] += $row->count;
            }
        }
        
        return $counts;
    }
    
    /**
     * Update admin notes
     */
    public static function update_admin_notes($id, $notes) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'hs_enquiries';
        
        $result = $wpdb->update(
            $table_name,
            array('admin_notes' => sanitize_textarea_field($notes)),
            array('id' => $id),
            array('%s'),
            array('%d')
        );
        
        return $result !== false;
    }
    
    /**
     * Mark email as sent
     */
    public static function mark_email_sent($id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'hs_enquiries';
        
        // Check if this is the first email being sent
        $enquiry = self::get_enquiry($id);
        $update_data = array('email_sent' => 1);
        $update_format = array('%d');
        
        // Only set first_email_sent_at if it's null (first email)
        if ($enquiry && is_null($enquiry->first_email_sent_at)) {
            $update_data['first_email_sent_at'] = current_time('mysql');
            $update_format[] = '%s';
        }
        
        $result = $wpdb->update(
            $table_name,
            $update_data,
            array('id' => $id),
            $update_format,
            array('%d')
        );
        
        return $result !== false;
    }
    
    /**
     * Add a note to an enquiry
     */
    public static function add_note($enquiry_id, $note) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'hs_enquiry_notes';
        
        $result = $wpdb->insert(
            $table_name,
            array(
                'enquiry_id' => $enquiry_id,
                'note' => sanitize_textarea_field($note)
            ),
            array('%d', '%s')
        );
        
        return $result !== false ? $wpdb->insert_id : false;
    }
    
    /**
     * Get all notes for an enquiry
     */
    public static function get_notes($enquiry_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'hs_enquiry_notes';
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name WHERE enquiry_id = %d ORDER BY created_at ASC",
            $enquiry_id
        ));
    }
    
    /**
     * Delete a note
     */
    public static function delete_note($note_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'hs_enquiry_notes';
        
        $result = $wpdb->delete(
            $table_name,
            array('id' => $note_id),
            array('%d')
        );
        
        return $result !== false;
    }
    
    /**
     * ========================================
     * TRUCK MANAGEMENT METHODS
     * ========================================
     */
    
    /**
     * Get all trucks
     */
    public static function get_trucks($status = 'active') {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'hs_trucks';
        
        if ($status === 'all') {
            return $wpdb->get_results("SELECT * FROM $table_name ORDER BY name ASC");
        }
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name WHERE status = %s ORDER BY name ASC",
            $status
        ));
    }
    
    /**
     * Get single truck by ID
     */
    public static function get_truck($id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'hs_trucks';
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $id
        ));
    }
    
    /**
     * Insert new truck
     */
    public static function insert_truck($data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'hs_trucks';
        
        $result = $wpdb->insert(
            $table_name,
            array(
                'name' => sanitize_text_field($data['name']),
                'registration' => isset($data['registration']) ? sanitize_text_field($data['registration']) : '',
                'capacity' => isset($data['capacity']) ? sanitize_text_field($data['capacity']) : '',
                'status' => 'active'
            ),
            array('%s', '%s', '%s', '%s')
        );
        
        return $result !== false ? $wpdb->insert_id : false;
    }
    
    /**
     * Update truck
     */
    public static function update_truck($id, $data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'hs_trucks';
        
        $update_data = array();
        $update_format = array();
        
        if (isset($data['name'])) {
            $update_data['name'] = sanitize_text_field($data['name']);
            $update_format[] = '%s';
        }
        
        if (isset($data['registration'])) {
            $update_data['registration'] = sanitize_text_field($data['registration']);
            $update_format[] = '%s';
        }
        
        if (isset($data['capacity'])) {
            $update_data['capacity'] = sanitize_text_field($data['capacity']);
            $update_format[] = '%s';
        }
        
        if (isset($data['status'])) {
            $update_data['status'] = sanitize_text_field($data['status']);
            $update_format[] = '%s';
        }
        
        if (empty($update_data)) {
            return false;
        }
        
        $result = $wpdb->update(
            $table_name,
            $update_data,
            array('id' => $id),
            $update_format,
            array('%d')
        );
        
        return $result !== false;
    }
    
    /**
     * Delete truck (soft delete by setting status to inactive)
     */
    public static function delete_truck($id) {
        return self::update_truck($id, array('status' => 'inactive'));
    }
    
    /**
     * Get truck bookings for a date range
     */
    public static function get_truck_bookings($start_date = null, $end_date = null, $truck_id = null) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'hs_truck_bookings';
        
        $where = array();
        $params = array();
        
        if ($start_date && $end_date) {
            $where[] = 'booking_date BETWEEN %s AND %s';
            $params[] = $start_date;
            $params[] = $end_date;
        } elseif ($start_date) {
            $where[] = 'booking_date >= %s';
            $params[] = $start_date;
        }
        
        if ($truck_id) {
            $where[] = 'truck_id = %d';
            $params[] = $truck_id;
        }
        
        $where_clause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        $sql = "SELECT b.*, t.name as truck_name, e.first_name, e.last_name, e.address, 
                       e.delivery_from_address, e.delivery_to_address 
                FROM $table_name b
                LEFT JOIN {$wpdb->prefix}hs_trucks t ON b.truck_id = t.id
                LEFT JOIN {$wpdb->prefix}hs_enquiries e ON b.enquiry_id = e.id
                $where_clause
                ORDER BY booking_date ASC, start_time ASC";
        
        if (!empty($params)) {
            return $wpdb->get_results($wpdb->prepare($sql, $params));
        }
        
        return $wpdb->get_results($sql);
    }
    
    /**
     * Get single booking by ID
     */
    public static function get_booking($id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'hs_truck_bookings';
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $id
        ));
    }
    
    /**
     * Insert new booking
     */
    public static function insert_booking($data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'hs_truck_bookings';
        
        $insert_data = array(
            'truck_id' => intval($data['truck_id']),
            'booking_date' => sanitize_text_field($data['booking_date']),
            'notes' => isset($data['notes']) ? sanitize_textarea_field($data['notes']) : ''
        );
        
        if (isset($data['enquiry_id']) && !empty($data['enquiry_id'])) {
            $insert_data['enquiry_id'] = intval($data['enquiry_id']);
        }
        
        if (isset($data['start_time']) && !empty($data['start_time'])) {
            $insert_data['start_time'] = sanitize_text_field($data['start_time']);
        }
        
        if (isset($data['end_time']) && !empty($data['end_time'])) {
            $insert_data['end_time'] = sanitize_text_field($data['end_time']);
        }
        
        $result = $wpdb->insert($table_name, $insert_data);
        
        return $result !== false ? $wpdb->insert_id : false;
    }
    
    /**
     * Update booking
     */
    public static function update_booking($id, $data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'hs_truck_bookings';
        
        $update_data = array();
        
        if (isset($data['truck_id'])) {
            $update_data['truck_id'] = intval($data['truck_id']);
        }
        
        if (isset($data['enquiry_id'])) {
            $update_data['enquiry_id'] = !empty($data['enquiry_id']) ? intval($data['enquiry_id']) : null;
        }
        
        if (isset($data['booking_date'])) {
            $update_data['booking_date'] = sanitize_text_field($data['booking_date']);
        }
        
        if (isset($data['start_time'])) {
            $update_data['start_time'] = !empty($data['start_time']) ? sanitize_text_field($data['start_time']) : null;
        }
        
        if (isset($data['end_time'])) {
            $update_data['end_time'] = !empty($data['end_time']) ? sanitize_text_field($data['end_time']) : null;
        }
        
        if (isset($data['notes'])) {
            $update_data['notes'] = sanitize_textarea_field($data['notes']);
        }
        
        if (empty($update_data)) {
            return false;
        }
        
        $result = $wpdb->update(
            $table_name,
            $update_data,
            array('id' => $id)
        );
        
        return $result !== false;
    }
    
    /**
     * Delete booking
     */
    public static function delete_booking($id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'hs_truck_bookings';
        
        $result = $wpdb->delete(
            $table_name,
            array('id' => $id),
            array('%d')
        );
        
        return $result !== false;
    }
}
