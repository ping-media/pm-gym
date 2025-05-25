<?php
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;

$guests_table = PM_GYM_GUEST_USERS_TABLE;

// Get all guests from the custom table with sorting
$sql = "SELECT * FROM $guests_table ORDER BY id DESC";
$guests = $wpdb->get_results($sql);

// Calculate statistics
$total_guests = count($guests);
$active_guests = count(array_filter($guests, function ($guest) {
    return $guest->status === 'active';
}));
$inactive_guests = count(array_filter($guests, function ($guest) {
    return $guest->status === 'inactive';
}));
$today_guests = count(array_filter($guests, function ($guest) {
    return date('Y-m-d', strtotime($guest->last_visit_date_time)) === date('Y-m-d');
}));
?>

<div class="wrap">
    <h1 class="wp-heading-inline">Gym Guests</h1>

    <?php settings_errors('gym_guest'); ?>

    <!-- Statistics -->
    <div class="guest-stats">
        <div class="stat-box">
            <h3>Total Guests</h3>
            <p class="stat-number"><?php echo esc_html($total_guests); ?></p>
        </div>
        <div class="stat-box">
            <h3>Become Member</h3>
            <p class="stat-number"><?php echo esc_html($active_guests); ?></p>
        </div>
        <div class="stat-box">
            <h3>Pending</h3>
            <p class="stat-number"><?php echo esc_html($inactive_guests); ?></p>
        </div>
        <div class="stat-box">
            <h3>Guests Today</h3>
            <p class="stat-number"><?php echo esc_html($today_guests); ?></p>
        </div>
    </div>

    <div class="pm-gym-guests-container">
        <div class="search-box-container">
            <input type="text" id="guest-search" placeholder="Search by ID, Name or Phone..." class="regular-text">
        </div>
        <?php
        // Create base URL for sorting
        $current_url = add_query_arg(array('page' => 'pm-gym-guests'), admin_url('admin.php'));
        ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>
                        ID
                    </th>
                    <th>
                        Name
                    </th>
                    <th>Phone</th>
                    <th>
                        Last Visit Date
                    </th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($guests as $guest): ?>
                    <tr>
                        <td><?php echo esc_html($guest->id); ?></td>
                        <td><?php echo esc_html($guest->name); ?></td>
                        <td><?php echo esc_html($guest->phone); ?></td>
                        <td><?php echo esc_html(date('d M Y, h:i A', strtotime($guest->last_visit_date_time))); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo esc_attr($guest->status); ?>">
                                <?php echo esc_html(ucfirst($guest->status)); ?>
                            </span>
                        </td>
                        <td>
                            <button class="button make-member" data-id="<?php echo esc_attr($guest->id); ?>" data-name="<?php echo esc_attr($guest->name); ?>" data-phone="<?php echo esc_attr($guest->phone); ?>">Make Member</button>

                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
    .pm-gym-guests-container {
        margin-top: 20px;
    }

    .guest-stats {
        display: flex;
        gap: 20px;
        margin: 20px 0;
    }

    .stat-box {
        background: #fff;
        border: 1px solid #ccd0d4;
        border-radius: 4px;
        padding: 15px;
        flex: 1;
        text-align: center;
        box-shadow: 0 1px 1px rgba(0, 0, 0, .04);
    }

    .stat-box h3 {
        margin: 0 0 10px 0;
        color: #23282d;
        font-size: 14px;
    }

    .stat-box .stat-number {
        font-size: 24px;
        font-weight: 600;
        margin: 0;
        color: #0073aa;
    }

    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }
</style>

<script>
    jQuery(document).ready(function($) {
        // Search functionality
        $('#guest-search').on('keyup', function() {
            var value = $(this).val().toLowerCase();
            $('table tbody tr').filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
            });
        });

        // Handle make member button click
        $('.make-member').on('click', function() {
            var button = $(this);
            var guestId = button.data('id');
            var guestName = button.data('name');
            var guestPhone = button.data('phone');

            if (confirm('Are you sure you want to convert ' + guestName + ' to a member?')) {
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'convert_guest_to_member',
                        guest_id: guestId,
                        guest_name: guestName,
                        guest_phone: guestPhone,
                        nonce: '<?php echo wp_create_nonce("convert_guest_to_member"); ?>'
                    },
                    beforeSend: function() {
                        button.prop('disabled', true).text('Converting...');
                    },
                    success: function(response) {
                        if (response.success) {
                            alert('Guest successfully converted to member!');
                            location.reload();
                        } else {
                            alert('Error: ' + response.data);
                            button.prop('disabled', false).text('Make Member');
                        }
                    },
                    error: function() {
                        alert('Error occurred while converting guest to member.');
                        button.prop('disabled', false).text('Make Member');
                    }
                });
            }
        });
    });
</script>