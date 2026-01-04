<?php
/**
 * Truck Scheduler class
 */

if (!defined('ABSPATH')) {
    exit;
}

class HS_CRM_Truck_Scheduler {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_scheduler_page'));
        add_action('wp_ajax_hs_crm_get_trucks', array($this, 'ajax_get_trucks'));
        add_action('wp_ajax_hs_crm_add_truck', array($this, 'ajax_add_truck'));
        add_action('wp_ajax_hs_crm_update_truck', array($this, 'ajax_update_truck'));
        add_action('wp_ajax_hs_crm_delete_truck', array($this, 'ajax_delete_truck'));
        add_action('wp_ajax_hs_crm_get_bookings', array($this, 'ajax_get_bookings'));
        add_action('wp_ajax_hs_crm_add_booking', array($this, 'ajax_add_booking'));
        add_action('wp_ajax_hs_crm_update_booking', array($this, 'ajax_update_booking'));
        add_action('wp_ajax_hs_crm_delete_booking', array($this, 'ajax_delete_booking'));
    }
    
    /**
     * Add scheduler submenu page
     */
    public function add_scheduler_page() {
        add_submenu_page(
            'hs-crm-enquiries',
            'Truck Scheduler',
            'Truck Scheduler',
            'manage_options',
            'hs-crm-truck-scheduler',
            array($this, 'render_scheduler_page')
        );
    }
    
    /**
     * Render scheduler page
     */
    public function render_scheduler_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        $trucks = HS_CRM_Database::get_trucks('active');
        
        // Ensure trucks is always an array, even if null is returned
        if (!is_array($trucks)) {
            $trucks = array();
        }
        
        // Get current month or requested month
        $current_month = isset($_GET['month']) ? sanitize_text_field($_GET['month']) : date('Y-m');
        $month_timestamp = strtotime($current_month . '-01');
        $prev_month = date('Y-m', strtotime('-1 month', $month_timestamp));
        $next_month = date('Y-m', strtotime('+1 month', $month_timestamp));
        
        // Get first and last day of month
        $first_day = date('Y-m-01', $month_timestamp);
        $last_day = date('Y-m-t', $month_timestamp);
        
        // Get all bookings for this month
        $bookings = HS_CRM_Database::get_truck_bookings($first_day, $last_day);
        
        // Ensure bookings is always an array, even if null is returned
        if (!is_array($bookings)) {
            $bookings = array();
        }
        
        // Organize bookings by date and truck
        $bookings_by_date = array();
        foreach ($bookings as $booking) {
            if (!isset($bookings_by_date[$booking->booking_date])) {
                $bookings_by_date[$booking->booking_date] = array();
            }
            if (!isset($bookings_by_date[$booking->booking_date][$booking->truck_id])) {
                $bookings_by_date[$booking->booking_date][$booking->truck_id] = array();
            }
            $bookings_by_date[$booking->booking_date][$booking->truck_id][] = $booking;
        }
        
        ?>
        <div class="wrap hs-crm-scheduler-wrap">
            <h1>Truck Scheduler</h1>
            
            <div style="margin-bottom: 20px;">
                <button type="button" class="button button-primary" id="hs-crm-add-truck-btn">+ Add Truck</button>
                <button type="button" class="button" id="hs-crm-add-booking-btn">+ Add Booking</button>
            </div>
            
            <div class="hs-crm-month-navigation" style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
                <a href="?page=hs-crm-truck-scheduler&month=<?php echo esc_attr($prev_month); ?>" class="button">← Previous Month</a>
                <h2><?php echo date('F Y', $month_timestamp); ?></h2>
                <a href="?page=hs-crm-truck-scheduler&month=<?php echo esc_attr($next_month); ?>" class="button">Next Month →</a>
            </div>
            
            <div class="hs-crm-trucks-list" style="margin-bottom: 30px;">
                <h3>Available Trucks</h3>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th style="width: 30%;">Truck Name</th>
                            <th style="width: 20%;">Registration</th>
                            <th style="width: 20%;">Capacity</th>
                            <th style="width: 15%;">Status</th>
                            <th style="width: 15%;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($trucks)): ?>
                            <tr>
                                <td colspan="5" style="text-align: center;">No trucks added yet. Click "Add Truck" to get started.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($trucks as $truck): ?>
                                <tr>
                                    <td><strong><?php echo esc_html($truck->name); ?></strong></td>
                                    <td><?php echo esc_html($truck->registration); ?></td>
                                    <td><?php echo esc_html($truck->capacity); ?></td>
                                    <td><span class="hs-crm-status-badge"><?php echo esc_html(ucfirst($truck->status)); ?></span></td>
                                    <td>
                                        <button type="button" class="button button-small hs-crm-edit-truck" data-truck-id="<?php echo esc_attr($truck->id); ?>">Edit</button>
                                        <button type="button" class="button button-small hs-crm-remove-truck" data-truck-id="<?php echo esc_attr($truck->id); ?>">Remove</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="hs-crm-schedule-calendar">
                <h3>Schedule Calendar</h3>
                <div style="overflow-x: auto;">
                    <table class="hs-crm-calendar-table" style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr>
                                <th style="border: 1px solid #ddd; padding: 10px; background: #f5f5f5;">Date</th>
                                <?php foreach ($trucks as $truck): ?>
                                    <th style="border: 1px solid #ddd; padding: 10px; background: #f5f5f5;">
                                        <?php echo esc_html($truck->name); ?>
                                    </th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $days_in_month = date('t', $month_timestamp);
                            for ($day = 1; $day <= $days_in_month; $day++):
                                $current_date = date('Y-m-d', strtotime($current_month . '-' . sprintf('%02d', $day)));
                                $day_name = date('D', strtotime($current_date));
                                $is_weekend = in_array($day_name, array('Sat', 'Sun'));
                            ?>
                                <tr style="<?php echo $is_weekend ? 'background: #f9f9f9;' : ''; ?>">
                                    <td style="border: 1px solid #ddd; padding: 10px;">
                                        <strong><?php echo date('D j', strtotime($current_date)); ?></strong>
                                    </td>
                                    <?php foreach ($trucks as $truck): ?>
                                        <td style="border: 1px solid #ddd; padding: 5px; min-height: 60px; vertical-align: top;" class="hs-crm-calendar-cell" data-date="<?php echo esc_attr($current_date); ?>" data-truck-id="<?php echo esc_attr($truck->id); ?>">
                                            <?php
                                            if (isset($bookings_by_date[$current_date][$truck->id])) {
                                                foreach ($bookings_by_date[$current_date][$truck->id] as $booking) {
                                                    $customer_name = '';
                                                    if ($booking->enquiry_id && $booking->first_name) {
                                                        $customer_name = $booking->first_name . ' ' . $booking->last_name;
                                                    }
                                                    $time_display = '';
                                                    if ($booking->start_time) {
                                                        $time_display = date('g:ia', strtotime($booking->start_time));
                                                        if ($booking->end_time) {
                                                            $time_display .= '-' . date('g:ia', strtotime($booking->end_time));
                                                        }
                                                    }
                                                    ?>
                                                    <div class="hs-crm-booking-item" data-booking-id="<?php echo esc_attr($booking->id); ?>" style="background: #e8f4f8; padding: 5px; margin-bottom: 5px; border-radius: 3px; cursor: pointer; font-size: 12px;">
                                                        <?php if ($time_display): ?>
                                                            <div style="font-weight: bold;"><?php echo esc_html($time_display); ?></div>
                                                        <?php endif; ?>
                                                        <?php if ($customer_name): ?>
                                                            <div><?php echo esc_html($customer_name); ?></div>
                                                        <?php elseif ($booking->notes): ?>
                                                            <div><?php echo esc_html(wp_trim_words($booking->notes, 5)); ?></div>
                                                        <?php else: ?>
                                                            <div><em>Blocked</em></div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <?php
                                                }
                                            }
                                            ?>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endfor; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Add/Edit Truck Modal -->
        <div id="hs-crm-truck-modal" class="hs-crm-modal" style="display: none;">
            <div class="hs-crm-modal-content">
                <span class="hs-crm-modal-close">&times;</span>
                <h2 id="truck-modal-title">Add Truck</h2>
                <form id="hs-crm-truck-form">
                    <input type="hidden" id="truck-id" name="truck_id">
                    
                    <div class="hs-crm-form-group">
                        <label for="truck-name">Truck Name *</label>
                        <input type="text" id="truck-name" name="name" required placeholder="e.g., Truck 1">
                    </div>
                    
                    <div class="hs-crm-form-group">
                        <label for="truck-registration">Registration</label>
                        <input type="text" id="truck-registration" name="registration" placeholder="e.g., ABC123">
                    </div>
                    
                    <div class="hs-crm-form-group">
                        <label for="truck-capacity">Capacity</label>
                        <input type="text" id="truck-capacity" name="capacity" placeholder="e.g., 3-bedroom house">
                    </div>
                    
                    <div class="hs-crm-form-group">
                        <button type="submit" class="button button-primary">Save Truck</button>
                        <button type="button" class="button hs-crm-modal-close">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Add/Edit Booking Modal -->
        <div id="hs-crm-booking-modal" class="hs-crm-modal" style="display: none;">
            <div class="hs-crm-modal-content">
                <span class="hs-crm-modal-close">&times;</span>
                <h2 id="booking-modal-title">Add Booking</h2>
                <form id="hs-crm-booking-form">
                    <input type="hidden" id="booking-id" name="booking_id">
                    
                    <div class="hs-crm-form-group">
                        <label for="booking-truck">Truck *</label>
                        <select id="booking-truck" name="truck_id" required>
                            <option value="">Select Truck</option>
                            <?php foreach ($trucks as $truck): ?>
                                <option value="<?php echo esc_attr($truck->id); ?>"><?php echo esc_html($truck->name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="hs-crm-form-group">
                        <label for="booking-date">Date *</label>
                        <input type="date" id="booking-date" name="booking_date" required>
                    </div>
                    
                    <div class="hs-crm-form-group">
                        <label for="booking-start-time">Start Time</label>
                        <input type="time" id="booking-start-time" name="start_time">
                    </div>
                    
                    <div class="hs-crm-form-group">
                        <label for="booking-end-time">End Time</label>
                        <input type="time" id="booking-end-time" name="end_time">
                    </div>
                    
                    <div class="hs-crm-form-group">
                        <label for="booking-enquiry">Link to Enquiry (Optional)</label>
                        <select id="booking-enquiry" name="enquiry_id">
                            <option value="">No enquiry linked</option>
                            <?php
                            // Get all active enquiries with move dates
                            $enquiries = HS_CRM_Database::get_enquiries('active', 'move_date', 'ASC');
                            // Ensure enquiries is always an array, even if null is returned
                            if (!is_array($enquiries)) {
                                $enquiries = array();
                            }
                            foreach ($enquiries as $enq):
                                if ($enq->move_date):
                            ?>
                                <option value="<?php echo esc_attr($enq->id); ?>">
                                    <?php echo esc_html($enq->first_name . ' ' . $enq->last_name); ?> - <?php echo esc_html(date('d/m/Y', strtotime($enq->move_date))); ?>
                                </option>
                            <?php 
                                endif;
                            endforeach; 
                            ?>
                        </select>
                    </div>
                    
                    <div class="hs-crm-form-group">
                        <label for="booking-notes">Notes</label>
                        <textarea id="booking-notes" name="notes" rows="3" placeholder="Additional notes about this booking..."></textarea>
                    </div>
                    
                    <div class="hs-crm-form-group">
                        <button type="submit" class="button button-primary">Save Booking</button>
                        <button type="button" class="button button-secondary hs-crm-delete-booking-btn" style="display: none;">Delete Booking</button>
                        <button type="button" class="button hs-crm-modal-close">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
        <?php
    }
    
    /**
     * AJAX: Get trucks
     */
    public function ajax_get_trucks() {
        check_ajax_referer('hs_crm_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permission denied.'));
        }
        
        $trucks = HS_CRM_Database::get_trucks('all');
        wp_send_json_success(array('trucks' => $trucks));
    }
    
    /**
     * AJAX: Add truck
     */
    public function ajax_add_truck() {
        check_ajax_referer('hs_crm_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permission denied.'));
        }
        
        $data = array(
            'name' => isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '',
            'registration' => isset($_POST['registration']) ? sanitize_text_field($_POST['registration']) : '',
            'capacity' => isset($_POST['capacity']) ? sanitize_text_field($_POST['capacity']) : ''
        );
        
        if (empty($data['name'])) {
            wp_send_json_error(array('message' => 'Truck name is required.'));
        }
        
        $truck_id = HS_CRM_Database::insert_truck($data);
        
        if ($truck_id) {
            wp_send_json_success(array('message' => 'Truck added successfully.', 'truck_id' => $truck_id));
        } else {
            wp_send_json_error(array('message' => 'Failed to add truck.'));
        }
    }
    
    /**
     * AJAX: Update truck
     */
    public function ajax_update_truck() {
        check_ajax_referer('hs_crm_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permission denied.'));
        }
        
        $truck_id = isset($_POST['truck_id']) ? intval($_POST['truck_id']) : 0;
        
        if (!$truck_id) {
            wp_send_json_error(array('message' => 'Invalid truck ID.'));
        }
        
        $data = array(
            'name' => isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '',
            'registration' => isset($_POST['registration']) ? sanitize_text_field($_POST['registration']) : '',
            'capacity' => isset($_POST['capacity']) ? sanitize_text_field($_POST['capacity']) : ''
        );
        
        $result = HS_CRM_Database::update_truck($truck_id, $data);
        
        if ($result !== false) {
            wp_send_json_success(array('message' => 'Truck updated successfully.'));
        } else {
            wp_send_json_error(array('message' => 'Failed to update truck.'));
        }
    }
    
    /**
     * AJAX: Delete truck
     */
    public function ajax_delete_truck() {
        check_ajax_referer('hs_crm_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permission denied.'));
        }
        
        $truck_id = isset($_POST['truck_id']) ? intval($_POST['truck_id']) : 0;
        
        if (!$truck_id) {
            wp_send_json_error(array('message' => 'Invalid truck ID.'));
        }
        
        $result = HS_CRM_Database::delete_truck($truck_id);
        
        if ($result) {
            wp_send_json_success(array('message' => 'Truck removed successfully.'));
        } else {
            wp_send_json_error(array('message' => 'Failed to remove truck.'));
        }
    }
    
    /**
     * AJAX: Get bookings
     */
    public function ajax_get_bookings() {
        check_ajax_referer('hs_crm_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permission denied.'));
        }
        
        $start_date = isset($_POST['start_date']) ? sanitize_text_field($_POST['start_date']) : null;
        $end_date = isset($_POST['end_date']) ? sanitize_text_field($_POST['end_date']) : null;
        
        $bookings = HS_CRM_Database::get_truck_bookings($start_date, $end_date);
        wp_send_json_success(array('bookings' => $bookings));
    }
    
    /**
     * AJAX: Add booking
     */
    public function ajax_add_booking() {
        check_ajax_referer('hs_crm_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permission denied.'));
        }
        
        $data = array(
            'truck_id' => isset($_POST['truck_id']) ? intval($_POST['truck_id']) : 0,
            'booking_date' => isset($_POST['booking_date']) ? sanitize_text_field($_POST['booking_date']) : '',
            'start_time' => isset($_POST['start_time']) ? sanitize_text_field($_POST['start_time']) : '',
            'end_time' => isset($_POST['end_time']) ? sanitize_text_field($_POST['end_time']) : '',
            'notes' => isset($_POST['notes']) ? sanitize_textarea_field($_POST['notes']) : ''
        );
        
        if (!empty($_POST['enquiry_id'])) {
            $data['enquiry_id'] = intval($_POST['enquiry_id']);
        }
        
        if (!$data['truck_id'] || !$data['booking_date']) {
            wp_send_json_error(array('message' => 'Truck and date are required.'));
        }
        
        $booking_id = HS_CRM_Database::insert_booking($data);
        
        if ($booking_id) {
            wp_send_json_success(array('message' => 'Booking added successfully.', 'booking_id' => $booking_id));
        } else {
            wp_send_json_error(array('message' => 'Failed to add booking.'));
        }
    }
    
    /**
     * AJAX: Update booking
     */
    public function ajax_update_booking() {
        check_ajax_referer('hs_crm_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permission denied.'));
        }
        
        $booking_id = isset($_POST['booking_id']) ? intval($_POST['booking_id']) : 0;
        
        if (!$booking_id) {
            wp_send_json_error(array('message' => 'Invalid booking ID.'));
        }
        
        $data = array(
            'truck_id' => isset($_POST['truck_id']) ? intval($_POST['truck_id']) : 0,
            'booking_date' => isset($_POST['booking_date']) ? sanitize_text_field($_POST['booking_date']) : '',
            'start_time' => isset($_POST['start_time']) ? sanitize_text_field($_POST['start_time']) : '',
            'end_time' => isset($_POST['end_time']) ? sanitize_text_field($_POST['end_time']) : '',
            'notes' => isset($_POST['notes']) ? sanitize_textarea_field($_POST['notes']) : ''
        );
        
        if (isset($_POST['enquiry_id'])) {
            $data['enquiry_id'] = !empty($_POST['enquiry_id']) ? intval($_POST['enquiry_id']) : null;
        }
        
        $result = HS_CRM_Database::update_booking($booking_id, $data);
        
        if ($result !== false) {
            wp_send_json_success(array('message' => 'Booking updated successfully.'));
        } else {
            wp_send_json_error(array('message' => 'Failed to update booking.'));
        }
    }
    
    /**
     * AJAX: Delete booking
     */
    public function ajax_delete_booking() {
        check_ajax_referer('hs_crm_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permission denied.'));
        }
        
        $booking_id = isset($_POST['booking_id']) ? intval($_POST['booking_id']) : 0;
        
        if (!$booking_id) {
            wp_send_json_error(array('message' => 'Invalid booking ID.'));
        }
        
        $result = HS_CRM_Database::delete_booking($booking_id);
        
        if ($result) {
            wp_send_json_success(array('message' => 'Booking deleted successfully.'));
        } else {
            wp_send_json_error(array('message' => 'Failed to delete booking.'));
        }
    }
}
