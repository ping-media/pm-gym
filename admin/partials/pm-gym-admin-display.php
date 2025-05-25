<?php
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <div class="pm-gym-admin-dashboard">
        <div class="pm-gym-stats-container">
            <div class="pm-gym-stat-box">
                <h3>Total Members</h3>
                <?php
                $members_table = $wpdb->prefix . 'pm_gym_members';
                $members_count = $wpdb->get_var("SELECT COUNT(*) FROM $members_table");
                echo '<p class="stat-number">' . esc_html($members_count) . '</p>';
                ?>
            </div>

            <div class="pm-gym-stat-box">
                <h3>Active Members</h3>
                <?php
                $active_members_count = $wpdb->get_var(
                    "SELECT COUNT(*) 
                    FROM $members_table 
                    WHERE status = 'active'"
                );
                echo '<p class="stat-number">' . esc_html($active_members_count) . '</p>';
                ?>
            </div>

            <div class="pm-gym-stat-box">
                <h3>Today's Attendance</h3>
                <?php
                $today = date('Y-m-d');
                $attendance_count = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM {$wpdb->posts} p 
                    JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id 
                    WHERE p.post_type = 'gym_attendance' 
                    AND pm.meta_key = 'check_in_time' 
                    AND DATE(pm.meta_value) = %s",
                    $today
                ));
                echo '<p class="stat-number">' . esc_html($attendance_count) . '</p>';
                ?>
            </div>

            <div class="pm-gym-stat-box">
                <h3>Monthly Revenue</h3>
                <?php
                $month_start = date('Y-m-01');
                $month_end = date('Y-m-t');
                $monthly_revenue = $wpdb->get_var($wpdb->prepare(
                    "SELECT SUM(meta_value) 
                    FROM {$wpdb->postmeta} pm
                    JOIN {$wpdb->posts} p ON pm.post_id = p.ID
                    WHERE p.post_type = 'gym_fee'
                    AND pm.meta_key = 'amount'
                    AND p.post_date BETWEEN %s AND %s
                    AND p.post_status = 'publish'",
                    $month_start,
                    $month_end
                ));
                $monthly_revenue = floatval($monthly_revenue);
                echo '<p class="stat-number">₹' . number_format($monthly_revenue, 2) . '</p>';
                ?>
            </div>
        </div>

        <div class="pm-gym-quick-actions">
            <h2>Quick Actions</h2>
            <div class="action-buttons">
                <a href="<?php echo esc_url(admin_url('admin.php?page=pm-gym-members')); ?>" class="button button-primary">
                    Manage Members
                </a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=pm-gym-attendance')); ?>" class="button button-primary">
                    Record Attendance
                </a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=pm-gym-fees')); ?>" class="button button-primary">
                    Manage Fees
                </a>
            </div>
        </div>
    </div>
</div>

<style>
    .pm-gym-admin-dashboard {
        margin-top: 20px;
    }

    .pm-gym-stats-container {
        display: flex;
        gap: 20px;
        margin-bottom: 30px;
    }

    .pm-gym-stat-box {
        background: #fff;
        padding: 20px;
        border-radius: 5px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        flex: 1;
        text-align: center;
    }

    .pm-gym-stat-box h3 {
        margin: 0 0 10px 0;
        color: #23282d;
    }

    .stat-number {
        font-size: 24px;
        font-weight: bold;
        color: #0073aa;
        margin: 0;
    }

    .pm-gym-quick-actions {
        background: #fff;
        padding: 20px;
        border-radius: 5px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        margin-bottom: 30px;
    }

    .action-buttons {
        display: flex;
        gap: 10px;
        margin-top: 15px;
    }

    .pm-gym-recent-activity {
        background: #fff;
        padding: 20px;
        border-radius: 5px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }
</style>