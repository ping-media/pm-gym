jQuery(document).ready(function($) {
    'use strict';

    // Toggle guest/member fields
    $('#is_guest').on('change', function() {
        if ($(this).is(':checked')) {
            $('#member-fields').hide();
            $('#guest-fields').show();
            $('#member_id').prop('required', false);
            $('#guest_name, #guest_phone').prop('required', true);
        } else {
            $('#member-fields').show();
            $('#guest-fields').hide();
            $('#member_id').prop('required', true);
            $('#guest_name, #guest_phone').prop('required', false);
        }
    });

    // Handle attendance form submission
    // $('#pm-gym-attendance-form').on('submit', function(e) {
    //     e.preventDefault();
        
    //     var $form = $(this);
    //     var $message = $('#attendance-message');
    //     var $submitButton = $form.find('button[type="submit"]');
    //     var isGuest = $('#is_guest').is(':checked');
        
    //     // Validate guest fields if guest is checked
    //     if (isGuest) {
    //         var guestName = $('#guest_name').val().trim();
    //         var guestPhone = $('#guest_phone').val().trim();
            
    //         if (!guestName || !guestPhone) {
    //             $message.addClass('error').text('Please provide both name and phone number for guest attendance');
    //             return;
    //         }
            
    //         if (!/^[0-9]{10}$/.test(guestPhone)) {
    //             $message.addClass('error').text('Please enter a valid 10-digit phone number');
    //             return;
    //         }
    //     }
        
    //     // Disable submit button
    //     $submitButton.prop('disabled', true);
        
    //     // Clear previous messages
    //     $message.removeClass('success error').empty();
        
    //     // Get form data
    //     var formData = {
    //         action: 'mark_attendance',
    //         is_guest: isGuest ? 1 : 0,
    //         member_id: $('#member_id').val(),
    //         guest_name: $('#guest_name').val(),
    //         guest_phone: $('#guest_phone').val(),
    //         attendance_type: $('input[name="attendance_type"]:checked').val()
    //     };
        
    //     // Send AJAX request
    //     $.ajax({
    //         url: pm_gym_public.ajax_url,
    //         type: 'POST',
    //         data: formData,
    //         success: function(response) {
    //             if (response.success) {
    //                 $message.addClass('success').text(response.data);
    //                 // Clear form on success
    //                 $form[0].reset();
    //                 // Reset fields visibility
    //                 $('#member-fields').show();
    //                 $('#guest-fields').hide();
    //                 $('#member_id').prop('required', true);
    //                 $('#guest_name, #guest_phone').prop('required', false);
    //             } else {
    //                 $message.addClass('error').text(response.data);
    //             }
    //         },
    //         error: function() {
    //             $message.addClass('error').text('An error occurred. Please try again.');
    //         },
    //         complete: function() {
    //             // Re-enable submit button
    //             $submitButton.prop('disabled', false);
    //         }
    //     });
    // });

    // Format member ID input
    $('#member_id').on('input', function() {
        var value = $(this).val();
        // Remove any non-digit characters
        value = value.replace(/\D/g, '');
        // Limit to 4 digits
        value = value.substring(0, 4);
        $(this).val(value);
    });

    // Format phone number input
    $('#guest_phone').on('input', function() {
        var value = $(this).val();
        // Remove any non-digit characters
        value = value.replace(/\D/g, '');
        // Limit to 10 digits
        value = value.substring(0, 10);
        $(this).val(value);
    });
}); 