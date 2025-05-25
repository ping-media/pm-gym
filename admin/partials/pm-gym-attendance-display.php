<?php
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$members_table = PM_GYM_MEMBERS_TABLE;

// Handle attendance check-in
if (isset($_POST['check_in']) && isset($_POST['member_id'])) {
    $member_id = intval($_POST['member_id']);
    $current_time = current_time('mysql');

    // Check if member already checked in today
    $existing_checkin = $wpdb->get_var($wpdb->prepare(
        "SELECT ID FROM {$wpdb->posts} p 
        JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id 
        WHERE p.post_type = 'gym_attendance' 
        AND pm.meta_key = 'member_id' 
        AND pm.meta_value = %d 
        AND DATE(p.post_date) = CURDATE()",
        $member_id
    ));

    if (!$existing_checkin) {
        // Create attendance record
        $attendance_data = array(
            'post_title' => 'Attendance Record',
            'post_type' => 'gym_attendance',
            'post_status' => 'publish',
            'post_date' => $current_time
        );

        $attendance_id = wp_insert_post($attendance_data);

        if (!is_wp_error($attendance_id)) {
            // Save attendance meta data
            update_post_meta($attendance_id, 'member_id', $member_id);
            update_post_meta($attendance_id, 'check_in_time', $current_time);

            add_settings_error(
                'gym_attendance',
                'attendance_recorded',
                'Attendance recorded successfully.',
                'success'
            );
        } else {
            add_settings_error(
                'gym_attendance',
                'attendance_error',
                'Error recording attendance.',
                'error'
            );
        }
    } else {
        add_settings_error(
            'gym_attendance',
            'already_checked_in',
            'Member has already checked in today.',
            'error'
        );
    }
}

// Handle check-out
if (isset($_POST['check_out']) && isset($_POST['attendance_id'])) {
    $attendance_id = intval($_POST['attendance_id']);
    $check_out_time = current_time('mysql');

    // Get check-in time
    $check_in_time = get_post_meta($attendance_id, 'check_in_time', true);

    // Calculate duration
    $check_in = new DateTime($check_in_time);
    $check_out = new DateTime($check_out_time);
    $duration = $check_in->diff($check_out);
    $duration_str = $duration->format('%H hours %i minutes');

    // Update attendance record
    update_post_meta($attendance_id, 'check_out_time', $check_out_time);
    update_post_meta($attendance_id, 'duration', $duration_str);

    add_settings_error(
        'gym_attendance',
        'checkout_success',
        'Check-out recorded successfully.',
        'success'
    );
}

// Handle CSV Export
add_action('wp_ajax_export_attendance_csv', 'pm_gym_export_attendance_csv');
function pm_gym_export_attendance_csv()
{
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized access');
    }

    $export_date = isset($_GET['date']) ? sanitize_text_field($_GET['date']) : current_time('Y-m-d');

    global $wpdb;

    // Get attendance data for export
    $export_data = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT 
                CASE 
                    WHEN a.user_type = 'member' THEN m.member_id
                    ELSE NULL
                END as member_id,
                a.user_type,
                CASE 
                    WHEN a.user_type = 'member' THEN m.name
                    WHEN a.user_type = 'guest' THEN g.name
                END as name,
                CASE 
                    WHEN a.user_type = 'member' THEN m.phone
                    WHEN a.user_type = 'guest' THEN g.phone
                END as phone,
                a.check_in_time,
                a.check_out_time,
                CASE 
                    WHEN a.user_type = 'member' THEN m.status
                    ELSE NULL
                END as status
            FROM " . PM_GYM_ATTENDANCE_TABLE . " a
            LEFT JOIN " . PM_GYM_MEMBERS_TABLE . " m ON a.user_id = m.id AND a.user_type = 'member'
            LEFT JOIN " . PM_GYM_GUEST_USERS_TABLE . " g ON a.user_id = g.id AND a.user_type = 'guest'
            WHERE DATE(a.check_in_date) = %s
            ORDER BY a.check_in_time DESC",
            $export_date
        )
    );

    // Create temporary file
    $upload_dir = wp_upload_dir();
    $export_dir = $upload_dir['basedir'] . '/gym-exports';

    // Create directory if it doesn't exist
    if (!file_exists($export_dir)) {
        wp_mkdir_p($export_dir);
    }

    $filename = 'attendance-' . $export_date . '.csv';
    $filepath = $export_dir . '/' . $filename;

    // Open file for writing
    $fp = fopen($filepath, 'w');

    // Add UTF-8 BOM for proper Excel encoding
    fprintf($fp, chr(0xEF) . chr(0xBB) . chr(0xBF));

    // Add CSV headers
    fputcsv($fp, array(
        'Member ID',
        'User Type',
        'Name',
        'Contact',
        'Check-in Time',
        'Check-out Time',
        'Duration',
        'Status'
    ));

    // Add data rows
    foreach ($export_data as $row) {
        $duration = '';
        if (!empty($row->check_out_time)) {
            $check_in = new DateTime($row->check_in_time);
            $check_out = new DateTime($row->check_out_time);
            $duration = $check_in->diff($check_out)->format('%H hours %i minutes');
        }

        fputcsv($fp, array(
            $row->user_type === 'member' ? PM_Gym_Helpers::format_member_id($row->member_id) : '-',
            ucfirst($row->user_type),
            $row->name,
            $row->phone,
            date('h:i A', strtotime($row->check_in_time)),
            !empty($row->check_out_time) ? date('h:i A', strtotime($row->check_out_time)) : '-',
            $duration,
            $row->status ? ucfirst($row->status) : '-'
        ));
    }

    fclose($fp);

    // Get the URL for the file
    $file_url = $upload_dir['baseurl'] . '/gym-exports/' . $filename;

    // Send success response with file URL
    wp_send_json_success(array(
        'file_url' => $file_url,
        'message' => 'Attendance data exported successfully'
    ));
}

// Get today's attendance records
$selected_date = isset($_GET['attendance_date']) ? sanitize_text_field($_GET['attendance_date']) : current_time('Y-m-d');
$today_attendance = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT a.*, 
            CASE 
                WHEN a.user_type = 'member' THEN m.name
                WHEN a.user_type = 'guest' THEN g.name
            END as name,
            CASE 
                WHEN a.user_type = 'member' THEN m.phone
                WHEN a.user_type = 'guest' THEN g.phone
            END as phone,
            CASE 
                WHEN a.user_type = 'member' THEN m.member_id
                ELSE NULL
            END as member_id_number,
            CASE 
                WHEN a.user_type = 'member' THEN m.status
                ELSE NULL
            END as status
        FROM " . PM_GYM_ATTENDANCE_TABLE . " a
        LEFT JOIN " . PM_GYM_MEMBERS_TABLE . " m ON a.user_id = m.id AND a.user_type = 'member'
        LEFT JOIN " . PM_GYM_GUEST_USERS_TABLE . " g ON a.user_id = g.id AND a.user_type = 'guest'
        WHERE DATE(a.check_in_date) = %s
        ORDER BY a.check_in_time DESC",
        $selected_date
    )
);

// Debug output
if ($wpdb->last_error) {
    error_log('Attendance Query Error: ' . $wpdb->last_error);
}



// Get all active members
$active_members = $wpdb->get_results(
    "SELECT * FROM $members_table 
    WHERE status = 'active' 
    ORDER BY name ASC"
);

// Calculate statistics
$total_members = count($active_members);
$today_count = count($today_attendance);
$attendance_rate = $total_members > 0 ? round(($today_count / $total_members) * 100, 1) : 0;
?>

<div class="wrap">
    <h1>Attendance Management</h1>

    <?php settings_errors('gym_attendance'); ?>

    <!-- Date Selection Form -->
    <div class="date-selection">
        <form method="get" action="">
            <input type="hidden" name="page" value="<?php echo esc_attr($_GET['page']); ?>">
            <label for="attendance_date">Select Date:</label>
            <input type="date" id="attendance_date" name="attendance_date" value="<?php echo esc_attr($selected_date); ?>" max="<?php echo current_time('Y-m-d'); ?>">
            <input type="submit" class="button" value="View Attendance">
            <button type="button" class="button export-csv-btn" data-date="<?php echo esc_attr($selected_date); ?>">Export to CSV</button>
        </form>
    </div>

    <!-- Statistics -->
    <div class="attendance-stats">
        <div class="stat-box">
            <h3>Today's Attendance</h3>
            <p class="stat-number"><?php echo esc_html($today_count); ?></p>
        </div>
        <div class="stat-box">
            <h3>Members Today</h3>
            <p class="stat-number"><?php echo esc_html(count(array_filter($today_attendance, function ($record) {
                                        return $record->user_type === 'member';
                                    }))); ?></p>
        </div>
        <div class="stat-box">
            <h3>Guests Today</h3>
            <p class="stat-number"><?php echo esc_html(count(array_filter($today_attendance, function ($record) {
                                        return $record->user_type === 'guest';
                                    }))); ?></p>
        </div>
        <div class="stat-box">
            <h3>Member Attendance Rate</h3>
            <p class="stat-number"><?php
                                    $member_attendance = count(array_filter($today_attendance, function ($record) {
                                        return $record->user_type === 'member' && $record->status === 'active';
                                    }));
                                    $member_attendance_rate = $total_members > 0 ? round(($member_attendance / $total_members) * 100, 1) : 0;
                                    echo esc_html($member_attendance_rate);
                                    ?>%</p>
        </div>
    </div>

    <div class="pm-gym-attendance-container">

        <!-- Today's Attendance -->
        <div class="today-attendance">
            <h2>Today's Attendance</h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Member ID</th>
                        <th>User Type</th>
                        <th>Name</th>
                        <th>Contact</th>
                        <th>Check-in Time</th>
                        <th>Check-out Time</th>
                        <th>Duration</th>
                        <th>Member Status</th>
                        <th>Attendance Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($today_attendance as $record): ?>
                        <tr>
                            <td><?php echo $record->user_type === 'member' ? esc_html(PM_Gym_Helpers::format_member_id($record->member_id_number)) : '-'; ?></td>
                            <td><?php echo esc_html($record->user_type); ?></td>
                            <td><?php echo esc_html($record->name); ?></td>
                            <td><?php echo esc_html($record->phone); ?></td>
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
                                <?php if ($record->user_type === 'member'): ?>
                                    <span class="status-<?php echo esc_attr($record->status); ?>"><?php echo esc_html(ucfirst($record->status)); ?></span>
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
                            <td>
                                <?php if (empty($record->check_out_time)): ?>
                                    <button type="button" class="button check-out-btn" data-attendance-id="<?php echo esc_attr($record->id); ?>">Check Out</button>
                                <?php endif; ?>
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
                    action: 'export_attendance_csv',
                    date: date
                },
                success: function(response) {
                    if (response.success) {
                        // Create a temporary link to download the file
                        var link = document.createElement('a');
                        link.href = response.data.file_url;
                        link.download = 'attendance-' + date + '.csv';
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