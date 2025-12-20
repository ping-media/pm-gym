<?php
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;

// Get filter parameters
$selected_date = isset($_GET['attendance_date']) ? sanitize_text_field($_GET['attendance_date']) : current_time('Y-m-d');
$guest_search = isset($_GET['guest_search']) ? sanitize_text_field($_GET['guest_search']) : '';
$time_period = isset($_GET['time_period']) ? sanitize_text_field($_GET['time_period']) : 'today';

// Build the query for guest attendance only
$query = "SELECT a.*, 
    g.name,
    g.phone
FROM " . PM_GYM_ATTENDANCE_TABLE . " a
LEFT JOIN " . PM_GYM_GUEST_USERS_TABLE . " g ON a.user_id = g.id AND a.user_type = 'guest'
WHERE a.user_type = 'guest'";

$query_params = array();

// Add time period filter
switch ($time_period) {
    case '7days':
        $query .= " AND a.check_in_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
        break;
    case '30days':
        $query .= " AND a.check_in_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
        break;
    case 'all':
        // No date filter for all time
        break;
    default: // today
        $query .= " AND DATE(a.check_in_date) = %s";
        $query_params[] = $selected_date;
        break;
}

// Add guest search filter (name or phone)
if (!empty($guest_search)) {
    $query .= " AND (g.name LIKE %s OR g.phone LIKE %s)";
    $search_term = '%' . $wpdb->esc_like($guest_search) . '%';
    $query_params[] = $search_term;
    $query_params[] = $search_term;
}

$query .= " ORDER BY a.check_in_date DESC, a.check_in_time DESC";

// Debug output
if ($wpdb->last_error) {
    error_log('Guest Attendance Query Error: ' . $wpdb->last_error);
    error_log('Query: ' . $query);
    error_log('Parameters: ' . print_r($query_params, true));
}

$guest_attendance = $wpdb->get_results(
    empty($query_params) ? $query : $wpdb->prepare($query, $query_params)
);

// Calculate statistics
$total_guests = count($guest_attendance);
$active_guests = count(array_filter($guest_attendance, function ($record) {
    return empty($record->check_out_time);
}));
$checked_out_guests = $total_guests - $active_guests;
?>

<div class="wrap">
    <h1>Guest Attendance Management</h1>

    <?php settings_errors('gym_attendance'); ?>

    <!-- Date Selection Form -->
    <div class="date-selection">
        <form method="get" action="">
            <input type="hidden" name="page" value="<?php echo esc_attr($_GET['page']); ?>">
            <div class="filter-options">
                <label for="time_period">Time Period:</label>
                <select name="time_period" id="time_period" class="time-period-select">
                    <option value="today" <?php selected($time_period, 'today'); ?>>Today</option>
                    <option value="7days" <?php selected($time_period, '7days'); ?>>Last 7 Days</option>
                    <option value="30days" <?php selected($time_period, '30days'); ?>>Last 30 Days</option>
                    <option value="all" <?php selected($time_period, 'all'); ?>>All Time</option>
                </select>
            </div>
            <div class="date-filter" id="date-filter" style="<?php echo $time_period !== 'today' ? 'display: none;' : ''; ?>">
                <label for="attendance_date">Select Date:</label>
                <input type="date" id="attendance_date" name="attendance_date" value="<?php echo esc_attr($selected_date); ?>" max="<?php echo current_time('Y-m-d'); ?>">
            </div>
            <label for="guest_search">Search Guest:</label>
            <input type="text" id="guest_search" name="guest_search" value="<?php echo esc_attr($guest_search); ?>" placeholder="Search by Name or Phone">
            <input type="submit" class="button" value="View Attendance">
            <button type="button" class="button export-csv-btn" data-date="<?php echo esc_attr($selected_date); ?>">Export to CSV</button>
            <a href="<?php echo esc_url(remove_query_arg(['attendance_date', 'guest_search', 'time_period'])); ?>" class="button reset-filter-btn">Reset Filters</a>
        </form>
    </div>

    <!-- Statistics -->
    <div class="attendance-stats">
        <div class="stat-box">
            <h3>Total Guest Attendance</h3>
            <p class="stat-number"><?php echo esc_html($total_guests); ?></p>
        </div>
        <div class="stat-box">
            <h3>Active Guests</h3>
            <p class="stat-number"><?php echo esc_html($active_guests); ?></p>
        </div>
        <div class="stat-box">
            <h3>Checked Out Guests</h3>
            <p class="stat-number"><?php echo esc_html($checked_out_guests); ?></p>
        </div>
    </div>

    <div class="pm-gym-attendance-container">
        <!-- Guest Attendance -->
        <div class="today-attendance">
            <h2>Guest Attendance</h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Guest Name</th>
                        <th>Phone Number</th>
                        <th>Check-in <br> Date</th>
                        <th>Check-in <br> Time</th>
                        <th>Check-out <br> Time</th>
                        <th>Duration</th>
                        <th>Attendance <br> Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($guest_attendance)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 20px;">
                                No guest attendance records found for the selected period.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($guest_attendance as $record): ?>
                            <tr>
                                <td><?php echo esc_html($record->name); ?></td>
                                <td><?php echo esc_html($record->phone); ?></td>
                                <td><?php echo esc_html(date('d M, Y', strtotime($record->check_in_date))); ?></td>
                                <td><?php echo esc_html(date('h:i A', strtotime($record->check_in_time))); ?></td>
                                <td>
                                    <?php if (!empty($record->check_out_time)): ?>
                                        <?php echo esc_html(date('h:i A', strtotime($record->check_out_time))); ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($record->check_out_time)): ?>
                                        <?php echo esc_html(PM_Gym_Helpers::calculate_duration($record->check_in_time, $record->check_out_time)); ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($record->check_out_time)): ?>
                                        <span class="status-checked-out">Checked Out</span>
                                    <?php else: ?>
                                        <span class="status-checked-in">Checked In</span>
                                    <?php endif; ?>
                                </td>
                                <td class="actions-column">
                                    <?php if (empty($record->check_out_time)): ?>
                                        <button type="button" class="button check-out-btn" data-attendance-id="<?php echo esc_attr($record->id); ?>">Check Out</button>
                                    <?php endif; ?>
                                    <br>
                                    <a href="javascript:void(0)" class="delete-attendance-btn" data-attendance-id="<?php echo esc_attr($record->id); ?>" onclick="return confirm('Are you sure you want to delete this attendance record?')">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    .pm-gym-attendance-container {
        margin-top: 20px;
    }

    /* Reset Filter Button Styles */
    .reset-filter-btn {
        background-color: #dc3545 !important;
        color: white !important;
        border-color: #dc3545 !important;
    }

    .reset-filter-btn:hover {
        background-color: #c82333 !important;
        border-color: #bd2130 !important;
    }

    /* Datepicker Styles */
    .ui-datepicker {
        background: #fff;
        padding: 10px;
        border-radius: 4px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    }

    .ui-datepicker .ui-datepicker-header {
        background: #f5f5f5;
        border: none;
        border-radius: 4px;
        padding: 5px;
    }

    .ui-datepicker .ui-datepicker-title {
        color: #23282d;
        font-weight: bold;
    }

    .ui-datepicker th {
        color: #23282d;
        font-weight: bold;
    }

    .ui-datepicker td {
        padding: 2px;
    }

    .ui-datepicker td span,
    .ui-datepicker td a {
        text-align: center;
        padding: 5px;
        border-radius: 3px;
    }

    .ui-datepicker .ui-state-active {
        background: #0073aa;
        color: #fff;
    }

    .ui-datepicker .ui-state-hover {
        background: #f0f0f0;
    }

    .date-selection {
        background: #fff;
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 20px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .date-selection form {
        display: flex;
        gap: 10px;
        align-items: center;
    }

    .date-selection label {
        font-weight: bold;
        margin-right: 5px;
    }

    .date-selection input[type="date"] {
        padding: 5px;
        border: 1px solid #ddd;
        border-radius: 4px;
    }

    .today-attendance {
        background: #fff;
        padding: 20px;
        border-radius: 5px;
        margin-bottom: 20px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        overflow-x: auto;
    }

    .attendance-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }

    .stat-box {
        background: #fff;
        padding: 20px;
        border-radius: 5px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        text-align: center;
    }

    .stat-box h3 {
        margin-top: 0;
        color: #23282d;
    }

    .stat-number {
        font-size: 2em;
        font-weight: bold;
        margin: 0;
        color: #0073aa;
    }

    .status-checked-in {
        color: #46b450;
        font-weight: bold;
    }

    .status-checked-out {
        color: #dc3232;
        font-weight: bold;
    }

    .wp-list-table td {
        vertical-align: middle;
    }

    .delete-attendance-btn {
        color: #dc3545;
        margin-left: 5px;
        background-color: transparent;
        border: none;
    }

    .wp-list-table .actions-column {
        white-space: nowrap;
    }

    .wp-list-table .actions-column .button {
        margin-right: 5px;
        margin-bottom: 2px;
    }

    .filter-options {
        margin-bottom: 10px;
    }

    .time-period-select {
        padding: 5px;
        border: 1px solid #ddd;
        border-radius: 4px;
        margin-right: 10px;
    }

    .date-filter {
        display: inline-block;
        margin-right: 10px;
    }
</style>

<script>
    jQuery(document).ready(function($) {
        // Initialize datepicker
        $('#attendance_date').datepicker({
            dateFormat: 'yy-mm-dd',
            maxDate: '0', // Disable future dates
            changeMonth: true,
            changeYear: true,
            yearRange: '-1:+0', // Show current year and previous year
            beforeShow: function(input, inst) {
                // Ensure the datepicker appears above other elements
                $('#ui-datepicker-div').css({
                    'z-index': 999999
                });
            }
        });

        // Handle date change
        $('#attendance_date').on('change', function() {
            var selectedDate = $(this).val();
            var currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set('attendance_date', selectedDate);
            window.location.href = currentUrl.toString();
        });

        // Auto-refresh attendance list every minute
        setInterval(function() {
            location.reload();
        }, 60000);

        $('.check-out-btn').on('click', function() {
            var attendanceId = $(this).data('attendance-id');

            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'mark_check_out_attendance',
                    attendance_id: attendanceId
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Error checking out attendance.');
                    }
                }
            });
        });

        // Handle Delete Attendance
        $('.delete-attendance-btn').on('click', function() {
            var attendanceId = $(this).data('attendance-id');
            var $deleteBtn = $(this);

            // Show loading state
            $deleteBtn.prop('disabled', true).text('Deleting...');

            $.ajax({
                url: pm_gym_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'delete_attendance',
                    id: attendanceId
                },
                success: function(response) {
                    if (response.success) {
                        showNotification('Attendance record deleted successfully', 'success');
                        location.reload();
                    } else {
                        showNotification('Error deleting attendance record: ' + response.data, 'error');
                        $deleteBtn.prop('disabled', false).text('Delete');
                    }
                },
                error: function(xhr, status, error) {
                    showNotification('Error deleting attendance record: ' + error, 'error');
                    $deleteBtn.prop('disabled', false).text('Delete');
                }
            });
        });

        // Handle CSV Export
        $('.export-csv-btn').on('click', function() {
            var date = $(this).data('date');
            var $exportBtn = $(this);

            // Show loading state
            $exportBtn.prop('disabled', true).addClass('loading');

            $.ajax({
                url: pm_gym_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'export_attendance',
                    user_type: 'guest'
                },
                success: function(response) {
                    if (response.success) {
                        // Create a temporary link to download the file
                        var link = document.createElement('a');
                        link.href = response.data.file_url;
                        link.download = 'guest-attendance-' + date + '.csv';
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);

                        // Show success message
                        showNotification('Guest attendance data exported successfully', 'success');
                    } else {
                        showNotification('Error exporting attendance data: ' + response.data.message, 'error');
                    }
                },
                error: function(xhr, status, error) {
                    showNotification('Error exporting attendance data: ' + error, 'error');
                },
                complete: function() {
                    // Remove loading state
                    $exportBtn.prop('disabled', false).removeClass('loading');
                }
            });
        });

        // Add notification function if not already present
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

        // Handle time period change
        $('#time_period').on('change', function() {
            if ($(this).val() === 'today') {
                $('#date-filter').show();
            } else {
                $('#date-filter').hide();
            }
        });
    });
</script>