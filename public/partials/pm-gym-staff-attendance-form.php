<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="pm-gym-staff-attendance-form-container">
    <div class="pm-gym-staff-attendance-form">
        <div style="text-align: center; margin-bottom: 20px;">
            <img src="/wp-content/plugins/pm-gym/public/img/logo-gym.png" alt="PM Gym Logo" style="width: 100px; height: 100px; margin-bottom: 20px;">
        </div>
        <h2>Staff Attendance</h2>
        <form id="pm-gym-staff-attendance-form" method="post">
            <div class="form-group">
                <label for="staff_id">Staff ID</label>
                <input type="text" id="staff_id" name="staff_id" required
                    placeholder="Enter your staff ID"
                    pattern="[0-9]{4}"
                    title="Please enter a valid staff ID" maxlength="4">
            </div>

            <div id="staff-info" style="display: none;">
                <h2 id="staff-greeting">Hello, <span id="staff-name"></span>!</h2>
                <p id="staff-role">Role: <span id="staff-role-value"></span></p>
            </div>

            <div class="form-group">
                <label>Attendance Type</label>
                <div class="radio-group">
                    <label class="radio-label">
                        <input type="radio" name="attendance_type" value="check_in" checked required>
                        Check In
                    </label>
                    <label class="radio-label">
                        <input type="radio" name="attendance_type" value="check_out" required>
                        Check Out
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label>Shift</label>
                <div class="radio-group">
                    <label class="radio-label">
                        <input type="radio" name="shift" value="morning" checked required>
                        Morning Shift
                    </label>
                    <label class="radio-label">
                        <input type="radio" name="shift" value="evening" required>
                        Evening Shift
                    </label>
                </div>
            </div>

            <div class="form-group">
                <button type="submit" class="button button-primary">CHECK IN</button>
            </div>
            <div id="attendance-message"></div>
            <div class="form-group reload-button-container">
                <button type="button" id="reload-page-btn" class="button button-secondary">Reload Page</button>
            </div>
        </form>
    </div>
</div>

<style>
    .pm-gym-staff-attendance-form-container {
        background: url(/wp-content/plugins/pm-gym/public/img/bg.jpg);
        height: 100vh;
        background-repeat: no-repeat;
        background-size: cover;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
    }

    .pm-gym-staff-attendance-form {
        margin: 0 auto;
    }

    .pm-gym-staff-attendance-form {
        max-width: 500px;
        width: 100%;
        margin: 20px auto;
        padding: 20px;
        background: #fff;
        border-radius: 5px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .pm-gym-staff-attendance-form {
        background: #fff;
        padding: 30px;
        border-radius: 30px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    }

    .pm-gym-staff-attendance-form h2 {
        text-align: center;
        font-size: 28px;
        margin-bottom: 25px;
        color: #1e2b3d;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: 500;
        color: #1e2b3d;
    }

    .form-group input[type="text"] {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 16px;
    }

    .radio-group {
        display: flex;
        gap: 30px;
    }

    .radio-label {
        display: flex;
        align-items: center;
        cursor: pointer;
    }

    .radio-label input {
        margin-right: 8px;
        width: 20px;
        height: 20px;
    }

    .button-primary {
        width: 100%;
        background: #4682B4;
        color: white;
        border: none;
        border-radius: 6px;
        padding: 15px 0;
        font-size: 18px;
        font-weight: 600;
        cursor: pointer;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .button-primary:hover {
        background: #3A6D99;
    }

    #staff-greeting {
        font-size: 24px;
        margin-bottom: 5px;
    }

    #staff-role {
        font-size: 16px;
        color: #000;
        margin-bottom: 25px;
    }

    #attendance-message {
        margin-top: 15px;
        padding: 10px;
        border-radius: 4px;
        text-align: center;
        font-weight: 500;
    }

    #staff-name {
        text-transform: capitalize;
    }

    .success {
        background-color: #d4edda;
        color: #155724;
    }

    .error {
        background-color: #f8d7da;
        color: #721c24;
    }

    .error-field {
        border-color: #dc3545 !important;
        background-color: #fff8f8;
    }

    #staff-info {
        padding: 10px;
        background: #f8dedc;
        border-radius: 10px;
        text-align: center;
    }

    .reload-button-container {
        text-align: center;
        margin-top: 15px;
    }

    .pm-gym-staff-attendance-form button.button-secondary {
        background: transparent;
        color: #797979;
        border: 1px solid #797979;
        border-radius: 6px;
        padding: 10px 15px;
        font-size: 16px;
        cursor: pointer;
    }

    .pm-gym-staff-attendance-form button.button-secondary:hover {
        background: #797979;
        color: #fff;
    }
</style>

<script>
    jQuery(document).ready(function($) {
        // Set default shift based on current time
        function setDefaultShift() {
            const currentHour = new Date().getHours();
            if (currentHour >= 16) {
                $('input[name="shift"][value="evening"]').prop('checked', true);
            } else {
                $('input[name="shift"][value="morning"]').prop('checked', true);
            }
        }

        // Call on page load
        setDefaultShift();

        // Reload page button
        $('#reload-page-btn').on('click', function() {
            window.location.reload();
        });

        // Update button text based on attendance type
        $('input[name="attendance_type"]').on('change', function() {
            if ($(this).val() === 'check_in') {
                $('.button-primary').text('CHECK IN');
            } else {
                $('.button-primary').text('CHECK OUT');
            }
        });

        // Form submission
        $('#pm-gym-staff-attendance-form').on('submit', function(e) {
            e.preventDefault();

            // Validate form
            let isValid = true;
            if (!$('#staff_id').val() || !$('#staff_id').val().match(/^\d{4}$/)) {
                $('#staff_id').addClass('error-field');
                isValid = false;
            } else {
                $('#staff_id').removeClass('error-field');
            }

            if (!isValid) {
                $('#attendance-message').removeClass('success').addClass('error').text('Please enter a valid staff ID.');
                return;
            }

            const staffId = $('#staff_id').val();

            // First get staff details
            $.ajax({
                type: 'POST',
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                data: {
                    action: 'get_staff_data',
                    staff_id: staffId
                },
                beforeSend: function() {
                    $('#attendance-message').removeClass('success error').text('Processing...');
                },
                success: function(response) {
                    if (response.success) {
                        const data = response.data;
                        $('#staff-name').text(data.name);
                        $('#staff-role-value').text(data.role);
                        $('#staff-info').show();

                        // Now mark attendance after getting staff details
                        markAttendance(data.id);
                    } else {
                        // Staff not found or error
                        $('#staff-info').hide();
                        $('#attendance-message').removeClass('success').addClass('error').text('Staff not found or error occurred.');
                    }
                },
                error: function() {
                    $('#staff-info').hide();
                    $('#attendance-message').removeClass('success').addClass('error').text('An error occurred while getting staff details.');
                }
            });

            // Function to mark attendance
            function markAttendance(staffId) {
                $.ajax({
                    type: 'POST',
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    data: $('#pm-gym-staff-attendance-form').serialize() + '&action=mark_staff_attendance&record_id=' + staffId,
                    beforeSend: function() {
                        $('#attendance-message').removeClass('success error').text('Processing...');
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#attendance-message').removeClass('error').addClass('success').text(response.data);
                        } else {
                            $('#attendance-message').removeClass('success').addClass('error').text(response.data);
                        }
                        setTimeout(function() {
                            window.location.reload();
                        }, 5000);
                    },
                    error: function() {
                        $('#attendance-message').removeClass('success').addClass('error').text('An error occurred. Please try again.');
                    }
                });
            }
        });
    });
</script>