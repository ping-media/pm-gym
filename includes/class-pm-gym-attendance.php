<?php
if (!defined('ABSPATH')) {
    exit;
}

class PM_Gym_Attendance
{
    private $plugin_name;
    private $version;

    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;

        // Register AJAX handlers
        add_action('wp_ajax_nopriv_mark_attendance', array($this, 'mark_attendance'));
        add_action('wp_ajax_mark_attendance', array($this, 'mark_attendance'));
        add_action('wp_ajax_export_attendance', array($this, 'export_attendance'));
        add_action('wp_ajax_delete_attendance', array($this, 'delete_attendance'));
    }

    /**
     * Mark attendance
     */
    public function mark_attendance()
    {
        $is_guest = isset($_POST['is_guest']) && $_POST['is_guest'] == '1' ? true : false;
        $attendance_type = isset($_POST['attendance_type']) ? sanitize_text_field($_POST['attendance_type']) : '';
        $today = current_time('Y-m-d');
        $current_time = current_time('mysql');

        // Validate attendance type
        if (!in_array($attendance_type, ['check_in', 'check_out'])) {
            wp_send_json_error('Invalid attendance type');
            return;
        }

        if ($is_guest) {
            // Handle guest attendance
            $guest_name = isset($_POST['guest_name']) ? sanitize_text_field($_POST['guest_name']) : '';
            $guest_phone = isset($_POST['guest_phone']) ? sanitize_text_field($_POST['guest_phone']) : '';

            if (empty($guest_name) || empty($guest_phone)) {
                wp_send_json_error('Please provide both name and phone number for guest attendance');
                return;
            }

            if (!preg_match('/^[0-9]{10}$/', $guest_phone)) {
                wp_send_json_error('Please enter a valid 10-digit phone number');
                return;
            }

            // Check if guest already has attendance for today
            global $wpdb;
            $attendance_table = PM_GYM_ATTENDANCE_TABLE;
            $guest_users_table = PM_GYM_GUEST_USERS_TABLE;

            // First check if guest exists in guest users table
            $existing_guest_user = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM $guest_users_table WHERE phone = %s",
                $guest_phone
            ));

            // If guest doesn't exist, create new guest record
            if (!$existing_guest_user) {
                $guest_data = array(
                    'name' => $guest_name,
                    'phone' => $guest_phone,
                    'last_visit_date_time' => $current_time,
                    'notes' => 'First visit',
                    'status' => 'not_member'
                );

                $guest_format = array('%s', '%s', '%s', '%s', '%s');
                $wpdb->insert($guest_users_table, $guest_data, $guest_format);

                $guest_id = $wpdb->insert_id;
            } else {
                // Update last visit date for existing guest
                $wpdb->update(
                    $guest_users_table,
                    array('last_visit_date_time' => $current_time),
                    array('phone' => $guest_phone),
                    array('%s'),
                    array('%s')
                );

                $guest_id = $existing_guest_user->id;
            }

            $existing_guest = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM $attendance_table 
                WHERE is_guest = 1 
                AND guest_id = %d 
                AND DATE(check_in_time) = %s 
                LIMIT 1",
                $guest_id,
                $today
            ));

            if ($attendance_type === 'check_in') {
                if (!empty($existing_guest)) {
                    wp_send_json_error(sprintf('%s Your attendance is already recorded for today', $guest_name));
                    return;
                }

                $attendance_data = array(
                    'user_id' => $guest_id, // 0 for guests
                    'user_type' => 'guest',
                    'check_in_time' => $current_time,
                    'check_in_date' => $today
                );

                $format = array('%d', '%s', '%s', '%s', '%s', '%s');

                // Insert into attendance table
                $result = $wpdb->insert($attendance_table, $attendance_data, $format);

                if ($result) {
                    $attendance_id = $wpdb->insert_id;
                    wp_send_json_success(sprintf('%s Your attendance is recorded successfully', $guest_name));
                } else {
                    wp_send_json_error('Error recording guest check-in: ' . $wpdb->last_error);
                }
            } else { // check_out
                if (empty($existing_guest)) {
                    wp_send_json_error('No guest check-in found for today');
                    return;
                }

                if (!empty($existing_guest->check_out_time)) {
                    wp_send_json_error('Guest check-out already recorded for today');
                    return;
                }

                // Calculate duration
                $check_in = new DateTime($existing_guest->check_in_time);
                $check_out = new DateTime($current_time);
                $duration = $check_in->diff($check_out);
                $duration_minutes = ($duration->h * 60) + $duration->i;

                // Update attendance record
                $result = $wpdb->update(
                    $attendance_table,
                    array(
                        'check_out_time' => $current_time,
                        'attendance_type' => 'complete',
                        'duration_minutes' => $duration_minutes
                    ),
                    array('id' => $existing_guest->id),
                    array('%s', '%s', '%d'),
                    array('%d')
                );

                if ($result) {
                    wp_send_json_success('Guest check-out recorded successfully');
                } else {
                    wp_send_json_error('Error recording check-out: Database error');
                }
            }
        } else {
            // Handle regular member attendance
            $member_id = isset($_POST['member_id']) ? sanitize_text_field($_POST['member_id']) : '';

            // Validate member ID format
            if (!preg_match('/^[0-9]{4}$/', $member_id)) {
                wp_send_json_error('Invalid member ID format');
                return;
            }

            // Find member by ID
            global $wpdb;
            $members_table = PM_GYM_MEMBERS_TABLE;

            // Convert the 4-digit member ID to the actual member_id in the database
            $member_id_numeric = intval($member_id);

            $member = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT * FROM $members_table WHERE member_id = %d",
                    $member_id_numeric
                )
            );

            if (empty($member)) {
                wp_send_json_error('Member not found');
                return;
            }

            // Check membership status from the members table
            if ($member->status === 'suspended') {
                wp_send_json_error('Hello ' . $member->name . ', your membership is suspended. Please contact the gym staff.');
                return;
            }

            // Get expiry date if needed
            $expiry_date = $member->expiry_date ?? '';

            // if (!empty($expiry_date) && $today > $expiry_date && $attendance_type === 'check_in') {
            //     wp_send_json_error('Your membership has expired. Please renew your membership.');
            //     return;
            // }

            // Check existing attendance for today
            // Check existing attendance for today from the attendance table
            $attendance_table = PM_GYM_ATTENDANCE_TABLE;
            $existing_attendance = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM $attendance_table 
                    WHERE user_id = %d 
                    AND check_in_date = %s",
                    $member->id,
                    $today
                )
            );

            if ($attendance_type === 'check_in') {
                // Use a transient to lock check-ins for this member to prevent race conditions
                $lock_key = 'pm_gym_checkin_lock_' . $member->id;
                if (false === add_transient($lock_key, true, 15)) { // Lock for 15 seconds
                    wp_send_json_error('A check-in is already in progress. Please wait a moment and try again.');
                    return;
                }

                // Re-check for an existing check-in now that we have a lock.
                $already_checked_in = $wpdb->get_var($wpdb->prepare(
                    "SELECT id FROM $attendance_table WHERE user_id = %d AND check_in_date = %s",
                    $member->id,
                    $today
                ));

                if (!empty($already_checked_in)) {
                    delete_transient($lock_key); // Release the lock
                    wp_send_json_error('Hello ' . $member->name . ', you have already checked in today!');
                    return;
                }

                // Insert record into attendance table
                $attendance_data = array(
                    'user_id' => $member->id,
                    'user_type' => 'member',
                    'check_in_date' => $today,
                    'check_in_time' => PM_Gym_Helpers::get_current_time()
                );

                $result = $wpdb->insert(
                    $attendance_table,
                    $attendance_data,
                    array('%d', '%s', '%s', '%s')
                );

                delete_transient($lock_key); // Always release the lock after the operation

                // Print detailed error information if the insert fails
                if ($result === false) {
                    $db_error = $wpdb->last_error;
                    error_log('Attendance check-in error: ' . $db_error);
                    wp_send_json_error('Error recording check-in: ' . $db_error);
                    return;
                }

                if ($result) {
                    wp_send_json_success('Hello ' . $member->name . ', your check-in has been recorded successfully!');
                } else {
                    wp_send_json_error('Error recording check-in: Database error');
                }
            } else {
                // Handle check-out
                if (empty($existing_attendance)) {
                    wp_send_json_error('No check-in found for today');
                    return;
                }

                // Get the latest check-in record for today
                $attendance_record = $existing_attendance[0];

                // Check if checkout already recorded
                if (!empty($attendance_record->check_out_time)) {
                    wp_send_json_error('Check-out already recorded for today');
                    return;
                }

                // Calculate duration
                $check_in_time = $attendance_record->check_in_time;
                $duration = $this->calculate_duration($check_in_time, $current_time);

                // Update with check-out time
                $result = $wpdb->update(
                    $attendance_table,
                    array(
                        'check_out_time' => $current_time,
                    ),
                    array('id' => $attendance_record->id),
                    array('%s'),
                    array('%d')
                );

                if ($result) {
                    wp_send_json_success(array(
                        'message' => 'Check-out recorded successfully'
                    ));
                } else {
                    wp_send_json_error('Error recording check-out: Database error');
                }
            }
        }
    }

    // Handle staff attendance CSV Export
    public function export_attendance()
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized access');
        }

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
                ORDER BY a.check_in_time DESC"
            )
        );

        // Create temporary file
        $upload_dir = wp_upload_dir();
        $export_dir = $upload_dir['basedir'] . '/gym-exports';

        // Create directory if it doesn't exist
        if (!file_exists($export_dir)) {
            wp_mkdir_p($export_dir);
        }

        $filename = 'attendance-' . date('Y-m-d') . '.csv';
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
            'Date',
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
                date('Y-m-d', strtotime($row->check_in_date)),
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

    /**
     * Delete attendance record
     */
    public function delete_attendance()
    {
        // Check if user has permission
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized access');
            return;
        }

        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

        if ($id > 0) {
            global $wpdb;
            $attendance_table = PM_GYM_ATTENDANCE_TABLE;

            $result = $wpdb->delete(
                $attendance_table,
                array('id' => $id),
                array('%d')
            );

            if ($result) {
                wp_send_json_success(array('message' => 'Attendance record deleted successfully'));
                return;
            }
        }

        wp_send_json_error('Error deleting attendance record');
    }

    /**
     * Calculate duration between two times
     */
    private function calculate_duration($check_in_time, $check_out_time)
    {
        $check_in = new DateTime($check_in_time);
        $check_out = new DateTime($check_out_time);
        $duration = $check_in->diff($check_out);
        return $duration->format('%H:%I:%S');
    }
}
