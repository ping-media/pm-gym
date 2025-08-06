<?php
if (!defined('ABSPATH')) {
    exit;
}

class PM_Gym
{
    protected $loader;
    protected $plugin_name;
    protected $version;

    public function __construct()
    {
        $this->plugin_name = 'pm-gym';
        $this->version = '1.2.4';
        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_public_hooks();
        $this->define_cron_hooks();

        // Hide admin bar for all users everywhere except user ID 1
        add_action('init', function () {
            if (get_current_user_id() !== 1) {
                add_filter('show_admin_bar', '__return_false');
                add_filter('wp_admin_bar_class', '__return_false');
            }
        });
    }

    private function load_dependencies()
    {
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-pm-gym-activator.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-pm-gym-deactivator.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-pm-gym-loader.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-pm-gym-helpers.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-pm-gym-admin.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-pm-gym-shortcodes.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-pm-gym-staff-attendance.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-pm-gym-attendance.php';

        $this->loader = new PM_Gym_Loader();
    }

    private function define_admin_hooks()
    {
        $plugin_admin = new PM_Gym_Admin($this->get_plugin_name(), $this->get_version());
        $staff_attendance = new PM_Gym_Staff_Attendance($this->get_plugin_name(), $this->get_version());
        $member_attendance = new PM_Gym_Attendance($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
        $this->loader->add_action('admin_menu', $plugin_admin, 'add_admin_menu');
    }

    private function define_public_hooks()
    {
        $plugin_shortcodes = new PM_Gym_Shortcodes($this->get_plugin_name(), $this->get_version());

        // Initialize shortcodes
        add_action('init', array($plugin_shortcodes, 'init'));

        // Add alternative shortcode name
        add_shortcode('pm_gym_attendance', array($plugin_shortcodes, 'attendance_form_shortcode'));

        // Enqueue public scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_public_scripts'));
    }

    private function define_cron_hooks()
    {
        // Register cron callback for member expiry
        add_action('pm_gym_daily_member_expiry', array('PM_Gym_Helpers', 'handle_member_expiry'));
    }

    public function enqueue_public_scripts()
    {
        // Enqueue jQuery (it's usually already included in WordPress)
        wp_enqueue_script('jquery');

        // Enqueue SignaturePad
        wp_enqueue_script(
            'signature-pad',
            'https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js',
            array(),
            '4.1.7',
            true
        );

        // Localize the script with new data
        wp_localize_script(
            'jquery',
            'pm_gym_ajax',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('pm_gym_nonce')
            )
        );
    }

    public function run()
    {
        $this->loader->run();
    }

    public function get_plugin_name()
    {
        return $this->plugin_name;
    }

    public function get_version()
    {
        return $this->version;
    }
}
