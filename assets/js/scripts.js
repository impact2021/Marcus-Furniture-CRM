jQuery(document).ready(function($) {
    
    // Initialize Google Places Autocomplete for address field (NZ only)
    function initAddressAutocomplete() {
        if (typeof google !== 'undefined' && google.maps && google.maps.places) {
            var addressInput = document.getElementById('hs_address');
            if (addressInput) {
                var autocomplete = new google.maps.places.Autocomplete(addressInput, {
                    componentRestrictions: { country: 'nz' }, // Restrict to New Zealand only
                    fields: ['formatted_address', 'geometry', 'name'],
                    types: ['address']
                });
                
                autocomplete.addListener('place_changed', function() {
                    var place = autocomplete.getPlace();
                    if (place.formatted_address) {
                        $(addressInput).val(place.formatted_address);
                    }
                });
            }
        }
    }
    
    // Try to initialize immediately
    initAddressAutocomplete();
    
    // Also try after a short delay in case Google Maps API is still loading
    setTimeout(initAddressAutocomplete, 1000);
    
    // Handle contact form submission
    $('#hs-crm-contact-form').on('submit', function(e) {
        e.preventDefault();
        
        var $form = $(this);
        var $messages = $('.hs-crm-form-messages');
        var $submitBtn = $form.find('.hs-crm-submit-btn');
        
        $submitBtn.prop('disabled', true).text('Submitting...');
        $messages.html('');
        
        $.ajax({
            url: hsCrmAjax.ajaxurl,
            type: 'POST',
            data: $form.serialize() + '&action=hs_crm_submit_form',
            success: function(response) {
                if (response.success) {
                    // Redirect to thank you page
                    window.location.href = hsCrmAjax.thankYouUrl;
                } else {
                    $messages.html('<div class="hs-crm-error">' + response.data.message + '</div>');
                    $submitBtn.prop('disabled', false).text('Submit Enquiry');
                }
            },
            error: function() {
                $messages.html('<div class="hs-crm-error">An error occurred. Please try again.</div>');
                $submitBtn.prop('disabled', false).text('Submit Enquiry');
            }
        });
    });
    
    // Admin page functionality
    if ($('.hs-crm-admin-wrap').length > 0) {
        
        // Handle status change
        $('.hs-crm-status-select').on('change', function() {
            var $select = $(this);
            var enquiryId = $select.data('enquiry-id');
            var newStatus = $select.val();
            var oldStatus = $select.data('current-status');
            
            if (!newStatus) {
                return;
            }
            
            if (!confirm('Are you sure you want to change the status to "' + newStatus + '"?')) {
                $select.val('');
                return;
            }
            
            $.ajax({
                url: hsCrmAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'hs_crm_update_status',
                    nonce: hsCrmAjax.nonce,
                    enquiry_id: enquiryId,
                    status: newStatus,
                    old_status: oldStatus
                },
                success: function(response) {
                    if (response.success) {
                        // Update status badge
                        var $row = $select.closest('tr');
                        var statusClass = newStatus.toLowerCase().replace(/\s+/g, '-');
                        $row.find('.hs-crm-status-badge')
                            .removeClass()
                            .addClass('hs-crm-status-badge status-' + statusClass)
                            .text(newStatus);
                        
                        // Update current status
                        $select.data('current-status', newStatus);
                        $select.val('');
                        
                        // Add the note row if note data is returned
                        if (response.data.note) {
                            var note = response.data.note;
                            // Use the server-provided formatted date (already in Auckland timezone)
                            var formattedDate = note.formatted_date || '';
                            
                            // Get the row class from the current enquiry row with fallback
                            var rowClasses = $row.attr('class');
                            var rowClassMatch = rowClasses ? rowClasses.match(/hs-crm-(even|odd)-row/) : null;
                            var rowClass = rowClassMatch ? rowClassMatch[0] : 'hs-crm-even-row';
                            
                            // Find the add note row for this specific enquiry using enquiry-id
                            var $addNoteRow = $row.closest('tbody').find('.hs-crm-add-note-row[data-enquiry-id="' + enquiryId + '"]');
                            
                            // Create the new note row HTML
                            var $noteRow = $('<tr class="hs-crm-note-row ' + rowClass + '" data-note-id="' + note.id + '" data-enquiry-id="' + enquiryId + '">' +
                                '<td class="hs-crm-note-date">' + formattedDate + '</td>' +
                                '<td colspan="3" class="hs-crm-note-content">' +
                                    '<div class="hs-crm-note-text">' + $('<div>').text(note.text).html() + '</div>' +
                                '</td>' +
                                '<td colspan="2" class="hs-crm-note-actions">' +
                                    '<button type="button" class="button button-small hs-crm-delete-note" data-note-id="' + note.id + '">Delete</button>' +
                                '</td>' +
                            '</tr>');
                            
                            // Insert the note row before the add note row
                            $noteRow.insertBefore($addNoteRow).hide().fadeIn(300);
                        }
                        
                        alert(response.data.message);
                    } else {
                        alert('Error: ' + response.data.message);
                    }
                },
                error: function() {
                    alert('An error occurred while updating the status.');
                }
            });
        });
        
        // Handle truck assignment
        $('.hs-crm-truck-select').on('change', function() {
            var $select = $(this);
            var enquiryId = $select.data('enquiry-id');
            var truckId = $select.val();
            
            $.ajax({
                url: hsCrmAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'hs_crm_update_truck_assignment',
                    nonce: hsCrmAjax.nonce,
                    enquiry_id: enquiryId,
                    truck_id: truckId
                },
                success: function(response) {
                    if (response.success) {
                        $select.data('current-truck', truckId);
                        // Optional: Show success message
                        // alert(response.data.message);
                    } else {
                        alert('Error: ' + response.data.message);
                    }
                },
                error: function() {
                    alert('An error occurred while updating truck assignment.');
                }
            });
        });
        
        // Handle notes toggle
        $('.hs-crm-notes-toggle-row').on('click', function() {
            var $row = $(this);
            var enquiryId = $row.data('enquiry-id');
            var $icon = $row.find('.hs-crm-notes-toggle');
            var $noteRows = $row.closest('tbody').find('.hs-crm-note-row[data-enquiry-id="' + enquiryId + '"]');
            var $addNoteRow = $row.closest('tbody').find('.hs-crm-add-note-row[data-enquiry-id="' + enquiryId + '"]');
            var $toggleText = $row.find('td');
            
            if ($noteRows.is(':visible')) {
                $noteRows.slideUp(200);
                $addNoteRow.slideUp(200);
                $icon.removeClass('dashicons-arrow-up').addClass('dashicons-arrow-down');
                $toggleText.html('<span class="hs-crm-notes-toggle dashicons dashicons-arrow-down" style="font-size: 16px; vertical-align: middle;"></span><strong>Notes (' + $noteRows.length + ')</strong> - Click to expand');
            } else {
                $noteRows.slideDown(200);
                $addNoteRow.slideDown(200);
                $icon.removeClass('dashicons-arrow-down').addClass('dashicons-arrow-up');
                $toggleText.html('<span class="hs-crm-notes-toggle dashicons dashicons-arrow-up" style="font-size: 16px; vertical-align: middle;"></span><strong>Notes (' + $noteRows.length + ')</strong> - Click to collapse');
            }
        });
        
        // Handle action dropdown (send quote/invoice/receipt/edit)
        $('.hs-crm-action-select').on('change', function() {
            var $select = $(this);
            var enquiryId = $select.data('enquiry-id');
            var actionType = $select.val();
            
            if (!actionType) {
                return;
            }
            
            // Handle edit_details action
            if (actionType === 'edit_details') {
                // Get enquiry data
                $.ajax({
                    url: hsCrmAjax.ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'hs_crm_get_enquiry',
                        nonce: hsCrmAjax.nonce,
                        enquiry_id: enquiryId
                    },
                    success: function(response) {
                        if (response.success) {
                            var enquiry = response.data.enquiry;
                            $('#enquiry-modal-title').text('Edit Enquiry Details');
                            $('#enquiry-id').val(enquiry.id);
                            $('#enquiry-first-name').val(enquiry.first_name);
                            $('#enquiry-last-name').val(enquiry.last_name);
                            $('#enquiry-email').val(enquiry.email);
                            $('#enquiry-phone').val(enquiry.phone);
                            $('#enquiry-address').val(enquiry.address);
                            $('#enquiry-pickup-address').val(enquiry.pickup_address || '');
                            $('#enquiry-dropoff-address').val(enquiry.dropoff_address || '');
                            $('#enquiry-delivery-from-address').val(enquiry.delivery_from_address || '');
                            $('#enquiry-delivery-to-address').val(enquiry.delivery_to_address || '');
                            $('#enquiry-suburb').val(enquiry.suburb || '');
                            $('#enquiry-house-size').val(enquiry.house_size || '');
                            $('#enquiry-number-of-bedrooms').val(enquiry.number_of_bedrooms || '');
                            $('#enquiry-number-of-rooms').val(enquiry.number_of_rooms || '');
                            $('#enquiry-total-rooms').val(enquiry.total_rooms || '');
                            $('#enquiry-property-notes').val(enquiry.property_notes || '');
                            $('#enquiry-stairs').val(enquiry.stairs || '');
                            $('#enquiry-move-date').val(enquiry.move_date || '');
                            $('#enquiry-move-time').val(enquiry.move_time || '');
                            $('#enquiry-booking-start-time').val(enquiry.booking_start_time || '');
                            $('#enquiry-booking-end-time').val(enquiry.booking_end_time || '');
                            $('#enquiry-contact-source').val(enquiry.contact_source);
                            $('#enquiry-status').val(enquiry.status);
                            $('#hs-crm-enquiry-modal').fadeIn();
                        }
                        $select.val('');
                    },
                    error: function() {
                        alert('An error occurred while loading enquiry data.');
                        $select.val('');
                    }
                });
                return;
            }
            
            // Handle email actions (send quote/invoice/receipt)
            // Get enquiry data from the row
            var $row = $select.closest('tr');
            
            // Fetch full enquiry data via AJAX
            $.ajax({
                url: hsCrmAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'hs_crm_get_enquiry',
                    nonce: hsCrmAjax.nonce,
                    enquiry_id: enquiryId
                },
                success: function(response) {
                    if (response.success && response.data.enquiry) {
                        showEmailModal(response.data.enquiry, actionType);
                        $select.val('');
                    } else {
                        alert('Error: Could not load enquiry data.');
                        $select.val('');
                    }
                },
                error: function() {
                    alert('An error occurred while loading enquiry data.');
                    $select.val('');
                }
            });
        });
        
        // Email modal functionality
        var $modal = $('#hs-crm-email-modal');
        
        function showEmailModal(enquiry, emailType) {
            // Try to get first name, fall back to full name, then to generic greeting
            var firstName = (enquiry.first_name != null && enquiry.first_name !== '') 
                ? enquiry.first_name 
                : (enquiry.name ? enquiry.name.split(' ')[0] : '');
            var fullName = ((enquiry.first_name || '') + ' ' + (enquiry.last_name || '')).trim() || enquiry.name || '';
            
            $('#email-enquiry-id').val(enquiry.id);
            $('#email-to').val(enquiry.email);
            $('#email-customer').val(fullName + ' - ' + enquiry.phone);
            $('#email-customer-name').val(firstName);
            $('#email-type').val(emailType);
            
            // Set title, subject and message based on email type
            var greeting = firstName ? 'Dear ' + firstName : 'Dear Customer';
            var title, subject, message;
            
            switch(emailType) {
                case 'send_quote':
                    title = 'Send Quote';
                    subject = 'Quote for Painting Services';
                    message = greeting + ',\n\nThank you for your enquiry. Please find our quote below:';
                    break;
                case 'send_invoice':
                    title = 'Send Invoice';
                    subject = 'Invoice for Painting Services';
                    message = greeting + ',\n\nThank you for your business. Please find your invoice below:';
                    break;
                case 'send_receipt':
                    title = 'Send Receipt';
                    subject = 'Receipt for Painting Services';
                    message = greeting + ',\n\nThank you for your payment. Please find your receipt below:';
                    break;
                default:
                    title = 'Send Email';
                    subject = 'Home Shield Painters';
                    message = greeting + ',\n\n';
            }
            
            $('#email-modal-title').text(title);
            $('#email-subject').val(subject);
            $('#email-message').val(message);
            
            // Reset quote table to one row
            $('#quote-items-body').html(getQuoteItemRowHtml());
            calculateQuoteTotals();
            
            $modal.fadeIn();
        }
        
        $('.hs-crm-modal-close').on('click', function() {
            $modal.fadeOut();
        });
        
        $(window).on('click', function(e) {
            if ($(e.target).is('#hs-crm-email-modal')) {
                $modal.fadeOut();
            }
        });
        
        // Quote table functionality
        function getQuoteItemRowHtml() {
            return '<tr class="quote-item-row">' +
                '<td><input type="text" class="quote-description" placeholder="e.g., 3-bedroom house move"></td>' +
                '<td><input type="number" class="quote-cost" placeholder="0.00" step="0.01" min="0"></td>' +
                '<td class="quote-gst">$0.00</td>' +
                '<td><button type="button" class="remove-quote-item button">×</button></td>' +
                '</tr>';
        }
        
        $('#add-quote-item').on('click', function() {
            $('#quote-items-body').append(getQuoteItemRowHtml());
        });
        
        $(document).on('click', '.remove-quote-item', function() {
            var $tbody = $('#quote-items-body');
            if ($tbody.find('.quote-item-row').length > 1) {
                $(this).closest('.quote-item-row').remove();
                calculateQuoteTotals();
            } else {
                alert('You must have at least one quote item.');
            }
        });
        
        $(document).on('input', '.quote-cost', function() {
            var cost = parseFloat($(this).val()) || 0;
            var gst = cost * 0.15;
            $(this).closest('tr').find('.quote-gst').text('$' + gst.toFixed(2));
            calculateQuoteTotals();
        });
        
        function calculateQuoteTotals() {
            var subtotal = 0;
            var totalGst = 0;
            
            $('.quote-item-row').each(function() {
                var cost = parseFloat($(this).find('.quote-cost').val()) || 0;
                var gst = cost * 0.15;
                subtotal += cost;
                totalGst += gst;
            });
            
            var grandTotal = subtotal + totalGst;
            
            $('#quote-subtotal').text('$' + subtotal.toFixed(2));
            $('#quote-total-gst').text('$' + totalGst.toFixed(2));
            $('#quote-total').html('<strong>$' + grandTotal.toFixed(2) + '</strong>');
        }
        
        // Handle email form submission
        $('#hs-crm-email-form').on('submit', function(e) {
            e.preventDefault();
            
            var $form = $(this);
            var quoteItems = [];
            
            $('.quote-item-row').each(function() {
                var description = $(this).find('.quote-description').val();
                var cost = $(this).find('.quote-cost').val();
                
                if (description && cost) {
                    quoteItems.push({
                        description: description,
                        cost: cost
                    });
                }
            });
            
            var formData = {
                action: 'hs_crm_send_email',
                nonce: hsCrmAjax.nonce,
                enquiry_id: $('#email-enquiry-id').val(),
                subject: $('#email-subject').val(),
                message: $('#email-message').val(),
                quote_items: quoteItems,
                email_type: $('#email-type').val()
            };
            
            $.ajax({
                url: hsCrmAjax.ajaxurl,
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        alert(response.data.message);
                        $modal.fadeOut();
                        location.reload(); // Reload to update the table
                    } else {
                        alert('Error: ' + response.data.message);
                    }
                },
                error: function() {
                    alert('An error occurred while sending the email.');
                }
            });
        });
        
        // Handle notes save (legacy support)
        $('.hs-crm-save-notes').on('click', function() {
            var $button = $(this);
            var enquiryId = $button.data('enquiry-id');
            var $textarea = $('.hs-crm-admin-notes[data-enquiry-id="' + enquiryId + '"]');
            var notes = $textarea.val();
            
            $button.prop('disabled', true).text('Saving...');
            
            $.ajax({
                url: hsCrmAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'hs_crm_save_notes',
                    nonce: hsCrmAjax.nonce,
                    enquiry_id: enquiryId,
                    notes: notes
                },
                success: function(response) {
                    if (response.success) {
                        $button.text('Saved!');
                        setTimeout(function() {
                            $button.text('Save');
                        }, 2000);
                    } else {
                        alert('Error: ' + response.data.message);
                        $button.text('Save');
                    }
                },
                error: function() {
                    alert('An error occurred while saving notes.');
                    $button.text('Save');
                },
                complete: function() {
                    $button.prop('disabled', false);
                }
            });
        });
        
        // Handle add note
        $('.hs-crm-add-note').on('click', function() {
            var $button = $(this);
            var enquiryId = $button.data('enquiry-id');
            var $textarea = $('.hs-crm-new-note[data-enquiry-id="' + enquiryId + '"]');
            var note = $textarea.val();
            
            if (!note.trim()) {
                alert('Please enter a note.');
                return;
            }
            
            $button.prop('disabled', true).text('Adding...');
            
            $.ajax({
                url: hsCrmAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'hs_crm_add_note',
                    nonce: hsCrmAjax.nonce,
                    enquiry_id: enquiryId,
                    note: note
                },
                success: function(response) {
                    if (response.success) {
                        location.reload(); // Reload to show the new note
                    } else {
                        alert('Error: ' + response.data.message);
                        $button.prop('disabled', false).text('Add Note');
                    }
                },
                error: function() {
                    alert('An error occurred while adding the note.');
                    $button.prop('disabled', false).text('Add Note');
                }
            });
        });
        
        // Handle delete note
        $('.hs-crm-delete-note').on('click', function() {
            var $button = $(this);
            var noteId = $button.data('note-id');
            
            if (!confirm('Are you sure you want to delete this note?')) {
                return;
            }
            
            $button.prop('disabled', true).text('Deleting...');
            
            $.ajax({
                url: hsCrmAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'hs_crm_delete_note',
                    nonce: hsCrmAjax.nonce,
                    note_id: noteId
                },
                success: function(response) {
                    if (response.success) {
                        // Remove the note row from the table
                        $button.closest('tr').fadeOut(300, function() {
                            $(this).remove();
                        });
                    } else {
                        alert('Error: ' + response.data.message);
                        $button.prop('disabled', false).text('Delete');
                    }
                },
                error: function() {
                    alert('An error occurred while deleting the note.');
                    $button.prop('disabled', false).text('Delete');
                }
            });
        });
        
        // Handle "Add New Enquiry" button
        $('#hs-crm-add-new-enquiry').on('click', function() {
            $('#enquiry-modal-title').text('Add New Enquiry');
            $('#hs-crm-enquiry-form')[0].reset();
            $('#enquiry-id').val('');
            $('#hs-crm-enquiry-modal').fadeIn();
        });
        
        // Handle enquiry form submission (add/edit)
        $('#hs-crm-enquiry-form').on('submit', function(e) {
            e.preventDefault();
            
            var $form = $(this);
            var enquiryId = $('#enquiry-id').val();
            var actionName = enquiryId ? 'hs_crm_update_enquiry' : 'hs_crm_create_enquiry';
            
            $.ajax({
                url: hsCrmAjax.ajaxurl,
                type: 'POST',
                data: $form.serialize() + '&action=' + actionName + '&nonce=' + hsCrmAjax.nonce,
                success: function(response) {
                    if (response.success) {
                        alert(response.data.message);
                        location.reload();
                    } else {
                        alert('Error: ' + response.data.message);
                    }
                },
                error: function() {
                    alert('An error occurred. Please try again.');
                }
            });
        });
        
        // Handle enquiry modal close
        $('#hs-crm-enquiry-modal .hs-crm-modal-close').on('click', function() {
            $('#hs-crm-enquiry-modal').fadeOut();
        });
        
        // Close enquiry modal when clicking outside
        $(window).on('click', function(e) {
            if ($(e.target).is('#hs-crm-enquiry-modal')) {
                $('#hs-crm-enquiry-modal').fadeOut();
            }
        });
    }
    
    // Truck Scheduler page functionality
    if ($('.hs-crm-scheduler-wrap').length > 0) {
        
        // Track if end time has been manually edited
        var endTimeManuallyEdited = false;
        
        // Add Truck button
        $('#hs-crm-add-truck-btn').on('click', function() {
            $('#truck-modal-title').text('Add Truck');
            $('#hs-crm-truck-form')[0].reset();
            $('#truck-id').val('');
            $('#hs-crm-truck-modal').fadeIn();
        });
        
        // Edit Truck button
        $(document).on('click', '.hs-crm-edit-truck', function() {
            var truckId = $(this).data('truck-id');
            var $row = $(this).closest('tr');
            
            $('#truck-modal-title').text('Edit Truck');
            $('#truck-id').val(truckId);
            $('#truck-name').val($row.find('td:eq(0)').text().trim());
            $('#truck-registration').val($row.find('td:eq(1)').text().trim());
            $('#truck-capacity').val($row.find('td:eq(2)').text().trim());
            $('#hs-crm-truck-modal').fadeIn();
        });
        
        // Remove Truck button
        $(document).on('click', '.hs-crm-remove-truck', function() {
            if (!confirm('Are you sure you want to remove this truck? It will be marked as inactive.')) {
                return;
            }
            
            var truckId = $(this).data('truck-id');
            
            $.ajax({
                url: hsCrmAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'hs_crm_delete_truck',
                    nonce: hsCrmAjax.nonce,
                    truck_id: truckId
                },
                success: function(response) {
                    if (response.success) {
                        alert(response.data.message);
                        location.reload();
                    } else {
                        alert('Error: ' + response.data.message);
                    }
                }
            });
        });
        
        // Truck form submission
        $('#hs-crm-truck-form').on('submit', function(e) {
            e.preventDefault();
            
            var $form = $(this);
            var truckId = $('#truck-id').val();
            var actionName = truckId ? 'hs_crm_update_truck' : 'hs_crm_add_truck';
            
            $.ajax({
                url: hsCrmAjax.ajaxurl,
                type: 'POST',
                data: $form.serialize() + '&action=' + actionName + '&nonce=' + hsCrmAjax.nonce,
                success: function(response) {
                    if (response.success) {
                        alert(response.data.message);
                        location.reload();
                    } else {
                        alert('Error: ' + response.data.message);
                    }
                }
            });
        });
        
        // Add Booking button
        $('#hs-crm-add-booking-btn').on('click', function() {
            $('#booking-modal-title').text('Add Booking');
            $('#hs-crm-booking-form')[0].reset();
            $('#booking-id').val('');
            $('.hs-crm-delete-booking-btn').hide();
            $('#hs-crm-booking-modal').fadeIn();
            // Reset manual edit flag when opening new booking
            endTimeManuallyEdited = false;
        });
        
        // Click on calendar cell to add booking
        $(document).on('click', '.hs-crm-calendar-cell', function(e) {
            // Don't trigger if clicking on a booking item
            if ($(e.target).closest('.hs-crm-booking-item').length > 0) {
                return;
            }
            
            var date = $(this).data('date');
            var truckId = $(this).data('truck-id');
            
            $('#booking-modal-title').text('Add Booking');
            $('#hs-crm-booking-form')[0].reset();
            $('#booking-id').val('');
            $('#booking-date').val(date);
            $('#booking-truck').val(truckId);
            $('.hs-crm-delete-booking-btn').hide();
            $('#hs-crm-booking-modal').fadeIn();
            // Reset manual edit flag when opening new booking from calendar
            endTimeManuallyEdited = false;
        });
        
        // Click on booking item to edit
        $(document).on('click', '.hs-crm-booking-item', function() {
            var bookingId = $(this).data('booking-id');
            
            // Get booking data via AJAX
            $.ajax({
                url: hsCrmAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'hs_crm_get_bookings',
                    nonce: hsCrmAjax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        var booking = response.data.bookings.find(b => b.id == bookingId);
                        if (booking) {
                            $('#booking-modal-title').text('Edit Booking');
                            $('#booking-id').val(booking.id);
                            $('#booking-truck').val(booking.truck_id);
                            $('#booking-date').val(booking.booking_date);
                            $('#booking-start-time').val(booking.start_time);
                            $('#booking-end-time').val(booking.end_time);
                            $('#booking-enquiry').val(booking.enquiry_id || '');
                            $('#booking-notes').val(booking.notes);
                            $('.hs-crm-delete-booking-btn').show();
                            $('#hs-crm-booking-modal').fadeIn();
                            // When editing, consider end time as manually set if it exists
                            endTimeManuallyEdited = !!booking.end_time;
                        }
                    }
                }
            });
        });
        
        // Booking form submission
        $('#hs-crm-booking-form').on('submit', function(e) {
            e.preventDefault();
            
            var $form = $(this);
            var bookingId = $('#booking-id').val();
            var actionName = bookingId ? 'hs_crm_update_booking' : 'hs_crm_add_booking';
            
            $.ajax({
                url: hsCrmAjax.ajaxurl,
                type: 'POST',
                data: $form.serialize() + '&action=' + actionName + '&nonce=' + hsCrmAjax.nonce,
                success: function(response) {
                    if (response.success) {
                        alert(response.data.message);
                        location.reload();
                    } else {
                        alert('Error: ' + response.data.message);
                    }
                }
            });
        });
        
        // Auto-calculate end time based on start time and default duration
        $('#booking-end-time').on('change', function() {
            // Mark end time as manually edited if user changes it
            endTimeManuallyEdited = true;
        });
        
        $('#booking-start-time').on('change', function() {
            var startTime = $(this).val();
            if (!startTime) {
                return;
            }
            
            // Validate time format
            var timeParts = startTime.split(':');
            if (timeParts.length !== 2) {
                return; // Invalid format
            }
            
            // Parse start time
            var hours = parseInt(timeParts[0], 10);
            var minutes = parseInt(timeParts[1], 10);
            
            // Validate parsed values
            if (isNaN(hours) || isNaN(minutes) || hours < 0 || hours > 23 || minutes < 0 || minutes > 59) {
                return; // Invalid time values
            }
            
            // Get default duration in hours from settings
            var durationHours = hsCrmAjax.defaultBookingDuration || 3;
            
            // Calculate end time - convert duration to minutes first to avoid floating-point issues
            var durationMinutes = Math.round(durationHours * 60);
            var totalMinutes = hours * 60 + minutes + durationMinutes;
            var endHours = Math.floor(totalMinutes / 60) % 24;
            var endMinutes = totalMinutes % 60;
            
            // Format as HH:MM
            var endTime = String(endHours).padStart(2, '0') + ':' + String(endMinutes).padStart(2, '0');
            
            // Set end time only if it hasn't been manually edited
            if (!endTimeManuallyEdited) {
                $('#booking-end-time').val(endTime);
            }
        });
        
        // Delete booking button
        $('.hs-crm-delete-booking-btn').on('click', function() {
            if (!confirm('Are you sure you want to delete this booking?')) {
                return;
            }
            
            var bookingId = $('#booking-id').val();
            
            $.ajax({
                url: hsCrmAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'hs_crm_delete_booking',
                    nonce: hsCrmAjax.nonce,
                    booking_id: bookingId
                },
                success: function(response) {
                    if (response.success) {
                        alert(response.data.message);
                        location.reload();
                    } else {
                        alert('Error: ' + response.data.message);
                    }
                }
            });
        });
    }
});
