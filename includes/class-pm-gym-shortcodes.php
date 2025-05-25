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

        // Enqueue scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));

        // Hide admin bar everywhere
        add_filter('show_admin_bar', '__return_false');
        add_action('admin_init', array($this, 'hide_admin_bar'));
    }

    public function hide_admin_bar()
    {
        // Remove admin bar in admin area
        remove_action('admin_footer', 'wp_admin_bar_render', 1000);
        remove_action('wp_footer', 'wp_admin_bar_render', 1000);
        remove_action('in_admin_header', 'wp_admin_bar_render', 0);

        // Remove admin bar styles
        remove_action('wp_head', '_admin_bar_bump_cb');
        remove_action('wp_head', 'wp_admin_bar_header');
        remove_action('wp_head', 'wp_admin_bar_render', 1000);

        // Remove admin bar from admin area
        add_filter('admin_body_class', function ($classes) {
            return $classes . ' no-admin-bar';
        });
    }

    public function enqueue_scripts()
    {
        wp_enqueue_script($this->plugin_name . '-public', plugin_dir_url(dirname(__FILE__)) . 'public/js/pm-gym-public.js', array('jquery'), $this->version, true);
        wp_enqueue_style($this->plugin_name . '-public', plugin_dir_url(dirname(__FILE__)) . 'public/css/pm-gym-public.css', array(), $this->version);

        // Localize script
        wp_localize_script($this->plugin_name . '-public', 'pm_gym_public', array(
            'ajax_url' => admin_url('admin-ajax.php'),
        ));
    }

    public function attendance_form_shortcode($atts)
    {
        // Start output buffering
        ob_start();
?>
        <div class="pm-gym-attendance-form-container">
            <div class="pm-gym-attendance-form">
                <h2>Mark Your Attendance</h2>
                <form id="pm-gym-attendance-form" method="post">
                    <div class="form-group">
                        <label for="member_id">Member ID</label>
                        <input type="text" id="member_id" name="member_id" required
                            placeholder="Enter your member ID (e.g., 12345)"
                            pattern="[0-9]{4,5}"
                            title="Please enter a valid member ID">
                    </div>

                    <div id="member-info" style="display: none;">
                        <h2 id="member-greeting">Hello, <span id="member-name"></span>!</h2>
                        <p id="membership-status">Your membership: <span id="membership-remaining"></span></p>
                    </div>

                    <div class="form-group guest-checkbox">
                        <label class="checkbox-label">
                            <input type="checkbox" id="is_guest" name="is_guest" value="1">
                            I am a guest member
                        </label>
                    </div>

                    <div id="guest-fields" style="display: none;">
                        <div class="form-group">
                            <label for="guest_name">Full Name</label>
                            <input type="text" id="guest_name" name="guest_name"
                                placeholder="Enter your full name">
                        </div>
                        <div class="form-group">
                            <label for="guest_phone">Phone Number</label>
                            <input type="tel" id="guest_phone" name="guest_phone"
                                placeholder="Enter your phone number"
                                pattern="[0-9]{10}"
                                title="Please enter a valid 10-digit phone number">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Attendance Type</label>
                        <div class="radio-group">
                            <label class="radio-label">
                                <input type="radio" name="attendance_type" value="check_in" checked required>
                                Check In
                            </label>
                            <label class="radio-label">
                                <input type="radio" name="attendance_type" value="check_out" required>
                                Check Out
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="button button-primary">CHECK IN</button>
                    </div>
                    <div id="attendance-message"></div>
                    <div class="guest-toggle">Not a member? Click here to attend as a guest</div>
                    <div class="form-group reload-button-container">
                        <button type="button" id="reload-page-btn" class="button button-secondary">Reload Page</button>
                    </div>
                </form>
            </div>
        </div>

        <style>
            .pm-gym-attendance-form button {
                /* background: #28a745; */
            }

            .pm-gym-attendance-form-container {
                margin: 0 auto;
            }

            .pm-gym-attendance-form {
                background: #fff;
                padding: 30px;
                border-radius: 30px;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            }

            .pm-gym-attendance-form h2 {
                text-align: center;
                font-size: 28px;
                margin-bottom: 25px;
                color: #1e2b3d;
            }

            .form-group {
                margin-bottom: 20px;
            }

            .form-group label {
                display: block;
                margin-bottom: 5px;
                font-weight: 500;
                color: #1e2b3d;
            }

            .form-group input[type="text"],
            .form-group input[type="tel"] {
                width: 100%;
                padding: 12px 15px;
                border: 1px solid #ddd;
                border-radius: 6px;
                font-size: 16px;
            }

            .radio-group {
                display: flex;
                gap: 30px;
            }

            .radio-label {
                display: flex;
                align-items: center;
                cursor: pointer;
            }

            .radio-label input {
                margin-right: 8px;
                width: 20px;
                height: 20px;
            }

            .button-primary {
                width: 100%;
                background: #4682B4;
                color: white;
                border: none;
                border-radius: 6px;
                padding: 15px 0;
                font-size: 18px;
                font-weight: 600;
                cursor: pointer;
                text-transform: uppercase;
                letter-spacing: 1px;
            }

            .button-primary:hover {
                background: #3A6D99;
            }

            #member-greeting {
                font-size: 24px;
                margin-bottom: 5px;
            }

            #membership-status {
                font-size: 16px;
                color: #000;
                margin-bottom: 25px;
            }

            #membership-status.expired {
                color: #dc3545;
                font-weight: 500;
            }

            .guest-checkbox {
                margin: 15px 0;
                display: none;
                /* Hide by default */
            }

            .checkbox-label {
                display: flex;
                align-items: center;
            }

            .checkbox-label input {
                margin-right: 8px;
            }

            #attendance-message {
                margin-top: 15px;
                padding: 10px;
                border-radius: 4px;
                text-align: center;
                font-weight: 500;
            }

            #member-name {
                text-transform: capitalize;
            }

            .success {
                background-color: #d4edda;
                color: #155724;
            }

            .error {
                background-color: #f8d7da;
                color: #721c24;
            }

            .error-field {
                border-color: #dc3545 !important;
                background-color: #fff8f8;
            }

            /* Show guest option on click of toggle link */
            .guest-toggle {
                text-align: center;
                margin-top: 15px;
                font-size: 14px;
                color: #666;
                cursor: pointer;
                text-decoration: underline;
            }

            #member-info {
                padding: 10px;
                background: #f8dedc;
                border-radius: 10px;
                text-align: center;
            }

            .guest-toggle:hover {
                color: #333;
            }

            .reload-button-container {
                text-align: center;
                margin-top: 15px;
            }

            .pm-gym-attendance-form button.button-secondary {
                background: transparent;
                color: #797979;
                border: 1px solid #797979;
                border-radius: 6px;
                padding: 10px 15px;
                font-size: 16px;
                cursor: pointer;
            }

            .pm-gym-attendance-form button.button-secondary:hover {
                background: #797979;
                color: #fff;
            }
        </style>

        <script>
            jQuery(document).ready(function($) {
                // Reload page button
                $('#reload-page-btn').on('click', function() {
                    window.location.reload();
                });

                // Toggle guest option
                $('.guest-toggle').on('click', function() {
                    if ($(this).text() === "Not a member? Click here to attend as a guest") {
                        // Switch to guest mode
                        $('#member_id').closest('.form-group').hide();
                        $('#member_id').prop('required', false); // Remove required attribute
                        $('#guest-fields').show();
                        $('#guest_name, #guest_phone').prop('required', true); // Make guest fields required
                        $('#member-info').hide();
                        $('#is_guest').prop('checked', true);
                        $(this).text("Already a member? Click here to sign in");
                    } else {
                        // Switch to member mode
                        $('#member_id').closest('.form-group').show();
                        $('#member_id').prop('required', true); // Add required attribute back
                        $('#guest-fields').hide();
                        $('#guest_name, #guest_phone').prop('required', false); // Make guest fields not required
                        $('#is_guest').prop('checked', false);
                        $(this).text("Not a member? Click here to attend as a guest");
                    }
                });

                // Toggle guest fields visibility (keep this for the checkbox)
                $('#is_guest').on('change', function() {
                    if ($(this).is(':checked')) {
                        $('#member_id').closest('.form-group').hide();
                        $('#member_id').prop('required', false); // Remove required attribute
                        $('#guest-fields').show();
                        $('#guest_name, #guest_phone').prop('required', true); // Make guest fields required
                        $('#member-info').hide();
                        $('.guest-toggle').text("Already a member? Click here to sign in");
                    } else {
                        $('#member_id').closest('.form-group').show();
                        $('#member_id').prop('required', true); // Add required attribute back
                        $('#guest-fields').hide();
                        $('#guest_name, #guest_phone').prop('required', false); // Make guest fields not required
                        $('.guest-toggle').text("Not a member? Click here to attend as a guest");
                    }
                });

                // Update button text based on attendance type
                $('input[name="attendance_type"]').on('change', function() {
                    if ($(this).val() === 'check_in') {
                        $('.button-primary').text('CHECK IN');
                    } else {
                        $('.button-primary').text('CHECK OUT');
                    }
                });

                // Form submission
                $('#pm-gym-attendance-form').on('submit', function(e) {
                    e.preventDefault();

                    // Validate form based on current mode
                    let isValid = true;

                    if ($('#is_guest').is(':checked')) {
                        // Guest mode validation
                        if (!$('#guest_name').val()) {
                            $('#guest_name').addClass('error-field');
                            isValid = false;
                        } else {
                            $('#guest_name').removeClass('error-field');
                        }

                        if (!$('#guest_phone').val() || !$('#guest_phone').val().match(/^\d{10}$/)) {
                            $('#guest_phone').addClass('error-field');
                            isValid = false;
                        } else {
                            $('#guest_phone').removeClass('error-field');
                        }
                    } else {
                        // Member mode validation
                        if (!$('#member_id').val() || !$('#member_id').val().match(/^\d{4,5}$/)) {
                            $('#member_id').addClass('error-field');
                            isValid = false;
                        } else {
                            $('#member_id').removeClass('error-field');
                        }
                    }

                    if (!isValid) {
                        $('#attendance-message').removeClass('success').addClass('error').text('Please fill in all required fields correctly.');
                        return;
                    }

                    // For members, first get the member details and then mark attendance
                    if (!$('#is_guest').is(':checked')) {
                        const memberId = $('#member_id').val();

                        // First get member details
                        $.ajax({
                            type: 'POST',
                            url: '<?php echo admin_url('admin-ajax.php'); ?>',
                            data: {
                                action: 'get_member_details_for_front_end',
                                member_id: memberId
                            },
                            beforeSend: function() {
                                $('#attendance-message').removeClass('success error').text('Processing...');
                            },
                            success: function(response) {
                                if (response.success) {
                                    const data = response.data;
                                    $('#member-name').text(data.name);
                                    if (data.remaining && data.status == 'active') {
                                        $('#membership-remaining').text(data.remaining);
                                    } else {
                                        $('#membership-remaining').text(data.status);
                                    }
                                    $('#member-info').show();

                                    // If membership expired, show warning
                                    if (data.status === 'expired') {
                                        $('#membership-status').addClass('expired');
                                    } else {
                                        $('#membership-status').removeClass('expired');
                                    }

                                    // Now mark attendance after getting member details
                                    markAttendance();
                                } else {
                                    // Member not found or error
                                    $('#member-info').hide();
                                    $('#attendance-message').removeClass('success').addClass('error').text('Member not found or error occurred.');
                                }
                            },
                            error: function() {
                                $('#member-info').hide();
                                $('#attendance-message').removeClass('success').addClass('error').text('An error occurred while getting member details.');
                            }
                        });
                    } else {
                        // For guests, directly mark attendance
                        markAttendance();
                    }

                    // Function to mark attendance
                    function markAttendance() {
                        $.ajax({
                            type: 'POST',
                            url: '<?php echo admin_url('admin-ajax.php'); ?>',
                            data: $('#pm-gym-attendance-form').serialize() + '&action=mark_attendance',
                            beforeSend: function() {
                                $('#attendance-message').removeClass('success error').text('Processing...');
                            },
                            success: function(response) {
                                console.log(response);
                                if (response.success) {
                                    $('#attendance-message').removeClass('error').addClass('success').text(response.data);

                                    // Clear form fields for guests
                                    if ($('#is_guest').is(':checked')) {
                                        $('#guest_name, #guest_phone').val('');
                                    }


                                } else {
                                    $('#attendance-message').removeClass('success').addClass('error').text(response.data);
                                }
                                setTimeout(function() {
                                    window.location.reload();
                                }, 5000);
                            },
                            error: function() {
                                $('#attendance-message').removeClass('success').addClass('error').text('An error occurred. Please try again.');
                            }
                        });
                    }
                });
            });
        </script>
<?php
        return ob_get_clean();
    }

    public function member_registration_form_shortcode($atts)
    {
        ob_start();
        include PM_GYM_PLUGIN_DIR . 'public/partials/pm-gym-member-registration-form.php';
        return ob_get_clean();
    }

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
}
