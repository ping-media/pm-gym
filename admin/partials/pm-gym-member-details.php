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

        <div class="member-info-item">
            <strong>Face Recognition:</strong>
            <?php
            $face_descriptor = PM_Gym_Helpers::get_member_face_descriptor($user_id);
            if ($face_descriptor) {
                echo '<span style="color: #28a745;">✓ Enrolled</span>';
            } else {
                echo '<span style="color: #dc3545;">Not Enrolled</span>';
            }
            ?>
        </div>
    </div>

    <!-- Face Enrollment Section -->
    <div class="face-enrollment-section" style="margin-top: 30px; padding: 20px; background: #f9f9f9; border-radius: 8px;">
        <h3>Face Recognition Enrollment</h3>
        <p style="color: #666; margin-bottom: 15px;">Capture member's face for quick check-in using face scanning</p>

        <div class="face-enrollment-container">
            <div id="face-enroll-preview-container" style="display: none;">
                <video id="face-enroll-video" autoplay playsinline style="width: 100%; max-width: 400px; border-radius: 8px; background: #000;"></video>
                <canvas id="face-enroll-canvas" style="display: none;"></canvas>
                <div id="face-enroll-status" style="margin-top: 10px; text-align: center; font-weight: 500;"></div>
            </div>
            <div id="face-enroll-controls">
                <button type="button" id="start-face-enroll" class="button button-primary">Start Face Enrollment</button>
                <button type="button" id="capture-face-enroll" class="button button-primary" style="display: none;">Capture Face</button>
                <button type="button" id="retake-face-enroll" class="button button-secondary" style="display: none;">Retake</button>
                <?php if ($face_descriptor): ?>
                    <button type="button" id="delete-face-enroll" class="button button-secondary" style="margin-left: 10px;">Delete Face Data</button>
                <?php endif; ?>
            </div>
            <div id="face-enroll-preview-thumbnail" style="display: none; margin-top: 15px; text-align: center;">
                <p style="font-weight: 500; color: #28a745;">✓ Face captured successfully</p>
                <img id="face-enroll-thumbnail" src="" alt="Captured face" style="max-width: 150px; border-radius: 8px; margin-top: 10px; border: 2px solid #28a745;">
            </div>
            <div id="face-enroll-error" class="field-error" style="margin-top: 10px;"></div>
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

    .face-enrollment-section {
        margin-top: 30px;
        padding: 20px;
        background: #f9f9f9;
        border-radius: 8px;
        border: 1px solid #ddd;
    }

    .face-enrollment-section h3 {
        margin-top: 0;
        color: #23282d;
    }

    .face-enrollment-container video {
        width: 100%;
        max-width: 400px;
        border-radius: 8px;
        background: #000;
        display: block;
        margin: 0 auto;
    }

    #face-enroll-preview-thumbnail img {
        max-width: 150px;
        border-radius: 8px;
        margin-top: 10px;
        border: 2px solid #28a745;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .field-error {
        color: #dc3545;
        font-size: 12px;
        margin-top: 5px;
    }
</style>

<script>
    jQuery(document).ready(function($) {
        const memberId = <?php echo intval($user_id); ?>;
        let faceModelsLoaded = false;
        let faceEnrollStream = null;
        let faceEnrollDescriptor = null;
        let faceEnrollDetectionInterval = null;

        // Check if face-api.js is loaded
        function checkFaceApiLoaded() {
            if (typeof faceapi === 'undefined') {
                $('#face-enroll-error').html('Face recognition library is loading... Please wait a moment and try again.');
                return false;
            }
            return true;
        }

        // Load face-api.js models
        async function loadFaceEnrollModels() {
            if (faceModelsLoaded) return true;

            if (!checkFaceApiLoaded()) {
                setTimeout(() => {
                    if (checkFaceApiLoaded()) {
                        loadFaceEnrollModels();
                    } else {
                        $('#face-enroll-error').html('Face recognition library failed to load. Please refresh the page.');
                    }
                }, 1000);
                return false;
            }

            try {
                $('#face-enroll-status').text('Loading face recognition models...');

                const MODEL_URL = 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model/';

                await Promise.all([
                    faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
                    faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
                    faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL)
                ]);

                faceModelsLoaded = true;
                $('#face-enroll-status').text('Models loaded. Ready to capture.');
                $('#face-enroll-error').text('');
                return true;
            } catch (error) {
                console.error('Error loading face models:', error);
                $('#face-enroll-error').text('Failed to load face recognition models. Please refresh the page.');
                return false;
            }
        }

        // Start camera for face enrollment
        $('#start-face-enroll').on('click', async function() {
            if (!faceModelsLoaded) {
                const loaded = await loadFaceEnrollModels();
                if (!loaded) return;
            }

            // Check if we're in a secure context (HTTPS, localhost, or .local domains)
            const isSecureContext = window.location.protocol === 'https:' ||
                window.location.hostname === 'localhost' ||
                window.location.hostname === '127.0.0.1' ||
                window.location.hostname.includes('.local');

            if (!isSecureContext) {
                $('#face-enroll-error').html('Camera access requires HTTPS, localhost, or .local domain. Current: ' + window.location.protocol + '://' + window.location.hostname);
                console.warn('Camera access blocked: Not in secure context. Protocol:', window.location.protocol, 'Hostname:', window.location.hostname);
                return;
            }

            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                $('#face-enroll-error').text('Camera access is not supported in this browser.');
                return;
            }

            try {
                const stream = await navigator.mediaDevices.getUserMedia({
                    video: {
                        width: 640,
                        height: 480,
                        facingMode: 'user'
                    }
                });

                faceEnrollStream = stream;
                const video = document.getElementById('face-enroll-video');
                video.srcObject = stream;

                $('#face-enroll-preview-container').show();
                $('#start-face-enroll').hide();
                $('#capture-face-enroll').show();
                $('#face-enroll-status').text('Position your face in the frame');
                $('#face-enroll-error').text('');

                // Start face detection
                detectFaceInEnrollVideo();
            } catch (error) {
                console.error('Error accessing camera:', error);
                let errorMessage = 'Could not access camera. ';

                if (error.name === 'NotAllowedError' || error.name === 'PermissionDeniedError') {
                    errorMessage += 'Please allow camera access and try again.';
                } else if (error.name === 'NotFoundError') {
                    errorMessage += 'No camera found.';
                } else {
                    errorMessage += 'Please check camera permissions.';
                }

                $('#face-enroll-error').text(errorMessage);
                $('#face-enroll-preview-container').hide();
                $('#start-face-enroll').show();
            }
        });

        // Detect face in video stream
        async function detectFaceInEnrollVideo() {
            const video = document.getElementById('face-enroll-video');
            const canvas = document.getElementById('face-enroll-canvas');
            const status = document.getElementById('face-enroll-status');

            if (!video || !canvas) return;

            if (faceEnrollDetectionInterval) {
                clearInterval(faceEnrollDetectionInterval);
            }

            const displaySize = {
                width: video.videoWidth || 640,
                height: video.videoHeight || 480
            };
            faceapi.matchDimensions(canvas, displaySize);

            faceEnrollDetectionInterval = setInterval(async () => {
                if (!faceEnrollStream || faceEnrollDescriptor) {
                    if (faceEnrollDetectionInterval) {
                        clearInterval(faceEnrollDetectionInterval);
                        faceEnrollDetectionInterval = null;
                    }
                    return;
                }

                if (video.readyState === video.HAVE_ENOUGH_DATA) {
                    try {
                        const detections = await faceapi
                            .detectAllFaces(video, new faceapi.TinyFaceDetectorOptions())
                            .withFaceLandmarks()
                            .withFaceDescriptors();

                        if (detections.length > 0) {
                            status.text('Face detected! Click "Capture Face" to save.');
                            status.css('color', '#28a745');
                        } else {
                            status.text('No face detected. Please position your face in the frame.');
                            status.css('color', '#dc3545');
                        }
                    } catch (error) {
                        console.error('Face detection error:', error);
                    }
                }
            }, 500);
        }

        // Capture face descriptor
        $('#capture-face-enroll').on('click', async function() {
            const video = document.getElementById('face-enroll-video');
            const canvas = document.getElementById('face-enroll-canvas');

            try {
                const detections = await faceapi
                    .detectAllFaces(video, new faceapi.TinyFaceDetectorOptions())
                    .withFaceLandmarks()
                    .withFaceDescriptors();

                if (detections.length === 0) {
                    $('#face-enroll-error').text('No face detected. Please try again.');
                    return;
                }

                if (detections.length > 1) {
                    $('#face-enroll-error').text('Multiple faces detected. Please ensure only one person is in frame.');
                    return;
                }

                faceEnrollDescriptor = detections[0].descriptor;
                const descriptorJson = JSON.stringify(Array.from(faceEnrollDescriptor));

                // Capture thumbnail
                const displaySize = {
                    width: video.videoWidth || 640,
                    height: video.videoHeight || 480
                };
                faceapi.matchDimensions(canvas, displaySize);
                const resizedDetections = faceapi.resizeResults(detections, displaySize);

                canvas.width = displaySize.width;
                canvas.height = displaySize.height;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(video, 0, 0, displaySize.width, displaySize.height);

                resizedDetections.forEach(detection => {
                    const box = detection.detection.box;
                    ctx.strokeStyle = '#28a745';
                    ctx.lineWidth = 2;
                    ctx.strokeRect(box.x, box.y, box.width, box.height);
                });

                const thumbnail = canvas.toDataURL('image/jpeg', 0.8);
                $('#face-enroll-thumbnail').attr('src', thumbnail);
                $('#face-enroll-preview-thumbnail').show();

                // Stop video stream
                if (faceEnrollStream) {
                    faceEnrollStream.getTracks().forEach(track => track.stop());
                    faceEnrollStream = null;
                }

                $('#face-enroll-preview-container').hide();
                $('#capture-face-enroll').hide();
                $('#retake-face-enroll').show();
                $('#face-enroll-status').text('Face captured successfully!');
                $('#face-enroll-error').text('');

                // Save to server
                $.ajax({
                    type: 'POST',
                    url: pm_gym_ajax.ajax_url,
                    data: {
                        action: 'update_face_descriptor',
                        member_id: memberId,
                        face_descriptor: descriptorJson
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#face-enroll-error').text('').after('<div style="color: #28a745; margin-top: 10px;">✓ Face enrolled successfully!</div>');
                            setTimeout(() => {
                                location.reload();
                            }, 2000);
                        } else {
                            $('#face-enroll-error').text('Error: ' + (response.data || 'Failed to save face data'));
                        }
                    },
                    error: function() {
                        $('#face-enroll-error').text('Error saving face data. Please try again.');
                    }
                });
            } catch (error) {
                console.error('Error capturing face:', error);
                $('#face-enroll-error').text('Error capturing face. Please try again.');
            }
        });

        // Retake face
        $('#retake-face-enroll').on('click', function() {
            faceEnrollDescriptor = null;
            $('#face-enroll-preview-thumbnail').hide();
            $('#retake-face-enroll').hide();
            $('#start-face-enroll').show();
            $('#face-enroll-status').text('');
        });

        // Delete face data
        $('#delete-face-enroll').on('click', function() {
            if (!confirm('Are you sure you want to delete this member\'s face data?')) {
                return;
            }

            $.ajax({
                type: 'POST',
                url: pm_gym_ajax.ajax_url,
                data: {
                    action: 'update_face_descriptor',
                    member_id: memberId,
                    face_descriptor: ''
                },
                success: function(response) {
                    if (response.success) {
                        alert('Face data deleted successfully.');
                        location.reload();
                    } else {
                        alert('Error: ' + (response.data || 'Failed to delete face data'));
                    }
                },
                error: function() {
                    alert('Error deleting face data. Please try again.');
                }
            });
        });

        // Cleanup
        $(window).on('beforeunload', function() {
            if (faceEnrollStream) {
                faceEnrollStream.getTracks().forEach(track => track.stop());
            }
            if (faceEnrollDetectionInterval) {
                clearInterval(faceEnrollDetectionInterval);
            }
        });
    });
</script>