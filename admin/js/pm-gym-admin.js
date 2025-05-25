jQuery(document).ready(function($) {
    'use strict';

    // Member Management
    function initMemberManagement() {

        // Cancel Edit
        $('#cancel-edit').on('click', function(e) {
            e.preventDefault();
            resetMemberForm();
        });

        // Reset form
        function resetMemberForm() {
            $('#member-form')[0].reset();
            $('#member_id').val('');
            $('#form-title').text('Add New Member');
            $('#cancel-edit').hide();
            $('#member-form').slideUp();
        }

        // // Delete Member
        // $(document).on('click', '.delete-member', function(e) {
        //     e.preventDefault();
        //     if (confirm(pm_gym_ajax.i18n.confirm_delete)) {
        //         var memberId = $(this).data('id');
        //         var $row = $(this).closest('tr');
                
        //         $.ajax({
        //             url: pm_gym_ajax.ajax_url,
        //             type: 'POST',
        //             data: {
        //                 action: 'delete_member',
        //                 member_id: memberId,
        //                 // nonce: pm_gym_ajax.nonce
        //             },
        //             success: function(response) {
        //                 if (response.success) {
        //                     $row.fadeOut(function() {
        //                         $(this).remove();
        //                     });
        //                     showNotification('Member deleted successfully', 'success');
        //                 } else {
        //                     showNotification(response.data || 'Error deleting member', 'error');
        //                 }
        //             },
        //             error: function(xhr, status, error) {
        //                 console.error('AJAX error:', {xhr: xhr, status: status, error: error});
        //                 showNotification('Error deleting member: ' + error, 'error');
        //             }
        //         });
        //     }
        // });
    }

    // Attendance Management
    function initAttendanceManagement() {
        // Check-in/Check-out
        // $(document).on('click', '.check-in-btn, .check-out-btn', function(e) {
        //     e.preventDefault();
        //     var memberId = $(this).data('member-id');
        //     var action = $(this).hasClass('check-in-btn') ? 'check_in' : 'check_out';
        //     var $btn = $(this);
            
        //     $btn.addClass('loading');
            
        //     $.ajax({
        //         url: pm_gym_ajax.ajax_url,
        //         type: 'POST',
        //         data: {
        //             action: 'handle_attendance',
        //             member_id: memberId,
        //             attendance_action: action,
        //             nonce: pm_gym_ajax.nonce
        //         },
        //         success: function(response) {
        //             if (response.success) {
        //                 showNotification(response.data.message, 'success');
        //                 // Refresh attendance list or update UI
        //                 location.reload();
        //             } else {
        //                 showNotification(response.data.message, 'error');
        //             }
        //         },
        //         error: function() {
        //             showNotification('Error processing attendance', 'error');
        //         },
        //         complete: function() {
        //             $btn.removeClass('loading');
        //         }
        //     });
        // });

        // Date Filter
        $('#attendance-date').on('change', function() {
            var date = $(this).val();
            // Add date filter functionality here
        });
    }

    // Fee Management
    function initFeeManagement() {
        // Amount Format
        $('#amount').on('input', function() {
            var value = $(this).val();
            if (value < 0) {
                $(this).val(0);
            }
        });

        // Payment Date
        $('#payment_date').on('change', function() {
            var date = $(this).val();
            // Add date validation if needed
        });

        // Payment Method
        $('#payment_method').on('change', function() {
            var method = $(this).val();
            // Add payment method specific functionality if needed
        });
    }

    

    function formatCurrency(amount) {
        return '₹' + parseFloat(amount).toFixed(2);
    }

    function showNotification(message, type) {
        var notification = $('<div class="notice notice-' + type + ' is-dismissible"><p>' + message + '</p></div>');
        $('.wrap h1').after(notification);
        
        // Auto dismiss after 3 seconds
        setTimeout(function() {
            notification.fadeOut(function() {
                $(this).remove();
            });
        }, 3000);
    }

    // Initialize all functionality
    function init() {
        initMemberManagement();
        initAttendanceManagement();
        initFeeManagement();
    }

    // Run initialization
    init();
}); 