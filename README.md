# PM Gym Management

A comprehensive gym management system for WordPress with member management, staff management, attendance tracking, fee management, and digital signature capabilities.

## Description

PM Gym Management is a powerful WordPress plugin designed to help gym owners efficiently manage their business operations. This plugin provides a complete solution for managing gym members and staff, tracking attendance with shift-based systems, handling membership packages, processing payments, capturing digital signatures, and generating comprehensive reports through CSV exports.

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

- **Data Export & Reporting**

  - CSV export functionality for all data types
  - Export members, staff, and attendance data
  - Automatic export directory creation
  - Downloadable reports with proper formatting

- **Shortcodes & Frontend Integration**

  - Member registration form shortcode
  - Attendance tracking form shortcode
  - Staff attendance form shortcode
  - Member signature display shortcode
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

## Usage

After activation, the plugin will create necessary database tables and set up the required pages automatically.

You can access the plugin settings from the WordPress dashboard under "PM Gym" menu.

## Shortcodes

The plugin provides various shortcodes that you can use to display gym management features on your website:

- `[attendance_form_shortcode]` - Displays the member attendance tracking form
- `[member_registration_form_shortcode]` - Displays the member registration form with digital signature
- `[staff_attendance_form_shortcode]` - Displays the staff attendance tracking form
- `[member_signature member_id="123"]` - Displays a member's digital signature (replace 123 with actual member ID)

These shortcodes can be used on any page or post to integrate gym management functionality into your website's frontend.

## Support

For support requests or bug reports, please contact:

- Website: [https://wpexpertdeep.com/pm-gym](https://wpexpertdeep.com/pm-gym)
- Author: Deep Goyal
- Author Website: [https://wpexpertdeep.com](https://wpexpertdeep.com)

## License

This plugin is licensed under the GPL v2 or later.

## Changelog

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
