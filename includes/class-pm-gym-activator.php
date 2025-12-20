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

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

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
            renewal_date date NULL,
            reference varchar(255) NULL,
            status varchar(20) NULL DEFAULT 'active',
            date_created datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            date_modified datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY phone (phone),
            UNIQUE KEY aadhar_number (aadhar_number),
            UNIQUE KEY member_id (member_id)
        ) $charset_collate;";
        dbDelta($sql);

        // Create guest_users table
        $guest_users_table_name = $wpdb->prefix . 'pm_gym_guest_users';
        $sql = "CREATE TABLE IF NOT EXISTS $guest_users_table_name (
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
        dbDelta($sql);

        // Create attendance table
        $attendance_table_name = $wpdb->prefix . 'pm_gym_attendance';
        $sql = "CREATE TABLE IF NOT EXISTS $attendance_table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            user_type varchar(20) NOT NULL DEFAULT 'member',
            check_in_date date NOT NULL,
            check_in_time time NOT NULL,
            check_out_time time DEFAULT NULL,
            attendance_type varchar(20) DEFAULT NULL,
            duration_minutes int DEFAULT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY unique_attendance (user_id, user_type, check_in_date),
            KEY user_id (user_id),
            KEY user_type (user_type),
            KEY check_in_date (check_in_date)
        ) $charset_collate;";
        dbDelta($sql);

        // Create member_meta table for additional member data
        $member_meta_table_name = $wpdb->prefix . 'pm_gym_member_meta';
        $sql = "CREATE TABLE IF NOT EXISTS $member_meta_table_name (
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
        dbDelta($sql);

        // Create staff table
        $staff_table_name = $wpdb->prefix . 'pm_gym_staff';
        $sql = "CREATE TABLE IF NOT EXISTS $staff_table_name (
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
        dbDelta($sql);

        // Create staff attendance table
        $staff_attendance_table_name = $wpdb->prefix . 'pm_gym_staff_attendance';
        $sql = "CREATE TABLE IF NOT EXISTS $staff_attendance_table_name (
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
        dbDelta($sql);

        // Log any errors
        if (!empty($wpdb->last_error)) {
            error_log("PM Gym: Database error during table creation: " . $wpdb->last_error);
        }
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

    /**
     * Check if tables exist and create them if they don't
     * This method can be called during plugin initialization to ensure tables exist
     * 
     * @return bool True if tables exist or were created successfully
     */
    public static function check_and_create_tables()
    {
        global $wpdb;

        // Check if members table exists (using it as the primary table)
        $members_table_name = $wpdb->prefix . 'pm_gym_members';
        // Use direct query instead of prepare for SHOW TABLES
        $table_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $wpdb->esc_like($members_table_name)));

        if (!$table_exists) {
            // Tables don't exist, create them
            self::create_tables();

            // Verify creation was successful
            $table_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $wpdb->esc_like($members_table_name)));
            if (!$table_exists) {
                error_log("PM Gym: Failed to create tables. Last error: " . $wpdb->last_error);
            }
            return (bool) $table_exists;
        }

        return true;
    }

    /**
     * Check if required columns exist and add them if missing
     * This handles schema updates for existing installations
     * 
     * @return bool True if all columns exist or were added successfully
     */
    public static function check_and_add_columns()
    {
        global $wpdb;
        $members_table_name = $wpdb->prefix . 'pm_gym_members';

        // Check if table exists first
        $table_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $wpdb->esc_like($members_table_name)));
        if (!$table_exists) {
            // Table doesn't exist, create it with all columns
            self::create_tables();
            return true;
        }

        // Get existing columns - use backticks for table name, not prepare
        $columns = $wpdb->get_col("SHOW COLUMNS FROM `{$members_table_name}`");
        if (empty($columns)) {
            error_log("PM Gym: Could not retrieve columns from {$members_table_name}");
            return false;
        }
        $columns_lower = array_map('strtolower', $columns);

        // List of required columns that might be missing
        $required_columns = array(
            'renewal_date' => "ALTER TABLE `{$members_table_name}` ADD COLUMN renewal_date date NULL AFTER expiry_date"
        );

        $all_added = true;
        foreach ($required_columns as $column_name => $alter_sql) {
            if (!in_array(strtolower($column_name), $columns_lower)) {
                // Column doesn't exist, add it
                $result = $wpdb->query($alter_sql);
                if ($result === false) {
                    error_log("PM Gym: Failed to add column {$column_name} to {$members_table_name}. Error: " . $wpdb->last_error);
                    $all_added = false;
                } else {
                    error_log("PM Gym: Successfully added column {$column_name} to {$members_table_name}");
                }
            }
        }

        return $all_added;
    }
}
