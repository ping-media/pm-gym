<?php
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;

$members_table = PM_GYM_MEMBERS_TABLE;

// Handle member deletion
if (isset($_POST['delete_member']) && isset($_POST['member_id'])) {
    $member_id = intval($_POST['member_id']);

    $result = $wpdb->delete(
        $members_table,
        array('id' => $member_id),
        array('%d')
    );

    if ($result) {
        add_settings_error(
            'gym_member',
            'member_deleted',
            'Member deleted successfully.',
            'success'
        );
    } else {
        add_settings_error(
            'gym_member',
            'delete_error',
            'Error deleting member.',
            'error'
        );
    }
}

// Get current sort parameters
$orderby = isset($_GET['orderby']) ? sanitize_text_field($_GET['orderby']) : 'id';
$order = isset($_GET['order']) ? sanitize_text_field($_GET['order']) : 'DESC';

// Make sure orderby is valid to prevent SQL injection
$allowed_orderby = array('id', 'name', 'join_date', 'status', 'membership_type', 'expiry_date');
if (!in_array($orderby, $allowed_orderby)) {
    $orderby = 'id';
}

// Make sure order is valid
$order = strtoupper($order);
if ($order !== 'ASC' && $order !== 'DESC') {
    $order = 'DESC';
}

// Get all members from the custom table with sorting
$sql = "SELECT * FROM $members_table ORDER BY ";
switch ($orderby) {
    case 'name':
        $sql .= "name";
        break;
    case 'join_date':
        $sql .= "join_date";
        break;
    case 'status':
        $sql .= "status";
        break;
    case 'membership_type':
        $sql .= "membership_type";
        break;
    case 'expiry_date':
        $sql .= "expiry_date";
        break;
    default:
        $sql .= "id";
}
$sql .= " $order";
$members = $wpdb->get_results($sql);

// Get membership types
$membership_types = get_terms(array(
    'taxonomy' => 'membership_type',
    'hide_empty' => false,
    'orderby' => 'name',
    'order' => 'ASC'
));

// If no membership types exist, create default ones
if (empty($membership_types) || is_wp_error($membership_types)) {
    $default_types = array('Basic', 'Premium', 'VIP');
    foreach ($default_types as $type) {
        if (!term_exists($type, 'membership_type')) {
            wp_insert_term($type, 'membership_type');
        }
    }
    // Get the terms again after creating defaults
    $membership_types = get_terms(array(
        'taxonomy' => 'membership_type',
        'hide_empty' => false,
        'orderby' => 'name',
        'order' => 'ASC'
    ));
}
?>



<div class="wrap">
    <h1 class="wp-heading-inline">Gym Members</h1>
    <a href="#" class="page-title-action" id="add-new-member">Add New Member</a>
    <a href="#" class="page-title-action" id="export-members">Export Members Data</a>
    <!-- <a href="#" class="page-title-action" id="bulk-upload-members">Bulk Upload Members</a> -->

    <?php settings_errors('gym_member'); ?>

    <?php
    // Get total members count
    $total_members = $wpdb->get_var("SELECT COUNT(*) FROM $members_table");

    // Get active members count (membership not expired)
    $active_members = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $members_table WHERE status = 'active'",
    ));

    // Get expired members count
    $expired_members = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $members_table WHERE status = 'expired'",
    ));

    // Get today's new members count
    $today_new_members = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $members_table WHERE DATE(join_date) = %s",
        current_time('Y-m-d')
    ));
    ?>

    <div class="pm-stats-container">
        <div class="pm-stat-box">
            <h3>Total Members</h3>
            <p class="pm-stat-number"><?php echo esc_html($total_members); ?></p>
        </div>
        <div class="pm-stat-box">
            <h3>Active Members</h3>
            <p class="pm-stat-number"><?php echo esc_html($active_members); ?></p>
        </div>
        <div class="pm-stat-box">
            <h3>Expired Members</h3>
            <p class="pm-stat-number"><?php echo esc_html($expired_members); ?></p>
        </div>
        <div class="pm-stat-box">
            <h3>Today's New Members</h3>
            <p class="pm-stat-number"><?php echo esc_html($today_new_members); ?></p>
        </div>
    </div>

    <div class="pm-gym-members-container">
        <div class="search-box-container">
            <input type="text" id="member-search" placeholder="Search by ID, Name or Phone..." class="regular-text">
        </div>
        <?php
        // Create base URL for sorting
        $current_url = add_query_arg(array('page' => 'pm-gym-members'), admin_url('admin.php'));
        ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>
                        <?php
                        $id_sort_order = ($orderby === 'id' && $order === 'ASC') ? 'DESC' : 'ASC';
                        $id_sort_url = add_query_arg(array('orderby' => 'id', 'order' => $id_sort_order), $current_url);
                        $id_sort_indicator = '';
                        if ($orderby === 'id') {
                            $id_sort_indicator = $order === 'ASC' ? ' ▲' : ' ▼';
                        }
                        ?>
                        <a href="<?php echo esc_url($id_sort_url); ?>" class="sortable">
                            ID<?php echo esc_html($id_sort_indicator); ?>
                        </a>
                    </th>
                    <th>
                        <?php
                        $name_sort_order = ($orderby === 'name' && $order === 'ASC') ? 'DESC' : 'ASC';
                        $name_sort_url = add_query_arg(array('orderby' => 'name', 'order' => $name_sort_order), $current_url);
                        $name_sort_indicator = '';
                        if ($orderby === 'name') {
                            $name_sort_indicator = $order === 'ASC' ? ' ▲' : ' ▼';
                        }
                        ?>
                        <a href="<?php echo esc_url($name_sort_url); ?>" class="sortable">
                            Name<?php echo esc_html($name_sort_indicator); ?>
                        </a>
                    </th>
                    <th>Phone</th>
                    <th>
                        <?php
                        $membership_sort_order = ($orderby === 'membership_type' && $order === 'ASC') ? 'DESC' : 'ASC';
                        $membership_sort_url = add_query_arg(array('orderby' => 'membership_type', 'order' => $membership_sort_order), $current_url);
                        $membership_sort_indicator = '';
                        if ($orderby === 'membership_type') {
                            $membership_sort_indicator = $order === 'ASC' ? ' ▲' : ' ▼';
                        }
                        ?>
                        <a href="<?php echo esc_url($membership_sort_url); ?>" class="sortable">
                            Membership Type<?php echo esc_html($membership_sort_indicator); ?>
                        </a>
                    </th>
                    <th>
                        <?php
                        // Create sort URL for Join Date
                        $sort_order = ($orderby === 'join_date' && $order === 'ASC') ? 'DESC' : 'ASC';
                        $sort_url = add_query_arg(array('orderby' => 'join_date', 'order' => $sort_order), $current_url);

                        // Add sort indicator
                        $sort_indicator = '';
                        if ($orderby === 'join_date') {
                            $sort_indicator = $order === 'ASC' ? ' ▲' : ' ▼';
                        }
                        ?>
                        <a href="<?php echo esc_url($sort_url); ?>" class="sortable">
                            Join Date<?php echo esc_html($sort_indicator); ?>
                        </a>
                    </th>
                    <th>
                        <?php
                        $expiry_sort_order = ($orderby === 'expiry_date' && $order === 'ASC') ? 'DESC' : 'ASC';
                        $expiry_sort_url = add_query_arg(array('orderby' => 'expiry_date', 'order' => $expiry_sort_order), $current_url);
                        $expiry_sort_indicator = '';
                        if ($orderby === 'expiry_date') {
                            $expiry_sort_indicator = $order === 'ASC' ? ' ▲' : ' ▼';
                        }
                        ?>
                        <a href="<?php echo esc_url($expiry_sort_url); ?>" class="sortable">
                            Expiry Date<?php echo esc_html($expiry_sort_indicator); ?>
                        </a>
                    </th>
                    <th>
                        <?php
                        $status_sort_order = ($orderby === 'status' && $order === 'ASC') ? 'DESC' : 'ASC';
                        $status_sort_url = add_query_arg(array('orderby' => 'status', 'order' => $status_sort_order), $current_url);
                        $status_sort_indicator = '';
                        if ($orderby === 'status') {
                            $status_sort_indicator = $order === 'ASC' ? ' ▲' : ' ▼';
                        }
                        ?>
                        <a href="<?php echo esc_url($status_sort_url); ?>" class="sortable">
                            Status<?php echo esc_html($status_sort_indicator); ?>
                        </a>
                    </th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (empty($members)) {
                    echo '<tr><td colspan="8" style="text-align: center;">No members found</td></tr>';
                } else {
                    foreach ($members as $member):
                        // Get membership type name from ID
                        $membership_type_name = $member->membership_type;

                        $formatted_id = PM_Gym_Helpers::format_member_id($member->member_id);
                ?>
                        <tr>
                            <td><?php echo esc_html($formatted_id); ?></td>
                            <td class="text-capitalize"><?php echo esc_html($member->name); ?></td>
                            <td><?php echo esc_html($member->phone); ?></td>
                            <td><?php echo esc_html(PM_Gym_Helpers::format_membership_type($member->membership_type)); ?></td>
                            <td><?php echo esc_html(PM_Gym_Helpers::format_date($member->join_date)); ?></td>
                            <td><?php echo empty($member->expiry_date) ? '--' : esc_html(PM_Gym_Helpers::format_date($member->expiry_date)); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo esc_attr($member->status); ?>">
                                    <?php echo esc_html(ucfirst($member->status)); ?>
                                </span>
                            </td>
                            <td>
                                <button class="button edit-member" data-id="<?php echo esc_attr($member->id); ?>">Edit</button>

                                <?php if (current_user_can('administrator') && get_current_user_id() === 1): ?>
                                    <button class="button delete-member" data-id="<?php echo esc_attr($member->id); ?>">Delete</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                <?php
                    endforeach;
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add/Edit Member Modal -->
<div id="member-modal" class="pm-gym-modal" style="display: none;">
    <div class="pm-gym-modal-content">
        <span class="pm-gym-modal-close">&times;</span>
        <h2>Add New Member</h2>
        <form id="member-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <input type="hidden" name="action" value="save_gym_member">
            <input type="hidden" name="record_id" id="record_id">

            <div class="row">
                <div class="col-4">
                    <div class="form-field">
                        <label for="member_name">Name <span class="required">*</span></label>
                        <input type="text" name="member_name" id="member_name" required>
                    </div>
                </div>
                <div class="col-4">
                    <div class="form-field">
                        <label for="phone">Phone <span class="required">*</span></label>
                        <input type="tel" name="phone" id="phone" required pattern="[0-9]{10}" maxlength="10" title="Please enter a valid 10-digit phone number" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                        <div id="phone-error" class="field-error"></div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="form-field">
                        <label for="email">Email</label>
                        <input type="email" name="email" id="email">
                    </div>
                </div>

            </div>

            <div class="row">
                <div class="col-4">
                    <div class="form-field">
                        <label for="member_id">Member ID <span class="required">*</span></label>
                        <input type="number" name="member_id" id="member_id" required min="1" max="9999" oninput="javascript: if (this.value.length > 4) this.value = this.value.slice(0,4);">
                        <div id="member-id-error" class="field-error"></div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="form-field">
                        <label for="membership_type">Membership Type</label>
                        <select name="membership_type" id="membership_type" required>
                            <option value="">Select</option>
                            <option value="1">1 Month</option>
                            <option value="2">2 Months</option>
                            <option value="3">3 Months</option>
                            <option value="6">6 Months</option>
                            <option value="12">12 Months</option>
                        </select>
                    </div>
                </div>
                <div class="col-4">
                    <div class="form-field">
                        <label for="expiry_date">Expiry Date</label>
                        <input type="text" name="expiry_date" id="expiry_date" readonly>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-4">
                    <div class="form-field">
                        <label for="aadhar_number">Aadhar Card Number <span class="required">*</span></label>
                        <input type="text" name="aadhar_number" id="aadhar_number" maxlength="12" pattern="[0-9]{12}" title="Please enter a valid 12-digit Aadhar number" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0,12)">
                        <div id="aadhar-error" class="field-error"></div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="form-field">
                        <label for="gender">Gender <span class="required">*</span></label>
                        <select name="gender" id="gender" required>
                            <option value="">Select gender...</option>
                            <option value="male" selected>Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>
                <div class="col-4">
                    <div class="form-field">
                        <label for="dob">Date of Birth <span class="required">*</span></label>
                        <input type="date" name="dob" id="dob">
                    </div>
                </div>
            </div>

            <div class="form-field">
                <label for="address">Address <span class="required">*</span></label>
                <textarea name="address" id="address" rows="2" required></textarea>
            </div>

            <div class="form-field">
                <label for="status">Status</label>
                <select name="status" id="status">
                    <option value="active">Active</option>
                    <option value="pending">Pending</option>
                    <option value="expired">Expired</option>
                    <option value="suspended">Suspended</option>

                </select>
            </div>

            <div class="form-field">
                <label for="signature">Digital Signature</label>
                <div class="signature-display" id="signature-display">

                </div>
            </div>

            <div class="form-submit">
                <button type="submit" class="button button-primary">Save Member</button>
            </div>
        </form>
    </div>
</div>

<!-- Add Bulk Upload Modal -->
<div id="bulk-upload-modal" class="pm-gym-modal" style="display: none;">
    <div class="pm-gym-modal-content">
        <span class="pm-gym-modal-close">&times;</span>
        <h2>Bulk Upload Members</h2>
        <div class="bulk-upload-instructions">
            <p>Please upload a CSV file with the following columns in exact order:</p>
            <ol>
                <li><strong>Name</strong> (required) - Member's full name</li>
                <li><strong>Phone</strong> (required) - 10-digit phone number</li>
                <li><strong>Email</strong> (optional) - Valid email address</li>
                <li><strong>Member ID</strong> (required) - Unique 4-digit number (0001-9999)</li>
                <li><strong>Membership Type</strong> (required) - Duration in months (1-12)</li>
                <li><strong>Aadhar Number</strong> (required) - 12-digit Aadhar card number</li>
                <li><strong>Gender</strong> (required) - male, female, or other</li>
                <li><strong>Date of Birth</strong> (required) - Format: YYYY-MM-DD</li>
                <li><strong>Address</strong> (optional) - Member's address</li>
                <li><strong>Status</strong> (optional) - active, inactive, or suspended (default: active)</li>
            </ol>
            <p><strong>Important:</strong> Phone numbers, Member IDs, and Aadhar numbers must be unique. Duplicate entries will be skipped.</p>
            <p><a href="#" id="download-template">Download CSV Template</a></p>
        </div>
        <form id="bulk-upload-form" method="post" enctype="multipart/form-data">
            <div class="form-field">
                <label for="csv_file">Select CSV File</label>
                <input type="file" name="csv_file" id="csv_file" accept=".csv" required>
            </div>
            <div class="form-submit">
                <button type="submit" class="button button-primary">Upload Members</button>
            </div>
        </form>
        <div id="upload-progress" style="display: none;">
            <div class="progress-bar">
                <div class="progress-bar-fill"></div>
            </div>
            <div class="progress-status">Processing...</div>
        </div>
    </div>
</div>

<style>
    .pm-gym-members-container {
        margin-top: 20px;
    }

    .pm-gym-modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0, 0, 0, 0.4);
    }

    .pm-gym-modal-content {
        background-color: #fefefe;
        margin: 10% auto;
        padding: 20px;
        border: 1px solid #ddd;
        width: 50%;
        max-width: 800px;
        border-radius: 4px;
        position: relative;
    }

    .pm-gym-modal-close {
        position: absolute;
        right: 10px;
        top: 10px;
        font-size: 24px;
        cursor: pointer;
    }

    .form-field {
        margin-bottom: 15px;
    }

    .form-field label {
        display: block;
        margin-bottom: 5px;
        font-weight: 600;
    }

    .form-field input[type="text"],
    .form-field input[type="tel"],
    .form-field select,
    .form-field textarea {
        width: 100%;
    }

    .required {
        color: #dc3232;
        margin-left: 3px;
    }

    .field-error {
        color: #dc3232;
        font-size: 12px;
        margin-top: 5px;
        display: none;
    }

    .status-badge {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 3px;
        color: white;
        font-size: 12px;
        text-transform: uppercase;
    }

    .status-active {
        background-color: #46b450;
    }

    .status-inactive,
    .status-expired {
        background-color: #fdadad;
        color: #d10707;
    }

    .status-suspended {
        background-color: #dc3232;
    }

    .status-badge.status-pending {
        background-color: #ffc107;
    }

    .search-box-container {
        margin-bottom: 15px;
    }

    /* Sortable columns */
    th a.sortable {
        display: block;
        color: #23282d;
        text-decoration: none;
        padding: 5px 0;
    }

    th a.sortable:hover {
        color: #0073aa;
    }

    /* Loading Indicator */
    .loading {
        position: relative;
        opacity: 0.7;
        pointer-events: none;
    }

    .loading:after {
        content: "";
        position: absolute;
        top: 50%;
        left: 50%;
        width: 20px;
        height: 20px;
        margin: -10px 0 0 -10px;
        border: 2px solid rgba(0, 0, 0, 0.2);
        border-top-color: #000;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }

    /* Bulk Upload Styles */
    .bulk-upload-instructions {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 4px;
        margin-bottom: 20px;
    }

    .bulk-upload-instructions ul {
        margin: 10px 0;
        padding-left: 20px;
    }

    .bulk-upload-instructions li {
        margin-bottom: 5px;
    }

    .progress-bar {
        width: 100%;
        height: 20px;
        background-color: #f0f0f0;
        border-radius: 10px;
        overflow: hidden;
        margin: 10px 0;
    }

    .progress-bar-fill {
        height: 100%;
        background-color: #0073aa;
        width: 0;
        transition: width 0.3s ease-in-out;
    }

    .progress-status {
        text-align: center;
        margin-top: 10px;
        font-weight: 600;
    }

    #download-template {
        color: #0073aa;
        text-decoration: none;
    }

    #download-template:hover {
        color: #00a0d2;
        text-decoration: underline;
    }
</style>

<script>
    jQuery(document).ready(function($) {
        // Modal handling
        $('#add-new-member').on('click', function(e) {
            e.preventDefault();
            $('#member-form')[0].reset();
            $('#record_id').val('');
            // Make member ID field editable in add mode
            $('#member_id').prop('readonly', false);

            // Get next available member ID
            $.ajax({
                url: pm_gym_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'get_next_member_id'
                },
                success: function(response) {
                    if (response.success) {
                        $('#member_id').val(String(response.data.next_id).padStart(4, '0'));
                    }
                }
            });

            $('#member-modal h2').text('Add New Member');
            $('#member-modal').show();
        });

        $('.pm-gym-modal-close').on('click', function() {
            $('#member-modal').hide();
        });

        // Form Submission
        $('#member-form').on('submit', function(e) {
            e.preventDefault();

            // Clear any previous error messages
            clearFieldErrors();

            var record_id = $('#record_id').val();
            var phone = $('#phone').val();
            var name = $('#member_name').val();
            var address = $('#address').val();
            var status = $('#status').val();
            var membership_type = $('#membership_type').val();
            var member_id = $('#member_id').val();
            var aadhar_number = $('#aadhar_number').val();
            var dob = $('#dob').val();
            var gender = $('#gender').val();
            var email = $('#email').val();
            var expiry_date = $('#expiry_date').val();

            // Validate all required fields
            var isValid = true;

            if (!name) {
                showFieldError('member_name', 'Name is required', false, true);
                isValid = false;
            }

            if (!phone || !isValidPhone(phone)) {
                showFieldError('phone-error', 'Please enter a valid 10-digit phone number');
                isValid = false;
            }

            if (!member_id) {
                showFieldError('member-id-error', 'Member ID is required');
                isValid = false;
            }

            if (aadhar_number && aadhar_number.length !== 12) {
                showFieldError('aadhar-error', 'Please enter a valid 12-digit Aadhar number');
                isValid = false;
            }

            if (!gender) {
                showFieldError('gender', 'Gender is required', true);
                isValid = false;
            }

            if (!address) {
                showFieldError('address', 'Address is required', true);
                isValid = false;
            }

            if (!isValid) {
                return false;
            }

            // Show loading state
            var $submitBtn = $(this).find('button[type="submit"]');
            $submitBtn.prop('disabled', true).addClass('loading');

            // Get form data
            var formData = {
                action: 'save_gym_member',
                record_id: record_id,
                member_name: name,
                phone: phone,
                address: address,
                status: status,
                membership_type: membership_type,
                member_id: member_id,
                aadhar_number: aadhar_number,
                dob: dob,
                gender: gender,
                email: email,
                expiry_date: expiry_date
            };

            // Send AJAX request
            $.ajax({
                url: pm_gym_ajax.ajax_url,
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        showNotification(response.data.message, 'success');
                        // Reset form and hide it
                        resetMemberForm();
                        // Reload page to show updated data
                        location.reload();
                    } else {
                        // Check for specific error messages and display them under the appropriate field
                        if (response.data) {
                            if (response.data.indexOf('phone number already exists') !== -1) {
                                showFieldError('phone-error', 'This phone number already exists');
                            } else if (response.data.indexOf('aadhar number already exists') !== -1) {
                                showFieldError('aadhar-error', 'This Aadhar number already exists');
                            } else if (response.data.indexOf('member with this ID already exists') !== -1) {
                                showFieldError('member-id-error', 'This Member ID already exists');
                            } else {
                                showNotification(response.data, 'error');
                            }
                        } else {
                            showNotification('Error saving member', 'error');
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', {
                        xhr: xhr,
                        status: status,
                        error: error
                    });
                    showNotification('Error saving member: ' + error, 'error');
                },
                complete: function() {
                    // Remove loading state
                    $submitBtn.prop('disabled', false).removeClass('loading');
                }
            });
        });

        // Close modal when clicking outside
        $(window).on('click', function(e) {
            if ($(e.target).is('.pm-gym-modal')) {
                $('.pm-gym-modal').hide();
            }
        });

        // Edit member
        $('.edit-member').on('click', function(e) {
            e.preventDefault();
            var memberId = $(this).data('id');

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'get_member_data',
                    member_id: memberId
                },
                success: function(response) {
                    if (response.success) {
                        var data = response.data;

                        console.log(data)

                        $('#record_id').val(data.ID);
                        $('#member_name').val(data.title);
                        $('#phone').val(data.phone);
                        $('#address').val(data.address);
                        $('#status').val(data.status);
                        $('#membership_type').val(data.membership_type);
                        $('#aadhar_number').val(data.aadhar_number);
                        $('#dob').val(data.dob);
                        $('#gender').val(data.gender);
                        $('#email').val(data.email);
                        $('#member_id').val(data.member_id);
                        $('#expiry_date').val(data.expiry_date);
                        if (data.signature) {
                            try {
                                // Try to clean the signature data if it's not properly formatted
                                let signatureString = data.signature;

                                // Check if the signature data is wrapped in quotes and remove them
                                if (signatureString.startsWith('"') && signatureString.endsWith('"')) {
                                    signatureString = signatureString.substring(1, signatureString.length - 1);
                                }

                                // Check if the data needs to be escaped (sometimes WordPress adds slashes)
                                if (signatureString.includes('\\\"') || signatureString.includes('\\\\')) {
                                    signatureString = signatureString.replace(/\\\"/g, '"').replace(/\\\\/g, '\\');
                                }

                                const signatureData = JSON.parse(signatureString);
                                console.log('Parsed signature data:', signatureData);

                                if (signatureData && signatureData.signature) {
                                    const signatureHtml = `
                                        <img src="${signatureData.signature}" alt="Member Signature" style="max-height: 100px; width: 200px;" />
                                        ${signatureData.metadata ? `
                                            <div style="font-size: 10px; color: #666; margin-top: 5px;">
                                                Signed: ${signatureData.metadata.timestamp || 'N/A'}
                                                ${signatureData.metadata.ip ? `| IP: ${signatureData.metadata.ip}` : ''}
                                                ${signatureData.metadata.phone ? `| Phone: ${signatureData.metadata.phone}` : ''}
                                            </div>
                                        ` : ''}
                                    `;
                                    $('#signature-display').html(signatureHtml);
                                } else {
                                    $('#signature-display').html('<p>Invalid signature format</p>');
                                }
                            } catch (e) {
                                console.error('Error parsing signature data:', e);
                                console.log('Raw signature data:', data.signature);
                                $('#signature-display').html('<p>Invalid signature format</p>');
                            }
                        } else {
                            $('#signature-display').html('<p>No signature available</p>');
                        }
                        // Make member ID field read-only in edit mode
                        $('#member_id').prop('readonly', true);

                        $('#member-modal h2').text('Edit Member');
                        $('#member-modal').show();
                    } else {
                        alert('Error loading member data');
                    }
                },
                error: function() {
                    alert('Error loading member data');
                }
            });
        });

        // Delete member
        $('.delete-member').on('click', function(e) {
            e.preventDefault();
            if (confirm('Are you sure you want to delete this member?')) {
                var memberId = $(this).data('id');

                var form = $('<form>', {
                    'method': 'post',
                    'action': ''
                }).append(
                    $('<input>', {
                        'name': 'delete_member',
                        'value': '1',
                        'type': 'hidden'
                    }),
                    $('<input>', {
                        'name': 'member_id',
                        'value': memberId,
                        'type': 'hidden'
                    })
                );

                $('body').append(form);
                form.submit();
            }
        });

        // Search functionality
        $('#member-search').on('keyup', function() {
            var value = $(this).val().toLowerCase();
            $('table tbody tr').filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
            });
        });

        // Utility Functions
        function isValidPhone(phone) {
            // Basic phone validation - can be customized based on requirements
            var phoneRegex = /^[0-9]{10}$/;
            return phoneRegex.test(phone);
        }

        function showFieldError(elementId, message, isSelect, isNameField) {
            if (isSelect) {
                // For select and date fields that don't have a specific error div
                $('#' + elementId).css('border-color', '#dc3232');
                // Show notification for these fields
                showNotification(message, 'error');
            } else if (isNameField) {
                // Special case for name field which doesn't have an error div
                $('#' + elementId).css('border-color', '#dc3232');
                showNotification(message, 'error');
            } else {
                var errorElement = $('#' + elementId);
                errorElement.text(message).show();
                // Also highlight the input field
                if (elementId === 'aadhar-error') {
                    // For Aadhar errors, make them more prominent
                    $('#aadhar_number').css('border-color', '#dc3232');
                    $('#aadhar_number').css('background-color', '#ffebeb');
                } else {
                    errorElement.prev('input').css('border-color', '#dc3232');
                }
            }
        }

        function clearFieldErrors() {
            $('.field-error').hide().text('');
            // Reset input field borders and backgrounds
            $('.form-field input, .form-field select').css({
                'border-color': '',
                'background-color': ''
            });
        }

        function showNotification(message, type) {
            var notification = $('<div class="notice notice-' + type + ' is-dismissible"><p>' + message + '</p></div>');
            $('.wrap h1').after(notification);

            // Auto dismiss after 3 seconds
            setTimeout(function() {
                notification.fadeOut(function() {
                    $(this).remove();
                });
            }, 3000);
        }

        function showDetailedNotification(message, type) {
            var notification = $('<div class="notice notice-' + type + ' is-dismissible"><p>' + message + '</p></div>');
            $('.wrap h1').after(notification);

            // Auto dismiss after 5 seconds for detailed notifications
            // setTimeout(function() {
            //     notification.fadeOut(function() {
            //         $(this).remove();
            //     });
            // }, 5000);
        }

        // Reset form
        function resetMemberForm() {
            $('#member-form')[0].reset();
            $('#member_id').val('');
            $('#form-title').text('Add New Member');
            $('#cancel-edit').hide();
            $('#member-form').slideUp();
            $('#member-modal').hide();
            // Make member ID field editable when resetting form
            $('#member_id').prop('readonly', false);
        }

        // Calculate expiry date when membership type changes
        $('#membership_type').on('change', function() {
            var months = parseInt($(this).val()) || 0;
            if (months > 0) {
                var joinDate = new Date();
                var expiryDate = new Date(joinDate.setMonth(joinDate.getMonth() + months));
                var formattedDate = expiryDate.toISOString().split('T')[0];
                $('#expiry_date').val(formattedDate);
            } else {
                $('#expiry_date').val('');
            }
        });

        // Calculate initial expiry date when form loads
        $('#membership_type').trigger('change');

        // Bulk Upload Functionality
        $('#bulk-upload-members').on('click', function(e) {
            e.preventDefault();
            $('#bulk-upload-modal').show();
        });

        // Close bulk upload modal
        $('.pm-gym-modal-close').on('click', function() {
            $('#bulk-upload-modal').hide();
        });

        // Download CSV template
        $('#download-template').on('click', function(e) {
            e.preventDefault();
            var csvContent = "Name,Phone,Email,Member ID,Membership Type,Aadhar Number,Gender,Date of Birth,Address,Status\n" +
                "John Doe,9876543210,john@example.com,0001,3,123456789012,male,1990-01-01,123 Main St Delhi,active\n" +
                "Jane Smith,9876543211,jane@example.com,0002,6,123456789013,female,1992-05-15,456 Park Ave Mumbai,active\n" +
                "Mike Johnson,9876543212,mike@example.com,0003,12,123456789014,male,1988-12-20,789 Oak St Bangalore,inactive";

            var blob = new Blob([csvContent], {
                type: 'text/csv;charset=utf-8;'
            });
            var link = document.createElement("a");
            if (link.download !== undefined) {
                var url = URL.createObjectURL(blob);
                link.setAttribute("href", url);
                link.setAttribute("download", "members_template.csv");
                link.style.visibility = 'hidden';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
        });

        // Handle bulk upload form submission
        $('#bulk-upload-form').on('submit', function(e) {
            e.preventDefault();

            var formData = new FormData(this);
            formData.append('action', 'bulk_upload_members');

            // Show progress bar
            $('#upload-progress').show();
            $('.progress-bar-fill').css('width', '0%');
            $('.progress-status').text('Processing...');

            $.ajax({
                url: pm_gym_ajax.ajax_url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                xhr: function() {
                    var xhr = new window.XMLHttpRequest();
                    xhr.upload.addEventListener("progress", function(evt) {
                        if (evt.lengthComputable) {
                            var percentComplete = evt.loaded / evt.total;
                            percentComplete = parseInt(percentComplete * 100);
                            $('.progress-bar-fill').css('width', percentComplete + '%');
                        }
                    }, false);
                    return xhr;
                },
                success: function(response) {
                    if (response.success) {
                        var message = response.data.message;
                        if (response.data.errors && response.data.errors.length > 0) {
                            message += '<br><br><strong>Errors encountered:</strong><br>';
                            message += response.data.errors.slice(0, 10).join('<br>'); // Show first 10 errors
                            if (response.data.errors.length > 10) {
                                message += '<br>... and ' + (response.data.errors.length - 10) + ' more errors.';
                            }
                        }
                        showDetailedNotification(message, 'success');
                        $('#bulk-upload-modal').hide();
                        setTimeout(function() {
                            // location.reload();
                        }, 3000);
                    } else {
                        var message = response.data.message || 'Error uploading file';
                        if (response.data.errors && response.data.errors.length > 0) {
                            message += '<br><br><strong>Errors encountered:</strong><br>';
                            message += response.data.errors.slice(0, 10).join('<br>'); // Show first 10 errors
                            if (response.data.errors.length > 10) {
                                message += '<br>... and ' + (response.data.errors.length - 10) + ' more errors.';
                            }
                        }
                        showDetailedNotification(message, 'error');
                    }
                },
                error: function(xhr, status, error) {
                    showNotification('Error uploading file: ' + error, 'error');
                },
                complete: function() {
                    $('#upload-progress').hide();
                }
            });
        });

        // Export members to CSV
        $('#export-members').on('click', function(e) {
            e.preventDefault();

            // Show loading state
            var $exportBtn = $(this);
            $exportBtn.prop('disabled', true).addClass('loading');

            $.ajax({
                url: pm_gym_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'export_members_csv'
                },
                success: function(response) {
                    if (response.success) {
                        // Create a temporary link to download the file
                        var link = document.createElement('a');
                        link.href = response.data.file_url;
                        link.download = 'gym_members_' + new Date().toISOString().split('T')[0] + '.csv';
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);

                        // Show success message
                        showNotification('Members exported successfully', 'success');
                    } else {
                        showNotification('Error exporting members: ' + response.data.message, 'error');
                    }
                },
                error: function(xhr, status, error) {
                    showNotification('Error exporting members: ' + error, 'error');
                },
                complete: function() {
                    // Remove loading state
                    $exportBtn.prop('disabled', false).removeClass('loading');
                }
            });
        });
    });
</script>