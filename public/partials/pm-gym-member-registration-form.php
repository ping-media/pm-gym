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
            <div class="col-6">
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
            <div class="col-12">
                <div class="form-field">
                    <label for="face-capture">Face Recognition (Optional)</label>

                    <div class="face-capture-container">
                        <div id="face-preview-container" style="display: none;">
                            <video id="face-video" autoplay playsinline style="width: 100%; max-width: 400px; border-radius: 8px; background: #000; max-height: 200px;"></video>
                            <canvas id="face-canvas" style="display: none;"></canvas>
                            <div id="face-status" style="margin-top: 10px; text-align: center; font-weight: 500;"></div>
                        </div>
                        <div id="face-capture-controls">
                            <button type="button" id="start-face-capture" class="button button-secondary">Start Camera</button>
                            <button type="button" id="capture-face" class="button button-primary" style="display: none;">Capture Face</button>
                            <button type="button" id="retake-face" class="button button-secondary" style="display: none;">Retake</button>
                            <p style="font-size: 12px; color: #666; margin-bottom: 10px;">Capture your face to enable quick check-in using face scanning</p>
                        </div>
                        <div id="face-preview-thumbnail" style="display: none; margin-top: 15px; text-align: center;">
                            <p style="font-weight: 500; color: #28a745; margin-bottom: 0px;">✓ Face captured successfully</p>
                            <img id="face-thumbnail" src="" alt="Captured face" style="max-width: 150px; height: 140px; border-radius: 8px; margin-top: 10px; border: 2px solid #28a745;">
                        </div>
                        <input type="hidden" name="face_descriptor" id="face-descriptor-input">
                        <div id="face-error" class="field-error"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-submit">
            <button type="submit" class="button button-primary">Save Member</button>
        </div>
    </form>
    <div style="text-align: center; margin-top: 25px;">
        <a href="/" class="button button-secondary" style="font-weight:600; padding:10px 32px; border-radius:7px; text-decoration:none;">
            Back to mark attendance
        </a>
    </div>
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

                // Verify face descriptor is included if it was captured
                var faceDescriptorValue = $('#face-descriptor-input').val();
                if (faceDescriptorValue) {
                    console.log('Face descriptor will be submitted:', faceDescriptorValue.length, 'characters');
                } else {
                    console.log('No face descriptor to submit (optional)');
                }

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
                            // Show confirmation message below the Register button
                            if ($('#registration-success-message').length === 0) {
                                $('<div id="registration-success-message" style="margin-top:15px;color:#28a745;font-weight:600;">Member registered successfully!</div>')
                                    .insertAfter('#member-form button[type="submit"]');
                            } else {
                                $('#registration-success-message').text('Member registered successfully!').show();
                            }
                            // Reset form and clear pad just in case
                            $('#member-form')[0].reset();
                            signaturePad.clear();
                            $('#signature-input').val('');
                            // Reload page after short delay to show alert
                            setTimeout(function() {
                                window.location.reload();
                            }, 1500);
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

        // Face Recognition Setup
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

        // Get user media with fallback support for different browsers
        function getUserMedia(constraints) {
            // Modern browsers
            if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                return navigator.mediaDevices.getUserMedia(constraints);
            }
            // Fallback for older browsers
            else if (navigator.getUserMedia) {
                return new Promise(function(resolve, reject) {
                    navigator.getUserMedia(constraints, resolve, reject);
                });
            }
            // Fallback for webkit browsers
            else if (navigator.webkitGetUserMedia) {
                return new Promise(function(resolve, reject) {
                    navigator.webkitGetUserMedia(constraints, resolve, reject);
                });
            }
            // Fallback for mozilla browsers
            else if (navigator.mozGetUserMedia) {
                return new Promise(function(resolve, reject) {
                    navigator.mozGetUserMedia(constraints, resolve, reject);
                });
            } else {
                return Promise.reject(new Error('getUserMedia is not supported'));
            }
        }

        // Load face-api.js models
        async function loadFaceModels() {
            if (faceModelsLoaded) return true;

            // Check if face-api is available
            if (!checkFaceApiLoaded()) {
                // Wait a bit and try again
                setTimeout(() => {
                    if (checkFaceApiLoaded()) {
                        loadFaceModels();
                    } else {
                        $('#face-error').html('Face recognition library failed to load. Please refresh the page or use Member ID for attendance.');
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

        // Start camera for face capture
        $('#start-face-capture').on('click', async function() {
            if (!faceModelsLoaded) {
                const loaded = await loadFaceModels();
                if (!loaded) {
                    $('#face-error').text('Failed to load face recognition. Face capture is optional - you can skip this step.');
                    return;
                }
            }

            // Check if we're in a secure context (HTTPS or localhost)
            const isSecureContext = window.location.protocol === 'https:' ||
                window.location.hostname === 'localhost' ||
                window.location.hostname === '127.0.0.1' ||
                window.location.hostname.includes('.local');

            if (!isSecureContext) {
                $('#face-error').html('Camera access requires HTTPS, localhost, or .local domain. Current: ' + window.location.protocol + '://' + window.location.hostname + '. Please use Member ID for attendance, or contact your administrator to enable HTTPS.');
                console.warn('Camera access blocked: Not in secure context. Protocol:', window.location.protocol, 'Hostname:', window.location.hostname);
                return;
            }

            // Check if getUserMedia is supported (with fallbacks)
            const hasGetUserMedia = !!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia) ||
                !!(navigator.getUserMedia) ||
                !!(navigator.webkitGetUserMedia) ||
                !!(navigator.mozGetUserMedia);

            if (!hasGetUserMedia) {
                console.warn('getUserMedia not available:', {
                    mediaDevices: !!navigator.mediaDevices,
                    getUserMedia: !!navigator.getUserMedia,
                    webkitGetUserMedia: !!navigator.webkitGetUserMedia,
                    mozGetUserMedia: !!navigator.mozGetUserMedia
                });
                $('#face-error').text('Camera access is not supported in this browser. Please use Member ID for attendance.');
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

                // Start continuous face detection
                detectFaceInVideo();
            } catch (error) {
                console.error('Error accessing camera:', error);
                let errorMessage = 'Could not access camera. ';

                if (error.name === 'NotAllowedError' || error.name === 'PermissionDeniedError') {
                    errorMessage += 'Please allow camera access and try again.';
                } else if (error.name === 'NotFoundError' || error.name === 'DevicesNotFoundError') {
                    errorMessage += 'No camera found. Please use Member ID for attendance.';
                } else if (error.name === 'NotReadableError' || error.name === 'TrackStartError') {
                    errorMessage += 'Camera is being used by another application.';
                } else {
                    errorMessage += 'Please check camera permissions or use Member ID for attendance.';
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
            const $status = $('#face-status'); // Use jQuery

            if (!video || !canvas) return;

            // Clear any existing interval
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
                        // Continue trying, don't show error to user
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

                // Get the face descriptor (128-dimensional array)
                faceDescriptor = detections[0].descriptor;

                // Convert to array and store descriptor in hidden input
                const descriptorArray = Array.from(faceDescriptor);
                const descriptorJson = JSON.stringify(descriptorArray);

                // Validate descriptor before storing
                if (descriptorArray.length !== 128) {
                    $('#face-error').text('Error: Invalid face descriptor. Please try again.');
                    console.error('Invalid descriptor length:', descriptorArray.length);
                    return;
                }

                // Store descriptor in hidden input
                $('#face-descriptor-input').val(descriptorJson);
                console.log('Face descriptor saved:', descriptorArray.length, 'dimensions');

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

                // Draw face detection box
                resizedDetections.forEach(detection => {
                    const box = detection.detection.box;
                    ctx.strokeStyle = '#28a745';
                    ctx.lineWidth = 2;
                    ctx.strokeRect(box.x, box.y, box.width, box.height);
                });

                // Show thumbnail
                const thumbnail = canvas.toDataURL('image/jpeg', 0.8);
                $('#face-thumbnail').attr('src', thumbnail);
                $('#face-preview-thumbnail').show();

                // Stop video stream
                if (videoStream) {
                    videoStream.getTracks().forEach(track => track.stop());
                    videoStream = null;
                }

                $('#face-preview-container').hide();
                $('#capture-face').hide();
                $('#retake-face').show();
                $('#face-status').text('Face captured successfully!');
                $('#face-error').text('');

                // Verify the descriptor was saved
                const savedDescriptor = $('#face-descriptor-input').val();
                if (savedDescriptor) {
                    console.log('✓ Face descriptor saved successfully');
                } else {
                    console.error('✗ Face descriptor not saved!');
                    $('#face-error').text('Warning: Face descriptor may not have been saved. Please try again.');
                }
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
            $('#start-face-capture').show();
            $('#face-status').text('');
        });

        // Cleanup on form submit or page unload
        $(window).on('beforeunload', function() {
            if (videoStream) {
                videoStream.getTracks().forEach(track => track.stop());
            }
            if (faceDetectionInterval) {
                clearInterval(faceDetectionInterval);
            }
        });

        // Cleanup when form is submitted
        $('#member-form').on('submit', function() {
            if (videoStream) {
                videoStream.getTracks().forEach(track => track.stop());
            }
            if (faceDetectionInterval) {
                clearInterval(faceDetectionInterval);
            }
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

    @media(max-width: 768px) {
        .signature-pad-container {
            width: 100%;
        }
    }
</style>