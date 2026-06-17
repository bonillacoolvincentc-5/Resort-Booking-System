# YP4K Resort Authentication System

A complete authentication system for YP4K Resort that handles user and admin registration, login, email verification, and password reset functionality.

## Features

- **Dual Registration System**: Separate registration for users and admins
- **Email Verification**: OTP-based email verification system
- **Secure Login**: Role-based authentication with session management
- **Password Reset**: Secure password reset via email links
- **Security Features**: Rate limiting, CSRF protection, session timeout
- **Audit Logging**: Complete audit trail for security events

## Database Setup

1. Import the database structure using `setup_database_updated.sql` in phpMyAdmin
2. The database includes:
   - `users` table for regular users
   - `admins` table for administrators
   - `otp_verifications` table for email verification
   - `password_resets` table for password reset tokens
   - `login_logs` table for security auditing
   - Additional security and monitoring tables

## File Structure

```
phpAuthentication/
├── db_config.php                    # Database configuration
├── email_helper.php                  # PHPMailer email functions
├── session_helper_new.php            # Security and session utilities
├── setup_database_updated.sql        # Database structure
├── register_process_new.php          # Registration handler
├── verify_otp_new.php               # OTP verification handler
├── login_process_new.php            # Login handler
├── forgot_password_process_new.php  # Forgot password handler
├── reset_password_process_new.php   # Password reset handler
├── logout_new.php                   # Logout handler
└── README.md                        # This file
```

## Configuration

### Database Configuration

Edit `db_config.php` with your database credentials:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'your_db_username');
define('DB_PASS', 'your_db_password');
define('DB_NAME', 'yp4k_resort_db');
```

### Email Configuration

Edit `email_helper.php` with your email settings:

```php
private static $emailConfig = [
    'host' => 'smtp.gmail.com',           // Your SMTP server
    'username' => 'your-email@gmail.com', // Your email address
    'password' => 'your-app-password',    // Your app password
    'from_email' => 'your-email@gmail.com', // From email address
    'from_name' => 'YP4K Resort',         // From name
    'port' => 587,                        // SMTP port
    'encryption' => 'tls'                 // 'tls' or 'ssl'
];
```

**Important for Gmail Users:**
1. Enable 2-factor authentication on your Gmail account
2. Generate an App Password: https://myaccount.google.com/apppasswords
3. Use the App Password (not your regular password) in the configuration

## API Endpoints

### Registration
- **URL**: `phpAuthentication/register_process_new.php`
- **Method**: POST
- **Fields**:
  - `first_name` (required)
  - `middle_name` (optional)
  - `last_name` (required)
  - `suffix` (optional)
  - `email` (required)
  - `phone_number` (required, format: 09XXXXXXXXX)
  - `password` (required, min 8 chars, 1 uppercase, 1 lowercase, 1 number)
  - `confirm_password` (required)
  - `user_type` (required: 'user' or 'admin')
  - `region` (required for users)
  - `province` (required for users)
  - `city_municipality` (required for users)
  - `barangay` (required for users)
  - `agree_terms` (required)

### OTP Verification
- **URL**: `phpAuthentication/verify_otp_new.php`
- **Method**: POST
- **Fields**:
  - `email` (required)
  - `otp` (required, 6-digit code)

### Login
- **URL**: `phpAuthentication/login_process_new.php`
- **Method**: POST
- **Fields**:
  - `email` (required)
  - `password` (required)
  - `remember` (optional)

### Forgot Password
- **URL**: `phpAuthentication/forgot_password_process_new.php`
- **Method**: POST
- **Fields**:
  - `email` (required)

### Reset Password
- **URL**: `phpAuthentication/reset_password_process_new.php`
- **Method**: POST
- **Fields**:
  - `token` (required, from reset link)
  - `password` (required)
  - `confirm_password` (required)

### Logout
- **URL**: `phpAuthentication/logout_new.php`
- **Method**: GET

## Security Features

### Password Security
- Passwords are hashed using `PASSWORD_DEFAULT` (bcrypt)
- Strong password requirements enforced
- Secure password reset with time-limited tokens

### Session Security
- Session timeout after 30 minutes of inactivity
- Secure session configuration
- Remember me functionality with secure tokens

### Rate Limiting
- Login attempts limited to prevent brute force attacks
- OTP verification attempts limited
- Password reset attempts monitored

### CSRF Protection
- CSRF tokens generated and validated
- All forms should include CSRF token

### Input Validation
- All inputs are sanitized and validated
- SQL injection prevention with prepared statements
- XSS prevention with output encoding

## Integration with Frontend

### JavaScript Integration

All endpoints return JSON responses. Example integration:

```javascript
// Registration
async function register(formData) {
    try {
        const response = await fetch('phpAuthentication/register_process_new.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        
        if (result.success) {
            // Redirect to OTP verification
            window.location.href = 'verify-otp.html?email=' + encodeURIComponent(formData.get('email'));
        } else {
            // Show error message
            showError(result.message);
        }
    } catch (error) {
        showError('Registration failed. Please try again.');
    }
}

// Login
async function login(formData) {
    try {
        const response = await fetch('phpAuthentication/login_process_new.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        
        if (result.success) {
            // Redirect based on user type
            window.location.href = result.redirect;
        } else {
            // Show error message
            showError(result.message);
        }
    } catch (error) {
        showError('Login failed. Please try again.');
    }
}
```

### Form Requirements

Your HTML forms should:

1. Include all required fields as specified in the API endpoints
2. Use proper input types (email, password, tel)
3. Include client-side validation
4. Handle AJAX responses appropriately
5. Show loading states during form submission

## Testing

### Email Testing
To test email functionality without sending real emails, you can temporarily modify the `email_helper.php` to log emails instead of sending them.

### Database Testing
Use the provided SQL file to set up a test database with sample data.

## Troubleshooting

### Common Issues

1. **Email not sending**: Check SMTP credentials and ensure you're using an App Password for Gmail
2. **Database connection failed**: Verify database credentials in `db_config.php`
3. **OTP verification failing**: Ensure server time is synchronized and OTP expiry is working
4. **Session issues**: Check PHP session configuration and ensure cookies are enabled

### Error Logging
All errors are logged to PHP error log. Check your server's error logs for detailed debugging information.

## Security Considerations

1. **Change default credentials**: Always change default database and email credentials
2. **HTTPS**: Use HTTPS in production to protect sensitive data
3. **File permissions**: Ensure proper file permissions on PHP files
4. **Regular updates**: Keep PHP and dependencies updated
5. **Monitoring**: Regularly check security logs for suspicious activity

## Support

For issues or questions regarding the authentication system, please check:
1. PHP error logs
2. Database connection status
3. Email server configuration
4. Server time and timezone settings

## License

This authentication system is part of the YP4K Resort project. Use according to your project's licensing requirements.
