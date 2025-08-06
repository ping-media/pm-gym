<?php
// Ensure variables are defined to prevent errors
$member_id = isset($member_id) ? esc_html(PM_Gym_Helpers::format_member_id($member_id)) : '';
$user_id = isset($member->id) ? $member->id : '';
$member_name = isset($member->name) ? $member->name : '';
$member_phone = isset($member->phone) ? $member->phone : '';
$member_email = isset($member->email) ? $member->email : 'N/A';
$member_address = isset($member->address) ? $member->address : 'N/A';
$member_trainer_name = isset($member->trainer_name) ? $member->trainer_name : 'N/A';
$member_membership_type = isset($member->membership_type) ? PM_Gym_Helpers::format_membership_type($member->membership_type) : 'N/A';
$member_join_date = isset($member->join_date) ? PM_Gym_Helpers::format_date($member->join_date) : 'N/A';
$member_expiry_date = isset($member->expiry_date) ? PM_Gym_Helpers::format_date($member->expiry_date) : 'N/A';
$renewal_date = isset($member->renewal_date) ? PM_Gym_Helpers::format_date($member->renewal_date) : 'N/A';
$member_status = isset($member->status) ? $member->status : 'N/A';
$reference = isset($member->reference) ? $member->reference : 'N/A';
$gender = isset($member->gender) ? $member->gender : 'N/A';
$aadhar_number = isset($member->aadhar_number) ? $member->aadhar_number : 'N/A';
?>

<div class="pm-gym-single-member-details">
    <button type="button" class="button button-secondary go-back-btn" onclick="history.back()">
        ← Go Back
    </button>
    <div class="member-details-header">

        <h2>Member Details</h2>
    </div>

    <div class="member-info-grid">
        <div class="member-info-item">
            <strong>Member ID:</strong> <?php echo esc_html($member_id); ?>
        </div>

        <div class="member-info-item">
            <strong>Name:</strong> <?php echo esc_html($member_name); ?>
        </div>

        <div class="member-info-item">
            <strong>Phone:</strong> <?php echo esc_html($member_phone); ?>
        </div>

        <div class="member-info-item">
            <strong>Email:</strong> <?php echo esc_html($member_email); ?>
        </div>

        <div class="member-info-item">
            <strong>Address:</strong> <?php echo esc_html($member_address); ?>
        </div>

        <div class="member-info-item">
            <strong>Trainer:</strong> <?php echo esc_html($member_trainer_name); ?>
        </div>

        <div class="member-info-item">
            <strong>Membership Type:</strong> <?php echo esc_html($member_membership_type); ?>
        </div>

        <div class="member-info-item">
            <strong>Join Date:</strong> <?php echo esc_html($member_join_date); ?>
        </div>

        <div class="member-info-item">
            <strong>Expiry Date:</strong> <?php echo esc_html($member_expiry_date); ?>
        </div>

        <div class="member-info-item">
            <strong>Renewal Date:</strong> <?php echo esc_html($renewal_date); ?>
        </div>

        <div class="member-info-item">
            <strong>Status:</strong>
            <span class="status-badge status-<?php echo esc_attr($member_status); ?>">
                <?php echo esc_html(ucfirst($member_status)); ?>
            </span>
        </div>

        <div class="member-info-item">
            <strong>Reference:</strong> <?php echo esc_html($reference); ?>
        </div>

        <div class="member-info-item">
            <strong>Gender:</strong> <?php echo esc_html($gender); ?>
        </div>

        <div class="member-info-item">
            <strong>Aadhar Number:</strong> <?php echo esc_html($aadhar_number); ?>
        </div>
    </div>

    <?php
    // Get attendance records for this member
    global $wpdb;
    $attendance_table = PM_GYM_ATTENDANCE_TABLE;
    $member_attendance = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $attendance_table WHERE user_id = %d ORDER BY check_in_date DESC",
        $user_id
    ));

    ?>

    <div class="member-attendance-section">
        <h3>Attendance History</h3>

        <?php if (empty($member_attendance)): ?>
            <p class="no-attendance">No attendance records found for this member.</p>
        <?php else: ?>
            <div class="attendance-table-container">
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Check In</th>
                            <th>Check Out</th>
                            <th>Duration</th>
                            <th>Type</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($member_attendance as $attendance): ?>
                            <tr>
                                <td><?php echo esc_html(PM_Gym_Helpers::format_date($attendance->check_in_date)); ?></td>
                                <td>
                                    <?php
                                    if (!empty($attendance->check_in_time)) {
                                        echo esc_html(date('h:i A', strtotime($attendance->check_in_time)));
                                    } else {
                                        echo '--';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    if (!empty($attendance->check_out_time)) {
                                        echo esc_html(date('h:i A', strtotime($attendance->check_out_time)));
                                    } else {
                                        echo '--';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    if (!empty($attendance->check_in_time) && !empty($attendance->check_out_time)) {
                                        $check_in = new DateTime($attendance->check_in_time);
                                        $check_out = new DateTime($attendance->check_out_time);
                                        $duration = $check_in->diff($check_out);
                                        echo esc_html($duration->format('%Hh %Im'));
                                    } else {
                                        echo '--';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <span class="attendance-type-badge attendance-type-<?php echo esc_attr($attendance->attendance_type); ?>">
                                        <?php echo esc_html(ucfirst(str_replace('_', ' ', $attendance->attendance_type))); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="attendance-summary">
                <p><strong>Total Attendance Records:</strong> <?php echo count($member_attendance); ?></p>
                <p><strong>Last Visit:</strong>
                    <?php
                    if (!empty($member_attendance)) {
                        echo esc_html(PM_Gym_Helpers::format_date($member_attendance[0]->check_in_date));
                    } else {
                        echo 'No visits recorded';
                    }
                    ?>
                </p>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
    .pm-gym-single-member-details {
        background: #fff;
        padding: 20px;
        border: 1px solid #ddd;
        border-radius: 4px;
        margin: 20px 0;
    }

    .pm-gym-single-member-details h2 {
        margin-top: 0;
        color: #23282d;
        border-bottom: 2px solid #0073aa;
        padding-bottom: 10px;
    }

    .member-info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 15px;
        margin-top: 20px;
    }

    .member-info-item {
        padding: 10px;
        background: #f9f9f9;
        border-radius: 4px;
        border-left: 4px solid #0073aa;
    }

    .member-info-item strong {
        color: #0073aa;
        display: inline-block;
        min-width: 120px;
    }

    .member-details-header {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 20px;
    }

    .go-back-btn {
        background: #f7f7f7 !important;
        border: 1px solid #ccc !important;
        color: #555 !important;
        padding: 8px 16px !important;
        text-decoration: none !important;
        border-radius: 3px !important;
        cursor: pointer !important;
        font-size: 13px !important;
        line-height: 1.4 !important;
        transition: all 0.2s ease !important;
        margin-bottom: 20px !important;
    }

    .go-back-btn:hover {
        background: #e5e5e5 !important;
        border-color: #999 !important;
        color: #333 !important;
    }

    .member-details-header h2 {
        margin: 0;
        flex-grow: 1;
    }
</style>