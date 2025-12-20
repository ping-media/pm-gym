# PM Gym Management

A comprehensive gym management system for WordPress with member management, staff management, attendance tracking with face recognition, fee management, and digital signature capabilities.

## Description

PM Gym Management is a powerful WordPress plugin designed to help gym owners efficiently manage their business operations. This plugin provides a complete solution for managing gym members and staff, tracking attendance with face recognition technology and shift-based systems, handling membership packages, processing payments, capturing digital signatures, and generating comprehensive reports through CSV exports.

## Features

- **Member Management**

  - Store and manage member profiles with complete information
  - Track membership status and expiration dates
  - Manage member meta data and preferences
  - Digital signature capture and storage for member registration

- **Staff Management**

  - Complete staff profile management system
  - Role-based access control for different staff positions
  - Staff attendance tracking with shift-based validation
  - Morning and evening shift support with time restrictions

- **Guest User Management**

  - Register and track guest users
  - Guest attendance monitoring

- **Membership Management**

  - Create and manage different membership plans
  - Handle membership renewals and expirations
  - Package assignment and customization

- **Attendance Tracking**

  - Record and monitor member attendance
  - Staff attendance tracking with shift validation
  - Generate comprehensive attendance reports
  - Real-time attendance status monitoring

- **Payment Processing**

  - Record and track payment transactions
  - Generate payment receipts and invoices
  - Payment history and reporting

- **Package Management**

  - Create and customize gym membership packages
  - Assign packages to members
  - Package pricing and duration management

- **Digital Signature System**

  - Capture digital signatures during member registration
  - Signature storage with metadata (timestamp, IP, phone)
  - Signature display shortcode for frontend integration
  - Multi-level JSON encoding support for signature data

- **Face Recognition & Enrollment**

  - Real-time face recognition for attendance check-in/check-out
  - Automatic member identification using face matching technology
  - Face enrollment during member registration process
  - Dedicated face enrollment form for existing members
  - Admin face enrollment management from member details page
  - Face descriptor storage using 128-dimensional vectors
  - Face data validation and caching for improved performance
  - Configurable face matching threshold for accuracy control
  - Secure camera access with HTTPS/localhost requirements
  - Real-time face detection and matching using machine learning models

- **Data Export & Reporting**

  - CSV export functionality for all data types
  - Export members, staff, and attendance data
  - Automatic export directory creation
  - Downloadable reports with proper formatting

- **Shortcodes & Frontend Integration**

  - Member registration form shortcode
  - Attendance tracking form shortcode with face recognition support
  - Staff attendance form shortcode
  - Member signature display shortcode
  - Face enrollment form shortcode
  - Responsive design for mobile compatibility

- **Security & Validation**
  - Enhanced data validation for all forms
  - Shift-based attendance validation
  - Role-based access control
  - Secure data handling and storage

## Installation

1. Download the plugin zip file
2. Go to WordPress Dashboard > Plugins > Add New
3. Click on "Upload Plugin" and select the downloaded zip file
4. Click "Install Now" and then "Activate Plugin"

## Requirements

- WordPress 5.0 or higher
- PHP 7.2 or higher
- MySQL 5.6 or higher
- HTTPS connection, localhost, or .local domain (required for face recognition camera access)
- Modern web browser with camera support (for face recognition features)

## Usage

After activation, the plugin will create necessary database tables and set up the required pages automatically.

You can access the plugin settings from the WordPress dashboard under "PM Gym" menu.

## Shortcodes

The plugin provides various shortcodes that you can use to display gym management features on your website:

- `[attendance_form_shortcode]` - Displays the member attendance tracking form with face recognition support
- `[member_registration_form_shortcode]` - Displays the member registration form with digital signature and face enrollment
- `[staff_attendance_form_shortcode]` - Displays the staff attendance tracking form
- `[member_signature member_id="123"]` - Displays a member's digital signature (replace 123 with actual member ID)
- `[face_enrollment_form]` - Displays the face enrollment form for members to register or update their face recognition data

These shortcodes can be used on any page or post to integrate gym management functionality into your website's frontend.

## Support

For support requests or bug reports, please contact:

- Website: [https://wpexpertdeep.com/pm-gym](https://wpexpertdeep.com/pm-gym)
- Author: Deep Goyal
- Author Website: [https://wpexpertdeep.com](https://wpexpertdeep.com)

## License

This plugin is licensed under the GPL v2 or later.

## Changelog

### 1.3.0

- Added comprehensive face recognition system for attendance tracking
- Implemented real-time face scanning during attendance check-in/check-out
- Added automatic member identification using face matching technology
- Integrated "Scan Face" button in attendance form for quick member recognition
- Created dedicated face enrollment form with shortcode `[face_enrollment_form]`
- Added face enrollment capability during member registration process
- Implemented admin face enrollment management from member details page
- Integrated face-api.js library (@vladmandic/face-api v0.22.2) for face recognition
- Added machine learning models: TinyFaceDetector, FaceLandmark68Net, and FaceRecognitionNet
- Implemented face descriptor storage using 128-dimensional vectors in member meta
- Added face data validation and caching system for improved performance
- Implemented configurable face matching threshold (default: 0.6, configurable via PM_GYM_FACE_MATCH_THRESHOLD constant)
- Added secure camera access validation (requires HTTPS, localhost, or .local domain)
- Enhanced attendance form with face recognition fallback to manual member ID entry
- Added face enrollment status indicators and member verification system
- Implemented real-time face detection with visual feedback during scanning
- Added face descriptor update and deletion functionality for admin users
- Enhanced member registration form with optional face enrollment during signup
- Improved user experience with face capture preview and retake functionality
- Added comprehensive error handling for camera access and face recognition failures
- Implemented face matching algorithm with distance calculation for member identification
- Added support for multiple face enrollment methods (registration, dedicated form, admin panel)

### 1.2.5

- Added dedicated Guest Attendance admin page for managing guest attendance records
- Implemented comprehensive guest attendance filtering by time period (Today, Last 7 Days, Last 30 Days, All Time)
- Added date selection filter for viewing attendance on specific dates
- Implemented search functionality to filter guests by name or phone number
- Added guest attendance statistics showing total attendance, active guests, and checked-out guests
- Enhanced guest attendance table with check-in/check-out times, duration, and status indicators
- Implemented check-out functionality for guests directly from attendance page
- Added delete functionality for guest attendance records
- Enhanced CSV export functionality to support guest-only attendance exports
- Improved export function with user type filtering and proper date handling
- Added reset filters button for easy filter clearing
- Implemented auto-refresh functionality for real-time attendance updates
- Enhanced attendance search functionality to support multi-field search (Member ID, Phone Number, and Name)
- Updated search field UI with improved label and placeholder text
- Implemented comprehensive search across member and guest records for better attendance filtering

### 1.2.4

- Enhanced member attendance form to display member expiry date and days remaining
- Updated staff attendance form evening shift time from 4 PM to 3 PM
- Created comprehensive member detail template showing all member information and complete attendance history
- Added member detail page with grid layout displaying member ID, name, contact info, membership details, and status
- Implemented attendance history section with detailed check-in/check-out times and duration calculations
- Added attendance summary showing total records and last visit information
- Enhanced member detail view with status badges and attendance type indicators
- Improved member detail styling with responsive grid layout and professional design
- Added go-back functionality for better navigation in member detail pages

### 1.2.3

- Added automatic member expiry functionality with daily cron job
- Implemented daily scheduled task to check and update expired member statuses
- Added member expiry callback function with comprehensive logging
- Enhanced member status management with automatic expiry detection
- Improved database efficiency with batch member status updates
- Added cron event scheduling and cleanup on plugin activation/deactivation
- Enhanced error logging for member expiry operations

### 1.2.2

- Added staff attendance tracking functionality with morning/evening shift support
- Implemented staff management system with role-based access
- Enhanced admin interface with staff attendance display and management
- Added staff attendance form for public access with shift validation
- Improved database structure for staff management with shift tracking
- Added staff attendance shortcodes for frontend integration
- Implemented CSV export functionality for staff attendance data
- Added digital signature capture and display system for member registration
- Enhanced member signature shortcode with metadata support
- Added comprehensive data export features (members, staff, attendance)
- Implemented shift-based attendance validation (morning before 4 PM, evening after 4 PM)
- Enhanced security and data validation for all forms
- Added automatic export directory creation for CSV downloads
- Bug fixes and performance improvements

### 1.0.0

- Initial release
