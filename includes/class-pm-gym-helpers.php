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
        $members_table = $wpdb->prefix . 'members';

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
        $members_table = $wpdb->prefix . 'members';

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
        $members_table = $wpdb->prefix . 'members';

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
}
