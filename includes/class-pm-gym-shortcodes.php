<?php
if (!defined('ABSPATH')) {
    exit;
}

class PM_Gym_Shortcodes
{
    private $plugin_name;
    private $version;

    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    public function init()
    {
        // Register shortcodes
        add_shortcode('attendance_form_shortcode', array($this, 'attendance_form_shortcode'));
        add_shortcode('member_registration_form_shortcode', array($this, 'member_registration_form_shortcode'));
        add_shortcode('member_signature', array($this, 'member_signature_shortcode'));
        add_shortcode('staff_attendance_form_shortcode', array($this, 'staff_attendance_form_shortcode'));
        add_shortcode('face_enrollment_form', array($this, 'face_enrollment_form_shortcode'));

        // Enqueue scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
    }

    public function enqueue_scripts()
    {
        wp_enqueue_script($this->plugin_name . '-public', plugin_dir_url(dirname(__FILE__)) . 'public/js/pm-gym-public.js', array('jquery'), $this->version, true);
        wp_enqueue_style($this->plugin_name . '-public', plugin_dir_url(dirname(__FILE__)) . 'public/css/pm-gym-public.css?cache=' . time(), array(), $this->version);

        // Localize script
        wp_localize_script($this->plugin_name . '-public', 'pm_gym_public', array(
            'ajax_url' => admin_url('admin-ajax.php'),
        ));
    }

    // Attendance form shortcode
    public function attendance_form_shortcode($atts)
    {
        // Start output buffering
        ob_start();
        include PM_GYM_PLUGIN_DIR . 'public/partials/pm-gym-attendance-form.php';
        return ob_get_clean();
    }

    // Member registration form shortcode
    public function member_registration_form_shortcode($atts)
    {
        ob_start();
        include PM_GYM_PLUGIN_DIR . 'public/partials/pm-gym-member-registration-form.php';
        return ob_get_clean();
    }

    // Staff attendance form shortcode
    public function staff_attendance_form_shortcode($atts)
    {
        ob_start();
        include PM_GYM_PLUGIN_DIR . 'public/partials/pm-gym-staff-attendance-form.php';
        return ob_get_clean();
    }

    // Member signature shortcode
    public function member_signature_shortcode($atts)
    {
        // Extract shortcode attributes
        $atts = shortcode_atts(
            array(
                'member_id' => 0,
            ),
            $atts,
            'member_signature'
        );

        // Get member ID
        $member_id = intval($atts['member_id']);

        if (!$member_id) {
            return '<p class="error">Missing member ID</p>';
        }

        // Output HTML
        ob_start();

        // Directly fetch the signature from the database
        global $wpdb;
        $member_meta_table = PM_GYM_MEMBER_META_TABLE;

        $signature = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT meta_value FROM $member_meta_table WHERE member_id = %d AND meta_key = 'signature'",
                $member_id
            )
        );

        echo "<div class='member-signature-container'>";

        if ($signature) {
            // Try to decode multiple layers of JSON if needed
            $clean_signature = $this->decode_multilevel_json($signature);

            if (is_array($clean_signature) && isset($clean_signature['signature'])) {
                // If we have a structured signature object with data URL
                echo "<img src='" . esc_attr($clean_signature['signature']) . "' alt='Member Signature' style='max-height: 100px; width: 200px;' />";

                // Optionally display metadata if available
                if (isset($clean_signature['metadata'])) {
                    echo "<div style='font-size: 10px; color: #666; margin-top: 5px;'>";
                    echo "Signed: " . esc_html($clean_signature['metadata']['timestamp']);
                    echo " | IP: " . esc_html($clean_signature['metadata']['ip']);
                    echo " | Phone: " . esc_html($clean_signature['metadata']['phone']);
                    echo "</div>";
                }
            } else {
                // Fallback to displaying raw data
                echo "<p>Invalid signature format</p>";
            }
        } else {
            echo "<p>Signature not found</p>";
        }

        echo "</div>";

        return ob_get_clean();
    }

    /**
     * Helper function to decode multilevel JSON encoded data
     * 
     * @param string $data The potentially multi-encoded JSON string
     * @return mixed The decoded data
     */
    private function decode_multilevel_json($data)
    {
        // Maximum recursion depth to prevent infinite loops
        $max_depth = 5;
        $depth = 0;
        $result = $data;

        // Keep trying to decode as long as we have a string and haven't reached max depth
        while (is_string($result) && $depth < $max_depth) {
            // Replace escaped quotes and other escaped characters if present
            if (strpos($result, '\\') !== false) {
                $result = stripcslashes($result);
            }

            // Try to decode as JSON
            $decoded = json_decode($result, true);

            // If decoding succeeded and gave different result, continue with decoded value
            if ($decoded !== null && json_last_error() === JSON_ERROR_NONE) {
                $result = $decoded;
                $depth++;
            } else {
                // Not valid JSON anymore, stop decoding
                break;
            }
        }

        return $result;
    }

    // Face enrollment form shortcode
    public function face_enrollment_form_shortcode($atts)
    {
        ob_start();
        include PM_GYM_PLUGIN_DIR . 'public/partials/pm-gym-face-enrollment-form.php';
        return ob_get_clean();
    }
}
