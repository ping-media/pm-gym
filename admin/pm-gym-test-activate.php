<?php

/**
 * Test script to manually trigger the activation process
 * 
 * This file can be accessed directly to force table creation if the activation hook didn't run properly.
 * ACCESS THIS FILE DIRECTLY VIA: /wp-content/plugins/pm-gym/admin/pm-gym-test-activate.php
 */

// Define the absolute path to the WordPress directory
if (!defined('ABSPATH')) {
    // Calculate the base path by going up two directories from this file
    $base_path = dirname(dirname(dirname(dirname(__FILE__))));
    define('ABSPATH', $base_path . '/');
}

// Load WordPress environment
require_once(ABSPATH . 'wp-load.php');

// Security check - only allow administrators to run this file
if (!current_user_can('manage_options')) {
    wp_die('You do not have sufficient permissions to access this page.');
}

// Include the activator class
require_once dirname(dirname(__FILE__)) . '/includes/class-pm-gym-activator.php';

// Run the activation function
PM_Gym_Activator::activate();

echo 'Activation process complete. Tables should now be created.';
echo '<br><a href="' . admin_url('admin.php?page=pm-gym-members') . '">Return to Gym Members</a>';
