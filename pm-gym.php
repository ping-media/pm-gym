<?php

/**
 * Plugin Name: PM Gym Management
 * Plugin URI: https://wpexpertdeep.com/pm-gym
 * Description: A comprehensive gym management system for WordPress with member management, attendance tracking, and fee management.
 * Version: 1.0.0
 * Author: Deep Goyal
 * Author URI: https://wpexpertdeep.com
 * Text Domain: pm-gym
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Define plugin constants
define('PM_GYM_VERSION', '1.0.0');
define('PM_GYM_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('PM_GYM_PLUGIN_URL', plugin_dir_url(__FILE__));



// Get WordPress database object
global $wpdb;

// Define database table names
// Member related tables
define('PM_GYM_MEMBERS_TABLE', $wpdb->prefix . 'pm_gym_members');           // Stores member information
define('PM_GYM_GUEST_USERS_TABLE', $wpdb->prefix . 'pm_gym_guest_users');   // Stores guest user information
define('PM_GYM_MEMBERSHIPS_TABLE', $wpdb->prefix . 'pm_gym_memberships');    // Stores membership details
define('PM_GYM_ATTENDANCE_TABLE', $wpdb->prefix . 'pm_gym_attendance');      // Tracks member attendance
define('PM_GYM_PAYMENTS_TABLE', $wpdb->prefix . 'pm_gym_payments');          // Records payment transactions
define('PM_GYM_PACKAGES_TABLE', $wpdb->prefix . 'pm_gym_packages');          // Stores available gym packages
define('PM_GYM_MEMBER_META_TABLE', $wpdb->prefix . 'pm_gym_member_meta');    // Stores member meta data

// Include required files
require_once PM_GYM_PLUGIN_DIR . 'includes/class-pm-gym.php';
require_once PM_GYM_PLUGIN_DIR . 'includes/class-pm-gym-activator.php';
require_once PM_GYM_PLUGIN_DIR . 'includes/class-pm-gym-deactivator.php';
require_once PM_GYM_PLUGIN_DIR . 'includes/class-pm-gym-shortcodes.php';

// Activation and deactivation hooks
register_activation_hook(__FILE__, array('PM_Gym_Activator', 'activate'));
register_deactivation_hook(__FILE__, array('PM_Gym_Deactivator', 'deactivate'));

// Initialize the plugin
function run_pm_gym()
{
    $plugin = new PM_Gym();
    $plugin->run();
}
run_pm_gym();

add_action('wp_ajax_testing', function () {
    echo "Hello World";
    global $wpdb;
    $members_table = PM_GYM_MEMBERS_TABLE;
    $today = current_time('Y-m-d');

    // Get all members
    $members = $wpdb->get_results("SELECT * FROM $members_table");

    // Loop through members and update status if expired
    foreach ($members as $member) {
        if (!empty($member->expiry_date) && $member->expiry_date < $today) {
            $wpdb->update(
                $members_table,
                array('status' => 'expired'),
                array('id' => $member->id),
                array('%s'),
                array('%d')
            );
            echo "Updated status to expired for member: " . esc_html($member->name) . " (ID: " . esc_html($member->id) . ")<br>";
        }
    }
    wp_die();
});
