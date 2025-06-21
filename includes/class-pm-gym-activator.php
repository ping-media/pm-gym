<?php
if (!defined('ABSPATH')) {
    exit;
}

class PM_Gym_Activator
{
    public static function activate()
    {
        // Create custom tables
        self::create_tables();

        // Create Gym Manager role
        self::create_gym_manager_role();

        // Schedule daily cron event for member expiry
        self::schedule_member_expiry_cron();

        // Flush rewrite rules
        flush_rewrite_rules();
    }

    private static function create_tables()
    {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        // Create members table
        $members_table_name = $wpdb->prefix . 'pm_gym_members';
        $sql = "CREATE TABLE IF NOT EXISTS $members_table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            member_id bigint(20) NULL,
            name varchar(255) NOT NULL,
            phone varchar(20) NULL,
            email varchar(255) NULL,
            gender varchar(20) NULL,
            dob date NULL,
            aadhar_number varchar(20) NULL,
            address text NULL,
            membership_type varchar(10) NULL,
            join_date date NULL,
            expiry_date date NULL,
            reference varchar(255) NULL,
            status varchar(20) NULL DEFAULT 'active',
            date_created datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            date_modified datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY phone (phone),
            UNIQUE KEY aadhar_number (aadhar_number),
            UNIQUE KEY member_id (member_id)
        ) $charset_collate;";

        // Create guest_users table
        $guest_users_table_name = $wpdb->prefix . 'pm_gym_guest_users';
        $sql .= "CREATE TABLE IF NOT EXISTS $guest_users_table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            phone varchar(20) NOT NULL,
            last_visit_date date NOT NULL,
            notes text,
            date_created datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY phone (phone),
            KEY last_visit_date (last_visit_date)
        ) $charset_collate;";

        // Create attendance table
        $attendance_table_name = $wpdb->prefix . 'pm_gym_attendance';
        $sql .= "CREATE TABLE IF NOT EXISTS $attendance_table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            user_type varchar(20) NOT NULL DEFAULT 'member',
            check_in_date date NOT NULL,
            check_in_time time NOT NULL,
            check_out_time time DEFAULT NULL,
            attendance_type varchar(20) DEFAULT NULL,
            duration_minutes int DEFAULT NULL,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY user_type (user_type),
            KEY check_in_date (check_in_date)
        ) $charset_collate;";

        // Create member_meta table for additional member data
        $member_meta_table_name = $wpdb->prefix . 'pm_gym_member_meta';
        $sql .= "CREATE TABLE IF NOT EXISTS $member_meta_table_name (
            meta_id bigint(20) NOT NULL AUTO_INCREMENT,
            member_id bigint(20) NOT NULL,
            meta_key varchar(255) NOT NULL,
            meta_value longtext,
            date_created datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            date_modified datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (meta_id),
            KEY member_id (member_id),
            KEY meta_key (meta_key(191))
        ) $charset_collate;";

        // Create staff table
        $staff_table_name = $wpdb->prefix . 'pm_gym_staff';
        $sql .= "CREATE TABLE IF NOT EXISTS $staff_table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            staff_id bigint(20) NOT NULL,
            name varchar(255) NOT NULL,
            role varchar(50) NOT NULL,
            phone varchar(20) NOT NULL,
            aadhar_number varchar(20) NULL,
            address text NULL,
            status varchar(20) NOT NULL DEFAULT 'active',
            date_created datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            date_modified datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY phone (phone),
            UNIQUE KEY staff_id (staff_id),
            UNIQUE KEY aadhar_number (aadhar_number)
        ) $charset_collate;";

        // Create staff attendance table
        $staff_attendance_table_name = $wpdb->prefix . 'pm_gym_staff_attendance';
        $sql .= "CREATE TABLE IF NOT EXISTS $staff_attendance_table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            staff_id varchar(10) NOT NULL,
            shift varchar(20) NOT NULL DEFAULT 'morning',
            check_in_time datetime NOT NULL,
            check_out_time datetime DEFAULT NULL,
            check_in_date date NOT NULL,
            created_at datetime NOT NULL,
            updated_at datetime DEFAULT NULL,
            PRIMARY KEY  (id),
            KEY staff_id (staff_id),
            KEY check_in_time (check_in_time),
            KEY check_in_date (check_in_date)
        ) $charset_collate;";

        // // Create fees table
        // $fees_table_name = $wpdb->prefix . 'gym_fees';
        // $sql .= "CREATE TABLE IF NOT EXISTS $fees_table_name (
        //     id bigint(20) NOT NULL AUTO_INCREMENT,
        //     member_id bigint(20) NOT NULL,
        //     amount decimal(10,2) NOT NULL,
        //     payment_date datetime NOT NULL,
        //     payment_method varchar(50) NOT NULL,
        //     status varchar(20) NOT NULL,
        //     notes text,
        //     created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        //     updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        //     PRIMARY KEY  (id),
        //     KEY member_id (member_id),
        //     KEY payment_date (payment_date),
        //     KEY status (status)
        // ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        // Execute each statement separately
        dbDelta($sql);
    }

    private static function create_gym_manager_role()
    {
        add_role('gym_manager', 'Gym Manager', array(
            'read' => true,
            'edit_posts' => false,
            'edit_others_posts' => false,
            'edit_private_posts' => false,
            'edit_published_posts' => false,
            'edit_gym_member' => true,
            'edit_gym_members' => true,
            'edit_others_gym_members' => true,
            'edit_private_gym_members' => true,
            'edit_published_gym_members' => true,

            'delete_posts' => true,
            'upload_files' => true,
            'publish_posts' => true,
            'edit_published_posts' => true,
            'delete_published_posts' => true,
            'manage_options' => false
        ));
    }

    private static function schedule_member_expiry_cron()
    {
        // Check if the cron event is already scheduled
        if (!wp_next_scheduled('pm_gym_daily_member_expiry')) {
            // Schedule the event to run daily at midnight
            wp_schedule_event(time(), 'daily', 'pm_gym_daily_member_expiry');
        }
    }
}
