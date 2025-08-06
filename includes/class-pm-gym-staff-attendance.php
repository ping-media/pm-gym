<?php
if (!defined('ABSPATH')) {
    exit;
}

class PM_Gym_Staff_Attendance
{
    private $plugin_name;
    private $version;

    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;

        // Register AJAX handlers
        add_action('wp_ajax_mark_staff_attendance', array($this, 'mark_staff_attendance'));
        add_action('wp_ajax_nopriv_mark_staff_attendance', array($this, 'mark_staff_attendance'));
        add_action('wp_ajax_export_staff_attendance', array($this, 'export_staff_attendance'));
        add_action('wp_ajax_delete_staff_attendance', array($this, 'delete_staff_attendance'));
    }

    /**
     * Mark staff attendance
     */
    public function mark_staff_attendance()
    {
        $attendance_type = isset($_POST['attendance_type']) ? sanitize_text_field($_POST['attendance_type']) : '';
        $record_id = isset($_POST['record_id']) ? sanitize_text_field($_POST['record_id']) : '';
        $shift = isset($_POST['shift']) ? sanitize_text_field($_POST['shift']) : 'morning';

        if (empty($attendance_type) || empty($record_id)) {
            wp_send_json_error('Attendance type and record ID are required');
            return;
        }

        if (!in_array($attendance_type, array('check_in', 'check_out'))) {
            wp_send_json_error('Invalid attendance type');
            return;
        }

        if (!in_array($shift, array('morning', 'evening'))) {
            wp_send_json_error('Invalid shift');
            return;
        }

        global $wpdb;
        $staff_table = PM_GYM_STAFF_TABLE;
        $attendance_table = PM_GYM_STAFF_ATTENDANCE_TABLE;

        // Check if staff exists and is active
        $staff = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $staff_table WHERE id = %d AND status = 'active'",
                $record_id
            )
        );

        if (!$staff) {
            wp_send_json_error('Staff not found or inactive');
            return;
        }

        $current_date = current_time('Y-m-d');
        $current_time = current_time('mysql');
        $current_hour = current_time('G'); // Get current hour in 24-hour format

        // Validate shift timing
        // if ($shift === 'morning' && $current_hour >= 15) {
        //     wp_send_json_error('Morning shift attendance can only be marked before 3:00 PM');
        //     return;
        // }

        // if ($shift === 'evening' && $current_hour <= 15) {
        //     wp_send_json_error('Evening shift attendance can only be marked after 3:00 PM');
        //     return;
        // }

        // For check-in
        if ($attendance_type === 'check_in') {
            // Check if already checked in for this shift today
            $existing_check_in = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT * FROM $attendance_table 
                    WHERE staff_id = %d 
                    AND DATE(check_in_date) = %s
                    AND shift = %s",
                    $record_id,
                    $current_date,
                    $shift
                )
            );

            if ($existing_check_in) {
                wp_send_json_error('You have already checked in for this shift today');
                return;
            }

            // Insert check-in record
            $result = $wpdb->insert(
                $attendance_table,
                array(
                    'staff_id' => $record_id,
                    'shift' => $shift,
                    'check_in_time' => $current_time,
                    'check_in_date' => $current_date
                ),
                array('%d', '%s', '%s', '%s')
            );

            if ($result === false) {
                wp_send_json_error('Failed to mark check-in', $wpdb->last_error);
                return;
            }

            wp_send_json_success('Check-in marked successfully');
        }
        // For check-out
        else {
            // Check if checked in for this shift today
            $check_in_record = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT * FROM $attendance_table 
                    WHERE staff_id = %d 
                    AND DATE(check_in_date) = %s 
                    AND shift = %s
                    AND check_in_time IS NOT NULL",
                    $record_id,
                    $current_date,
                    $shift
                )
            );

            if (!$check_in_record) {
                wp_send_json_error('No check-in record found for this shift today');
                return;
            }

            // Update check-out time
            $result = $wpdb->update(
                $attendance_table,
                array(
                    'check_out_time' => $current_time
                ),
                array(
                    'id' => $check_in_record->id
                ),
                array('%s'),
                array('%d')
            );

            if ($result === false) {
                wp_send_json_error('Failed to mark check-out');
                return;
            }

            wp_send_json_success('Check-out marked successfully');
        }
    }

    // Handle staff attendance CSV Export
    public function export_staff_attendance()
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized access');
        }

        global $wpdb;

        // Get attendance data for export
        $export_data = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT 
                    s.staff_id,
                    s.name,
                    s.phone,
                    s.role,
                    a.shift,
                    a.check_in_time,
                    a.check_out_time,
                    a.check_in_date,
                    s.status
                FROM " . PM_GYM_STAFF_ATTENDANCE_TABLE . " a
                LEFT JOIN " . PM_GYM_STAFF_TABLE . " s ON a.staff_id = s.id
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

        $filename = 'staff-attendance-' . current_time('Y-m-d') . '.csv';
        $filepath = $export_dir . '/' . $filename;

        // Open file for writing
        $fp = fopen($filepath, 'w');

        // Add UTF-8 BOM for proper Excel encoding
        fprintf($fp, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Add CSV headers
        fputcsv($fp, array(
            'Staff ID',
            'Name',
            'Phone',
            'Role',
            'Shift',
            'Check-in Date',
            'Check-in Time',
            'Check-out Time',
            'Duration',
            'Staff Status',
            'Attendance Status'
        ));

        // Add data rows
        foreach ($export_data as $row) {
            $duration = '';
            if (!empty($row->check_out_time)) {
                $check_in = new DateTime($row->check_in_time);
                $check_out = new DateTime($row->check_out_time);
                $duration = $check_in->diff($check_out)->format('%H:%I:%S');
            }

            fputcsv($fp, array(
                PM_Gym_Helpers::format_staff_id($row->staff_id),
                $row->name,
                $row->phone,
                $row->role,
                $row->shift,
                $row->check_in_date,
                date('h:i A', strtotime($row->check_in_time)),
                !empty($row->check_out_time) && $row->check_out_time != '00:00:00' ? date('h:i A', strtotime($row->check_out_time)) : '-',
                $duration,
                $row->status ? ucfirst($row->status) : '-',
                !empty($row->check_out_time) && $row->check_out_time != '00:00:00' ? 'Checked Out' : 'Checked In'
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
     * Delete staff attendance record
     */
    public function delete_staff_attendance()
    {
        // Check if user has permission
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized access');
            return;
        }

        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

        if ($id > 0) {
            global $wpdb;
            $staff_attendance_table = PM_GYM_STAFF_ATTENDANCE_TABLE;

            $result = $wpdb->delete(
                $staff_attendance_table,
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
}
