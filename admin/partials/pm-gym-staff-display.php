<?php
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;

$staff_table = PM_GYM_STAFF_TABLE;

// Handle member deletion
if (isset($_POST['delete_staff']) && isset($_POST['record_id'])) {
    $record_id = intval($_POST['record_id']);

    $result = $wpdb->delete(
        $staff_table,
        array('id' => $record_id),
        array('%d')
    );

    if ($result) {
        add_settings_error(
            'gym_staff',
            'staff_deleted',
            'Staff deleted successfully.',
            'success'
        );
    } else {
        add_settings_error(
            'gym_staff',
            'delete_error',
            'Error deleting staff.',
            'error'
        );
    }
}

// Get current sort parameters
$orderby = isset($_GET['orderby']) ? sanitize_text_field($_GET['orderby']) : 'id';
$order = isset($_GET['order']) ? sanitize_text_field($_GET['order']) : 'DESC';

// Make sure orderby is valid to prevent SQL injection
$allowed_orderby = array('id', 'name', 'role', 'phone', 'status');
if (!in_array($orderby, $allowed_orderby)) {
    $orderby = 'id';
}

// Make sure order is valid
$order = strtoupper($order);
if ($order !== 'ASC' && $order !== 'DESC') {
    $order = 'DESC';
}

// Get all members from the custom table with sorting
$sql = "SELECT * FROM $staff_table ORDER BY ";
switch ($orderby) {
    case 'name':
        $sql .= "name";
        break;
    case 'status':
        $sql .= "status";
        break;
    case 'phone':
        $sql .= "phone";
        break;
    default:
        $sql .= "id";
}
$sql .= " $order";
$staff = $wpdb->get_results($sql);





?>



<div class="wrap">
    <h1 class="wp-heading-inline">Gym Staff</h1>
    <a href="#" class="page-title-action" id="add-new-staff">Add New Staff</a>
    <a href="#" class="page-title-action" id="export-staff">Export Staff Data</a>
    <!-- <a href="#" class="page-title-action" id="bulk-upload-staff">Bulk Upload Staff</a> -->

    <?php settings_errors('gym_staff'); ?>

    <?php
    // Get total members count
    $total_staff = $wpdb->get_var("SELECT COUNT(*) FROM $staff_table");

    // Get active members count (membership not expired)
    $active_members = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $staff_table WHERE status = 'active'",
    ));

    // Get inactive members count
    $inactive_members = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $staff_table WHERE status = 'inactive'",
    ));

    ?>

    <div class="pm-stats-container">
        <div class="pm-stat-box">
            <h3>Total Staff</h3>
            <p class="pm-stat-number"><?php echo esc_html($total_staff); ?></p>
        </div>
        <div class="pm-stat-box">
            <h3>Active Staff</h3>
            <p class="pm-stat-number"><?php echo esc_html($active_members); ?></p>
        </div>
        <div class="pm-stat-box">
            <h3>Inactive Staff</h3>
            <p class="pm-stat-number"><?php echo esc_html($inactive_members); ?></p>
        </div>
    </div>

    <div class="pm-gym-members-container">
        <div class="search-box-container">
            <input type="text" id="staff-search" placeholder="Search by ID, Name or Phone..." class="regular-text">
        </div>
        <?php
        // Create base URL for sorting
        $current_url = add_query_arg(array('page' => 'pm-gym-staff'), admin_url('admin.php'));
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
                            Staff ID<?php echo esc_html($id_sort_indicator); ?>
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
                    <th>Role</th>
                    <th>Status</th>
                    <th>Join Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (empty($staff)) {
                    echo '<tr><td colspan="6" style="text-align: center;">No staff found</td></tr>';
                } else {
                    foreach ($staff as $staff_member):
                ?>
                        <tr>
                            <td><?php echo esc_html(PM_Gym_Helpers::format_staff_id($staff_member->staff_id)); ?></td>
                            <td class="text-capitalize"><?php echo esc_html($staff_member->name); ?></td>
                            <td><?php echo esc_html($staff_member->phone); ?></td>
                            <td><?php echo esc_html(ucfirst($staff_member->role)); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo esc_attr($staff_member->status); ?>">
                                    <?php echo esc_html(ucfirst($staff_member->status)); ?>
                                </span>
                            </td>
                            <td><?php echo esc_html(PM_Gym_Helpers::format_date($staff_member->date_created)); ?></td>
                            <td>
                                <button class="button edit-staff" data-id="<?php echo esc_attr($staff_member->staff_id); ?>">Edit</button>
                                <?php if (current_user_can('administrator') && get_current_user_id() === 1): ?>
                                    <button class="button delete-staff" data-id="<?php echo esc_attr($staff_member->id); ?>">Delete</button>
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

<!-- Add/Edit Staff Modal -->
<div id="staff-modal" class="pm-gym-modal" style="display: none;">
    <div class="pm-gym-modal-content">
        <span class="pm-gym-modal-close">&times;</span>
        <h2>Add New Staff</h2>
        <form id="staff-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <input type="hidden" name="action" value="save_gym_staff">
            <input type="hidden" name="record_id" id="record_id">

            <div class="row">
                <div class="col-4">
                    <div class="form-field">
                        <label for="staff_id">Staff ID <span class="required">*</span></label>
                        <input type="number" name="staff_id" id="staff_id" required min="1" max="9999" oninput="javascript: if (this.value.length > 4) this.value = this.value.slice(0,4);">
                        <div id="staff-id-error" class="field-error"></div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="form-field">
                        <label for="staff_name">Name <span class="required">*</span></label>
                        <input type="text" name="staff_name" id="staff_name" required>
                    </div>
                </div>
                <div class="col-4">
                    <div class="form-field">
                        <label for="staff_phone">Phone <span class="required">*</span></label>
                        <input type="tel" name="staff_phone" id="staff_phone" required pattern="[0-9]{10}" maxlength="10" title="Please enter a valid 10-digit phone number" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                        <div id="staff-phone-error" class="field-error"></div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-4">
                    <div class="form-field">
                        <label for="staff_role">Role <span class="required">*</span></label>
                        <select name="staff_role" id="staff_role" required>
                            <option value="">Select Role</option>
                            <option value="trainer">Trainer</option>
                            <option value="assistant">Assistant</option>
                            <option value="care_taker">Care Taker</option>
                        </select>
                    </div>
                </div>
                <div class="col-4">
                    <div class="form-field">
                        <label for="staff_aadhar">Aadhar Card Number <span class="required">*</span></label>
                        <input type="text" name="staff_aadhar" id="staff_aadhar" maxlength="12" pattern="[0-9]{12}" title="Please enter a valid 12-digit Aadhar number" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0,12)" required>
                        <div id="staff-aadhar-error" class="field-error"></div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="form-field">
                        <label for="staff_status">Status <span class="required">*</span></label>
                        <select name="staff_status" id="staff_status" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-field">
                <label for="staff_address">Address <span class="required">*</span></label>
                <textarea name="staff_address" id="staff_address" rows="2" required></textarea>
            </div>

            <div class="form-submit">
                <button type="submit" class="button button-primary">Save Staff</button>
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
        $('#add-new-staff').on('click', function(e) {
            e.preventDefault();
            $('#staff-form')[0].reset();
            $('#record_id').val('');
            // Make member ID field editable in add mode
            $('#staff_id').prop('readonly', false);

            // Get next available member ID
            $.ajax({
                url: pm_gym_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'get_next_staff_id'
                },
                success: function(response) {
                    if (response.success) {
                        $('#staff_id').val(String(response.data.next_id).padStart(4, '0'));
                    }
                }
            });

            $('#staff-modal h2').text('Add New Staff');
            $('#staff-modal').show();
        });

        $('.pm-gym-modal-close').on('click', function() {
            $('#staff-modal').hide();
        });

        // Form Submission
        $('#staff-form').on('submit', function(e) {
            e.preventDefault();

            // Clear any previous error messages
            clearFieldErrors();

            var record_id = $('#record_id').val();
            var phone = $('#staff_phone').val();
            var name = $('#staff_name').val();
            var address = $('#staff_address').val();
            var status = $('#staff_status').val();
            var aadhar_number = $('#staff_aadhar').val();
            var staff_id = $('#staff_id').val();
            var role = $('#staff_role').val();

            // Validate all required fields
            var isValid = true;

            if (!name) {
                showFieldError('staff_name', 'Name is required', false, true);
                isValid = false;
            }

            if (!phone || !isValidPhone(phone)) {
                showFieldError('staff_phone-error', 'Please enter a valid 10-digit phone number');
                isValid = false;
            }

            if (!staff_id) {
                showFieldError('staff-id-error', 'Staff ID is required');
                isValid = false;
            }

            if (aadhar_number && aadhar_number.length !== 12) {
                showFieldError('staff-aadhar-error', 'Please enter a valid 12-digit Aadhar number');
                isValid = false;
            }

            if (!address) {
                showFieldError('staff_address', 'Address is required', true);
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
                action: 'save_gym_staff',
                record_id: record_id,
                staff_id: staff_id,
                staff_name: name,
                phone: phone,
                address: address,
                status: status,
                aadhar_number: aadhar_number,
                address: address,
                role: role,
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
                        resetStaffForm();
                        // Reload page to show updated data
                        location.reload();
                    } else {
                        // Check for specific error messages and display them under the appropriate field
                        if (response.data) {
                            if (response.data.indexOf('phone number already exists') !== -1) {
                                showFieldError('staff_phone-error', 'This phone number already exists');
                            } else if (response.data.indexOf('aadhar number already exists') !== -1) {
                                showFieldError('staff-aadhar-error', 'This Aadhar number already exists');
                            } else if (response.data.indexOf('staff with this ID already exists') !== -1) {
                                showFieldError('staff-id-error', 'This Staff ID already exists');
                            } else {
                                showNotification(response.data, 'error');
                            }
                        } else {
                            showNotification('Error saving staff', 'error');
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', {
                        xhr: xhr,
                        status: status,
                        error: error
                    });
                    showNotification('Error saving staff: ' + error, 'error');
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
        $('.edit-staff').on('click', function(e) {
            e.preventDefault();
            var staffId = $(this).data('id');

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'get_staff_data',
                    staff_id: staffId
                },
                success: function(response) {
                    if (response.success) {
                        var data = response.data;

                        console.log(data)

                        $('#record_id').val(data.id);   
                        $('#staff_name').val(data.name);
                        $('#staff_phone').val(data.phone);
                        $('#staff_address').val(data.address);
                        $('#staff_status').val(data.status);
                        $('#staff_aadhar').val(data.aadhar_number);
                        $('#staff_id').val(data.staff_id);
                        $('#staff_role').val(data.role);
                        
                        // Make member ID field read-only in edit mode
                        $('#staff_id').prop('readonly', true);

                        $('#staff-modal h2').text('Edit Staff');
                        $('#staff-modal').show();
                    } else {
                        alert('Error loading staff data');
                    }
                },
                error: function() {
                    alert('Error loading staff data');
                }
            });
        });

        // Delete member
        $('.delete-staff').on('click', function(e) {
            e.preventDefault();
            if (confirm('Are you sure you want to delete this staff?')) {
                var recordId = $(this).data('id');

                var form = $('<form>', {
                    'method': 'post',
                    'action': ''
                }).append(
                    $('<input>', {
                        'name': 'delete_staff',
                        'value': '1',
                        'type': 'hidden'
                    }),
                    $('<input>', {
                        'name': 'record_id',
                        'value': recordId,
                        'type': 'hidden'
                    })
                );

                $('body').append(form);
                form.submit();
            }
        });

        // Search functionality
        $('#staff-search').on('keyup', function() {
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
        function resetStaffForm() {
            $('#staff-form')[0].reset();
            $('#staff_id').val('');
            $('#form-title').text('Add New Staff');
            $('#cancel-edit').hide();
            $('#staff-form').slideUp();
            $('#staff-modal').hide();
            // Make staff ID field editable when resetting form
            $('#staff_id').prop('readonly', false);
        }

        // Calculate expiry date when membership type changes
        $('#staff_role').on('change', function() {
            var months = parseInt($(this).val()) || 0;
            if (months > 0) {
                var joinDate = new Date();
                var expiryDate = new Date(joinDate.setMonth(joinDate.getMonth() + months));
                var formattedDate = expiryDate.toISOString().split('T')[0];
                $('#staff_expiry_date').val(formattedDate);
            } else {
                $('#staff_expiry_date').val('');
            }
        });

        // Calculate initial expiry date when form loads
        $('#staff_role').trigger('change');

        // Bulk Upload Functionality
        $('#bulk-upload-staff').on('click', function(e) {
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
                link.setAttribute("download", "staff_template.csv");
                link.style.visibility = 'hidden';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
        });

        // Export members to CSV
        $('#export-staff').on('click', function(e) {
            e.preventDefault();

            // Show loading state
            var $exportBtn = $(this);
            $exportBtn.prop('disabled', true).addClass('loading');

            $.ajax({
                url: pm_gym_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'export_staff_csv'
                },
                success: function(response) {
                    if (response.success) {
                        // Create a temporary link to download the file
                        var link = document.createElement('a');
                        link.href = response.data.file_url;
                        link.download = 'gym_staff_' + new Date().toISOString().split('T')[0] + '.csv';
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);

                        // Show success message
                        showNotification('Staff exported successfully', 'success');
                    } else {
                        showNotification('Error exporting staff: ' + response.data.message, 'error');
                    }
                },
                error: function(xhr, status, error) {
                    showNotification('Error exporting staff: ' + error, 'error');
                },
                complete: function() {
                    // Remove loading state
                    $exportBtn.prop('disabled', false).removeClass('loading');
                }
            });
        });
    });
</script>