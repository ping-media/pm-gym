<div class="pm-gym-attendance-form-container">
    <div class="pm-gym-attendance-form">
        <div style="text-align: center; margin-bottom: 20px;">
            <img src="/wp-content/plugins/pm-gym/public/img/logo-gym.png" alt="PM Gym Logo" style="width: 100px; height: 100px; margin-bottom: 0px;">
        </div>
        <h2>Mark Your Attendance</h2>
        <form id="pm-gym-attendance-form" method="post">
            <div class="form-group">
                <label for="member_id">Member ID</label>
                <div style="display: flex; gap: 10px; align-items: center; flex-direction: column;">
                    <input type="text" id="member_id" name="member_id" required
                        placeholder="Enter your member ID (e.g., 12345)"
                        pattern="[0-9]{4,5}"
                        title="Please enter a valid member ID"
                        style="flex: 1;">
                    <span style="color: #666; font-weight: 500;">OR</span>
                    <button type="button" id="scan-face-btn" class="button button-secondary" style="white-space: nowrap;">Scan Face</button>
                </div>
            </div>

            <!-- Face Recognition Modal/Container -->
            <div id="face-scan-container" style="display: none; margin-top: 20px;">
                <div class="face-scan-preview">
                    <video id="face-scan-video" autoplay playsinline style="width: 100%; max-width: 640px; border-radius: 8px; background: #000;"></video>
                    <canvas id="face-scan-canvas" style="display: none;"></canvas>
                    <div id="face-scan-status" style="margin-top: 10px; text-align: center; font-weight: 500; min-height: 24px;"></div>
                    <div id="face-scan-controls" style="margin-top: 15px; text-align: center;">
                        <button type="button" id="stop-face-scan" class="button button-secondary">Cancel</button>
                    </div>
                </div>
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
        <div class="form-group" style="text-align:center; margin-top:20px;">
            <a href="/member-register" class="button button-link" style="color:#0073aa; font-weight:600; text-decoration:underline; background:none; border:none; cursor:pointer;">
                Not a member yet? Register here
            </a>
        </div>
        <div class="form-group" style="text-align:center; margin-top:20px;">
            <a href="/enroll-face" class="button button-link" style="color:#0073aa; font-weight:600; text-decoration:underline; background:none; border:none; cursor:pointer;">
                Enroll your face Here
            </a>
        </div>
    </div>
</div>

<style>
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
        // Face Recognition Variables
        let faceModelsLoaded = false;
        let faceScanStream = null;
        let allFaceDescriptors = [];
        let faceScanInterval = null;
        let isScanning = false;

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
                        console.log(response);
                        if (response.success) {
                            const data = response.data;
                            $('#member-name').text(data.name);
                            if (data.remaining && data.status == 'active') {
                                $('#membership-remaining').text(data.remaining);
                            } else if (data.status == 'expired') {
                                let expire_date = new Date(data.expiry_date);
                                expire_date_value = expire_date.toLocaleDateString('en-US', {
                                    day: 'numeric',
                                    month: 'short',
                                    year: 'numeric'
                                });
                                let days_diff = Math.ceil((new Date() - expire_date) / (1000 * 60 * 60 * 24));
                                let expire_msg = ' expired <strong>' + days_diff + ' days ago</strong> on <strong>' + expire_date_value + '</strong>';
                                // let expire_msg = ' expired on ' + expire_date_value;
                                $('#membership-remaining').html(expire_msg);
                                $('#membership-status').addClass('expired');
                            } else {
                                $('#membership-remaining').text(data.status);
                            }
                            $('#member-info').show();
                            // $('#membership-status').removeClass('expired');

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

        // Check if face-api.js is loaded
        function checkFaceApiLoaded() {
            if (typeof faceapi === 'undefined') {
                $('#face-scan-status').text('Face recognition library is loading... Please wait.').css('color', '#dc3545');
                return false;
            }
            return true;
        }

        // Face Recognition Functions
        async function loadFaceModels() {
            if (faceModelsLoaded) return true;

            // Check if face-api is available
            if (!checkFaceApiLoaded()) {
                // Wait a bit and try again
                setTimeout(() => {
                    if (checkFaceApiLoaded()) {
                        loadFaceModels();
                    } else {
                        $('#face-scan-status').text('Face recognition library failed to load. Please refresh the page or use Member ID.').css('color', '#dc3545');
                    }
                }, 1000);
                return false;
            }

            try {
                $('#face-scan-status').text('Loading face recognition models...').css('color', '#333');

                const MODEL_URL = 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model/';

                await Promise.all([
                    faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
                    faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
                    faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL)
                ]);

                faceModelsLoaded = true;
                return true;
            } catch (error) {
                console.error('Error loading face models:', error);
                $('#face-scan-status').text('Failed to load face recognition models.').css('color', '#dc3545');
                return false;
            }
        }

        async function loadAllFaceDescriptors() {
            try {
                const response = await $.ajax({
                    type: 'POST',
                    url: pm_gym_ajax.ajax_url,
                    data: {
                        action: 'get_all_face_descriptors'
                    }
                });

                if (response.success && Array.isArray(response.data)) {
                    allFaceDescriptors = response.data;
                    return true;
                } else {
                    console.error('Failed to load face descriptors');
                    return false;
                }
            } catch (error) {
                console.error('Error loading face descriptors:', error);
                return false;
            }
        }

        function calculateFaceDistance(descriptor1, descriptor2) {
            let sum = 0;
            for (let i = 0; i < descriptor1.length; i++) {
                const diff = descriptor1[i] - descriptor2[i];
                sum += diff * diff;
            }
            return Math.sqrt(sum);
        }

        function findMatchingFace(detectedDescriptor) {
            const threshold = pm_gym_ajax.face_match_threshold || 0.6;
            let bestMatch = null;
            let bestDistance = Infinity;

            for (let i = 0; i < allFaceDescriptors.length; i++) {
                const member = allFaceDescriptors[i];
                const distance = calculateFaceDistance(detectedDescriptor, member.descriptor);

                if (distance < threshold && distance < bestDistance) {
                    bestDistance = distance;
                    bestMatch = member;
                }
            }

            return bestMatch;
        }

        // Start face scanning
        $('#scan-face-btn').on('click', async function() {
            if (isScanning) return;

            // Hide member ID field temporarily
            $('#member_id').closest('.form-group').hide();
            $('#scan-face-btn').hide();
            $('#face-scan-container').show();
            isScanning = true;

            // Load models if not loaded
            if (!faceModelsLoaded) {
                const modelsLoaded = await loadFaceModels();
                if (!modelsLoaded) {
                    stopFaceScan();
                    return;
                }
            }

            // Load face descriptors
            $('#face-scan-status').text('Loading member face data...');
            const descriptorsLoaded = await loadAllFaceDescriptors();
            if (!descriptorsLoaded || allFaceDescriptors.length === 0) {
                $('#face-scan-status').text('No face data available. Please use Member ID entry.').css('color', '#dc3545');
                setTimeout(() => {
                    stopFaceScan();
                }, 3000);
                return;
            }

            // Check if we're in a secure context (HTTPS, localhost, or .local domains)
            const isSecureContext = window.location.protocol === 'https:' ||
                window.location.hostname === 'localhost' ||
                window.location.hostname === '127.0.0.1' ||
                window.location.hostname.includes('.local');

            if (!isSecureContext) {
                $('#face-scan-status').html('Camera access requires HTTPS, localhost, or .local domain. Current: ' + window.location.protocol + '://' + window.location.hostname).css('color', '#dc3545');
                console.warn('Camera access blocked: Not in secure context. Protocol:', window.location.protocol, 'Hostname:', window.location.hostname);
                setTimeout(() => {
                    stopFaceScan();
                }, 5000);
                return;
            }

            // Check if getUserMedia is supported
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                $('#face-scan-status').text('Camera access is not supported in this browser. Please use Member ID.').css('color', '#dc3545');
                setTimeout(() => {
                    stopFaceScan();
                }, 3000);
                return;
            }

            // Start camera
            try {
                const stream = await navigator.mediaDevices.getUserMedia({
                    video: {
                        width: 640,
                        height: 480,
                        facingMode: 'user'
                    }
                });

                faceScanStream = stream;
                const video = document.getElementById('face-scan-video');
                video.srcObject = stream;

                $('#face-scan-status').text('Position your face in the frame...').css('color', '#333');

                // Start face detection loop
                let frameCount = 0;
                faceScanInterval = setInterval(async () => {
                    if (!isScanning) {
                        clearInterval(faceScanInterval);
                        return;
                    }

                    frameCount++;
                    // Process every 3rd frame for performance
                    if (frameCount % 3 !== 0) return;

                    const video = document.getElementById('face-scan-video');
                    if (video.readyState !== video.HAVE_ENOUGH_DATA) return;

                    try {
                        const detections = await faceapi
                            .detectAllFaces(video, new faceapi.TinyFaceDetectorOptions())
                            .withFaceLandmarks()
                            .withFaceDescriptors();

                        if (detections.length === 0) {
                            $('#face-scan-status').text('No face detected. Please position your face in the frame.').css('color', '#dc3545');
                            return;
                        }

                        if (detections.length > 1) {
                            $('#face-scan-status').text('Multiple faces detected. Please ensure only one person is in frame.').css('color', '#dc3545');
                            return;
                        }

                        const detectedDescriptor = Array.from(detections[0].descriptor);
                        const match = findMatchingFace(detectedDescriptor);

                        if (match) {
                            // Match found!
                            clearInterval(faceScanInterval);
                            stopFaceScan();

                            // Auto-fill member ID
                            $('#member_id').val(match.member_id.toString().padStart(4, '0'));
                            $('#member_id').removeClass('error-field');

                            // Show member info
                            $('#face-scan-status').text('✓ Face recognized! Welcome, ' + match.name).css('color', '#28a745');

                            // Trigger member details fetch to show full info
                            $.ajax({
                                type: 'POST',
                                url: pm_gym_ajax.ajax_url,
                                data: {
                                    action: 'get_member_details_for_front_end',
                                    member_id: match.member_id
                                },
                                success: function(response) {
                                    if (response.success) {
                                        const data = response.data;
                                        $('#member-name').text(data.name);
                                        if (data.remaining && data.status == 'active') {
                                            $('#membership-remaining').text(data.remaining);
                                        } else if (data.status == 'expired') {
                                            let expire_date = new Date(data.expiry_date);
                                            expire_date_value = expire_date.toLocaleDateString('en-US', {
                                                day: 'numeric',
                                                month: 'short',
                                                year: 'numeric'
                                            });
                                            let days_diff = Math.ceil((new Date() - expire_date) / (1000 * 60 * 60 * 24));
                                            let expire_msg = ' expired <strong>' + days_diff + ' days ago</strong> on <strong>' + expire_date_value + '</strong>';
                                            $('#membership-remaining').html(expire_msg);
                                            $('#membership-status').addClass('expired');
                                        } else {
                                            $('#membership-remaining').text(data.status);
                                        }
                                        $('#member-info').show();
                                    }
                                }
                            });

                            // Show member ID field again
                            $('#member_id').closest('.form-group').show();
                            $('#scan-face-btn').show();

                            setTimeout(() => {
                                $('#face-scan-container').hide();
                                $('#face-scan-status').text('');
                            }, 2000);
                        } else {
                            $('#face-scan-status').text('Face not recognized. Please try again or use Member ID.').css('color', '#dc3545');
                        }
                    } catch (error) {
                        console.error('Error during face detection:', error);
                        // Continue trying, don't show error to user unless it's persistent
                        if (error.message && error.message.includes('model')) {
                            $('#face-scan-status').text('Face recognition error. Please use Member ID.').css('color', '#dc3545');
                            setTimeout(() => {
                                stopFaceScan();
                            }, 3000);
                        }
                    }
                }, 500); // Check every 500ms

            } catch (error) {
                console.error('Error accessing camera:', error);
                let errorMessage = 'Could not access camera. ';

                if (error.name === 'NotAllowedError' || error.name === 'PermissionDeniedError') {
                    errorMessage += 'Please allow camera access and try again, or use Member ID.';
                } else if (error.name === 'NotFoundError' || error.name === 'DevicesNotFoundError') {
                    errorMessage += 'No camera found. Please use Member ID.';
                } else if (error.name === 'NotReadableError' || error.name === 'TrackStartError') {
                    errorMessage += 'Camera is being used by another application.';
                } else {
                    errorMessage += 'Please check camera permissions or use Member ID.';
                }

                $('#face-scan-status').text(errorMessage).css('color', '#dc3545');
                setTimeout(() => {
                    stopFaceScan();
                }, 5000);
            }
        });

        function stopFaceScan() {
            isScanning = false;

            if (faceScanInterval) {
                clearInterval(faceScanInterval);
                faceScanInterval = null;
            }

            if (faceScanStream) {
                faceScanStream.getTracks().forEach(track => track.stop());
                faceScanStream = null;
            }

            const video = document.getElementById('face-scan-video');
            if (video) {
                video.srcObject = null;
            }

            $('#face-scan-container').hide();
            $('#member_id').closest('.form-group').show();
            $('#scan-face-btn').show();
            $('#face-scan-status').text('');
        }

        $('#stop-face-scan').on('click', function() {
            stopFaceScan();
        });

        // Cleanup on page unload
        $(window).on('beforeunload', function() {
            stopFaceScan();
        });
    });
</script>