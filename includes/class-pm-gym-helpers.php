<?php
if (!defined('ABSPATH')) {
    exit;
}

class PM_Gym_Helpers
{
    /**
     * Format member ID with leading zeros and hash prefix
     * 
     * @param int $id The member ID to format
     * @param int $length The desired length of the ID (default: 4)
     * @param string $prefix The prefix to add (default: '#')
     * @return string Formatted member ID
     */
    public static function format_member_id($id, $length = 4, $prefix = '')
    {
        return $prefix . str_pad($id, $length, '0', STR_PAD_LEFT);
    }

    /**
     * Format staff ID with leading zeros and hash prefix
     * 
     * @param int $id The staff ID to format
     * @param int $length The desired length of the ID (default: 4)
     * @param string $prefix The prefix to add (default: '#')
     * @return string Formatted staff ID
     */
    public static function format_staff_id($id, $length = 4, $prefix = '')
    {
        return $prefix . str_pad($id, $length, '0', STR_PAD_LEFT);
    }

    /**
     * Parse formatted member ID to get the numeric ID
     * 
     * @param string $formatted_id The formatted member ID (e.g., "#0001")
     * @return int|false The numeric ID or false if invalid format
     */
    public static function parse_member_id($formatted_id)
    {
        // Remove any non-digit characters
        $id = preg_replace('/[^0-9]/', '', $formatted_id);

        // Check if we have a valid number
        if (is_numeric($id)) {
            return intval($id);
        }

        return false;
    }

    /**
     * Get current date in Y-m-d format
     * 
     * @return string Current date
     */
    public static function get_current_date()
    {
        return current_time('Y-m-d');
    }

    /**
     * Get current time in mysql format
     * 
     * @return string Current time
     */
    public static function get_current_time()
    {
        return current_time('mysql');
    }

    /**
     * Format date to display format
     * 
     * @param string $date Date in Y-m-d format
     * @param string $format Desired output format (default: 'd M Y')
     * @return string Formatted date
     */
    public static function format_date($date, $format = 'd M Y')
    {
        return date($format, strtotime($date));
    }

    /**
     * Format time to display format
     * 
     * @param string $time Time in mysql format
     * @param string $format Desired output format (default: 'h:i A')
     * @return string Formatted time
     */
    public static function format_time($time, $format = 'h:i A')
    {
        return date($format, strtotime($time));
    }

    /**
     * Calculate duration between two times
     * 
     * @param string $start_time Start time in mysql format
     * @param string $end_time End time in mysql format
     * @return string Duration in hours and minutes
     */
    public static function calculate_duration($start_time, $end_time)
    {
        if ($start_time == '00:00:00' || $end_time == '00:00:00') {
            return '--';
        }

        $start = strtotime($start_time);
        $end = strtotime($end_time);
        $diff = $end - $start;

        $hours = floor($diff / 3600);
        $minutes = floor(($diff % 3600) / 60);

        if ($hours > 0) {
            return sprintf('%d hr %d min', $hours, $minutes);
        }

        return sprintf('%d min', $minutes);
    }

    /**
     * Sanitize phone number
     * 
     * @param string $phone Phone number to sanitize
     * @return string Sanitized phone number
     */
    public static function sanitize_phone($phone)
    {
        // Remove any non-digit characters
        return preg_replace('/[^0-9]/', '', $phone);
    }

    /**
     * Format phone number
     * 
     * @param string $phone Phone number to format
     * @return string Formatted phone number
     */
    public static function format_phone($phone)
    {
        $phone = self::sanitize_phone($phone);
        if (strlen($phone) === 10) {
            return sprintf(
                '(%s) %s-%s',
                substr($phone, 0, 3),
                substr($phone, 3, 3),
                substr($phone, 6)
            );
        }
        return $phone;
    }

    /**
     * Get a member by ID from the members table
     * 
     * @param int $id Member ID
     * @return object|null Member data or null if not found
     */
    public static function get_member($id)
    {
        global $wpdb;
        $members_table = $wpdb->prefix . 'members';

        return $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $members_table WHERE id = %d", $id)
        );
    }

    /**
     * Get a member by phone number from the members table
     * 
     * @param string $phone Phone number
     * @return object|null Member data or null if not found
     */
    public static function get_member_by_phone($phone)
    {
        global $wpdb;
        $members_table = PM_GYM_MEMBERS_TABLE;

        return $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $members_table WHERE phone = %s", $phone)
        );
    }

    /**
     * Get all members from the members table with optional filtering
     * 
     * @param array $args Query arguments
     * @return array Array of member objects
     */
    public static function get_members($args = array())
    {
        global $wpdb;
        $members_table = PM_GYM_MEMBERS_TABLE;

        $defaults = array(
            'status' => '',
            'membership_type_id' => '',
            'orderby' => 'id',
            'order' => 'DESC',
            'limit' => -1,
            'offset' => 0,
            'search' => ''
        );

        $args = wp_parse_args($args, $defaults);

        $where = array('1=1');
        $prepare_args = array();

        // Add status condition
        if (!empty($args['status'])) {
            $where[] = 'status = %s';
            $prepare_args[] = $args['status'];
        }

        // Add membership type condition
        if (!empty($args['membership_type_id'])) {
            $where[] = 'membership_type_id = %d';
            $prepare_args[] = $args['membership_type_id'];
        }

        // Add search condition
        if (!empty($args['search'])) {
            $search_term = '%' . $wpdb->esc_like($args['search']) . '%';
            $where[] = '(name LIKE %s OR phone LIKE %s)';
            $prepare_args[] = $search_term;
            $prepare_args[] = $search_term;
        }

        // Build query
        $query = "SELECT * FROM $members_table WHERE " . implode(' AND ', $where);

        // Add order
        $query .= " ORDER BY {$args['orderby']} {$args['order']}";

        // Add limit
        if ($args['limit'] > 0) {
            $query .= " LIMIT %d OFFSET %d";
            $prepare_args[] = $args['limit'];
            $prepare_args[] = $args['offset'];
        }

        // Prepare and execute query
        if (count($prepare_args) > 0) {
            $query = $wpdb->prepare($query, $prepare_args);
        }

        return $wpdb->get_results($query);
    }

    /**
     * Count members with optional filtering
     * 
     * @param array $args Query arguments
     * @return int Number of members
     */
    public static function count_members($args = array())
    {
        global $wpdb;
        $members_table = PM_GYM_MEMBERS_TABLE;

        $defaults = array(
            'status' => '',
            'membership_type_id' => '',
            'search' => ''
        );

        $args = wp_parse_args($args, $defaults);

        $where = array('1=1');
        $prepare_args = array();

        // Add status condition
        if (!empty($args['status'])) {
            $where[] = 'status = %s';
            $prepare_args[] = $args['status'];
        }

        // Add membership type condition
        if (!empty($args['membership_type_id'])) {
            $where[] = 'membership_type_id = %d';
            $prepare_args[] = $args['membership_type_id'];
        }

        // Add search condition
        if (!empty($args['search'])) {
            $search_term = '%' . $wpdb->esc_like($args['search']) . '%';
            $where[] = '(name LIKE %s OR phone LIKE %s)';
            $prepare_args[] = $search_term;
            $prepare_args[] = $search_term;
        }

        // Build query
        $query = "SELECT COUNT(*) FROM $members_table WHERE " . implode(' AND ', $where);

        // Prepare and execute query
        if (count($prepare_args) > 0) {
            $query = $wpdb->prepare($query, $prepare_args);
        }

        return (int) $wpdb->get_var($query);
    }

    /**
     * Format month count with proper pluralization
     * 
     * @param int $month_count Number of months
     * @return string Formatted month string (e.g., "1 month", "2 months")
     */
    public static function format_membership_type($month_count)
    {
        $month_count = absint($month_count);
        return sprintf(
            '%d %s',
            $month_count,
            $month_count === 1 ? 'month' : 'months'
        );
    }

    /**
     * Get the next available member ID
     * 
     * @return int The next available member ID
     */
    public static function get_next_member_id()
    {
        global $wpdb;
        $members_table = PM_GYM_MEMBERS_TABLE;

        // Get the highest member_id
        $highest_member_id = $wpdb->get_var("SELECT MAX(member_id) FROM $members_table");

        // If no members exist yet, start with 1, otherwise increment the highest ID
        return $highest_member_id ? intval($highest_member_id) + 1 : 1;
    }

    /**
     * Get the next available staff ID
     * 
     * @return int The next available staff ID
     */
    public static function get_next_staff_id()
    {
        global $wpdb;
        $staff_table = PM_GYM_STAFF_TABLE;

        // Get the highest staff_id
        $highest_staff_id = $wpdb->get_var("SELECT MAX(staff_id) FROM $staff_table");

        // If no members exist yet, start with 1, otherwise increment the highest ID
        return $highest_staff_id ? intval($highest_staff_id) + 1 : 1;
    }

    /**
     * Add member meta data
     * 
     * @param int $member_id Member ID
     * @param string $meta_key Meta key
     * @param mixed $meta_value Meta value
     * @param bool $unique Whether the meta key should be unique for the member
     * @return int|false Meta ID on success, false on failure
     */
    public static function add_member_meta($member_id, $meta_key, $meta_value, $unique = false)
    {
        global $wpdb;
        $member_meta_table = PM_GYM_MEMBER_META_TABLE;

        if (empty($meta_key) || empty($meta_value)) {
            return false;
        }

        // Check if meta key already exists and unique is true
        if ($unique) {
            $exists = $wpdb->get_var($wpdb->prepare(
                "SELECT meta_id FROM $member_meta_table WHERE member_id = %d AND meta_key = %s",
                $member_id,
                $meta_key
            ));
            if ($exists) {
                return false;
            }
        }

        // Insert new meta
        $result = $wpdb->insert(
            $member_meta_table,
            array(
                'member_id' => $member_id,
                'meta_key' => $meta_key,
                'meta_value' => maybe_serialize($meta_value)
            ),
            array('%d', '%s', '%s')
        );

        return $result ? $wpdb->insert_id : false;
    }

    /**
     * Update member meta data
     * 
     * @param int $member_id Member ID
     * @param string $meta_key Meta key
     * @param mixed $meta_value Meta value
     * @param mixed $prev_value Previous value to update (optional)
     * @return bool True on success, false on failure
     */
    public static function update_member_meta($member_id, $meta_key, $meta_value, $prev_value = '')
    {
        global $wpdb;
        $member_meta_table = PM_GYM_MEMBER_META_TABLE;

        // Check if meta exists
        $meta_id = $wpdb->get_var($wpdb->prepare(
            "SELECT meta_id FROM $member_meta_table WHERE member_id = %d AND meta_key = %s",
            $member_id,
            $meta_key
        ));

        if ($meta_id) {
            // Update existing meta
            if (empty($prev_value)) {
                $result = $wpdb->update(
                    $member_meta_table,
                    array('meta_value' => maybe_serialize($meta_value)),
                    array('meta_id' => $meta_id),
                    array('%s'),
                    array('%d')
                );
            } else {
                $result = $wpdb->update(
                    $member_meta_table,
                    array('meta_value' => maybe_serialize($meta_value)),
                    array(
                        'meta_id' => $meta_id,
                        'meta_value' => maybe_serialize($prev_value)
                    ),
                    array('%s'),
                    array('%d', '%s')
                );
            }
        } else {
            // Add new meta if it doesn't exist
            $result = self::add_member_meta($member_id, $meta_key, $meta_value);
        }

        return $result !== false;
    }

    /**
     * Get member meta data
     * 
     * @param int $member_id Member ID
     * @param string $meta_key Meta key (optional)
     * @param bool $single Whether to return a single value (optional)
     * @return mixed Meta value(s)
     */
    public static function get_member_meta($member_id, $meta_key = '', $single = false)
    {
        global $wpdb;
        $member_meta_table = PM_GYM_MEMBER_META_TABLE;

        if (empty($meta_key)) {
            // Get all meta for the member
            $results = $wpdb->get_results($wpdb->prepare(
                "SELECT meta_key, meta_value FROM $member_meta_table WHERE member_id = %d",
                $member_id
            ));

            $meta = array();
            if ($results) {
                foreach ($results as $row) {
                    $meta[$row->meta_key] = maybe_unserialize($row->meta_value);
                }
            }
            return $meta;
        }

        // Get specific meta key
        $meta_value = $wpdb->get_var($wpdb->prepare(
            "SELECT meta_value FROM $member_meta_table WHERE member_id = %d AND meta_key = %s",
            $member_id,
            $meta_key
        ));

        if ($meta_value) {
            $meta_value = maybe_unserialize($meta_value);
            return $single ? $meta_value : array($meta_value);
        }

        return $single ? '' : array();
    }

    /**
     * Delete member meta data
     * 
     * @param int $member_id Member ID
     * @param string $meta_key Meta key (optional)
     * @param mixed $meta_value Meta value to delete (optional)
     * @return bool True on success, false on failure
     */
    public static function delete_member_meta($member_id, $meta_key = '', $meta_value = '')
    {
        global $wpdb;
        $member_meta_table = PM_GYM_MEMBER_META_TABLE;

        $where = array('member_id = %d');
        $prepare_args = array($member_id);

        if (!empty($meta_key)) {
            $where[] = 'meta_key = %s';
            $prepare_args[] = $meta_key;
        }

        if (!empty($meta_value)) {
            $where[] = 'meta_value = %s';
            $prepare_args[] = maybe_serialize($meta_value);
        }

        $query = "DELETE FROM $member_meta_table WHERE " . implode(' AND ', $where);
        $result = $wpdb->query($wpdb->prepare($query, $prepare_args));

        return $result !== false;
    }

    /**
     * Get staff name by ID
     * 
     * @param int $trainer_id Staff ID
     * @return string|null Staff name or null if not found
     */
    public static function get_staff_name($trainer_id)
    {
        global $wpdb;
        $staff_table = PM_GYM_STAFF_TABLE;

        $staff = $wpdb->get_row(
            $wpdb->prepare("SELECT name FROM $staff_table WHERE id = %d", $trainer_id)
        );

        return $staff ? $staff->name : '';
    }

    /**
     * Handle daily member expiry check
     * This function is called by the cron job to update expired member statuses
     */
    public static function handle_member_expiry()
    {
        global $wpdb;
        $members_table = PM_GYM_MEMBERS_TABLE;
        $current_date = current_time('Y-m-d');

        // Get all active members whose expiry date has passed
        $expired_members = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT id, name, phone, expiry_date 
                FROM $members_table 
                WHERE status = 'active' 
                AND expiry_date <= %s 
                AND expiry_date IS NOT NULL",
                $current_date
            )
        );

        if (empty($expired_members)) {
            return;
        }

        $updated_count = 0;
        $member_ids = array();

        foreach ($expired_members as $member) {
            $member_ids[] = $member->id;
        }

        // Update status to expired for all expired members
        if (!empty($member_ids)) {
            $placeholders = implode(',', array_fill(0, count($member_ids), '%d'));
            $query = $wpdb->prepare(
                "UPDATE $members_table 
                SET status = 'expired', 
                    date_modified = %s 
                WHERE id IN ($placeholders)",
                array_merge(array(current_time('mysql')), $member_ids)
            );

            $result = $wpdb->query($query);
            $updated_count = $wpdb->rows_affected;
        }

        // Log the expiry check
        error_log(sprintf(
            'PM Gym: Member expiry check completed. %d members marked as expired.',
            $updated_count
        ));

        return $updated_count;
    }

    /**
     * Get member face descriptor
     * 
     * @param int $member_id Member ID
     * @return array|false Face descriptor array or false if not found
     */
    public static function get_member_face_descriptor($member_id)
    {
        $descriptor_json = self::get_member_meta($member_id, 'face_descriptor', true);

        if (empty($descriptor_json)) {
            return false;
        }

        $descriptor = json_decode($descriptor_json, true);

        if (!is_array($descriptor) || count($descriptor) !== 128) {
            return false;
        }

        return $descriptor;
    }

    /**
     * Get all members with face descriptors
     * 
     * @return array Array of members with face descriptors
     */
    public static function get_all_members_with_faces()
    {
        global $wpdb;
        $members_table = PM_GYM_MEMBERS_TABLE;
        $member_meta_table = PM_GYM_MEMBER_META_TABLE;

        $results = $wpdb->get_results(
            "SELECT m.id, m.member_id, m.name, m.status, mm.meta_value as face_descriptor
            FROM $members_table m
            INNER JOIN $member_meta_table mm ON m.id = mm.member_id
            WHERE mm.meta_key = 'face_descriptor'
            AND m.status IN ('active', 'pending')
            ORDER BY m.member_id ASC"
        );

        $members = array();
        foreach ($results as $row) {
            $descriptor = json_decode($row->face_descriptor, true);
            if (is_array($descriptor) && count($descriptor) === 128) {
                $members[] = array(
                    'member_id' => intval($row->member_id),
                    'id' => intval($row->id),
                    'name' => $row->name,
                    'status' => $row->status,
                    'descriptor' => $descriptor
                );
            }
        }

        return $members;
    }

    /**
     * Validate face descriptor format
     * 
     * @param mixed $descriptor Face descriptor to validate
     * @return bool True if valid, false otherwise
     */
    public static function validate_face_descriptor($descriptor)
    {
        if (is_string($descriptor)) {
            $descriptor = json_decode($descriptor, true);
        }

        if (!is_array($descriptor)) {
            return false;
        }

        if (count($descriptor) !== 128) {
            return false;
        }

        // Check if all values are numeric
        foreach ($descriptor as $value) {
            if (!is_numeric($value)) {
                return false;
            }
        }

        return true;
    }
}
