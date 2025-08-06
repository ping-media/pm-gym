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

        // Add AJAX handlers for members
        add_action('wp_ajax_get_member_data', array($this, 'get_member_data'));
        add_action('wp_ajax_save_gym_member', array($this, 'save_gym_member'));
        add_action('wp_ajax_delete_member', array($this, 'delete_member'));
        add_action('wp_ajax_get_next_member_id', array($this, 'get_next_member_id'));
        add_action('wp_ajax_export_members_csv', array($this, 'export_members_csv'));


        // Add AJAX handler for front-end member details
        add_action('wp_ajax_get_member_details_for_front_end', array($this, 'get_member_details_for_front_end'));
        add_action('wp_ajax_nopriv_get_member_details_for_front_end', array($this, 'get_member_details_for_front_end'));

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

        // Add AJAX handlers for staff management
        add_action('wp_ajax_save_gym_staff', array($this, 'save_gym_staff'));
        add_action('wp_ajax_get_staff_data', array($this, 'get_staff_data'));
        add_action('wp_ajax_nopriv_get_staff_data', array($this, 'get_staff_data'));
        add_action('wp_ajax_delete_staff', array($this, 'delete_staff'));
        add_action('wp_ajax_get_next_staff_id', array($this, 'get_next_staff_id'));

        // Add new AJAX handler for getting member's assigned trainer
        add_action('wp_ajax_get_member_trainer', array($this, 'get_staff_data'));

        // Add new AJAX handler for bulk uploading members
        add_action('wp_ajax_bulk_upload_members', array($this, 'bulk_upload_members'));
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

        // Enqueue Flatpickr
        wp_enqueue_style('flatpickr', 'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css');
        wp_enqueue_script('flatpickr', 'https://cdn.jsdelivr.net/npm/flatpickr', array(), null, true);

        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/pm-gym-admin.js', array('jquery', 'flatpickr'), $this->version, false);

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
            2
        );

        add_menu_page(
            'Members',
            'Members',
            'manage_options',
            'pm-gym-members',
            array($this, 'display_members_page'),
            'dashicons-groups',
            2
        );

        add_menu_page(
            'Guests',
            'Guests',
            'manage_options',
            'pm-gym-guests',
            array($this, 'display_guests_page'),
            'dashicons-visibility',
            3
        );

        add_menu_page(
            'Member Attendance',
            'Member Attendance',
            'manage_options',
            'pm-gym-attendance',
            array($this, 'display_attendance_page'),
            'dashicons-calendar-alt',
            3
        );

        add_menu_page(
            'Staff',
            'Staff',
            'manage_options',
            'pm-gym-staff',
            array($this, 'display_staff_page'),
            'dashicons-businessperson',
            3
        );

        add_menu_page(
            'Staff Attendance',
            'Staff Attendance',
            'manage_options',
            'pm-gym-staff-attendance',
            array($this, 'display_staff_attendance_page'),
            'dashicons-calendar-alt',
            3
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
            remove_menu_page('options-general.php?page=updraftplus');
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

    public function display_staff_page()
    {
        require_once plugin_dir_path(__FILE__) . 'partials/pm-gym-staff-display.php';
    }

    public function display_staff_attendance_page()
    {
        require_once plugin_dir_path(__FILE__) . 'partials/pm-gym-staff-attendance-display.php';
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
                $trainer_id = PM_Gym_Helpers::get_member_meta($member->id, 'trainer_id', true);
                $trainer_name = PM_Gym_Helpers::get_staff_name($trainer_id) ?? '';
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
                    'member_id' => PM_Gym_Helpers::format_member_id($member->member_id),
                    'trainer_id' => $trainer_id,
                    'trainer_name' => $trainer_name
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
        $renewal_date = isset($_POST['renewal_date']) && !empty($_POST['renewal_date']) ? sanitize_text_field($_POST['renewal_date']) : null;
        $reference = isset($_POST['reference']) && !empty($_POST['reference']) ? sanitize_text_field($_POST['reference']) : null;
        $trainer_id = isset($_POST['trainer_id']) && !empty($_POST['trainer_id']) ? intval($_POST['trainer_id']) : null;

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
            'renewal_date' => $renewal_date,
            'reference' => $reference
        );

        $member_data_format = array('%s', '%s', '%s', '%s', '%d', '%s', '%d', '%s', '%s', '%s', '%s');

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
                if (!empty($trainer_id)) {
                    PM_Gym_Helpers::update_member_meta($record_id, 'trainer_id', $trainer_id, false);
                }

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
                $member_id = $wpdb->insert_id;
                if (!empty($trainer_id)) {
                    PM_Gym_Helpers::add_member_meta($member_id, 'trainer_id', $trainer_id, false);
                }

                // Insert member meta
                $member_meta_table = PM_GYM_MEMBER_META_TABLE;
                $signature = isset($_POST['signature']) ? $_POST['signature'] : null;


                if (!empty($signature)) {
                    // Insert member meta
                    // $member_meta_data = array(
                    //     'member_id' => $member_id,
                    //     'meta_key' => 'signature',
                    //     'meta_value' => $signature
                    // );

                    // $member_meta_format = array('%d', '%s', '%s');

                    // $wpdb->insert($member_meta_table, $member_meta_data, $member_meta_format);
                    PM_Gym_Helpers::add_member_meta($member_id, 'signature', $signature, false);
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
            'remaining' => $remaining_days,
            'expiry_date' => $member->expiry_date
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



    /**
     * AJAX handler to save gym staff
     */
    public function save_gym_staff()
    {
        // Check if user has permission
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized access');
            return;
        }

        global $wpdb;
        $staff_table = PM_GYM_STAFF_TABLE;

        // Get and sanitize staff data
        $id = isset($_POST['record_id']) ? intval($_POST['record_id']) : null;
        $staff_id = isset($_POST['staff_id']) ? intval($_POST['staff_id']) : null;
        $name = isset($_POST['staff_name']) ? sanitize_text_field($_POST['staff_name']) : '';
        $role = isset($_POST['role']) ? sanitize_text_field($_POST['role']) : '';
        $phone = isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '';
        $aadhar = isset($_POST['aadhar_number']) ? sanitize_text_field($_POST['aadhar_number']) : '';
        $address = isset($_POST['address']) ? sanitize_textarea_field($_POST['address']) : '';
        $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : 'active';

        // Validate required fields
        if (empty($staff_id)) {
            wp_send_json_error('Staff ID is required');
            return;
        }

        if (empty($name)) {
            wp_send_json_error('Name is required');
            return;
        }

        if (empty($phone)) {
            wp_send_json_error('Phone number is required');
            return;
        }

        if (empty($aadhar)) {
            wp_send_json_error('Aadhar number is required');
            return;
        }

        if (empty($address)) {
            wp_send_json_error('Address is required');
            return;
        }

        if (empty($role)) {
            wp_send_json_error('Role is required');
            return;
        }

        // Check if phone number already exists and belongs to different staff
        if (!empty($phone)) {
            $existing_staff = $wpdb->get_var(
                $wpdb->prepare("SELECT id FROM $staff_table WHERE phone = %s AND id != %d", $phone, $id)
            );

            if ($existing_staff) {
                wp_send_json_error('A staff member with this phone number already exists');
                return;
            }
        }

        // Prepare staff data for database
        $staff_data = array(
            'staff_id' => $staff_id,
            'name' => $name,
            'role' => $role,
            'phone' => $phone,
            'aadhar_number' => $aadhar,
            'address' => $address,
            'status' => $status
        );

        $staff_data_format = array('%d', '%s', '%s', '%s', '%s', '%s', '%s');

        // Update or insert staff
        if ($id > 0) {
            // Update existing staff
            $result = $wpdb->update(
                $staff_table,
                $staff_data,
                array('id' => $id),
                $staff_data_format,
                array('%d')
            );

            if ($result !== false) {
                wp_send_json_success(array(
                    'message' => 'Staff updated successfully',
                    'staff_id' => $id
                ));
            } else {
                wp_send_json_error('Error updating staff: ' . $wpdb->last_error);
            }
        } else {
            // Insert new staff
            $result = $wpdb->insert(
                $staff_table,
                $staff_data,
                $staff_data_format
            );

            if ($result) {
                wp_send_json_success(array(
                    'message' => 'Staff added successfully',
                    'staff_id' => $wpdb->insert_id
                ));
            } else {
                wp_send_json_error('Error adding staff: ' . $wpdb->last_error);
            }
        }
    }

    /**
     * AJAX handler to get staff data
     */
    public function get_staff_data()
    {
        // Check if user has permission
        // if (!current_user_can('manage_options')) {
        //     wp_send_json_error('Unauthorized access');
        //     return;
        // }

        $staff_id = isset($_POST['staff_id']) ? intval($_POST['staff_id']) : 0;

        global $wpdb;
        $staff_table = PM_GYM_STAFF_TABLE;

        $staff = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $staff_table WHERE staff_id = %s", $staff_id),
            ARRAY_A //ARRAY_A is used to return the result as an associative array
        );

        if ($staff) {
            wp_send_json_success($staff);
        } else {
            wp_send_json_error('Staff not found');
        }
    }

    /**
     * AJAX handler to delete staff
     */
    public function delete_staff()
    {
        // Check if user has permission
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized access');
            return;
        }

        $staff_id = isset($_POST['staff_id']) ? intval($_POST['staff_id']) : 0;

        global $wpdb;
        $staff_table = $wpdb->prefix . 'pm_gym_staff';

        $result = $wpdb->delete(
            $staff_table,
            array('id' => $staff_id),
            array('%d')
        );

        if ($result) {
            wp_send_json_success(array('message' => 'Staff deleted successfully'));
        } else {
            wp_send_json_error('Error deleting staff: ' . $wpdb->last_error);
        }
    }

    /**
     * AJAX handler to get the next available member ID
     */
    public function get_next_staff_id()
    {
        // Check if user has permission
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized access');
            return;
        }

        $next_id = PM_Gym_Helpers::get_next_staff_id();
        wp_send_json_success(array('next_id' => $next_id));
    }
}
