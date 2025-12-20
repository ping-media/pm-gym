<?php
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$staff_table = PM_GYM_STAFF_TABLE;

// Get date range parameters
$from_date = isset($_GET['from_date']) ? sanitize_text_field($_GET['from_date']) : current_time('Y-m-d');
$to_date = isset($_GET['to_date']) ? sanitize_text_field($_GET['to_date']) : current_time('Y-m-d');
$selected_staff_id = isset($_GET['staff_id']) ? sanitize_text_field($_GET['staff_id']) : '';
$date_range = isset($_GET['date_range']) ? sanitize_text_field($_GET['date_range']) : 'custom';

// Debug the selected dates and staff ID
error_log('From Date: ' . $from_date);
error_log('To Date: ' . $to_date);
error_log('Selected Staff ID: ' . $selected_staff_id);
error_log('Date Range: ' . $date_range);

// Get attendance records with error checking
$query = $wpdb->prepare(
    "SELECT a.*, s.name, s.phone, s.staff_id, s.status, s.role
    FROM " . PM_GYM_STAFF_ATTENDANCE_TABLE . " a
    LEFT JOIN " . PM_GYM_STAFF_TABLE . " s ON a.staff_id = s.id
    WHERE 1=1
    " . ($date_range === 'today' ? $wpdb->prepare("AND DATE(a.check_in_date) = %s", current_time('Y-m-d')) : "") . "
    " . ($date_range === 'week' ? $wpdb->prepare("AND a.check_in_date >= DATE_SUB(%s, INTERVAL 7 DAY)", current_time('Y-m-d')) : "") . "
    " . ($date_range === 'month' ? $wpdb->prepare("AND a.check_in_date >= DATE_SUB(%s, INTERVAL 30 DAY)", current_time('Y-m-d')) : "") . "
    " . ($date_range === 'custom' ? $wpdb->prepare("AND DATE(a.check_in_date) BETWEEN %s AND %s", $from_date, $to_date) : "") . "
    " . ($date_range === 'all' ? "" : "") . "
    " . ($selected_staff_id ? $wpdb->prepare("AND s.staff_id = %s", $selected_staff_id) : "") . "
    ORDER BY a.check_in_date DESC, a.shift ASC, a.check_in_time DESC"
);

// Debug the query
error_log('Attendance Query: ' . $query);

$today_attendance = $wpdb->get_results($query);

// Debug the results
error_log('Number of records found: ' . count($today_attendance));
if ($wpdb->last_error) {
    error_log('Database Error: ' . $wpdb->last_error);
}

// Debug output
if ($wpdb->last_error) {
    error_log('Attendance Query Error: ' . $wpdb->last_error);
}



// Get all active staff members for the dropdown
$active_staff = $wpdb->get_results(
    "SELECT id, staff_id, name FROM $staff_table 
    WHERE status = 'active' 
    ORDER BY name ASC"
);

// Get total active members
$total_staff = $wpdb->get_var("SELECT COUNT(*) FROM " . PM_GYM_STAFF_TABLE . " WHERE status = 'active'");

// Calculate statistics
$active_staff = count($active_staff);
$today_count = count($today_attendance);
$attendance_rate = $total_staff > 0 ? round(($today_count / $total_staff) * 100, 1) : 0;
?>

<div class="wrap">
    <h1>Attendance Management</h1>

    <?php settings_errors('gym_attendance'); ?>

    <!-- Date Selection Form -->
    <div class="date-selection">
        <form method="get" action="">
            <input type="hidden" name="page" value="<?php echo esc_attr($_GET['page']); ?>">

            <label for="date_range">Date Range:</label>
            <select id="date_range" name="date_range">
                <option value="today" <?php selected($date_range, 'today'); ?>>Today</option>
                <option value="week" <?php selected($date_range, 'week'); ?>>Last 7 Days</option>
                <option value="month" <?php selected($date_range, 'month'); ?>>Last 30 Days</option>
                <option value="custom" <?php selected($date_range, 'custom'); ?>>Custom Range</option>
                <option value="all" <?php selected($date_range, 'all'); ?>>All Time</option>
            </select>

            <div class="date-inputs">
                <label for="from_date">From Date:</label>
                <input type="date" id="from_date" name="from_date" value="<?php echo esc_attr($from_date); ?>" max="<?php echo current_time('Y-m-d'); ?>">

                <label for="to_date">To Date:</label>
                <input type="date" id="to_date" name="to_date" value="<?php echo esc_attr($to_date); ?>" max="<?php echo current_time('Y-m-d'); ?>">
            </div>

            <label for="staff_id">Staff:</label>
            <select id="staff_id" name="staff_id">
                <option value="">All Staff</option>
                <?php
                $active_staff = $wpdb->get_results(
                    "SELECT id, staff_id, name FROM $staff_table 
                    WHERE status = 'active' 
                    ORDER BY name ASC"
                );
                foreach ($active_staff as $staff):
                ?>
                    <option value="<?php echo esc_attr($staff->staff_id); ?>" <?php selected($selected_staff_id, $staff->staff_id); ?>>
                        <?php echo esc_html(PM_Gym_Helpers::format_staff_id($staff->staff_id) . ' - ' . $staff->name); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <input type="submit" class="button" value="View Attendance">
            <button type="button" class="button export-csv-btn" data-from-date="<?php echo esc_attr($from_date); ?>" data-to-date="<?php echo esc_attr($to_date); ?>">Export to CSV</button>
            <a href="<?php echo esc_url(remove_query_arg(['from_date', 'to_date', 'staff_id', 'date_range'])); ?>" class="button" style="background-color: #dc3545; color: white; border-color: #dc3545; text-decoration: none;">Reset</a>
        </form>
    </div>

    <!-- Statistics -->
    <div class="attendance-stats">
        <div class="stat-box">
            <h3>Attendance Records</h3>
            <p class="stat-number"><?php echo esc_html($today_count); ?></p>
        </div>
        <div class="stat-box">
            <h3>Morning Shift</h3>
            <p class="stat-number"><?php
                                    echo esc_html(count(array_filter($today_attendance, function ($record) {
                                        return $record->shift === 'morning';
                                    })));
                                    ?></p>
        </div>
        <div class="stat-box">
            <h3>Evening Shift</h3>
            <p class="stat-number">
                <?php
                echo esc_html(count(array_filter($today_attendance, function ($record) {
                    return $record->shift === 'evening';
                })));
                ?>
            </p>
        </div>

    </div>

    <div class="pm-gym-attendance-container">

        <!-- Today's Attendance -->
        <div class="today-attendance">
            <h2>Attendance Records </h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Staff ID</th>
                        <th>Name</th>
                        <th>Shift</th>
                        <th>Check-in <br> Date</th>
                        <th>Check-in <br> Time</th>
                        <th>Check-out <br> Time</th>
                        <th>Duration</th>
                        <th>Staff <br> Status</th>
                        <th>Attendance <br> Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($today_attendance as $record): ?>
                        <tr>
                            <td><?php echo esc_html(PM_Gym_Helpers::format_staff_id($record->staff_id)); ?></td>
                            <td><?php echo esc_html($record->name); ?><br><small><?php echo esc_html($record->phone); ?></small></td>
                            <td><?php echo esc_html(ucfirst($record->shift)); ?></td>
                            <td><?php echo esc_html(date('d M Y', strtotime($record->check_in_date))); ?></td>
                            <td><?php echo esc_html(date('h:i A', strtotime($record->check_in_time))); ?></td>
                            <td>
                                <?php if (!empty($record->check_out_time) && $record->check_out_time != '00:00:00'): ?>
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
                                <span class="status-<?php echo esc_attr($record->status); ?>"><?php echo esc_html(ucfirst($record->status)); ?></span>
                            </td>
                            <td>
                                <?php if (!empty($record->check_out_time) && $record->check_out_time != '00:00:00'): ?>
                                    <span class="status-checked-out">Checked Out</span>
                                <?php else: ?>
                                    <span class="status-checked-in">Checked In</span>
                                <?php endif; ?>
                            </td>
                            <td class="actions-column">
                                <?php if (empty($record->check_out_time) || $record->check_out_time == '00:00:00'): ?>
                                    <button type="button" class="button check-out-btn" data-attendance-id="<?php echo esc_attr($record->id); ?>">Check Out</button>
                                <?php endif; ?>
                                <br>
                                <a href="javascript:void(0)" class="delete-attendance-btn" data-attendance-id="<?php echo esc_attr($record->id); ?>" onclick="return confirm('Are you sure you want to delete this attendance record?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    .pm-gym-attendance-container {
        margin-top: 20px;
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
        /* flex-wrap: wrap; */
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

    .date-inputs {
        display: flex;
        gap: 10px;
        align-items: center;
    }

    .check-in-section {
        background: #fff;
        padding: 20px;
        border-radius: 5px;
        margin-bottom: 20px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .check-in-form {
        display: flex;
        gap: 10px;
        align-items: center;
    }

    .check-in-form select {
        min-width: 200px;
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

    .wp-list-table small {
        color: #666;
        display: block;
        margin-top: 3px;
    }

    /* .wp-list-table th {
        width: auto;
        min-width: 100px;
        white-space: nowrap;
        padding: 8px;
        text-align: left;
        vertical-align: middle;
        border-bottom: 1px solid #e1e1e1;
    } */

    .delete-attendance-btn {
        /* background-color: #dc3545; */
        /* border-color: #dc3545; */
        color: #dc3545;
        margin-left: 5px;
        background-color: transparent;
        border: none;
    }

    /* .delete-attendance-btn:hover {
        background-color: #c82333;
        border-color: #bd2130;
        color: #fff;
    } */

    .wp-list-table .actions-column {
        white-space: nowrap;
    }

    .wp-list-table .actions-column .button {
        margin-right: 5px;
        margin-bottom: 2px;
    }
</style>

<script>
    jQuery(document).ready(function($) {
        // Initialize datepickers
        $('#from_date, #to_date').datepicker({
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

        // Handle date range change
        $('#date_range').on('change', function() {
            var dateRange = $(this).val();
            if (dateRange === 'all') {
                $('#from_date, #to_date').prop('disabled', true);
            } else if (dateRange === 'custom') {
                $('#from_date, #to_date').prop('disabled', false);
            } else {
                $('#from_date, #to_date').prop('disabled', true);
            }
        });

        // Initial state
        if ($('#date_range').val() === 'all') {
            $('#from_date, #to_date').prop('disabled', true);
        } else if ($('#date_range').val() !== 'custom') {
            $('#from_date, #to_date').prop('disabled', true);
        }

        // Handle date changes
        $('#from_date, #to_date').on('change', function() {
            var fromDate = $('#from_date').val();
            var toDate = $('#to_date').val();

            if (fromDate && toDate) {
                var currentUrl = new URL(window.location.href);
                currentUrl.searchParams.set('from_date', fromDate);
                currentUrl.searchParams.set('to_date', toDate);
                currentUrl.searchParams.set('date_range', 'custom');
                window.location.href = currentUrl.toString();
            }
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
                    action: 'mark_staff_check_out_attendance',
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
                    action: 'delete_staff_attendance',
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
            var $exportBtn = $(this);
            var fromDate = $exportBtn.data('from-date');
            var toDate = $exportBtn.data('to-date');

            // Show loading state
            $exportBtn.prop('disabled', true).addClass('loading');

            $.ajax({
                url: pm_gym_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'export_staff_attendance',
                    from_date: fromDate,
                    to_date: toDate
                },
                success: function(response) {
                    if (response.success) {
                        // Create a temporary link to download the file
                        var link = document.createElement('a');
                        link.href = response.data.file_url;
                        link.download = 'staff-attendance-' + fromDate + '-to-' + toDate + '.csv';
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);

                        // Show success message
                        showNotification('Attendance data exported successfully', 'success');
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
    });
</script>