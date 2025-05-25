<?php
if (!defined('ABSPATH')) {
    exit;
}

class PM_Gym_Admin
{
    private $plugin_name;
    private $version;

    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;

        // Add AJAX handlers
        add_action('wp_ajax_get_member_data', array($this, 'get_member_data'));
        add_action('wp_ajax_save_gym_member', array($this, 'save_gym_member'));
        add_action('wp_ajax_delete_member', array($this, 'delete_member'));
        add_action('wp_ajax_bulk_upload_members', array($this, 'bulk_upload_members'));
        add_action('wp_ajax_get_next_member_id', array($this, 'get_next_member_id'));
        add_action('wp_ajax_export_members_csv', array($this, 'export_members_csv'));
        add_action('wp_ajax_export_attendance_csv', array($this, 'export_attendance_csv'));

        // Add AJAX handler for front-end member details
        add_action('wp_ajax_get_member_details_for_front_end', array($this, 'get_member_details_for_front_end'));
        add_action('wp_ajax_nopriv_get_member_details_for_front_end', array($this, 'get_member_details_for_front_end'));

        add_action('wp_ajax_nopriv_mark_attendance', array($this, 'mark_attendance'));
        add_action('wp_ajax_mark_attendance', array($this, 'mark_attendance'));

        add_action('wp_ajax_nopriv_mark_check_out_attendance', array($this, 'mark_check_out_attendance'));
        add_action('wp_ajax_mark_check_out_attendance', array($this, 'mark_check_out_attendance'));

        // Add menu hiding functionality
        add_action('admin_menu', array($this, 'hide_admin_menus'), 999);

        // Add dashboard metabox hiding functionality
        add_action('wp_dashboard_setup', array($this, 'remove_dashboard_metaboxes'), 999);

        // Enqueue admin styles and scripts
        add_action('admin_enqueue_scripts', array($this, 'enqueue_styles'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));

        // Add new AJAX handler for converting a guest to a member
        add_action('wp_ajax_convert_guest_to_member', array($this, 'convert_guest_to_member'));
    }

    public function enqueue_styles()
    {
        // Enqueue admin styles
        wp_enqueue_style(
            $this->plugin_name,
            plugin_dir_url(__FILE__) . 'css/pm-gym-admin.css',
            array(),
            $this->version,
            'all'
        );
    }

    public function enqueue_scripts()
    {
        // Enqueue jQuery UI datepicker
        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_style('jquery-ui', 'https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css');

        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/pm-gym-admin.js', array('jquery'), $this->version, false);

        // Localize the script with new data
        wp_localize_script($this->plugin_name, 'pm_gym_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php')
        ));
    }

    public function add_admin_menu()
    {
        add_menu_page(
            'Gym Management',
            'Gym Management',
            'manage_options',
            'pm-gym',
            array($this, 'display_plugin_admin_page'),
            'dashicons-universal-access',
            6
        );

        add_submenu_page(
            'pm-gym',
            'Members',
            'Members',
            'manage_options',
            'pm-gym-members',
            array($this, 'display_members_page')
        );

        add_submenu_page(
            'pm-gym',
            'Guests',
            'Guests',
            'manage_options',
            'pm-gym-guests',
            array($this, 'display_guests_page')
        );

        add_submenu_page(
            'pm-gym',
            'Attendance',
            'Attendance',
            'manage_options',
            'pm-gym-attendance',
            array($this, 'display_attendance_page')
        );

        // add_submenu_page(
        //     'pm-gym',
        //     'Fees',
        //     'Fees',
        //     'manage_options',
        //     'pm-gym-fees',
        //     array($this, 'display_fees_page')
        // );
    }

    public function hide_admin_menus()
    {
        // Get current user
        $user = wp_get_current_user();

        // hide for all and exclude only user id 1
        if ($user->ID !== 1) {
            remove_menu_page('index.php');                  // Dashboard
            remove_menu_page('edit.php');                   // Posts
            remove_menu_page('upload.php');
            remove_menu_page('edit.php?post_type=page');    // Pages
            remove_menu_page('edit-comments.php');          // Comments
            remove_menu_page('themes.php');                 // Appearance
            remove_menu_page('plugins.php');                // Plugins
            remove_menu_page('users.php');                  // Users
            remove_menu_page('tools.php');                  // Tools
            remove_menu_page('options-general.php');        // Settings
            remove_menu_page('profile.php');                // Profile
            remove_menu_page('oceanwp');
            remove_menu_page('ai1wm_export');
        }
    }

    /**
     * Remove all dashboard metaboxes/widgets
     */
    public function remove_dashboard_metaboxes()
    {
        // Get current user
        $user = wp_get_current_user();

        // Only apply to non-admin users (exclude user ID 1)
        if ($user->ID !== 1) {
            // Remove Welcome panel
            remove_action('welcome_panel', 'wp_welcome_panel');

            // Remove all dashboard widgets at once by emptying the global $wp_meta_boxes array for dashboard
            global $wp_meta_boxes;
            if (isset($wp_meta_boxes['dashboard'])) {
                $wp_meta_boxes['dashboard'] = array();
            }
        }
    }

    public function display_plugin_admin_page()
    {
        require_once plugin_dir_path(__FILE__) . 'partials/pm-gym-admin-display.php';
    }

    public function display_members_page()
    {
        require_once plugin_dir_path(__FILE__) . 'partials/pm-gym-members-display.php';
    }

    public function display_guests_page()
    {
        require_once plugin_dir_path(__FILE__) . 'partials/pm-gym-guests-display.php';
    }

    public function display_attendance_page()
    {
        require_once plugin_dir_path(__FILE__) . 'partials/pm-gym-attendance-display.php';
    }

    public function display_guest_attendance_page()
    {
        require_once plugin_dir_path(__FILE__) . 'partials/pm-gym-guests-display.php';
    }

    public function display_fees_page()
    {
        require_once plugin_dir_path(__FILE__) . 'partials/pm-gym-fees-display.php';
    }

    public function get_member_data()
    {
        // Check if user has permission
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized access');
            return;
        }

        $member_id = isset($_POST['member_id']) ? intval($_POST['member_id']) : 0;

        if ($member_id > 0) {
            global $wpdb;
            $members_table = PM_GYM_MEMBERS_TABLE;

            $member = $wpdb->get_row(
                $wpdb->prepare("SELECT * FROM $members_table WHERE id = %d", $member_id)
            );

            if ($member) {
                $data = array(
                    'ID' => $member->id,
                    'title' => $member->name,
                    'phone' => $member->phone,
                    'address' => $member->address,
                    'status' => $member->status,
                    'membership_type' => $member->membership_type,
                    'join_date' => $member->join_date,
                    'aadhar_number' => $member->aadhar_number,
                    'email' => $member->email,
                    'gender' => $member->gender,
                    'dob' => $member->dob,
                    'expiry_date' => $member->expiry_date,
                    'member_id' => PM_Gym_Helpers::format_member_id($member->member_id)
                );

                // Get signature data from member meta table
                $member_meta_table = PM_GYM_MEMBER_META_TABLE;
                $signature = $wpdb->get_var(
                    $wpdb->prepare(
                        "SELECT meta_value FROM $member_meta_table WHERE member_id = %d AND meta_key = 'signature'",
                        $member->id
                    )
                );

                if ($signature) {
                    // Ensure signature data is valid JSON
                    $decoded = json_decode($signature, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        // Data is already valid JSON, no need to encode again
                        $data['signature'] = $signature;
                    } else {
                        // Try to sanitize and fix the data
                        $signature = stripslashes($signature);
                        $decoded = json_decode($signature, true);
                        if (json_last_error() === JSON_ERROR_NONE) {
                            // Now it's valid
                            $data['signature'] = $signature;
                        } else {
                            // Still invalid, don't send it
                            error_log('Invalid signature data found for member ID: ' . $member->id);
                        }
                    }
                }
                wp_send_json_success($data);
                return;
            }
        }

        wp_send_json_error('Invalid member ID');
    }

    public function save_gym_member()
    {
        // Check if user has permission
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized access');
            return;
        }

        global $wpdb;
        $members_table = $wpdb->prefix . 'pm_gym_members';

        // Get and sanitize member data
        $member_id = isset($_POST['member_id']) ? intval($_POST['member_id']) : null;
        $member_name = isset($_POST['member_name']) ? sanitize_text_field($_POST['member_name']) : '';
        $phone = isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : null;
        $address = isset($_POST['address']) ? sanitize_textarea_field($_POST['address']) : null;
        $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : 'pending';
        $membership_type = isset($_POST['membership_type']) && $_POST['membership_type'] > 0 ? intval($_POST['membership_type']) : 0;
        $record_id = isset($_POST['record_id']) && $_POST['record_id'] > 0 ? intval($_POST['record_id']) : null;
        $aadhar_number = isset($_POST['aadhar_number']) && !empty($_POST['aadhar_number']) ? sanitize_text_field($_POST['aadhar_number']) : null;
        $email = isset($_POST['email']) && !empty($_POST['email']) ? sanitize_email($_POST['email']) : null;
        $gender = isset($_POST['gender']) && !empty($_POST['gender']) ? sanitize_text_field($_POST['gender']) : null;
        $dob = isset($_POST['dob']) && !empty($_POST['dob']) ? sanitize_text_field($_POST['dob']) : null;
        $expiry_date = isset($_POST['expiry_date']) && !empty($_POST['expiry_date']) ? sanitize_text_field($_POST['expiry_date']) : null;
        $reference = isset($_POST['reference']) && !empty($_POST['reference']) ? sanitize_text_field($_POST['reference']) : null;

        // Validate required fields
        if (empty($member_name)) {
            wp_send_json_error('Member name is required');
            return;
        }

        if (empty($member_id)) {
            $member_id = PM_Gym_Helpers::get_next_member_id();
        }

        if (empty($phone)) {
            wp_send_json_error('Phone number is required');
            return;
        }

        // Check if aadhar number already exists and belongs to different member
        if (!empty($aadhar_number)) {
            $existing_member = $wpdb->get_var(
                $wpdb->prepare("SELECT id FROM $members_table WHERE aadhar_number = %s AND id != %d", $aadhar_number, $record_id)
            );

            if ($existing_member) {
                wp_send_json_error('A member with this aadhar number already exists');
                return;
            }
        }

        // Check if phone number already exists and belongs to different member
        if (!empty($phone)) {
            $existing_member = $wpdb->get_var(
                $wpdb->prepare("SELECT id FROM $members_table WHERE phone = %s AND id != %d", $phone, $record_id)
            );

            if ($existing_member) {
                wp_send_json_error('A member with this phone number already exists');
                return;
            }
        }

        // Check if member ID already exists and belongs to a different member
        if (!empty($member_id)) {
            $existing_member = $wpdb->get_var(
                $wpdb->prepare("SELECT id FROM $members_table WHERE member_id = %d AND id != %d", $member_id, $record_id)
            );

            if ($existing_member) {
                wp_send_json_error('A member with this ID already exists');
                return;
            }
        }

        // Prepare member data for database
        $member_data = array(
            'name' => $member_name,
            'phone' => $phone,
            'address' => $address,
            'status' => $status,
            'membership_type' => $membership_type,
            'aadhar_number' => $aadhar_number,
            'member_id' => $member_id,
            'email' => $email,
            'gender' => $gender,
            'dob' => $dob,
            'expiry_date' => $expiry_date,
            'reference' => $reference
        );

        $member_data_format = array('%s', '%s', '%s', '%s', '%d', '%s', '%d', '%s', '%s', '%s', '%s', '%s');

        // Update or insert member
        if ($record_id > 0) {
            // Update existing member
            $result = $wpdb->update(
                $members_table,
                $member_data,
                array('id' => $record_id),
                $member_data_format,
                array('%d')
            );

            if ($result !== false) {
                wp_send_json_success(array(
                    'message' => 'Member updated successfully',
                    'member_id' => $record_id
                ));
            } else {
                wp_send_json_error('Error updating member: ' . $wpdb->last_error);
            }
        } else {
            // Add join date for new members
            $member_data['join_date'] = date('Y-m-d');
            $member_data_format[] = '%s';

            // Insert new member
            $result = $wpdb->insert(
                $members_table,
                $member_data,
                $member_data_format
            );

            if ($result) {
                // Insert member meta
                $member_meta_table = PM_GYM_MEMBER_META_TABLE;
                $signature = isset($_POST['signature']) ? $_POST['signature'] : null;
                $member_id = $wpdb->insert_id;

                if (!empty($signature)) {
                    // Insert member meta
                    $member_meta_data = array(
                        'member_id' => $member_id,
                        'meta_key' => 'signature',
                        'meta_value' => $signature
                    );

                    $member_meta_format = array('%d', '%s', '%s');

                    $wpdb->insert($member_meta_table, $member_meta_data, $member_meta_format);
                }

                wp_send_json_success(array(
                    'message' => 'Member added successfully',
                    'member_id' => $wpdb->insert_id
                ));
            } else {
                wp_send_json_error('Error adding member: ' . $wpdb->last_error);
            }
        }
    }

    public function delete_member()
    {
        // Check if user has permission
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized access');
            return;
        }

        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

        if ($id > 0) {
            global $wpdb;
            $members_table = $wpdb->prefix . 'pm_gym_members';

            $result = $wpdb->delete(
                $members_table,
                array('id' => $id),
                array('%d')
            );

            if ($result) {
                wp_send_json_success(array('message' => 'Member deleted successfully'));
                return;
            }
        }

        wp_send_json_error('Error deleting member');
    }

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
                if (!empty($existing_attendance)) {
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

    public function mark_check_out_attendance()
    {
        $attendance_id = isset($_POST['attendance_id']) ? intval($_POST['attendance_id']) : 0;
        $check_out_time = current_time('mysql');

        // Validate attendance ID
        if ($attendance_id <= 0) {
            wp_send_json_error('Invalid attendance ID');
            return;
        }

        // Validate check-out time
        if (empty($check_out_time)) {
            wp_send_json_error('Invalid check-out time');
            return;
        }

        global $wpdb;
        $attendance_table = PM_GYM_ATTENDANCE_TABLE;

        // Update attendance record
        $result = $wpdb->update(
            $attendance_table,
            array(
                'check_out_time' => $check_out_time,
            ),
            array('id' => $attendance_id),
            array('%s'),
            array('%d')
        );

        if ($result) {
            wp_send_json_success('Check-out recorded successfully');
        } else {
            wp_send_json_error('Error recording check-out: Database error');
        }
    }


    /**
     * Get member details based on member ID
     */
    public function get_member_details_for_front_end()
    {
        $member_id = isset($_POST['member_id']) ? sanitize_text_field($_POST['member_id']) : '';

        // Validate member ID format
        if (!preg_match('/^[0-9]{4,5}$/', $member_id)) {
            wp_send_json_error('Invalid member ID format');
            return;
        }

        // Find member by ID
        global $wpdb;
        $members_table = $wpdb->prefix . 'pm_gym_members';

        // Convert the member ID to numeric
        $member_id_numeric = intval($member_id);

        $member = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $members_table WHERE member_id = %d",
                $member_id_numeric
            )
        );

        if (!$member) {
            wp_send_json_error('Member not found');
            return;
        }

        // Calculate remaining months if expiry date is available
        $remaining_days = '';
        if (!empty($member->expiry_date)) {
            $today = new DateTime(current_time('Y-m-d'));
            $expiry = new DateTime($member->expiry_date);

            if ($expiry > $today) {
                $diff = $today->diff($expiry);
                $days = $diff->days;
                $remaining_days = $days . ' days remaining';
            } else {
                $remaining_days = 'Membership expired';
            }
        }

        // Send member details
        wp_send_json_success(array(
            'name' => $member->name,
            'status' => $member->status,
            'remaining' => $remaining_days
        ));
    }

    /**
     * Calculate duration between two timestamps in minutes
     * 
     * @param string $check_in_time Check-in timestamp
     * @param string $check_out_time Check-out timestamp
     * @return int Duration in minutes
     */
    private function calculate_duration($check_in_time, $check_out_time)
    {
        $check_in = new DateTime($check_in_time);
        $check_out = new DateTime($check_out_time);
        $duration = $check_in->diff($check_out);

        // Convert hours and minutes to total minutes
        return ($duration->h * 60) + $duration->i;
    }

    /**
     * Handle bulk upload of members via CSV
     */
    public function bulk_upload_members()
    {
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized access'));
            return;
        }

        // Check if file was uploaded
        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            wp_send_json_error(array('message' => 'File upload failed'));
            return;
        }

        $file = $_FILES['csv_file'];
        $file_path = $file['tmp_name'];

        // Validate file type
        $file_type = pathinfo($file['name'], PATHINFO_EXTENSION);
        if (strtolower($file_type) !== 'csv') {
            wp_send_json_error(array('message' => 'Invalid file type. Please upload a CSV file.'));
            return;
        }

        // Read and process CSV file
        $handle = fopen($file_path, 'r');
        if (!$handle) {
            wp_send_json_error(array('message' => 'Unable to read the CSV file'));
            return;
        }

        global $wpdb;
        $members_table = PM_GYM_MEMBERS_TABLE;

        $row_count = 0;
        $success_count = 0;
        $error_count = 0;
        $errors = array();

        // Skip header row
        $header = fgetcsv($handle);

        // Expected CSV format: Name,Phone,Email,Member ID,Membership Type,Aadhar Number,Gender,Date of Birth,Address,Status
        $expected_headers = array('name', 'phone', 'email', 'member_id', 'membership_type', 'aadhar_number', 'gender', 'address', 'join_date', 'expiry_date', 'reference');

        // Validate headers (optional but recommended)
        if ($header && count($header) !== count($expected_headers)) {
            fclose($handle);
            wp_send_json_error(array('message' => 'Invalid CSV format. Expected ' . count($expected_headers) . ' columns, got ' . count($header)));
            return;
        }

        while (($data = fgetcsv($handle)) !== FALSE) {
            $row_count++;

            // Skip empty rows
            if (empty(array_filter($data))) {
                continue;
            }

            // Validate required fields
            $member_id = isset($data[0]) ? intval(trim($data[0])) : null;
            $name = isset($data[1]) ? trim($data[1]) : null;
            $gender = isset($data[2]) ? trim(strtolower($data[2])) : null;
            $reference = isset($data[3]) ? trim($data[3]) : null;
            $phone = isset($data[4]) ? trim($data[4]) : null;
            $membership_type = isset($data[5]) ? intval(trim($data[5])) : 0;
            $join_date = isset($data[6]) ? trim($data[6]) : null;
            $expiry_date = isset($data[7]) ? trim($data[7]) : null;
            $address = isset($data[8]) ? trim($data[8]) : null;
            $aadhar_number = isset($data[9]) ? trim($data[9]) : null;
            $email = isset($data[10]) ? trim($data[10]) : null;

            // Validate required fields
            $row_errors = array();

            if (empty($name)) {
                $row_errors[] = 'Name is required';
            }

            if (empty($phone) || !preg_match('/^[0-9]{10}$/', $phone)) {
                $row_errors[] = 'Valid 10-digit phone number is required';
            }

            if (empty($member_id) || $member_id <= 0 || $member_id > 9999) {
                $row_errors[] = 'Valid member ID (1-9999) is required';
            }

            if ($membership_type <= 0) {
                $row_errors[] = 'Valid membership type (1-12 months) is required';
            }

            if (empty($aadhar_number) || !preg_match('/^[0-9]{12}$/', $aadhar_number)) {
                $row_errors[] = 'Valid 12-digit Aadhar number is required';
            }

            if (!in_array($gender, array('male', 'female', 'other'))) {
                $row_errors[] = 'Valid gender (male/female/other) is required';
            }

            if (empty($dob) || !strtotime($dob)) {
                $row_errors[] = 'Valid date of birth (YYYY-MM-DD) is required';
            }

            // if (!empty($row_errors)) {
            //     $error_count++;
            //     $errors[] = "Row $row_count: " . implode(', ', $row_errors);
            //     continue;
            // }

            // Check for duplicate phone number
            $existing_member = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM $members_table WHERE phone = %s",
                $phone
            ));

            if ($existing_member) {
                $error_count++;
                $errors[] = "Row $row_count: Phone number $phone already exists";
                continue;
            }

            // Check for duplicate member ID
            $existing_member_id = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM $members_table WHERE member_id = %d",
                $member_id
            ));

            if ($existing_member_id) {
                $error_count++;
                $errors[] = "Row $row_count: Member ID $member_id already exists";
                continue;
            }

            // Check for duplicate Aadhar number
            $existing_aadhar = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM $members_table WHERE aadhar_number = %s",
                $aadhar_number
            ));

            if ($existing_aadhar) {
                $error_count++;
                $errors[] = "Row $row_count: Aadhar number $aadhar_number already exists";
                continue;
            }

            // Calculate expiry date
            $join_date = !empty($join_date) ? $join_date : date('Y-m-d');
            $expiry_date = date('Y-m-d', strtotime($join_date . ' + ' . $membership_type . ' months'));

            // Prepare member data
            $member_data = array(
                'name' => sanitize_text_field($name),
                'phone' => sanitize_text_field($phone),
                'email' => sanitize_email($email),
                'member_id' => intval($member_id),
                'membership_type' => intval($membership_type),
                'aadhar_number' => sanitize_text_field($aadhar_number),
                'gender' => sanitize_text_field($gender),
                'address' => sanitize_textarea_field($address),
                'join_date' => sanitize_text_field($join_date),
                'expiry_date' => sanitize_text_field($expiry_date),
                'status' => 'active', // Add default status
                'reference' => sanitize_text_field($reference)
            );

            // Define format array matching the data array
            $format = array(
                '%s', // name
                '%s', // phone
                '%s', // email
                '%d', // member_id
                '%d', // membership_type
                '%s', // aadhar_number
                '%s', // gender
                '%s', // address
                '%s', // join_date
                '%s', // expiry_date
                '%s', // status
                '%s'  // reference
            );

            // Insert member with error handling
            $result = $wpdb->insert(
                $members_table,
                $member_data,
                $format
            );

            if ($result === false) {
                $error_count++;
                $errors[] = "Row $row_count: Database error - " . $wpdb->last_error;
                continue;
            } else {
                print_r("hello");
            }



            if ($result) {
                $success_count++;
            } else {
                $error_count++;
                // $errors[] = "Row $row_count: Database error - " . $wpdb->last_error;
            }
        }


        die();

        fclose($handle);

        // Prepare response message
        $message = "Bulk upload completed. ";
        $message .= "Successfully added: $success_count members. ";

        if ($error_count > 0) {
            $message .= "Errors: $error_count. ";
        }

        $response_data = array(
            'message' => $message,
            'success_count' => $success_count,
            'error_count' => $error_count,
            'total_rows' => $row_count
        );

        if (!empty($errors)) {
            // $response_data['errors'] = $errors;
        }

        if ($success_count > 0) {
            wp_send_json_success($response_data);
        } else {
            wp_send_json_error($response_data);
        }
    }

    /**
     * Convert a guest to a member
     */
    public function convert_guest_to_member()
    {
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized access');
            return;
        }

        // Get and validate guest data
        $guest_id = isset($_POST['guest_id']) ? intval($_POST['guest_id']) : 0;
        $guest_name = isset($_POST['guest_name']) ? sanitize_text_field($_POST['guest_name']) : '';
        $guest_phone = isset($_POST['guest_phone']) ? sanitize_text_field($_POST['guest_phone']) : '';

        if (!$guest_id || !$guest_name || !$guest_phone) {
            wp_send_json_error('Missing required guest information');
            return;
        }

        global $wpdb;
        $members_table = PM_GYM_MEMBERS_TABLE;
        $guests_table = PM_GYM_GUEST_USERS_TABLE;

        // Check if phone number already exists in members table
        $existing_member = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $members_table WHERE phone = %s",
            $guest_phone
        ));

        if ($existing_member) {
            wp_send_json_error('A member with this phone number already exists');
            return;
        }

        // Get the next available member ID using the helper function
        $new_member_id = PM_Gym_Helpers::get_next_member_id();

        // Prepare member data
        $member_data = array(
            'member_id' => $new_member_id,
            'name' => $guest_name,
            'phone' => $guest_phone,
            'status' => 'active',
            'join_date' => current_time('Y-m-d'),
            'expiry_date' => date('Y-m-d', strtotime('+1 month')), // Default to 1 month membership
            'membership_type' => '1' // Default to 1 month membership
        );

        // Insert new member
        $result = $wpdb->insert(
            $members_table,
            $member_data,
            array('%d', '%s', '%s', '%s', '%s', '%s', '%s')
        );

        if ($result) {
            // Delete the guest record
            $wpdb->delete(
                $guests_table,
                array('id' => $guest_id),
                array('%d')
            );

            wp_send_json_success(array(
                'message' => 'Guest successfully converted to member',
                'member_id' => $new_member_id
            ));
        } else {
            wp_send_json_error('Error converting guest to member: ' . $wpdb->last_error);
        }
    }

    /**
     * AJAX handler to get the next available member ID
     */
    public function get_next_member_id()
    {
        // Check if user has permission
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized access');
            return;
        }

        $next_id = PM_Gym_Helpers::get_next_member_id();
        wp_send_json_success(array('next_id' => $next_id));
    }

    /**
     * Export members data to CSV
     */
    public function export_members_csv()
    {
        // Check if user has permission
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized access');
            return;
        }

        global $wpdb;
        $members_table = PM_GYM_MEMBERS_TABLE;

        // Get all members
        $members = $wpdb->get_results("SELECT * FROM $members_table ORDER BY id DESC");

        if (empty($members)) {
            wp_send_json_error('No members found to export');
            return;
        }

        // Create temporary file
        $upload_dir = wp_upload_dir();
        $export_dir = $upload_dir['basedir'] . '/gym-exports';

        // Create directory if it doesn't exist
        if (!file_exists($export_dir)) {
            wp_mkdir_p($export_dir);
        }

        $filename = 'gym_members_' . date('Y-m-d_H-i-s') . '.csv';
        $filepath = $export_dir . '/' . $filename;

        // Open file for writing
        $fp = fopen($filepath, 'w');

        // Add UTF-8 BOM for proper Excel encoding
        fprintf($fp, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Add headers
        $headers = array(
            'Sr No',
            'Member ID',
            'Name',
            'Phone',
            'Email',
            'Address',
            'Gender',
            'Date of Birth',
            'Aadhar Number',
            'Membership Type',
            'Join Date',
            'Expiry Date',
            'Status'
        );
        fputcsv($fp, $headers);

        // Add member data
        foreach ($members as $member) {
            $row = array(
                $member->id,
                PM_Gym_Helpers::format_member_id($member->member_id),
                $member->name,
                $member->phone,
                $member->email,
                $member->address,
                $member->gender,
                $member->dob,
                $member->aadhar_number,
                PM_Gym_Helpers::format_membership_type($member->membership_type),
                $member->join_date,
                $member->expiry_date,
                $member->status
            );
            fputcsv($fp, $row);
        }

        fclose($fp);

        // Get the URL for the file
        $file_url = $upload_dir['baseurl'] . '/gym-exports/' . $filename;

        wp_send_json_success(array(
            'message' => 'Members exported successfully',
            'file_url' => $file_url
        ));
    }

    public function export_attendance_csv()
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
}
