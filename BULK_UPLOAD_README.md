# Bulk Members Upload Feature

## Overview

The bulk upload feature allows administrators to upload multiple gym members at once using a CSV file. This feature is integrated into the PM Gym Management plugin's Members page.

## How to Use

1. **Access the Feature**

   - Go to Gym Management > Members in the WordPress admin
   - Click on "Bulk Upload Members" button next to "Add New Member"

2. **Download Template**

   - Click "Download CSV Template" to get a properly formatted CSV file
   - The template includes sample data showing the correct format

3. **Prepare Your CSV File**
   - Use the exact column order as shown in the template
   - Ensure all required fields are filled
   - Follow the validation rules for each field

## CSV Format

The CSV file must contain the following columns in exact order:

1. **Name** (required) - Member's full name
2. **Phone** (required) - 10-digit phone number (must be unique)
3. **Email** (optional) - Valid email address
4. **Member ID** (required) - Unique 4-digit number (0001-9999)
5. **Membership Type** (required) - Duration in months (1-12)
6. **Aadhar Number** (required) - 12-digit Aadhar card number (must be unique)
7. **Gender** (required) - male, female, or other
8. **Date of Birth** (required) - Format: YYYY-MM-DD
9. **Address** (optional) - Member's address
10. **Status** (optional) - active, inactive, or suspended (default: active)

## Validation Rules

- **Phone Numbers**: Must be exactly 10 digits and unique across all members
- **Member ID**: Must be between 1-9999 and unique
- **Aadhar Numbers**: Must be exactly 12 digits and unique
- **Membership Type**: Must be between 1-12 months
- **Gender**: Must be exactly "male", "female", or "other"
- **Date of Birth**: Must be in YYYY-MM-DD format
- **Status**: Must be "active", "inactive", or "suspended" (defaults to "active" if not specified)

## Features

- **Duplicate Detection**: Automatically detects and skips duplicate phone numbers, member IDs, and Aadhar numbers
- **Data Validation**: Validates all fields according to the rules above
- **Error Reporting**: Shows detailed error messages for each row that fails validation
- **Progress Tracking**: Displays upload progress with a visual progress bar
- **Automatic Expiry Calculation**: Calculates membership expiry dates based on join date and membership type
- **Batch Processing**: Handles large files efficiently

## Error Handling

- Invalid rows are skipped and reported in the results
- Duplicate entries are detected and reported
- Detailed error messages show which rows failed and why
- Upload continues even if some rows fail

## Technical Implementation

### Files Modified

- `admin/partials/pm-gym-members-display.php` - Added UI and JavaScript
- `admin/class-pm-gym-admin.php` - Added server-side processing

### AJAX Handler

- Action: `bulk_upload_members`
- Security: Uses WordPress nonces for security
- Permissions: Requires `manage_options` capability

### Database Integration

- Uses existing `PM_GYM_MEMBERS_TABLE` table
- Maintains data integrity with existing member management system
- Properly formats data according to existing schema

## Security Features

- CSRF protection with WordPress nonces
- File type validation (only CSV files allowed)
- User permission checks
- SQL injection protection with prepared statements
- Input sanitization and validation

## Success/Error Response

The system provides detailed feedback including:

- Number of successfully uploaded members
- Number of errors encountered
- Detailed error messages for failed rows
- Summary statistics

## Future Enhancements

Potential improvements could include:

- Support for updating existing members
- Bulk photo uploads
- Excel file support
- Email notifications after upload
- Backup/restore functionality
