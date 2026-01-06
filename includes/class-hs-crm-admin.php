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
        add_action('wp_ajax_hs_crm_delete_enquiry', array($this, 'ajax_delete_enquiry'));
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            'Marcus Furniture Enquiries',
            'Enquiries',
            'view_crm_dashboard', // Changed from 'manage_options' to custom capability
            'hs-crm-enquiries',
            array($this, 'render_admin_page'),
            'dashicons-truck',
            26
        );
    }
    
    /**
     * Render admin page
     */
    public function render_admin_page() {
        if (!current_user_can('view_crm_dashboard')) { // Changed from 'manage_options'
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
                    
                    // Determine form type - prioritize job_type field if set, otherwise infer from fields
                    $is_pickup_delivery = false;
                    $form_type_label = 'Moving House'; // Default
                    $form_source_label = ''; // The actual form name (for Gravity Forms)
                    
                    if (!empty($enquiry->job_type)) {
                        // Use the job_type field if it's set (from Gravity Forms integration)
                        $form_type_label = $enquiry->job_type;
                        $is_pickup_delivery = ($enquiry->job_type === 'Pickup/Delivery');
                    } else {
                        // Fallback: infer from fields for older entries or non-Gravity Forms submissions
                        if (!empty($enquiry->delivery_from_address) || !empty($enquiry->delivery_to_address) || !empty($enquiry->items_being_collected)) {
                            $is_pickup_delivery = true;
                            $form_type_label = 'Pickup/Delivery';
                        }
                    }
                    
                    // Get the actual form name if available (from Gravity Forms)
                    if (!empty($enquiry->source_form_name)) {
                        $form_source_label = $enquiry->source_form_name;
                    }
                    
                    // Set header color based on type
                    $header_bg_color = $is_pickup_delivery ? '#FF8C00' : '#061257';
                    
                    $row_index++;
                ?>
                    <!-- Individual table for each enquiry -->
                    <table class="wp-list-table widefat fixed hs-crm-enquiries-table hs-crm-single-enquiry-table">
                        <tbody>
                            <!-- Customer Header Row -->
                            <tr class="hs-crm-customer-header-row <?php echo $row_class; ?>" style="background: <?php echo $header_bg_color; ?> !important;">
                                <th style="width: 12%;">
                                    Source & Dates
                                </th>
                                <th style="width: 16%;">Contact & Address</th>
                                <th style="width: 12%;">Moving From</th>
                                <th style="width: 12%;">Moving To</th>
                                <th style="width: 14%;">Items & Instructions</th>
                                <th style="width: 8%;">Truck</th>
                                <th style="width: 18%;">Status</th>
                                <th style="width: 8%;">Edit / Delete</th>
                            </tr>
                            <tr class="hs-crm-enquiry-row <?php echo $row_class; ?>" data-enquiry-id="<?php echo esc_attr($enquiry->id); ?>">
                                <td>
                                    <span class="hs-crm-source-badge"><?php echo esc_html(ucfirst($enquiry->contact_source)); ?></span><br>
                                    <?php if (!empty($form_source_label)): ?>
                                        <small style="color: #0073aa;"><strong><?php echo esc_html($form_source_label); ?></strong></small><br>
                                    <?php endif; ?>
                                    <?php if (!empty($enquiry->gravity_forms_entry_id) && !empty($enquiry->gravity_forms_form_id)): ?>
                                        <small>
                                            <a href="<?php echo esc_url(admin_url('admin.php?page=gf_entries&view=entry&id=' . $enquiry->gravity_forms_form_id . '&lid=' . $enquiry->gravity_forms_entry_id)); ?>" 
                                               target="_blank" 
                                               style="color: #2271b1; text-decoration: none;">
                                                📋 View Form Entry ↗
                                            </a>
                                        </small><br>
                                    <?php endif; ?>
                                    <small style="color: #666;"><strong><?php echo esc_html($form_type_label); ?></strong></small><br>
                                    <small style="color: #666;">Contact: <?php echo esc_html(hs_crm_format_date($enquiry->created_at, 'd/m/Y')); ?></small><br>
                                    <?php if (!empty($enquiry->move_date)): ?>
                                        <div class="hs-crm-move-date">
                                            <strong>Move:</strong> <span class="hs-crm-date-highlight"><?php echo esc_html(date('d/m/Y', strtotime($enquiry->move_date))); ?></span>
                                            <?php if (!empty($enquiry->move_time)): ?>
                                                <span class="hs-crm-time-highlight"><?php echo esc_html(date('g:iA', strtotime($enquiry->move_time))); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <small style="color: #999;">Move: Not set</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong class="hs-crm-editable-name" data-enquiry-id="<?php echo esc_attr($enquiry->id); ?>">
                                        <?php echo esc_html($enquiry->first_name . ' ' . $enquiry->last_name); ?>
                                    </strong><br>
                                    <small style="color: #666;"><?php echo esc_html($enquiry->phone); ?> | <?php echo esc_html($enquiry->email); ?></small>
                                    <?php if (!empty($enquiry->delivery_from_address) || !empty($enquiry->delivery_to_address)): ?>
                                        <br><small style="color: #0066cc;">
                                        <?php if (!empty($enquiry->delivery_from_address)): ?>
                                            <strong>From:</strong> <?php echo esc_html($enquiry->delivery_from_address); ?>
                                        <?php endif; ?>
                                        <?php if (!empty($enquiry->delivery_to_address)): ?>
                                            <br><strong>To:</strong> <?php echo esc_html($enquiry->delivery_to_address); ?>
                                        <?php endif; ?>
                                        </small>
                                    <?php endif; ?>
                                    <?php if (!empty($enquiry->suburb)): ?>
                                        <br><small style="color: #666;">Suburb: <?php echo esc_html($enquiry->suburb); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php 
                                    // Moving From details
                                    $from_details = array();
                                    if (!empty($enquiry->delivery_from_address)) {
                                        $from_details[] = '<strong>' . esc_html($enquiry->delivery_from_address) . '</strong>';
                                    }
                                    if (!empty($enquiry->stairs_from)) {
                                        $from_details[] = 'Stairs: ' . esc_html($enquiry->stairs_from);
                                    }
                                    if ($is_pickup_delivery && !empty($enquiry->stairs) && empty($enquiry->stairs_from)) {
                                        // Fallback for old entries
                                        $from_details[] = 'Stairs: ' . esc_html($enquiry->stairs);
                                    }
                                    if (empty($from_details)) {
                                        echo '<em style="color: #999;">Not set</em>';
                                    } else {
                                        echo '<small>' . implode('<br>', $from_details) . '</small>';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php 
                                    // Moving To details
                                    $to_details = array();
                                    if (!empty($enquiry->delivery_to_address)) {
                                        $to_details[] = '<strong>' . esc_html($enquiry->delivery_to_address) . '</strong>';
                                    }
                                    if (!empty($enquiry->stairs_to)) {
                                        $to_details[] = 'Stairs: ' . esc_html($enquiry->stairs_to);
                                    }
                                    if (!empty($enquiry->number_of_bedrooms)) {
                                        $to_details[] = esc_html($enquiry->number_of_bedrooms) . ' bedrooms';
                                    }
                                    if (!empty($enquiry->number_of_rooms)) {
                                        $to_details[] = esc_html($enquiry->number_of_rooms) . ' total rooms';
                                    }
                                    if (!empty($enquiry->property_notes)) {
                                        $to_details[] = 'Notes: ' . esc_html(wp_trim_words($enquiry->property_notes, 10));
                                    }
                                    if (empty($to_details)) {
                                        echo '<em style="color: #999;">Not set</em>';
                                    } else {
                                        echo '<small>' . implode('<br>', $to_details) . '</small>';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php 
                                    $items_details = array();
                                    if ($is_pickup_delivery) {
                                        // Show items being collected
                                        if (!empty($enquiry->items_being_collected)) {
                                            $items_details[] = '<strong>Items:</strong> ' . esc_html(wp_trim_words($enquiry->items_being_collected, 15));
                                        }
                                        if (!empty($enquiry->furniture_moved_question)) {
                                            $items_details[] = '<strong>Furniture moved?</strong> ' . esc_html($enquiry->furniture_moved_question);
                                        }
                                    }
                                    // Show special instructions for all types
                                    if (!empty($enquiry->special_instructions)) {
                                        $items_details[] = '<strong>Instructions:</strong> ' . esc_html(wp_trim_words($enquiry->special_instructions, 15));
                                    }
                                    if (empty($items_details)) {
                                        echo '<em style="color: #999;">-</em>';
                                    } else {
                                        echo '<small>' . implode('<br>', $items_details) . '</small>';
                                    }
                                    ?>
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
                                    <!-- Current Status Display -->
                                    <div style="margin-bottom: 10px;">
                                        <span class="hs-crm-status-badge status-<?php echo esc_attr(strtolower(str_replace(' ', '-', $enquiry->status))); ?>">
                                            <?php echo esc_html($enquiry->status); ?>
                                        </span>
                                    </div>
                                    
                                    <!-- Status Update Radio Buttons -->
                                    <div class="hs-crm-status-radio-group" data-enquiry-id="<?php echo esc_attr($enquiry->id); ?>" data-current-status="<?php echo esc_attr($enquiry->status); ?>">
                                        <label style="display: block; margin: 4px 0; font-size: 12px; cursor: pointer;">
                                            <input type="radio" name="status-<?php echo esc_attr($enquiry->id); ?>" value="First Contact" <?php checked($enquiry->status, 'First Contact'); ?>>
                                            I've contacted them
                                        </label>
                                        <label style="display: block; margin: 4px 0; font-size: 12px; cursor: pointer;">
                                            <input type="radio" name="status-<?php echo esc_attr($enquiry->id); ?>" value="Quote Sent" <?php checked($enquiry->status, 'Quote Sent'); ?>>
                                            I've sent a quote
                                        </label>
                                        <label style="display: block; margin: 4px 0; font-size: 12px; cursor: pointer;">
                                            <input type="radio" name="status-<?php echo esc_attr($enquiry->id); ?>" value="Booking Confirmed" <?php checked($enquiry->status, 'Booking Confirmed'); ?>>
                                            The booking has been confirmed
                                        </label>
                                        <label style="display: block; margin: 4px 0; font-size: 12px; cursor: pointer;">
                                            <input type="radio" name="status-<?php echo esc_attr($enquiry->id); ?>" value="Completed" <?php checked($enquiry->status, 'Completed'); ?>>
                                            The job has been done
                                        </label>
                                    </div>
                                </td>
                                <td>
                                    <button type="button" class="button button-small hs-crm-edit-enquiry" data-enquiry-id="<?php echo esc_attr($enquiry->id); ?>">Edit</button>
                                    <button type="button" class="button button-small hs-crm-delete-enquiry" data-enquiry-id="<?php echo esc_attr($enquiry->id); ?>" style="margin-top: 3px;">Delete</button>
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
            <div class="hs-crm-modal-content" style="max-width: 900px;">
                <span class="hs-crm-modal-close">&times;</span>
                <h2 id="enquiry-modal-title">Add New Enquiry</h2>
                
                <!-- Job Type Selector with Radio Buttons -->
                <div class="hs-crm-form-group" style="border: 2px solid #0073aa; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <label style="font-size: 16px; font-weight: 600; margin-bottom: 10px; display: block;">
                        Select Enquiry Type:
                    </label>
                    <div style="display: flex; gap: 30px;">
                        <label style="display: flex; align-items: center; cursor: pointer;">
                            <input type="radio" name="enquiry-type" id="enquiry-type-moving" value="moving-house" style="margin-right: 8px;">
                            <strong>Moving House</strong>
                        </label>
                        <label style="display: flex; align-items: center; cursor: pointer;">
                            <input type="radio" name="enquiry-type" id="enquiry-type-pickup" value="pickup-delivery" checked style="margin-right: 8px;">
                            <strong>Pickup/Delivery</strong>
                        </label>
                    </div>
                </div>
                
                <!-- Gravity Forms Embed Container -->
                <div id="gravity-forms-container">
                    <?php if (class_exists('GFForms')): ?>
                        <!-- Moving House Form (ID 11) - Initially Hidden -->
                        <div id="gf-moving-house" style="display: none;">
                            <?php echo do_shortcode('[gravityform id="11" title="false" description="false" ajax="true"]'); ?>
                        </div>
                        
                        <!-- Pickup/Delivery Form (ID 8) - Initially Visible -->
                        <div id="gf-pickup-delivery">
                            <?php echo do_shortcode('[gravityform id="8" title="false" description="false" ajax="true"]'); ?>
                        </div>
                    <?php else: ?>
                        <p style="color: #d63638; background: #fcf0f1; padding: 10px; border-radius: 4px;">
                            <strong>Note:</strong> Gravity Forms plugin is not active. Please activate Gravity Forms to use this feature, or manually enter enquiry details below.
                        </p>
                    <?php endif; ?>
                </div>
                
                <!-- Fallback Manual Entry Form (shown if Gravity Forms not available or for editing) -->
                <form id="hs-crm-enquiry-form">
                    <input type="hidden" id="enquiry-id" name="enquiry_id">
                    <input type="hidden" id="enquiry-job-type" name="job_type" value="Pickup/Delivery">
                    
                    <!-- Common Fields -->
                    <div class="hs-crm-form-group">
                        <label for="enquiry-first-name">First Name *</label>
                        <input type="text" id="enquiry-first-name" name="first_name" placeholder="First Name" required>
                    </div>
                    
                    <div class="hs-crm-form-group">
                        <label for="enquiry-last-name">Last Name *</label>
                        <input type="text" id="enquiry-last-name" name="last_name" placeholder="Last Name" required>
                    </div>
                    
                    <div class="hs-crm-form-group">
                        <label for="enquiry-phone">Phone Number *</label>
                        <input type="tel" id="enquiry-phone" name="phone" required>
                    </div>
                    
                    <div class="hs-crm-form-group">
                        <label for="enquiry-email">Email *</label>
                        <input type="email" id="enquiry-email" name="email" required>
                    </div>
                    
                    <div class="hs-crm-form-group">
                        <label for="enquiry-move-date">Moving/Delivery Date *</label>
                        <input type="date" id="enquiry-move-date" name="move_date" required>
                    </div>
                    
                    <div class="hs-crm-form-group">
                        <label for="enquiry-move-time">Preferred Time</label>
                        <input type="time" id="enquiry-move-time" name="move_time">
                    </div>
                    
                    <!-- Address Fields - shown for both types -->
                    <div class="hs-crm-form-group">
                        <label for="enquiry-from-address">From Address *</label>
                        <input type="text" id="enquiry-from-address" name="delivery_from_address" placeholder="Street Address" required>
                    </div>
                    
                    <div class="hs-crm-form-group">
                        <label for="enquiry-to-address">To Address *</label>
                        <input type="text" id="enquiry-to-address" name="delivery_to_address" placeholder="Street Address" required>
                    </div>
                    
                    <!-- Moving House Fields -->
                    <div id="moving-house-fields" style="display: none;">
                        <h3 style="margin: 20px 0 10px 0; border-top: 2px solid #0073aa; padding-top: 15px;">Moving House Details</h3>
                        
                        <div class="hs-crm-form-group">
                            <label for="enquiry-moving-from">Moving from: *</label>
                            <input type="text" id="enquiry-moving-from" name="delivery_from_address" placeholder="Street Address">
                        </div>
                        
                        <div class="hs-crm-form-group">
                            <label for="enquiry-stairs-from">Stairs involved? (From) *</label>
                            <select id="enquiry-stairs-from" name="stairs_from">
                                <option value="">Select...</option>
                                <option value="Yes">Yes</option>
                                <option value="No">No</option>
                            </select>
                        </div>
                        
                        <div class="hs-crm-form-group">
                            <label for="enquiry-moving-to">Moving to: *</label>
                            <input type="text" id="enquiry-moving-to" name="delivery_to_address" placeholder="Street Address">
                        </div>
                        
                        <div class="hs-crm-form-group">
                            <label for="enquiry-stairs-to">Stairs involved? (To) *</label>
                            <select id="enquiry-stairs-to" name="stairs_to">
                                <option value="">Select...</option>
                                <option value="Yes">Yes</option>
                                <option value="No">No</option>
                            </select>
                        </div>
                        
                        <div class="hs-crm-form-group">
                            <label for="enquiry-move-type">What's the type of your move? *</label>
                            <select id="enquiry-move-type" name="move_type">
                                <option value="">Select...</option>
                                <option value="Residential">Residential</option>
                                <option value="Office">Office</option>
                            </select>
                        </div>
                        
                        <div class="hs-crm-form-group">
                            <label for="enquiry-move-size">What's the size of your move? *</label>
                            <select id="enquiry-move-size" name="house_size">
                                <option value="">Select...</option>
                                <option value="1 Room Worth of Items Only">1 Room Worth of Items Only</option>
                                <option value="1 BR House - Big Items Only">1 BR House - Big Items Only</option>
                                <option value="1 BR House - Big Items and Boxes">1 BR House - Big Items and Boxes</option>
                                <option value="2 BR House - Big Items Only">2 BR House - Big Items Only</option>
                                <option value="2 BR House - Big Items and Boxes">2 BR House - Big Items and Boxes</option>
                                <option value="3 BR House - Big Items Only">3 BR House - Big Items Only</option>
                                <option value="3 BR House - Big Items and Boxes">3 BR House - Big Items and Boxes</option>
                                <option value="4 BR Houses or above">4 BR Houses or above</option>
                            </select>
                        </div>
                        
                        <div class="hs-crm-form-group">
                            <label for="enquiry-additional-info">Additional info</label>
                            <textarea id="enquiry-additional-info" name="property_notes" rows="3" placeholder="Any additional information..."></textarea>
                        </div>
                        
                        <div class="hs-crm-form-group">
                            <label for="enquiry-outdoor-plants">Any outdoor plants? *</label>
                            <select id="enquiry-outdoor-plants" name="outdoor_plants">
                                <option value="">Select...</option>
                                <option value="Yes">Yes</option>
                                <option value="No">No</option>
                            </select>
                        </div>
                        
                        <div class="hs-crm-form-group">
                            <label for="enquiry-oversize-items">Any oversize items such as piano, double-door fridge or spa? *</label>
                            <select id="enquiry-oversize-items" name="oversize_items">
                                <option value="">Select...</option>
                                <option value="Yes">Yes</option>
                                <option value="No">No</option>
                            </select>
                        </div>
                        
                        <div class="hs-crm-form-group">
                            <label for="enquiry-driveway-concerns">Anything that could be a concern with the driveway? *</label>
                            <select id="enquiry-driveway-concerns" name="driveway_concerns">
                                <option value="">Select...</option>
                                <option value="Yes">Yes</option>
                                <option value="No">No</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Pickup/Delivery Fields -->
                    <div id="pickup-delivery-fields">
                        <h3 style="margin: 20px 0 10px 0; border-top: 2px solid #FF8C00; padding-top: 15px;">Pickup/Delivery Details</h3>
                        
                        <div class="hs-crm-form-group">
                            <label for="enquiry-alt-date">Alternate delivery date</label>
                            <input type="date" id="enquiry-alt-date" name="alternate_date">
                        </div>
                        
                        <div class="hs-crm-form-group">
                            <label for="enquiry-pickup-from">Where is the item(s) being collected from? *</label>
                            <input type="text" id="enquiry-pickup-from" name="delivery_from_address" placeholder="Street Address">
                        </div>
                        
                        <div class="hs-crm-form-group">
                            <label for="enquiry-stairs-pickup">Stairs involved? (Pickup) *</label>
                            <select id="enquiry-stairs-pickup" name="stairs_from">
                                <option value="">Select...</option>
                                <option value="Yes">Yes</option>
                                <option value="No">No</option>
                            </select>
                        </div>
                        
                        <div class="hs-crm-form-group">
                            <label for="enquiry-deliver-to">Where is the item(s) being delivered to? *</label>
                            <input type="text" id="enquiry-deliver-to" name="delivery_to_address" placeholder="Street Address">
                        </div>
                        
                        <div class="hs-crm-form-group">
                            <label for="enquiry-stairs-delivery">Stairs involved? (Delivery) *</label>
                            <select id="enquiry-stairs-delivery" name="stairs_to">
                                <option value="">Select...</option>
                                <option value="Yes">Yes</option>
                                <option value="No">No</option>
                            </select>
                        </div>
                        
                        <div class="hs-crm-form-group">
                            <label for="enquiry-items-collected">What item(s) are being collected? *</label>
                            <textarea id="enquiry-items-collected" name="items_being_collected" rows="2" placeholder="E.g., Sofa, Dining Table, etc."></textarea>
                        </div>
                        
                        <div class="hs-crm-form-group">
                            <label for="enquiry-special-instructions">Any special Instructions?</label>
                            <textarea id="enquiry-special-instructions" name="special_instructions" rows="2"></textarea>
                        </div>
                        
                        <div class="hs-crm-form-group">
                            <label for="enquiry-assembly-help">Do you need help assembling the item we're collecting?</label>
                            <select id="enquiry-assembly-help" name="assembly_help">
                                <option value="">Select...</option>
                                <option value="Yes">Yes</option>
                                <option value="No">No</option>
                            </select>
                        </div>
                        
                        <div class="hs-crm-form-group">
                            <label for="enquiry-furniture-moved">Do you need any existing furniture moved? *</label>
                            <select id="enquiry-furniture-moved" name="furniture_moved_question">
                                <option value="">Select...</option>
                                <option value="Yes">Yes</option>
                                <option value="No">No</option>
                            </select>
                        </div>
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
        
        if (!current_user_can('manage_crm_enquiries')) {
            wp_send_json_error(array('message' => 'Permission denied.'));
        }
        
        $data = array(
            'first_name' => isset($_POST['first_name']) ? sanitize_text_field($_POST['first_name']) : '',
            'last_name' => isset($_POST['last_name']) ? sanitize_text_field($_POST['last_name']) : '',
            'email' => isset($_POST['email']) ? sanitize_email($_POST['email']) : '',
            'phone' => isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '',
            'delivery_from_address' => isset($_POST['delivery_from_address']) ? sanitize_textarea_field($_POST['delivery_from_address']) : '',
            'delivery_to_address' => isset($_POST['delivery_to_address']) ? sanitize_textarea_field($_POST['delivery_to_address']) : '',
            'number_of_bedrooms' => isset($_POST['number_of_bedrooms']) ? sanitize_text_field($_POST['number_of_bedrooms']) : '',
            'number_of_rooms' => isset($_POST['number_of_rooms']) ? sanitize_text_field($_POST['number_of_rooms']) : '',
            'stairs' => isset($_POST['stairs']) ? sanitize_text_field($_POST['stairs']) : '',
            'stairs_from' => isset($_POST['stairs_from']) ? sanitize_text_field($_POST['stairs_from']) : '',
            'stairs_to' => isset($_POST['stairs_to']) ? sanitize_text_field($_POST['stairs_to']) : '',
            'items_being_collected' => isset($_POST['items_being_collected']) ? sanitize_textarea_field($_POST['items_being_collected']) : '',
            'furniture_moved_question' => isset($_POST['furniture_moved_question']) ? sanitize_text_field($_POST['furniture_moved_question']) : '',
            'property_notes' => isset($_POST['property_notes']) ? sanitize_textarea_field($_POST['property_notes']) : '',
            'special_instructions' => isset($_POST['special_instructions']) ? sanitize_textarea_field($_POST['special_instructions']) : '',
            'job_type' => isset($_POST['job_type']) ? sanitize_text_field($_POST['job_type']) : '',
            'move_type' => isset($_POST['move_type']) ? sanitize_text_field($_POST['move_type']) : '',
            'house_size' => isset($_POST['house_size']) ? sanitize_text_field($_POST['house_size']) : '',
            'outdoor_plants' => isset($_POST['outdoor_plants']) ? sanitize_text_field($_POST['outdoor_plants']) : '',
            'oversize_items' => isset($_POST['oversize_items']) ? sanitize_text_field($_POST['oversize_items']) : '',
            'driveway_concerns' => isset($_POST['driveway_concerns']) ? sanitize_text_field($_POST['driveway_concerns']) : '',
            'assembly_help' => isset($_POST['assembly_help']) ? sanitize_text_field($_POST['assembly_help']) : '',
            'move_date' => isset($_POST['move_date']) ? sanitize_text_field($_POST['move_date']) : '',
            'move_time' => isset($_POST['move_time']) ? sanitize_text_field($_POST['move_time']) : '',
            'alternate_date' => isset($_POST['alternate_date']) ? sanitize_text_field($_POST['alternate_date']) : '',
            'contact_source' => 'manual',
        );
        
        // Validate required fields
        if (empty($data['first_name']) || empty($data['last_name']) || empty($data['email']) || empty($data['phone'])) {
            wp_send_json_error(array('message' => 'Please fill in all required fields (First Name, Last Name, Email, Phone).'));
        }
        
        $enquiry_id = HS_CRM_Database::insert_enquiry($data);
        
        if ($enquiry_id) {
            // Add note about manual creation
            $job_type = !empty($data['job_type']) ? sanitize_text_field($data['job_type']) : 'Unknown';
            $note_text = sprintf('Enquiry manually created via admin panel - Job Type: %s', esc_html($job_type));
            HS_CRM_Database::add_note($enquiry_id, $note_text);
            
            wp_send_json_success(array(
                'message' => 'Enquiry created successfully.',
                'enquiry_id' => $enquiry_id
            ));
        } else {
            global $wpdb;
            wp_send_json_error(array(
                'message' => 'Failed to create enquiry. Please check all required fields.',
                'debug' => $wpdb->last_error
            ));
        }
    }
    
    /**
     * AJAX handler for updating enquiry
     */
    public function ajax_update_enquiry() {
        check_ajax_referer('hs_crm_nonce', 'nonce');
        
        if (!current_user_can('manage_crm_enquiries')) {
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
        if (isset($_POST['delivery_from_address'])) {
            $data['delivery_from_address'] = sanitize_textarea_field($_POST['delivery_from_address']);
        }
        if (isset($_POST['delivery_to_address'])) {
            $data['delivery_to_address'] = sanitize_textarea_field($_POST['delivery_to_address']);
        }
        if (isset($_POST['number_of_bedrooms'])) {
            $data['number_of_bedrooms'] = sanitize_text_field($_POST['number_of_bedrooms']);
        }
        if (isset($_POST['number_of_rooms'])) {
            $data['number_of_rooms'] = sanitize_text_field($_POST['number_of_rooms']);
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
        if (isset($_POST['items_being_collected'])) {
            $data['items_being_collected'] = sanitize_textarea_field($_POST['items_being_collected']);
        }
        if (isset($_POST['furniture_moved_question'])) {
            $data['furniture_moved_question'] = sanitize_text_field($_POST['furniture_moved_question']);
        }
        if (isset($_POST['property_notes'])) {
            $data['property_notes'] = sanitize_textarea_field($_POST['property_notes']);
        }
        if (isset($_POST['special_instructions'])) {
            $data['special_instructions'] = sanitize_textarea_field($_POST['special_instructions']);
        }
        if (isset($_POST['job_type'])) {
            $data['job_type'] = sanitize_text_field($_POST['job_type']);
        }
        if (isset($_POST['move_type'])) {
            $data['move_type'] = sanitize_text_field($_POST['move_type']);
        }
        if (isset($_POST['house_size'])) {
            $data['house_size'] = sanitize_text_field($_POST['house_size']);
        }
        if (isset($_POST['outdoor_plants'])) {
            $data['outdoor_plants'] = sanitize_text_field($_POST['outdoor_plants']);
        }
        if (isset($_POST['oversize_items'])) {
            $data['oversize_items'] = sanitize_text_field($_POST['oversize_items']);
        }
        if (isset($_POST['driveway_concerns'])) {
            $data['driveway_concerns'] = sanitize_text_field($_POST['driveway_concerns']);
        }
        if (isset($_POST['assembly_help'])) {
            $data['assembly_help'] = sanitize_text_field($_POST['assembly_help']);
        }
        if (isset($_POST['move_date'])) {
            $data['move_date'] = sanitize_text_field($_POST['move_date']);
        }
        if (isset($_POST['move_time'])) {
            $data['move_time'] = sanitize_text_field($_POST['move_time']);
        }
        if (isset($_POST['alternate_date'])) {
            $data['alternate_date'] = sanitize_text_field($_POST['alternate_date']);
        }
        
        $result = HS_CRM_Database::update_enquiry($enquiry_id, $data);
        
        if ($result !== false) {
            wp_send_json_success(array('message' => 'Enquiry updated successfully.'));
        } else {
            global $wpdb;
            wp_send_json_error(array(
                'message' => 'Failed to update enquiry.',
                'debug' => $wpdb->last_error
            ));
        }
    }
    
    /**
     * AJAX handler for status update
     */
    public function ajax_update_status() {
        check_ajax_referer('hs_crm_nonce', 'nonce');
        
        // Prevent caching of AJAX responses
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        if (!current_user_can('manage_crm_enquiries')) {
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
        
        if (!current_user_can('manage_crm_enquiries')) {
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
        
        if (!current_user_can('manage_crm_enquiries')) {
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
        
        if (!current_user_can('manage_crm_enquiries')) {
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
        
        if (!current_user_can('manage_crm_enquiries')) {
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
        
        if (!current_user_can('manage_crm_enquiries')) {
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
    
    /**
     * AJAX handler for deleting (archiving) an enquiry
     */
    public function ajax_delete_enquiry() {
        check_ajax_referer('hs_crm_nonce', 'nonce');
        
        if (!current_user_can('manage_crm_enquiries')) {
            wp_send_json_error(array('message' => 'Permission denied.'));
        }
        
        $enquiry_id = isset($_POST['enquiry_id']) ? intval($_POST['enquiry_id']) : 0;
        
        if (!$enquiry_id) {
            wp_send_json_error(array('message' => 'Invalid enquiry ID.'));
        }
        
        // Archive the enquiry by setting status to 'Archived'
        $result = HS_CRM_Database::update_status($enquiry_id, 'Archived');
        
        if ($result) {
            // Add note about archiving
            HS_CRM_Database::add_note($enquiry_id, 'Enquiry archived (deleted from active view)');
            
            wp_send_json_success(array('message' => 'Enquiry archived successfully.'));
        } else {
            wp_send_json_error(array('message' => 'Failed to archive enquiry.'));
        }
    }
}
