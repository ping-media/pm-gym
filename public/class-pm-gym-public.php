<?php

class PM_Gym_Public
{
    private $plugin_name;
    private $version;

    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    public function enqueue_styles()
    {
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/pm-gym-public.css', array(), $this->version, 'all');
    }

    public function enqueue_scripts()
    {
        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/pm-gym-public.js', array('jquery'), $this->version, false);
    }

    public function register_shortcodes()
    {
        add_shortcode('gym_member_registration', array($this, 'member_registration_form'));
        add_shortcode('gym_attendance_form', array($this, 'attendance_form'));
        add_shortcode('gym_fee_payment', array($this, 'fee_payment_form'));
    }

    public function member_registration_form($atts)
    {
        ob_start();
        require_once 'partials/pm-gym-member-registration.php';
        return ob_get_clean();
    }

    public function attendance_form($atts)
    {
        ob_start();
        require_once 'partials/pm-gym-attendance-form.php';
        return ob_get_clean();
    }

    public function fee_payment_form($atts)
    {
        ob_start();
        require_once 'partials/pm-gym-fee-payment.php';
        return ob_get_clean();
    }

    public function handle_member_registration()
    {
        if (
            !isset($_POST['gym_member_registration_nonce']) ||
            !wp_verify_nonce($_POST['gym_member_registration_nonce'], 'gym_member_registration')
        ) {
            return;
        }

        $member_data = array(
            'post_title' => sanitize_text_field($_POST['member_name']),
            'post_type' => 'gym_member',
            'post_status' => 'publish'
        );

        $member_id = wp_insert_post($member_data);

        if (!is_wp_error($member_id)) {
            // Add member meta data
            update_post_meta($member_id, 'phone', sanitize_text_field($_POST['phone']));
            update_post_meta($member_id, 'email', sanitize_email($_POST['email']));
            update_post_meta($member_id, 'address', sanitize_textarea_field($_POST['address']));
            update_post_meta($member_id, 'membership_type', sanitize_text_field($_POST['membership_type']));

            wp_set_object_terms($member_id, $_POST['membership_type'], 'membership_type');
        }
    }

    public function handle_attendance()
    {
        if (
            !isset($_POST['gym_attendance_nonce']) ||
            !wp_verify_nonce($_POST['gym_attendance_nonce'], 'gym_attendance')
        ) {
            return;
        }

        $attendance_data = array(
            'post_title' => 'Attendance - ' . date('Y-m-d H:i:s'),
            'post_type' => 'gym_attendance',
            'post_status' => 'publish'
        );

        $attendance_id = wp_insert_post($attendance_data);

        if (!is_wp_error($attendance_id)) {
            update_post_meta($attendance_id, 'member_id', intval($_POST['member_id']));
            update_post_meta($attendance_id, 'check_in_time', current_time('mysql'));
        }
    }

    public function handle_fee_payment()
    {
        if (
            !isset($_POST['gym_fee_payment_nonce']) ||
            !wp_verify_nonce($_POST['gym_fee_payment_nonce'], 'gym_fee_payment')
        ) {
            return;
        }

        $fee_data = array(
            'post_title' => 'Fee Payment - ' . date('Y-m-d H:i:s'),
            'post_type' => 'gym_fee',
            'post_status' => 'publish'
        );

        $fee_id = wp_insert_post($fee_data);

        if (!is_wp_error($fee_id)) {
            update_post_meta($fee_id, 'member_id', intval($_POST['member_id']));
            update_post_meta($fee_id, 'amount', floatval($_POST['amount']));
            update_post_meta($fee_id, 'payment_date', current_time('mysql'));
            update_post_meta($fee_id, 'payment_method', sanitize_text_field($_POST['payment_method']));
        }
    }
}
