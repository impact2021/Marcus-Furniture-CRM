<?php
/**
 * Admin interface class
 */

if (!defined('ABSPATH')) {
    exit;
}

class HS_CRM_Admin {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('wp_ajax_hs_crm_update_status', array($this, 'ajax_update_status'));
        add_action('wp_ajax_hs_crm_save_notes', array($this, 'ajax_save_notes'));
        add_action('wp_ajax_hs_crm_get_enquiry', array($this, 'ajax_get_enquiry'));
        add_action('wp_ajax_hs_crm_add_note', array($this, 'ajax_add_note'));
        add_action('wp_ajax_hs_crm_delete_note', array($this, 'ajax_delete_note'));
        add_action('wp_ajax_hs_crm_create_enquiry', array($this, 'ajax_create_enquiry'));
        add_action('wp_ajax_hs_crm_update_enquiry', array($this, 'ajax_update_enquiry'));
        add_action('wp_ajax_hs_crm_update_truck_assignment', array($this, 'ajax_update_truck_assignment'));
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            'Marcus Furniture Enquiries',
            'MF Enquiries',
            'manage_options',
            'hs-crm-enquiries',
            array($this, 'render_admin_page'),
            'dashicons-move',
            26
        );
    }
    
    /**
     * Render admin page
     */
    public function render_admin_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        $current_status = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : 'active';
        $orderby = isset($_GET['orderby']) ? sanitize_text_field($_GET['orderby']) : 'move_date';
        $order = isset($_GET['order']) ? sanitize_text_field($_GET['order']) : 'ASC';
        
        $enquiries = HS_CRM_Database::get_enquiries($current_status === 'all' ? null : $current_status, $orderby, $order);
        $counts = HS_CRM_Database::get_status_counts();
        
        // Build sort URLs
        $base_url = '?page=hs-crm-enquiries&status=' . urlencode($current_status);
        $move_date_url = $base_url . '&orderby=move_date&order=' . ($orderby === 'move_date' && $order === 'ASC' ? 'DESC' : 'ASC');
        $created_at_url = $base_url . '&orderby=created_at&order=' . ($orderby === 'created_at' && $order === 'ASC' ? 'DESC' : 'ASC');
        
        ?>
        <div class="wrap hs-crm-admin-wrap">
            <h1>Marcus Furniture Enquiries</h1>
            
            <div style="margin-bottom: 20px;">
                <button type="button" class="button button-primary" id="hs-crm-add-new-enquiry">+ Add New Enquiry</button>
            </div>
            
            <div class="hs-crm-tabs">
                <a href="?page=hs-crm-enquiries&status=active" 
                   class="hs-crm-tab <?php echo $current_status === 'active' ? 'active' : ''; ?>">
                    Active leads (<?php echo $counts['active']; ?>)
                </a>
                <a href="?page=hs-crm-enquiries&status=all" 
                   class="hs-crm-tab <?php echo $current_status === 'all' ? 'active' : ''; ?>">
                    All (<?php echo $counts['all']; ?>)
                </a>
                <a href="?page=hs-crm-enquiries&status=First Contact" 
                   class="hs-crm-tab <?php echo $current_status === 'First Contact' ? 'active' : ''; ?>">
                    First Contact (<?php echo $counts['First Contact']; ?>)
                </a>
                <a href="?page=hs-crm-enquiries&status=Quote Sent" 
                   class="hs-crm-tab <?php echo $current_status === 'Quote Sent' ? 'active' : ''; ?>">
                    Quote Sent (<?php echo $counts['Quote Sent']; ?>)
                </a>
                <a href="?page=hs-crm-enquiries&status=Booking Confirmed" 
                   class="hs-crm-tab <?php echo $current_status === 'Booking Confirmed' ? 'active' : ''; ?>">
                    Booking Confirmed (<?php echo $counts['Booking Confirmed']; ?>)
                </a>
                <a href="?page=hs-crm-enquiries&status=Deposit Paid" 
                   class="hs-crm-tab <?php echo $current_status === 'Deposit Paid' ? 'active' : ''; ?>">
                    Deposit Paid (<?php echo $counts['Deposit Paid']; ?>)
                </a>
                <a href="?page=hs-crm-enquiries&status=Completed" 
                   class="hs-crm-tab <?php echo $current_status === 'Completed' ? 'active' : ''; ?>">
                    Completed (<?php echo $counts['Completed']; ?>)
                </a>
                <a href="?page=hs-crm-enquiries&status=Archived" 
                   class="hs-crm-tab <?php echo $current_status === 'Archived' ? 'active' : ''; ?>">
                    Archived (<?php echo $counts['Archived']; ?>)
                </a>
            </div>
            
            <?php if (empty($enquiries)): ?>
                <div class="hs-crm-no-enquiries">
                    <p style="text-align: center; padding: 20px;">No enquiries found.</p>
                </div>
            <?php else: ?>
                <?php 
                $row_index = 0;
                foreach ($enquiries as $enquiry): 
                    $notes = HS_CRM_Database::get_notes($enquiry->id);
                    $row_class = ($row_index % 2 === 0) ? 'hs-crm-even-row' : 'hs-crm-odd-row';
                    $has_notes = !empty($notes);
                    $add_note_row_style = $has_notes ? 'display: none;' : '';
                    $row_index++;
                ?>
                    <!-- Individual table for each enquiry -->
                    <table class="wp-list-table widefat fixed hs-crm-enquiries-table hs-crm-single-enquiry-table">
                        <tbody>
                            <!-- Customer Header Row -->
                            <tr class="hs-crm-customer-header-row <?php echo $row_class; ?>">
                                <th style="width: 16%;">
                                    Source & Dates
                                </th>
                                <th style="width: 20%;">Contact & Address</th>
                                <th style="width: 14%;">House Details</th>
                                <th style="width: 10%;">Status</th>
                                <th style="width: 10%;">Truck</th>
                                <th style="width: 12%;">Status Change</th>
                                <th style="width: 8%;">Edit</th>
                                <th style="width: 10%;">Action</th>
                            </tr>
                            <tr class="hs-crm-enquiry-row <?php echo $row_class; ?>" data-enquiry-id="<?php echo esc_attr($enquiry->id); ?>">
                                <td>
                                    <span class="hs-crm-source-badge"><?php echo esc_html(ucfirst($enquiry->contact_source)); ?></span><br>
                                    <small style="color: #666;">Contact: <?php echo esc_html(hs_crm_format_date($enquiry->created_at, 'd/m/Y')); ?></small><br>
                                    <?php if (!empty($enquiry->move_date)): ?>
                                        <small style="color: #666;">Move: <strong><?php echo esc_html(date('d/m/Y', strtotime($enquiry->move_date))); ?></strong>
                                        <?php if (!empty($enquiry->move_time)): ?>
                                            <?php echo esc_html(date('g:iA', strtotime($enquiry->move_time))); ?>
                                        <?php endif; ?>
                                        </small>
                                    <?php else: ?>
                                        <small style="color: #999;">Move: Not set</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong class="hs-crm-editable-name" data-enquiry-id="<?php echo esc_attr($enquiry->id); ?>">
                                        <?php echo esc_html($enquiry->first_name . ' ' . $enquiry->last_name); ?>
                                    </strong><br>
                                    <small style="color: #666;"><?php echo esc_html($enquiry->phone); ?> | <?php echo esc_html($enquiry->email); ?></small><br>
                                    <small style="color: #666;"><?php echo esc_html($enquiry->address); ?>
                                    <?php if (!empty($enquiry->suburb)): ?>
                                        , <?php echo esc_html($enquiry->suburb); ?>
                                    <?php endif; ?>
                                    </small>
                                    <?php if (!empty($enquiry->delivery_from_address) || !empty($enquiry->delivery_to_address)): ?>
                                        <br><small style="color: #0066cc; font-style: italic;">
                                        <?php if (!empty($enquiry->delivery_from_address)): ?>
                                            From: <?php echo esc_html($enquiry->delivery_from_address); ?>
                                        <?php endif; ?>
                                        <?php if (!empty($enquiry->delivery_to_address)): ?>
                                            <?php if (!empty($enquiry->delivery_from_address)): ?> | <?php endif; ?>
                                            To: <?php echo esc_html($enquiry->delivery_to_address); ?>
                                        <?php endif; ?>
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php 
                                    $house_details = array();
                                    if (!empty($enquiry->number_of_bedrooms)) {
                                        $house_details[] = esc_html($enquiry->number_of_bedrooms) . ' bedrooms';
                                    }
                                    if (!empty($enquiry->total_rooms)) {
                                        $house_details[] = esc_html($enquiry->total_rooms) . ' total rooms';
                                    }
                                    if (!empty($enquiry->stairs)) {
                                        $house_details[] = 'Stairs: ' . esc_html($enquiry->stairs);
                                    }
                                    if (!empty($enquiry->stairs_from)) {
                                        $house_details[] = 'Stairs (From): ' . esc_html($enquiry->stairs_from);
                                    }
                                    if (!empty($enquiry->stairs_to)) {
                                        $house_details[] = 'Stairs (To): ' . esc_html($enquiry->stairs_to);
                                    }
                                    if (!empty($enquiry->property_notes)) {
                                        $house_details[] = 'Notes: ' . esc_html($enquiry->property_notes);
                                    }
                                    if (empty($house_details)) {
                                        echo '<em style="color: #999;">Not set</em>';
                                    } else {
                                        echo '<small>' . implode('<br>', $house_details) . '</small>';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <span class="hs-crm-status-badge status-<?php echo esc_attr(strtolower(str_replace(' ', '-', $enquiry->status))); ?>">
                                        <?php echo esc_html($enquiry->status); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php 
                                    $trucks = HS_CRM_Database::get_trucks('active');
                                    if (!is_array($trucks)) {
                                        $trucks = array();
                                    }
                                    ?>
                                    <select class="hs-crm-truck-select" data-enquiry-id="<?php echo esc_attr($enquiry->id); ?>" data-current-truck="<?php echo esc_attr($enquiry->truck_id ?? ''); ?>">
                                        <option value="">No Truck</option>
                                        <?php foreach ($trucks as $truck): ?>
                                            <option value="<?php echo esc_attr($truck->id); ?>" <?php selected($enquiry->truck_id, $truck->id); ?>>
                                                <?php echo esc_html($truck->name); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td>
                                    <select class="hs-crm-status-select" data-enquiry-id="<?php echo esc_attr($enquiry->id); ?>" data-current-status="<?php echo esc_attr($enquiry->status); ?>">
                                        <option value="">Change Status...</option>
                                        <option value="First Contact">First Contact</option>
                                        <option value="Quote Sent">Quote Sent</option>
                                        <option value="Booking Confirmed">Booking Confirmed</option>
                                        <option value="Deposit Paid">Deposit Paid</option>
                                        <option value="Completed">Completed</option>
                                        <option value="Archived">Archived</option>
                                    </select>
                                </td>
                                <td>
                                    <button type="button" class="button button-small hs-crm-edit-enquiry" data-enquiry-id="<?php echo esc_attr($enquiry->id); ?>">Edit</button>
                                </td>
                                <td>
                                    <select class="hs-crm-action-select" data-enquiry-id="<?php echo esc_attr($enquiry->id); ?>">
                                        <option value="">Select Action...</option>
                                        <option value="send_quote">Send Quote</option>
                                        <option value="send_invoice">Send Invoice</option>
                                        <option value="send_receipt">Send Receipt</option>
                                    </select>
                                </td>
                            </tr>
                            
                            <!-- Notes section - collapsible -->
                            <?php if (!empty($notes)): ?>
                                <tr class="hs-crm-notes-toggle-row <?php echo $row_class; ?>" data-enquiry-id="<?php echo esc_attr($enquiry->id); ?>">
                                    <td colspan="8" style="padding: 5px 10px; cursor: pointer; background: #f9f9f9;">
                                        <span class="hs-crm-notes-toggle dashicons dashicons-arrow-down" style="font-size: 16px; vertical-align: middle;"></span>
                                        <strong>Notes (<?php echo count($notes); ?>)</strong> - Click to expand
                                    </td>
                                </tr>
                                <?php foreach ($notes as $note): ?>
                                    <tr class="hs-crm-note-row <?php echo $row_class; ?>" style="display: none;" data-note-id="<?php echo esc_attr($note->id); ?>" data-enquiry-id="<?php echo esc_attr($enquiry->id); ?>">
                                        <td class="hs-crm-note-date">
                                            <?php echo esc_html(hs_crm_format_date($note->created_at)); ?>
                                        </td>
                                        <td colspan="6" class="hs-crm-note-content">
                                            <div class="hs-crm-note-text"><?php echo esc_html(stripslashes($note->note)); ?></div>
                                        </td>
                                        <td class="hs-crm-note-actions">
                                            <button type="button" class="button button-small hs-crm-delete-note" data-note-id="<?php echo esc_attr($note->id); ?>">Delete</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            
                            <!-- Add note row -->
                            <tr class="hs-crm-add-note-row <?php echo $row_class; ?>" data-enquiry-id="<?php echo esc_attr($enquiry->id); ?>" style="<?php echo esc_attr($add_note_row_style); ?>">
                                <td colspan="7">
                                    <textarea class="hs-crm-new-note" data-enquiry-id="<?php echo esc_attr($enquiry->id); ?>" rows="2" placeholder="Add a new note..."></textarea>
                                </td>
                                <td>
                                    <button type="button" class="button button-small hs-crm-add-note" data-enquiry-id="<?php echo esc_attr($enquiry->id); ?>">Add Note</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <!-- Add/Edit Enquiry Modal -->
        <div id="hs-crm-enquiry-modal" class="hs-crm-modal" style="display: none;">
            <div class="hs-crm-modal-content">
                <span class="hs-crm-modal-close">&times;</span>
                <h2 id="enquiry-modal-title">Add New Enquiry</h2>
                <form id="hs-crm-enquiry-form">
                    <input type="hidden" id="enquiry-id" name="enquiry_id">
                    
                    <div class="hs-crm-form-group">
                        <label for="enquiry-first-name">First Name *</label>
                        <input type="text" id="enquiry-first-name" name="first_name" required>
                    </div>
                    
                    <div class="hs-crm-form-group">
                        <label for="enquiry-last-name">Last Name *</label>
                        <input type="text" id="enquiry-last-name" name="last_name" required>
                    </div>
                    
                    <div class="hs-crm-form-group">
                        <label for="enquiry-email">Email *</label>
                        <input type="email" id="enquiry-email" name="email" required>
                    </div>
                    
                    <div class="hs-crm-form-group">
                        <label for="enquiry-phone">Phone *</label>
                        <input type="tel" id="enquiry-phone" name="phone" required>
                    </div>
                    
                    <div class="hs-crm-form-group hs-crm-form-group-full">
                        <label for="enquiry-address">Address</label>
                        <textarea id="enquiry-address" name="address" rows="3"></textarea>
                    </div>
                    
                    <div class="hs-crm-form-group hs-crm-form-group-full">
                        <label for="enquiry-delivery-from-address">From Address</label>
                        <textarea id="enquiry-delivery-from-address" name="delivery_from_address" rows="2" placeholder="Pick-up location"></textarea>
                    </div>
                    
                    <div class="hs-crm-form-group hs-crm-form-group-full">
                        <label for="enquiry-delivery-to-address">To Address</label>
                        <textarea id="enquiry-delivery-to-address" name="delivery_to_address" rows="2" placeholder="Drop-off location"></textarea>
                    </div>
                    
                    <div class="hs-crm-form-group">
                        <label for="enquiry-suburb">Suburb</label>
                        <input type="text" id="enquiry-suburb" name="suburb">
                    </div>
                    
                    <div class="hs-crm-form-group">
                        <label for="enquiry-number-of-bedrooms">Number of Bedrooms</label>
                        <select id="enquiry-number-of-bedrooms" name="number_of_bedrooms">
                            <option value="">Select...</option>
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                            <option value="5">5</option>
                            <option value="6">6</option>
                        </select>
                    </div>
                    
                    <div class="hs-crm-form-group">
                        <label for="enquiry-number-of-rooms">Number of Rooms</label>
                        <select id="enquiry-number-of-rooms" name="number_of_rooms">
                            <option value="">Select...</option>
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                            <option value="5">5</option>
                            <option value="6">6</option>
                            <option value="7">7</option>
                            <option value="8">8</option>
                            <option value="9">9</option>
                            <option value="10">10</option>
                            <option value="11">11</option>
                            <option value="12">12</option>
                        </select>
                    </div>
                    
                    <div class="hs-crm-form-group">
                        <label for="enquiry-total-rooms">Total Number of Rooms</label>
                        <select id="enquiry-total-rooms" name="total_rooms">
                            <option value="">Select...</option>
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                            <option value="5">5</option>
                            <option value="6">6</option>
                            <option value="7">7</option>
                            <option value="8">8</option>
                            <option value="9">9</option>
                            <option value="10">10</option>
                            <option value="11">11</option>
                            <option value="12">12</option>
                        </select>
                    </div>
                    
                    <div class="hs-crm-form-group hs-crm-form-group-full">
                        <label for="enquiry-property-notes">Property Notes</label>
                        <textarea id="enquiry-property-notes" name="property_notes" rows="3" placeholder="Additional notes about the property"></textarea>
                    </div>
                    
                    <div class="hs-crm-form-group">
                        <label for="enquiry-stairs-from">Stairs Involved (From Address)</label>
                        <select id="enquiry-stairs-from" name="stairs_from">
                            <option value="">Select...</option>
                            <option value="No">No</option>
                            <option value="Yes - 1 floor">Yes - 1 floor</option>
                            <option value="Yes - 2+ floors">Yes - 2+ floors</option>
                        </select>
                    </div>
                    
                    <div class="hs-crm-form-group">
                        <label for="enquiry-stairs-to">Stairs Involved (To Address)</label>
                        <select id="enquiry-stairs-to" name="stairs_to">
                            <option value="">Select...</option>
                            <option value="No">No</option>
                            <option value="Yes - 1 floor">Yes - 1 floor</option>
                            <option value="Yes - 2+ floors">Yes - 2+ floors</option>
                        </select>
                    </div>
                    
                    <div class="hs-crm-form-group">
                        <label for="enquiry-move-date">Requested Move Date</label>
                        <input type="date" id="enquiry-move-date" name="move_date">
                    </div>
                    
                    <div class="hs-crm-form-group">
                        <label for="enquiry-move-time">Requested Move Time</label>
                        <input type="time" id="enquiry-move-time" name="move_time">
                    </div>
                    
                    <div class="hs-crm-form-group">
                        <label for="enquiry-booking-start-time">Booking Start Time</label>
                        <input type="time" id="enquiry-booking-start-time" name="booking_start_time">
                        <small>Actual start time of the booking (admin use)</small>
                    </div>
                    
                    <div class="hs-crm-form-group">
                        <label for="enquiry-booking-end-time">Booking End Time</label>
                        <input type="time" id="enquiry-booking-end-time" name="booking_end_time">
                        <small>Actual finish time of the booking (admin use)</small>
                    </div>
                    
                    <div class="hs-crm-form-group">
                        <label for="enquiry-contact-source">Contact Source *</label>
                        <select id="enquiry-contact-source" name="contact_source" required>
                            <option value="form">Website Form</option>
                            <option value="whatsapp">WhatsApp</option>
                            <option value="phone">Phone Call</option>
                            <option value="email">Direct Email</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    
                    <div class="hs-crm-form-group">
                        <label for="enquiry-status">Status</label>
                        <select id="enquiry-status" name="status">
                            <option value="First Contact">First Contact</option>
                            <option value="Quote Sent">Quote Sent</option>
                            <option value="Booking Confirmed">Booking Confirmed</option>
                            <option value="Deposit Paid">Deposit Paid</option>
                            <option value="Completed">Completed</option>
                            <option value="Archived">Archived</option>
                        </select>
                    </div>
                    
                    <div class="hs-crm-form-group hs-crm-form-buttons">
                        <button type="submit" class="button button-primary">Save Enquiry</button>
                        <button type="button" class="button hs-crm-modal-close">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Email Modal -->
        <div id="hs-crm-email-modal" class="hs-crm-modal" style="display: none;">
            <div class="hs-crm-modal-content">
                <span class="hs-crm-modal-close">&times;</span>
                <h2 id="email-modal-title">Send Email</h2>
                <form id="hs-crm-email-form">
                    <input type="hidden" id="email-enquiry-id" name="enquiry_id">
                    <input type="hidden" id="email-type" name="email_type">
                    
                    <div class="hs-crm-form-group">
                        <label for="email-to">To:</label>
                        <input type="email" id="email-to" name="email_to" readonly>
                    </div>
                    
                    <div class="hs-crm-form-group">
                        <label for="email-customer">Customer:</label>
                        <input type="text" id="email-customer" name="email_customer" readonly>
                    </div>
                    
                    <div class="hs-crm-form-group">
                        <label for="email-subject">Subject:</label>
                        <input type="text" id="email-subject" name="subject">
                    </div>
                    
                    <div class="hs-crm-form-group">
                        <label for="email-message">Message:</label>
                        <textarea id="email-message" name="message" rows="5"></textarea>
                    </div>
                    
                    <input type="hidden" id="email-customer-name" name="customer_first_name">
                    
                    <div class="hs-crm-form-group">
                        <label>Quote Items:</label>
                        <div id="quote-table-container">
                            <table id="quote-items-table" class="hs-crm-quote-table">
                                <thead>
                                    <tr>
                                        <th style="width: 50%;">Description of Work</th>
                                        <th style="width: 20%;">Cost (ex GST)</th>
                                        <th style="width: 20%;">GST (15%)</th>
                                        <th style="width: 10%;"></th>
                                    </tr>
                                </thead>
                                <tbody id="quote-items-body">
                                    <tr class="quote-item-row">
                                        <td><input type="text" class="quote-description" placeholder="e.g., 3-bedroom house move"></td>
                                        <td><input type="number" class="quote-cost" placeholder="0.00" step="0.01" min="0"></td>
                                        <td class="quote-gst">$0.00</td>
                                        <td><button type="button" class="remove-quote-item button">×</button></td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="4">
                                            <button type="button" id="add-quote-item" class="button">+ Add Item</button>
                                        </td>
                                    </tr>
                                    <tr class="quote-totals">
                                        <td colspan="1" style="text-align: right;"><strong>Subtotal (ex GST):</strong></td>
                                        <td id="quote-subtotal">$0.00</td>
                                        <td colspan="2"></td>
                                    </tr>
                                    <tr class="quote-totals">
                                        <td colspan="1" style="text-align: right;"><strong>Total GST:</strong></td>
                                        <td id="quote-total-gst">$0.00</td>
                                        <td colspan="2"></td>
                                    </tr>
                                    <tr class="quote-totals">
                                        <td colspan="1" style="text-align: right;"><strong>Total (inc GST):</strong></td>
                                        <td id="quote-total"><strong>$0.00</strong></td>
                                        <td colspan="2"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    
                    <div class="hs-crm-form-group">
                        <button type="submit" class="button button-primary">Send Email</button>
                        <button type="button" class="button hs-crm-modal-close">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
        <?php
    }
    
    /**
     * AJAX handler for creating new enquiry
     */
    public function ajax_create_enquiry() {
        check_ajax_referer('hs_crm_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permission denied.'));
        }
        
        $data = array(
            'first_name' => isset($_POST['first_name']) ? sanitize_text_field($_POST['first_name']) : '',
            'last_name' => isset($_POST['last_name']) ? sanitize_text_field($_POST['last_name']) : '',
            'email' => isset($_POST['email']) ? sanitize_email($_POST['email']) : '',
            'phone' => isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '',
            'address' => isset($_POST['address']) ? sanitize_textarea_field($_POST['address']) : '',
            'contact_source' => isset($_POST['contact_source']) ? sanitize_text_field($_POST['contact_source']) : 'form',
        );
        
        if (!empty($_POST['suburb'])) {
            $data['suburb'] = sanitize_text_field($_POST['suburb']);
        }
        
        if (!empty($_POST['delivery_from_address'])) {
            $data['delivery_from_address'] = sanitize_textarea_field($_POST['delivery_from_address']);
        }
        
        if (!empty($_POST['delivery_to_address'])) {
            $data['delivery_to_address'] = sanitize_textarea_field($_POST['delivery_to_address']);
        }
        
        if (!empty($_POST['move_date'])) {
            $data['move_date'] = sanitize_text_field($_POST['move_date']);
        }
        
        if (!empty($_POST['move_time'])) {
            $data['move_time'] = sanitize_text_field($_POST['move_time']);
        }
        
        if (!empty($_POST['booking_start_time'])) {
            $data['booking_start_time'] = sanitize_text_field($_POST['booking_start_time']);
        }
        
        if (!empty($_POST['booking_end_time'])) {
            $data['booking_end_time'] = sanitize_text_field($_POST['booking_end_time']);
        }
        
        if (!empty($_POST['number_of_bedrooms'])) {
            $data['number_of_bedrooms'] = sanitize_text_field($_POST['number_of_bedrooms']);
        }
        
        if (!empty($_POST['number_of_rooms'])) {
            $data['number_of_rooms'] = sanitize_text_field($_POST['number_of_rooms']);
        }
        
        if (!empty($_POST['total_rooms'])) {
            $data['total_rooms'] = sanitize_text_field($_POST['total_rooms']);
        }
        
        if (!empty($_POST['property_notes'])) {
            $data['property_notes'] = sanitize_textarea_field($_POST['property_notes']);
        }
        
        if (!empty($_POST['stairs'])) {
            $data['stairs'] = sanitize_text_field($_POST['stairs']);
        }
        
        if (!empty($_POST['stairs_from'])) {
            $data['stairs_from'] = sanitize_text_field($_POST['stairs_from']);
        }
        
        if (!empty($_POST['stairs_to'])) {
            $data['stairs_to'] = sanitize_text_field($_POST['stairs_to']);
        }
        
        // Validate required fields
        if (empty($data['first_name']) || empty($data['last_name']) || empty($data['email']) || empty($data['phone'])) {
            wp_send_json_error(array('message' => 'Please fill in all required fields.'));
        }
        
        $enquiry_id = HS_CRM_Database::insert_enquiry($data);
        
        if ($enquiry_id) {
            // Add note about manual creation
            $source_label = ucfirst($data['contact_source']);
            HS_CRM_Database::add_note($enquiry_id, "Enquiry manually created from {$source_label}");
            
            wp_send_json_success(array(
                'message' => 'Enquiry created successfully.',
                'enquiry_id' => $enquiry_id
            ));
        } else {
            wp_send_json_error(array('message' => 'Failed to create enquiry.'));
        }
    }
    
    /**
     * AJAX handler for updating enquiry
     */
    public function ajax_update_enquiry() {
        check_ajax_referer('hs_crm_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permission denied.'));
        }
        
        $enquiry_id = isset($_POST['enquiry_id']) ? intval($_POST['enquiry_id']) : 0;
        
        if (!$enquiry_id) {
            wp_send_json_error(array('message' => 'Invalid enquiry ID.'));
        }
        
        $data = array();
        
        if (isset($_POST['first_name'])) {
            $data['first_name'] = sanitize_text_field($_POST['first_name']);
        }
        if (isset($_POST['last_name'])) {
            $data['last_name'] = sanitize_text_field($_POST['last_name']);
        }
        if (isset($_POST['email'])) {
            $data['email'] = sanitize_email($_POST['email']);
        }
        if (isset($_POST['phone'])) {
            $data['phone'] = sanitize_text_field($_POST['phone']);
        }
        if (isset($_POST['address'])) {
            $data['address'] = sanitize_textarea_field($_POST['address']);
        }
        if (isset($_POST['delivery_from_address'])) {
            $data['delivery_from_address'] = sanitize_textarea_field($_POST['delivery_from_address']);
        }
        if (isset($_POST['delivery_to_address'])) {
            $data['delivery_to_address'] = sanitize_textarea_field($_POST['delivery_to_address']);
        }
        if (isset($_POST['suburb'])) {
            $data['suburb'] = sanitize_text_field($_POST['suburb']);
        }
        if (isset($_POST['move_date'])) {
            $data['move_date'] = sanitize_text_field($_POST['move_date']);
        }
        if (isset($_POST['move_time'])) {
            $data['move_time'] = sanitize_text_field($_POST['move_time']);
        }
        if (isset($_POST['booking_start_time'])) {
            $data['booking_start_time'] = sanitize_text_field($_POST['booking_start_time']);
        }
        if (isset($_POST['booking_end_time'])) {
            $data['booking_end_time'] = sanitize_text_field($_POST['booking_end_time']);
        }
        if (isset($_POST['number_of_bedrooms'])) {
            $data['number_of_bedrooms'] = sanitize_text_field($_POST['number_of_bedrooms']);
        }
        if (isset($_POST['number_of_rooms'])) {
            $data['number_of_rooms'] = sanitize_text_field($_POST['number_of_rooms']);
        }
        if (isset($_POST['total_rooms'])) {
            $data['total_rooms'] = sanitize_text_field($_POST['total_rooms']);
        }
        if (isset($_POST['property_notes'])) {
            $data['property_notes'] = sanitize_textarea_field($_POST['property_notes']);
        }
        if (isset($_POST['stairs'])) {
            $data['stairs'] = sanitize_text_field($_POST['stairs']);
        }
        if (isset($_POST['stairs_from'])) {
            $data['stairs_from'] = sanitize_text_field($_POST['stairs_from']);
        }
        if (isset($_POST['stairs_to'])) {
            $data['stairs_to'] = sanitize_text_field($_POST['stairs_to']);
        }
        if (isset($_POST['contact_source'])) {
            $data['contact_source'] = sanitize_text_field($_POST['contact_source']);
        }
        if (isset($_POST['status'])) {
            $data['status'] = sanitize_text_field($_POST['status']);
        }
        
        $result = HS_CRM_Database::update_enquiry($enquiry_id, $data);
        
        if ($result !== false) {
            wp_send_json_success(array('message' => 'Enquiry updated successfully.'));
        } else {
            wp_send_json_error(array('message' => 'Failed to update enquiry.'));
        }
    }
    
    /**
     * AJAX handler for status update
     */
    public function ajax_update_status() {
        check_ajax_referer('hs_crm_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permission denied.'));
        }
        
        $enquiry_id = isset($_POST['enquiry_id']) ? intval($_POST['enquiry_id']) : 0;
        $new_status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : '';
        $old_status = isset($_POST['old_status']) ? sanitize_text_field($_POST['old_status']) : '';
        
        if (!$enquiry_id || !$new_status) {
            wp_send_json_error(array('message' => 'Invalid data.'));
        }
        
        // Update status
        $result = HS_CRM_Database::update_status($enquiry_id, $new_status);
        
        if ($result) {
            // Add automatic note for status change
            if (!empty($old_status)) {
                $note_text = sprintf('Status changed from "%s" to "%s"', $old_status, $new_status);
            } else {
                $note_text = sprintf('Status changed to "%s"', $new_status);
            }
            $note_id = HS_CRM_Database::add_note($enquiry_id, $note_text);
            
            // Get current time formatted with plugin timezone
            $formatted_date = hs_crm_current_time_formatted('d/m/Y H:i');
            
            wp_send_json_success(array(
                'message' => 'Status updated successfully.',
                'note' => array(
                    'id' => $note_id,
                    'text' => $note_text,
                    'created_at' => current_time('mysql'),
                    'formatted_date' => $formatted_date
                )
            ));
        } else {
            wp_send_json_error(array('message' => 'Failed to update status.'));
        }
    }
    
    /**
     * AJAX handler for saving notes
     */
    public function ajax_save_notes() {
        check_ajax_referer('hs_crm_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permission denied.'));
        }
        
        $enquiry_id = isset($_POST['enquiry_id']) ? intval($_POST['enquiry_id']) : 0;
        $notes = isset($_POST['notes']) ? sanitize_textarea_field($_POST['notes']) : '';
        
        if (!$enquiry_id) {
            wp_send_json_error(array('message' => 'Invalid enquiry ID.'));
        }
        
        $result = HS_CRM_Database::update_admin_notes($enquiry_id, $notes);
        
        if ($result) {
            wp_send_json_success(array('message' => 'Notes saved successfully.'));
        } else {
            wp_send_json_error(array('message' => 'Failed to save notes.'));
        }
    }
    
    /**
     * AJAX handler for getting enquiry data
     */
    public function ajax_get_enquiry() {
        check_ajax_referer('hs_crm_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permission denied.'));
        }
        
        $enquiry_id = isset($_POST['enquiry_id']) ? intval($_POST['enquiry_id']) : 0;
        
        if (!$enquiry_id) {
            wp_send_json_error(array('message' => 'Invalid enquiry ID.'));
        }
        
        $enquiry = HS_CRM_Database::get_enquiry($enquiry_id);
        
        if ($enquiry) {
            wp_send_json_success(array('enquiry' => $enquiry));
        } else {
            wp_send_json_error(array('message' => 'Enquiry not found.'));
        }
    }
    
    /**
     * AJAX handler for adding a note
     */
    public function ajax_add_note() {
        check_ajax_referer('hs_crm_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permission denied.'));
        }
        
        $enquiry_id = isset($_POST['enquiry_id']) ? intval($_POST['enquiry_id']) : 0;
        $note = isset($_POST['note']) ? sanitize_textarea_field($_POST['note']) : '';
        
        if (!$enquiry_id || empty($note)) {
            wp_send_json_error(array('message' => 'Invalid data.'));
        }
        
        $note_id = HS_CRM_Database::add_note($enquiry_id, $note);
        
        if ($note_id) {
            // Get current time formatted with plugin timezone
            $formatted_date = hs_crm_current_time_formatted('d/m/Y H:i');
            
            wp_send_json_success(array(
                'message' => 'Note added successfully.',
                'note_id' => $note_id,
                'created_at' => current_time('mysql'),
                'formatted_date' => $formatted_date
            ));
        } else {
            wp_send_json_error(array('message' => 'Failed to add note.'));
        }
    }
    
    /**
     * AJAX handler for deleting a note
     */
    public function ajax_delete_note() {
        check_ajax_referer('hs_crm_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permission denied.'));
        }
        
        $note_id = isset($_POST['note_id']) ? intval($_POST['note_id']) : 0;
        
        if (!$note_id) {
            wp_send_json_error(array('message' => 'Invalid note ID.'));
        }
        
        // Verify the note exists before attempting to delete
        global $wpdb;
        $notes_table = $wpdb->prefix . 'hs_enquiry_notes';
        $note = $wpdb->get_row($wpdb->prepare("SELECT * FROM $notes_table WHERE id = %d", $note_id));
        
        if (!$note) {
            wp_send_json_error(array('message' => 'Note not found.'));
        }
        
        $result = HS_CRM_Database::delete_note($note_id);
        
        if ($result) {
            wp_send_json_success(array('message' => 'Note deleted successfully.'));
        } else {
            wp_send_json_error(array('message' => 'Failed to delete note.'));
        }
    }
    
    /**
     * AJAX handler for updating truck assignment
     */
    public function ajax_update_truck_assignment() {
        check_ajax_referer('hs_crm_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permission denied.'));
        }
        
        $enquiry_id = isset($_POST['enquiry_id']) ? intval($_POST['enquiry_id']) : 0;
        $truck_id = isset($_POST['truck_id']) ? intval($_POST['truck_id']) : null;
        
        if (!$enquiry_id) {
            wp_send_json_error(array('message' => 'Invalid enquiry ID.'));
        }
        
        // Allow null truck_id to unassign truck
        if ($truck_id === 0) {
            $truck_id = null;
        }
        
        // Update truck assignment
        $result = HS_CRM_Database::update_enquiry($enquiry_id, array('truck_id' => $truck_id));
        
        if ($result !== false) {
            // Add note about truck assignment
            if ($truck_id) {
                $truck = HS_CRM_Database::get_truck($truck_id);
                if ($truck) {
                    HS_CRM_Database::add_note($enquiry_id, 'Truck assigned: ' . $truck->name);
                }
            } else {
                HS_CRM_Database::add_note($enquiry_id, 'Truck unassigned');
            }
            
            wp_send_json_success(array('message' => 'Truck assignment updated successfully.'));
        } else {
            wp_send_json_error(array('message' => 'Failed to update truck assignment.'));
        }
    }
}
