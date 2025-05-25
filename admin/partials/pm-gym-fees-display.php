<?php
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;

// Handle fee payment
if (isset($_POST['add_payment']) && isset($_POST['member_id'])) {
    $member_id = intval($_POST['member_id']);
    $amount = floatval($_POST['amount']);
    $payment_date = sanitize_text_field($_POST['payment_date']);
    $payment_method = sanitize_text_field($_POST['payment_method']);
    $status = 'completed';

    // Create fee record
    $fee_data = array(
        'post_title' => 'Fee Payment',
        'post_type' => 'gym_fee',
        'post_status' => 'publish',
        'post_date' => $payment_date
    );

    $fee_id = wp_insert_post($fee_data);

    if (!is_wp_error($fee_id)) {
        // Save fee meta data
        update_post_meta($fee_id, 'member_id', $member_id);
        update_post_meta($fee_id, 'amount', $amount);
        update_post_meta($fee_id, 'payment_date', $payment_date);
        update_post_meta($fee_id, 'payment_method', $payment_method);
        update_post_meta($fee_id, 'status', $status);

        add_settings_error(
            'gym_fee',
            'payment_success',
            'Payment recorded successfully.',
            'success'
        );
    } else {
        add_settings_error(
            'gym_fee',
            'payment_error',
            'Error recording payment.',
            'error'
        );
    }
}

// Get all members
$members = get_posts(array(
    'post_type' => 'gym_member',
    'posts_per_page' => -1,
    'orderby' => 'title',
    'order' => 'ASC'
));

// Get recent payments
$recent_payments = $wpdb->get_results(
    "SELECT p.ID, p.post_date, pm.meta_value as member_id, 
    pm2.meta_value as amount, pm3.meta_value as payment_method,
    pm4.meta_value as status, m.post_title as member_name
    FROM {$wpdb->posts} p
    JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
    LEFT JOIN {$wpdb->postmeta} pm2 ON p.ID = pm2.post_id AND pm2.meta_key = 'amount'
    LEFT JOIN {$wpdb->postmeta} pm3 ON p.ID = pm3.post_id AND pm3.meta_key = 'payment_method'
    LEFT JOIN {$wpdb->postmeta} pm4 ON p.ID = pm4.post_id AND pm4.meta_key = 'status'
    JOIN {$wpdb->posts} m ON pm.meta_value = m.ID
    WHERE p.post_type = 'gym_fee'
    AND pm.meta_key = 'member_id'
    ORDER BY p.post_date DESC
    LIMIT 10"
);

// Calculate monthly revenue
$monthly_revenue = $wpdb->get_var(
    "SELECT SUM(meta_value) 
    FROM {$wpdb->postmeta} pm
    JOIN {$wpdb->posts} p ON pm.post_id = p.ID
    WHERE p.post_type = 'gym_fee'
    AND pm.meta_key = 'amount'
    AND MONTH(p.post_date) = MONTH(CURRENT_DATE())
    AND YEAR(p.post_date) = YEAR(CURRENT_DATE())"
);

$monthly_revenue = floatval($monthly_revenue);
$total_members = count($members);
$average_payment = $total_members > 0 ? $monthly_revenue / $total_members : 0;
?>

<div class="wrap">
    <h1>Fee Management</h1>

    <?php settings_errors('gym_fee'); ?>

    <div class="pm-gym-fees-container">
        <!-- Statistics -->
        <div class="fee-stats">
            <div class="stat-box">
                <h3>Monthly Revenue</h3>
                <p class="stat-number">₹<?php echo number_format($monthly_revenue, 2); ?></p>
            </div>
            <div class="stat-box">
                <h3>Total Members</h3>
                <p class="stat-number"><?php echo esc_html($total_members); ?></p>
            </div>
            <div class="stat-box">
                <h3>Average Payment</h3>
                <p class="stat-number">₹<?php echo number_format($average_payment, 2); ?></p>
            </div>
        </div>


        <!-- Add Payment Form -->
        <div class="payment-form">
            <h2>Add New Payment</h2>
            <form method="post">
                <div class="form-field">
                    <label for="member_id">Member</label>
                    <select name="member_id" id="member_id" required>
                        <option value="">Select Member...</option>
                        <?php foreach ($members as $member): ?>
                            <option value="<?php echo esc_attr($member->ID); ?>">
                                <?php echo esc_html(PM_Gym_Helpers::format_member_id($member->ID) . ' ' . $member->post_title); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-field">
                    <label for="amount">Amount (₹)</label>
                    <input type="number" name="amount" id="amount" step="0.01" min="0" required>
                </div>

                <div class="form-field">
                    <label for="payment_date">Payment Date</label>
                    <input type="date" name="payment_date" id="payment_date" value="<?php echo date('Y-m-d'); ?>" required>
                </div>

                <div class="form-field">
                    <label for="payment_method">Payment Method</label>
                    <select name="payment_method" id="payment_method" required>
                        <option value="cash">Cash</option>
                        <option value="card">Card</option>
                        <option value="upi">UPI</option>
                        <option value="bank_transfer">Bank Transfer</option>
                    </select>
                </div>

                <div class="form-submit">
                    <button type="submit" name="add_payment" class="button button-primary">Record Payment</button>
                </div>
            </form>
        </div>



        <!-- Recent Payments -->
        <div class="recent-payments">
            <h2>Recent Payments</h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Member</th>
                        <th>Amount</th>
                        <th>Payment Method</th>
                        <th>Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_payments as $payment): ?>
                        <tr>
                            <td><?php echo esc_html(PM_Gym_Helpers::format_member_id($payment->member_id) . ' ' . $payment->member_name); ?></td>
                            <td>₹<?php echo number_format($payment->amount, 2); ?></td>
                            <td><?php echo esc_html(ucfirst($payment->payment_method)); ?></td>
                            <td><?php echo esc_html(date('Y-m-d', strtotime($payment->post_date))); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo esc_attr($payment->status); ?>">
                                    <?php echo esc_html(ucfirst($payment->status)); ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    .pm-gym-fees-container {
        margin-top: 20px;
    }

    .payment-form {
        background: #fff;
        padding: 20px;
        border-radius: 5px;
        margin-bottom: 20px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .form-field {
        margin-bottom: 15px;
    }

    .form-field label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
    }

    .form-field input,
    .form-field select {
        width: 100%;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
    }

    .form-submit {
        margin-top: 20px;
    }

    .fee-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 20px;
    }

    .stat-box {
        background: #fff;
        padding: 20px;
        border-radius: 5px;
        text-align: center;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .stat-box h3 {
        margin: 0 0 10px 0;
        color: #23282d;
    }

    .stat-number {
        font-size: 24px;
        font-weight: bold;
        margin: 0;
        color: #0073aa;
    }

    .recent-payments {
        background: #fff;
        padding: 20px;
        border-radius: 5px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .status-badge {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 3px;
        font-size: 12px;
        font-weight: bold;
    }

    .status-completed {
        background: #dff0d8;
        color: #3c763d;
    }

    .status-pending {
        background: #fcf8e3;
        color: #8a6d3b;
    }

    .status-failed {
        background: #f2dede;
        color: #a94442;
    }
</style>

<script>
    jQuery(document).ready(function($) {
        // Format amount input
        $('#amount').on('input', function() {
            var value = $(this).val();
            if (value < 0) {
                $(this).val(0);
            }
        });
    });
</script>