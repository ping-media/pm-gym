<div class="pm-gym-member-registration-form-container">
    <form id="member-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <h2>Member Registration Form</h2>
        <input type="hidden" name="action" value="save_gym_member">
        <input type="hidden" name="frontend_trigger" value="1">
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
                    <label for="dob">Date of Birth </label>
                    <input type="date" name="dob" id="dob">
                </div>
            </div>
            <div class="col-4">
                <div class="form-field">
                    <label for="membership_type">Membership Type</label>
                    <select name="membership_type" id="membership_type" required>
                        <option value="">Select membership type...</option>
                        <option value="1" selected>1 Month</option>
                        <option value="2">2 Months</option>
                        <option value="3">3 Months</option>
                        <option value="6">6 Months</option>
                        <option value="12">12 Months</option>
                    </select>
                </div>
            </div>
            <div class="col-4">
                <div class="form-field">
                    <label for="aadhar_number">Aadhar Card Number </label>
                    <input type="text" name="aadhar_number" id="aadhar_number" maxlength="12" pattern="[0-9]{12}" title="Please enter a valid 12-digit Aadhar number" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0,12)">
                    <div id="aadhar-error" class="field-error"></div>
                </div>
            </div>
        </div>

        <div class="row">
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
                    <label for="address">Address <span class="required">*</span></label>
                    <input type="text" name="address" id="address" required>
                </div>
            </div>
            <div class="col-4">
                <div class="form-field">
                    <label for="reference">Any reference</label>
                    <input type="text" name="reference" id="reference">
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="form-field">
                    <label for="signature">Digital Signature <span class="required">*</span></label>
                    <div class="signature-pad-container">
                        <canvas id="signature-pad" width="400" height="200"></canvas>
                        <input type="hidden" name="signature" id="signature-input" required>
                        <!-- <button type="button" id="clear-signature" class="button">Clear Signature</button> -->
                    </div>
                    <div id="signature-error" class="field-error"></div>
                </div>
            </div>
        </div>

        <div class="form-submit">
            <button type="submit" class="button button-primary">Save Member</button>
        </div>
    </form>
</div>

<script>
    jQuery(document).ready(function($) {
        // Get user IP and timezone data from PHP
        var userData = {
            ip: '<?php echo htmlspecialchars($_SERVER['REMOTE_ADDR'], ENT_QUOTES); ?>',
            timezone: '<?php echo htmlspecialchars(date_default_timezone_get(), ENT_QUOTES); ?>',
            timestamp: '<?php echo htmlspecialchars(date('Y-m-d H:i:s'), ENT_QUOTES); ?>'
        };

        // Initialize signature pad
        var canvas = document.getElementById('signature-pad');
        var signaturePad = new SignaturePad(canvas, {
            backgroundColor: 'rgb(255, 255, 255)',
            penColor: 'rgb(0, 0, 0)'
        });

        // Adjust canvas size
        function resizeCanvas() {
            var ratio = Math.max(window.devicePixelRatio || 1, 1);
            canvas.width = canvas.offsetWidth * ratio;
            canvas.height = canvas.offsetHeight * ratio;
            canvas.getContext("2d").scale(ratio, ratio);
            signaturePad.clear(); // Clear the canvas after resize
        }

        // Initial resize
        resizeCanvas();
        window.addEventListener("resize", resizeCanvas);

        // Clear signature button
        $('#clear-signature').on('click', function() {
            signaturePad.clear();
            $('#signature-input').val('');
        });

        // Handle form submission
        $('#member-form').on('submit', function(e) {
            e.preventDefault();

            // Clear previous errors
            $('.field-error').text('');

            // Validate Aadhar number
            var aadharNumber = $('#aadhar_number').val();
            if (aadharNumber && aadharNumber.length !== 12) {
                $('#aadhar-error').text('Please enter a valid 12-digit Aadhar number');
                return false;
            }

            // Validate signature
            if (signaturePad.isEmpty()) {
                $('#signature-error').text('Please provide your signature');
                return false;
            }

            try {
                // Use PNG data URL instead of SVG for reliability
                var signatureDataUrl = signaturePad.toDataURL('image/png');

                if (!signatureDataUrl) {
                    $('#signature-error').text('Error capturing signature. Please try again.');
                    return false;
                }

                // Create a JSON object with signature data and metadata
                var signatureData = {
                    signature: signatureDataUrl,
                    metadata: {
                        ip: userData.ip,
                        timezone: userData.timezone,
                        timestamp: userData.timestamp,
                        phone: $('#phone').val()
                    }
                };

                // Convert to JSON string
                var signatureJSON = JSON.stringify(signatureData);

                // Store in hidden input
                $('#signature-input').val(signatureJSON);

                // Show loading state
                var $submitBtn = $(this).find('button[type="submit"]');
                $submitBtn.prop('disabled', true).text('Processing...');

                // Get form data
                var formData = new FormData(this);
                formData.append('action', 'save_gym_member');

                // Send AJAX request
                $.ajax({
                    url: pm_gym_ajax.ajax_url,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            // Show success message
                            alert('Member registered successfully!');
                            // Reset form
                            $('#member-form')[0].reset();
                            signaturePad.clear();
                            $('#signature-input').val('');
                        } else {
                            console.error('Server response:', response);
                            alert('Error: ' + (response.data || 'Unknown error occurred'));
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX error:', error);
                        alert('Error registering member. Please try again.');
                    },
                    complete: function() {
                        // Reset button state
                        $submitBtn.prop('disabled', false).text('Save Member');
                    }
                });

            } catch (error) {
                console.error('Error processing signature:', error);
                $('#signature-error').text('Error processing signature. Please try again.');
                return false;
            }
        });

        // Phone number validation
        $('#phone').on('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);
        });

        // Aadhar number validation
        $('#aadhar_number').on('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '').slice(0, 12);
        });

        // Member ID validation
        $('#member_id').on('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '').slice(0, 4);
        });
    });
</script>
<style>
    #signature-pad {
        width: 100%;
        height: 200px;
        background-color: #fff;
        border: 1px solid #ddd;
        border-radius: 5px;
        touch-action: none;
    }

    .signature-pad-container {
        margin: 10px 0;
        width: 400px;
    }
</style>