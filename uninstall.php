<?php
// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Include the necessary files
require_once plugin_dir_path(__FILE__) . 'includes/class-pm-gym-deactivator.php';

// Run the uninstall method
PM_Gym_Deactivator::uninstall();
