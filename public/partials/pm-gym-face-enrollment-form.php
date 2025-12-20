<div class="pm-gym-face-enrollment-form-container">
    <div class="pm-gym-face-enrollment-form">
        <div style="text-align: center; margin-bottom: 20px;">
            <img src="/wp-content/plugins/pm-gym/public/img/logo-gym.png" alt="PM Gym Logo" style="width: 100px; height: 100px; margin-bottom: 20px;">
        </div>
        <h2>Face Recognition Enrollment</h2>
        <p style="text-align: center; color: #666; margin-bottom: 25px;">Enter your Member ID to register or update your face recognition data</p>

        <form id="face-enrollment-form">
            <!-- Step 1: Member ID Verification -->
            <div id="member-verification-step" class="form-step">
                <div class="form-group">
                    <label for="enrollment_member_id">Member ID <span class="required">*</span></label>
                    <input type="text" id="enrollment_member_id" name="member_id" required
                        placeholder="Enter your member ID (e.g., 1234)"
                        pattern="[0-9]{4,5}"
                        title="Please enter a valid member ID">
                </div>
                <div class="form-group">
                    <button type="button" id="verify-member-btn" class="button button-primary">Verify Member</button>
                </div>
                <div id="member-verification-message" class="message"></div>
            </div>

            <!-- Step 2: Member Info Display -->
            <div id="member-info-step" class="form-step" style="display: none;">
                <div id="member-info-display" class="member-info-box">
                    <h3 id="member-greeting-text">Hello, <span id="member-name-display"></span>!</h3>
                    <p id="member-status-display"></p>
                    <p id="face-enrollment-status-display"></p>
                </div>
            </div>

            <!-- Step 3: Face Capture -->
            <div id="face-capture-step" class="form-step" style="display: none;">
                <div class="form-group">
                    <label>Face Capture</label>
                    <div class="face-capture-container">
                        <div id="face-preview-container" style="display: none;">
                            <video id="face-video" autoplay playsinline style="width: 100%; max-width: 400px; border-radius: 8px; background: #000; max-height: 300px;"></video>
                            <canvas id="face-canvas" style="display: none;"></canvas>
                            <div id="face-status" style="margin-top: 10px; text-align: center; font-weight: 500;"></div>
                        </div>
                        <div id="face-capture-controls">
                            <button type="button" id="start-face-capture" class="button button-primary">Start Camera</button>
                            <button type="button" id="capture-face" class="button button-primary" style="display: none;">Capture Face</button>
                            <button type="button" id="retake-face" class="button button-secondary" style="display: none;">Retake</button>
                        </div>
                        <div id="face-preview-thumbnail" style="display: none; margin-top: 15px; text-align: center;">
                            <p style="font-weight: 500; color: #28a745; margin-bottom: 0px;">✓ Face captured successfully</p>
                            <img id="face-thumbnail" src="" alt="Captured face" style="max-width: 150px; height: 140px; border-radius: 8px; margin-top: 10px; border: 2px solid #28a745;">
                        </div>
                        <input type="hidden" name="face_descriptor" id="face-descriptor-input">
                        <div id="face-error" class="field-error"></div>
                    </div>
                </div>
                <div class="form-group">
                    <button type="button" id="save-face-enrollment" class="button button-primary" style="display: none;">Save Face Enrollment</button>
                </div>
                <div id="face-enrollment-message" class="message"></div>
            </div>

            <!-- Back to verification button -->
            <div id="back-to-verification" style="display: none; text-align: center; margin-top: 20px;">
                <button type="button" id="reset-form-btn" class="button button-secondary">Enroll Another Member</button>
            </div>
        </form>

        <div class="form-group" style="text-align: center; margin-top: 20px;">
            <a href="/" class="button button-secondary" style="font-weight:600; padding:10px 32px; border-radius:7px; text-decoration:none;">
                Back to mark attendance
            </a>
        </div>
    </div>
</div>
</div>

<style>
    .pm-gym-face-enrollment-form-container {
        background: url(/wp-content/plugins/pm-gym/public/img/bg.jpg);
        min-height: 100vh;
        background-repeat: no-repeat;
        background-size: cover;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        padding: 20px;
    }

    .pm-gym-face-enrollment-form {
        background: #fff;
        padding: 30px;
        border-radius: 30px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        max-width: 600px;
        width: 100%;
    }

    .pm-gym-face-enrollment-form h2 {
        text-align: center;
        font-size: 28px;
        margin-bottom: 10px;
        color: #1e2b3d;
    }

    .form-step {
        margin-bottom: 25px;
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

    .form-group input[type="text"] {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 16px;
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

    .button-secondary {
        background: transparent;
        color: #797979;
        border: 1px solid #797979;
        border-radius: 6px;
        padding: 10px 15px;
        font-size: 16px;
        cursor: pointer;
    }

    .button-secondary:hover {
        background: #797979;
        color: #fff;
    }

    .member-info-box {
        padding: 20px;
        background: #f8dedc;
        border-radius: 10px;
        text-align: center;
        margin-bottom: 20px;
    }

    .member-info-box h3 {
        font-size: 24px;
        margin-bottom: 10px;
        color: #1e2b3d;
    }

    .member-info-box p {
        font-size: 16px;
        color: #333;
        margin: 5px 0;
    }

    .message {
        margin-top: 15px;
        padding: 10px;
        border-radius: 4px;
        text-align: center;
        font-weight: 500;
    }

    .message.success {
        background-color: #d4edda;
        color: #155724;
    }

    .message.error {
        background-color: #f8d7da;
        color: #721c24;
    }

    .field-error {
        color: #dc3545;
        font-size: 12px;
        margin-top: 5px;
    }

    .required {
        color: #dc3545;
    }

    .face-capture-container video {
        width: 100%;
        max-width: 400px;
        border-radius: 8px;
        background: #000;
        display: block;
        margin: 0 auto;
    }

    #face-preview-thumbnail img {
        max-width: 150px;
        height: 140px;
        border-radius: 8px;
        margin-top: 10px;
        border: 2px solid #28a745;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    @media screen and (max-width: 768px) {
        .pm-gym-face-enrollment-form {
            padding: 20px;
        }
    }
</style>

<script>
    jQuery(document).ready(function($) {
        let verifiedMemberId = null;
        let verifiedMemberDbId = null;
        let faceModelsLoaded = false;
        let videoStream = null;
        let faceDescriptor = null;
        let faceDetectionInterval = null;

        // Check if face-api.js is loaded
        function checkFaceApiLoaded() {
            if (typeof faceapi === 'undefined') {
                $('#face-error').html('Face recognition library is loading... Please wait a moment and try again.');
                return false;
            }
            return true;
        }

        // Load face-api.js models
        async function loadFaceModels() {
            if (faceModelsLoaded) return true;

            if (!checkFaceApiLoaded()) {
                setTimeout(() => {
                    if (checkFaceApiLoaded()) {
                        loadFaceModels();
                    } else {
                        $('#face-error').html('Face recognition library failed to load. Please refresh the page.');
                    }
                }, 1000);
                return false;
            }

            try {
                $('#face-status').text('Loading face recognition models...');

                const MODEL_URL = 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model/';

                await Promise.all([
                    faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
                    faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
                    faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL)
                ]);

                faceModelsLoaded = true;
                $('#face-status').text('Models loaded. Ready to capture.');
                $('#face-error').text('');
                return true;
            } catch (error) {
                console.error('Error loading face models:', error);
                $('#face-error').text('Failed to load face recognition models. Please refresh the page.');
                return false;
            }
        }

        // Get user media with fallback support
        function getUserMedia(constraints) {
            if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                return navigator.mediaDevices.getUserMedia(constraints);
            } else if (navigator.getUserMedia) {
                return new Promise(function(resolve, reject) {
                    navigator.getUserMedia(constraints, resolve, reject);
                });
            } else if (navigator.webkitGetUserMedia) {
                return new Promise(function(resolve, reject) {
                    navigator.webkitGetUserMedia(constraints, resolve, reject);
                });
            } else if (navigator.mozGetUserMedia) {
                return new Promise(function(resolve, reject) {
                    navigator.mozGetUserMedia(constraints, resolve, reject);
                });
            } else {
                return Promise.reject(new Error('getUserMedia is not supported'));
            }
        }

        // Verify Member ID
        $('#verify-member-btn').on('click', function() {
            const memberId = $('#enrollment_member_id').val().trim();

            if (!memberId || !memberId.match(/^\d{4,5}$/)) {
                $('#member-verification-message').removeClass('success').addClass('error').text('Please enter a valid 4-5 digit Member ID.');
                return;
            }

            $('#verify-member-btn').prop('disabled', true).text('Verifying...');
            $('#member-verification-message').removeClass('success error').text('');

            $.ajax({
                type: 'POST',
                url: pm_gym_ajax.ajax_url,
                data: {
                    action: 'verify_member_for_face_enrollment',
                    member_id: memberId
                },
                success: function(response) {
                    if (response.success) {
                        verifiedMemberId = memberId;
                        verifiedMemberDbId = response.data.db_id;

                        // Show member info
                        $('#member-name-display').text(response.data.name);
                        $('#member-status-display').html('<strong>Status:</strong> ' + response.data.status);

                        if (response.data.has_face) {
                            $('#face-enrollment-status-display').html('<span style="color: #28a745;">✓ Face already enrolled</span><br><small>You can update your face data below</small>');
                        } else {
                            $('#face-enrollment-status-display').html('<span style="color: #dc3545;">Face not enrolled</span><br><small>Please enroll your face below</small>');
                        }

                        // Show next steps
                        $('#member-verification-step').hide();
                        $('#member-info-step').show();
                        $('#face-capture-step').show();
                        $('#back-to-verification').show();
                        $('#member-verification-message').removeClass('error').addClass('success').text('Member verified successfully!');
                    } else {
                        $('#member-verification-message').removeClass('success').addClass('error').text(response.data || 'Member not found. Please check your Member ID.');
                    }
                },
                error: function() {
                    $('#member-verification-message').removeClass('success').addClass('error').text('An error occurred. Please try again.');
                },
                complete: function() {
                    $('#verify-member-btn').prop('disabled', false).text('Verify Member');
                }
            });
        });

        // Start camera for face capture
        $('#start-face-capture').on('click', async function() {
            if (!faceModelsLoaded) {
                const loaded = await loadFaceModels();
                if (!loaded) {
                    $('#face-error').text('Failed to load face recognition. Please refresh the page.');
                    return;
                }
            }

            const isSecureContext = window.location.protocol === 'https:' ||
                window.location.hostname === 'localhost' ||
                window.location.hostname === '127.0.0.1' ||
                window.location.hostname.includes('.local');

            if (!isSecureContext) {
                $('#face-error').html('Camera access requires HTTPS, localhost, or .local domain. Current: ' + window.location.protocol + '://' + window.location.hostname);
                return;
            }

            const hasGetUserMedia = !!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia) ||
                !!(navigator.getUserMedia) ||
                !!(navigator.webkitGetUserMedia) ||
                !!(navigator.mozGetUserMedia);

            if (!hasGetUserMedia) {
                $('#face-error').text('Camera access is not supported in this browser.');
                return;
            }

            try {
                const stream = await getUserMedia({
                    video: {
                        width: 640,
                        height: 480,
                        facingMode: 'user'
                    }
                });

                videoStream = stream;
                const video = document.getElementById('face-video');
                video.srcObject = stream;

                $('#face-preview-container').show();
                $('#start-face-capture').hide();
                $('#capture-face').show();
                $('#face-status').text('Position your face in the frame');
                $('#face-error').text('');

                detectFaceInVideo();
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

                $('#face-error').text(errorMessage);
                $('#face-preview-container').hide();
                $('#start-face-capture').show();
            }
        });

        // Detect face in video stream
        async function detectFaceInVideo() {
            const video = document.getElementById('face-video');
            const canvas = document.getElementById('face-canvas');
            const $status = $('#face-status');

            if (!video || !canvas) return;

            if (faceDetectionInterval) {
                clearInterval(faceDetectionInterval);
            }

            const displaySize = {
                width: video.videoWidth || 640,
                height: video.videoHeight || 480
            };
            faceapi.matchDimensions(canvas, displaySize);

            faceDetectionInterval = setInterval(async () => {
                if (!videoStream || faceDescriptor) {
                    if (faceDetectionInterval) {
                        clearInterval(faceDetectionInterval);
                        faceDetectionInterval = null;
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
                            $status.text('Face detected! Click "Capture Face" to save.');
                            $status.css('color', '#28a745');
                        } else {
                            $status.text('No face detected. Please position your face in the frame.');
                            $status.css('color', '#dc3545');
                        }
                    } catch (error) {
                        console.error('Face detection error:', error);
                    }
                }
            }, 500);
        }

        // Capture face descriptor
        $('#capture-face').on('click', async function() {
            const video = document.getElementById('face-video');
            const canvas = document.getElementById('face-canvas');

            try {
                const detections = await faceapi
                    .detectAllFaces(video, new faceapi.TinyFaceDetectorOptions())
                    .withFaceLandmarks()
                    .withFaceDescriptors();

                if (detections.length === 0) {
                    $('#face-error').text('No face detected. Please try again.');
                    return;
                }

                if (detections.length > 1) {
                    $('#face-error').text('Multiple faces detected. Please ensure only one person is in frame.');
                    return;
                }

                faceDescriptor = detections[0].descriptor;
                const descriptorArray = Array.from(faceDescriptor);
                const descriptorJson = JSON.stringify(descriptorArray);

                if (descriptorArray.length !== 128) {
                    $('#face-error').text('Error: Invalid face descriptor. Please try again.');
                    return;
                }

                $('#face-descriptor-input').val(descriptorJson);

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
                $('#face-thumbnail').attr('src', thumbnail);
                $('#face-preview-thumbnail').show();

                if (videoStream) {
                    videoStream.getTracks().forEach(track => track.stop());
                    videoStream = null;
                }

                $('#face-preview-container').hide();
                $('#capture-face').hide();
                $('#retake-face').show();
                $('#save-face-enrollment').show();
                $('#face-status').text('Face captured successfully!');
                $('#face-error').text('');
            } catch (error) {
                console.error('Error capturing face:', error);
                $('#face-error').text('Error capturing face. Please try again.');
            }
        });

        // Retake face
        $('#retake-face').on('click', function() {
            faceDescriptor = null;
            $('#face-descriptor-input').val('');
            $('#face-preview-thumbnail').hide();
            $('#retake-face').hide();
            $('#save-face-enrollment').hide();
            $('#start-face-capture').show();
            $('#face-status').text('');
        });

        // Save face enrollment
        $('#save-face-enrollment').on('click', function() {
            const faceDescriptorValue = $('#face-descriptor-input').val();

            if (!faceDescriptorValue) {
                $('#face-enrollment-message').removeClass('success').addClass('error').text('Please capture your face first.');
                return;
            }

            if (!verifiedMemberId || !verifiedMemberDbId) {
                $('#face-enrollment-message').removeClass('success').addClass('error').text('Please verify your Member ID first.');
                return;
            }

            $('#save-face-enrollment').prop('disabled', true).text('Saving...');
            $('#face-enrollment-message').removeClass('success error').text('Saving face enrollment...');

            $.ajax({
                type: 'POST',
                url: pm_gym_ajax.ajax_url,
                data: {
                    action: 'update_face_descriptor_frontend',
                    member_id: verifiedMemberId,
                    face_descriptor: faceDescriptorValue
                },
                success: function(response) {
                    if (response.success) {
                        $('#face-enrollment-message').removeClass('error').addClass('success').text('✓ Face enrollment saved successfully!');
                        $('#save-face-enrollment').hide();
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else {
                        $('#face-enrollment-message').removeClass('success').addClass('error').text(response.data || 'Error saving face enrollment. Please try again.');
                    }
                },
                error: function() {
                    $('#face-enrollment-message').removeClass('success').addClass('error').text('An error occurred. Please try again.');
                },
                complete: function() {
                    $('#save-face-enrollment').prop('disabled', false).text('Save Face Enrollment');
                }
            });
        });

        // Reset form
        $('#reset-form-btn').on('click', function() {
            verifiedMemberId = null;
            verifiedMemberDbId = null;
            faceDescriptor = null;
            $('#enrollment_member_id').val('');
            $('#face-descriptor-input').val('');
            $('#member-verification-step').show();
            $('#member-info-step').hide();
            $('#face-capture-step').hide();
            $('#back-to-verification').hide();
            $('#member-verification-message').removeClass('success error').text('');
            $('#face-enrollment-message').removeClass('success error').text('');
            $('#face-preview-thumbnail').hide();
            $('#start-face-capture').show();
            $('#capture-face').hide();
            $('#retake-face').hide();
            $('#save-face-enrollment').hide();

            if (videoStream) {
                videoStream.getTracks().forEach(track => track.stop());
                videoStream = null;
            }
        });

        // Cleanup
        $(window).on('beforeunload', function() {
            if (videoStream) {
                videoStream.getTracks().forEach(track => track.stop());
            }
            if (faceDetectionInterval) {
                clearInterval(faceDetectionInterval);
            }
        });
    });
</script>