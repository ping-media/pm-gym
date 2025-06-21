<?php
if (!defined('ABSPATH')) {
    exit;
}

class PM_Gym_Deactivator
{
    public static function deactivate()
    {
        // Clear scheduled cron event for member expiry
        self::clear_member_expiry_cron();

        // Flush rewrite rules
        flush_rewrite_rules();
    }

    private static function clear_member_expiry_cron()
    {
        // Clear the scheduled cron event
        wp_clear_scheduled_hook('pm_gym_daily_member_expiry');
    }

    /**
     * Uninstall the plugin
     * This method is called by the plugin uninstall hook
     */
    public static function uninstall()
    {
        // Check if we should remove data
        $remove_data = get_option('pm_gym_remove_data_on_uninstall', false);

        if ($remove_data) {
            global $wpdb;

            // Remove custom tables
            $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}members");
            $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}gym_attendance");
            $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}gym_fees");

            // Remove custom post types
            $post_types = array('gym_attendance', 'gym_fee');
            foreach ($post_types as $post_type) {
                $items = get_posts(array(
                    'post_type' => $post_type,
                    'post_status' => 'any',
                    'numberposts' => -1,
                    'fields' => 'ids'
                ));

                foreach ($items as $item) {
                    wp_delete_post($item, true);
                }
            }

            // Remove terms
            $terms = get_terms(array(
                'taxonomy' => 'membership_type',
                'hide_empty' => false,
                'fields' => 'ids'
            ));

            foreach ($terms as $term) {
                wp_delete_term($term, 'membership_type');
            }

            // Remove options
            delete_option('pm_gym_remove_data_on_uninstall');
            delete_option('pm_gym_version');
        }
    }
}
