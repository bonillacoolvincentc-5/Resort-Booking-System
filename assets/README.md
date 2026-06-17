# YP4K Resort - Booking & Trail Guide System

## 📋 Project Overview

YP4K Resort is a comprehensive booking and trail guide web application for a luxury resort. It features user authentication, resort booking management, activity scheduling, and a modern, responsive design using Bootstrap 5.

**Version:** 1.0.0  
**Last Updated:** February 25, 2026

---

## 🎯 Features

### 🏠 Home Page
- Professional, modern landing page
- Navigation menu with quick access to all sections
- Hero section with call-to-action buttons
- Activities showcase with images and descriptions
- Contact information
- Responsive design for all devices

### 🔐 Authentication System
- **User Registration**
  - Email validation
  - Password strength requirements (minimum 8 characters)
  - Password confirmation
  - Terms & conditions agreement
  - Account verification (ready to implement email verification)

- **User Login**
  - Email and password authentication
  - "Remember me" functionality with persistent cookies
  - Error handling and validation
  
- **Password Recovery**
  - Forgot password with email verification
  - Secure reset token generation
  - Token expiration (1 hour)
  - Password reset with confirmation

- **Password Toggle**
  - Eye icon to show/hide password
  - Available in login, registration, and reset pages

### 🏨 Resort Features
- **Activities**
  - Billiard hall
  - Kiddie pool
  - Adult pool
  - Fitness gym
  - Adventure rides (zip-lining)
  - Guided nature trails

- **Booking System** (Backend ready)
  - Room booking and management
  - Activity booking
  - Payment processing
  - Reservation history

### 👤 User Dashboard
- Profile management
- Active booking overview
- Activity history
- Quick action buttons
- Support contact

---

## 📁 File Structure

```
System Testing/
├── Index.html                 # Home page
├── login.html                 # Login page
├── register.html              # Registration page
├── forgot-password.html       # Password recovery
├── dashboard.html             # User dashboard
├── css/
│   └── style.css             # Custom styling
├── php/
│   ├── db_config.php         # Database configuration
│   ├── login_process.php     # Login handler
│   ├── register_process.php  # Registration handler
│   ├── forgot_password_process.php
│   ├── reset_password_process.php
│   └── logout.php            # Logout handler
└── Database Setup.sql        # Database structure and sample data
```

---

## 🚀 Getting Started

### Prerequisites
- PHP 7.4 or higher
- MySQL/MariaDB 5.7 or higher
- Web server (Apache, Nginx, etc.) or local development server
- Modern web browser

### Installation Steps

1. **Clone/Download the Project**
   ```bash
   cd "System Testing"
   ```

2. **Create Database**
   - Open phpMyAdmin or MySQL command line
   - Import the `Database Setup.sql` file
   - Or execute the SQL commands manually

3. **Configure Database Connection**
   - Edit `php/db_config.php`
   - Update database credentials:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_USER', 'root');
     define('DB_PASS', '');
     define('DB_NAME', 'yp4k_resort_db');
     ```

4. **Set Up Local Server**
   - If using PHP built-in server:
     ```bash
     php -S localhost:8000
     ```
   - Then visit: `http://localhost:8000`

5. **Test the Application**
   - Visit the home page
   - Create a new account
   - Login with your credentials
   - Explore the dashboard

---

## 🔧 Configuration

### Database Setup
The `Database Setup.sql` file automatically creates:
- **users table** - User accounts and authentication
- **bookings table** - Room and stay reservations
- **activities table** - Available activities
- **activity_bookings table** - Activity reservations
- **rooms table** - Room inventory
- **payments table** - Payment records
- **reviews table** - User reviews and ratings

Sample data is included for testing.

### Email Configuration (For Production)
To enable email verification and password reset:

1. Update the `sendVerificationEmail()` and `sendPasswordResetEmail()` functions in PHP files
2. Configure SMTP settings or use a mail service like:
   - PHPMailer
   - SwiftMailer
   - SendGrid
   - AWS SES

---

## 🎨 Design Features

### Bootstrap 5 Components
- Responsive navbar with mobile menu
- Card-based layouts
- Grid system for responsive design
- Modal dialogs for forms
- Alert messages for user feedback
- Button variants and states

### Custom Styling
- Gradient backgrounds (#667eea to #764ba2)
- Smooth animations and transitions
- Shadow effects for depth
- Modern color scheme
- Accessibility considerations

### Icons
- Bootstrap Icons (v1.11.0)
- Semantic and meaningful icons
- Consistent styling

---

## 💾 Database Schema

### Users Table
```sql
columns: id, firstName, lastName, email, phone, password, 
         verification_token, is_verified, reset_token, 
         reset_token_expiry, remember_token, token_expiry, 
         profile_image, bio, created_at, updated_at
```

### Bookings Table
```sql
columns: id, user_id, check_in_date, check_out_date, 
         number_of_guests, room_type, total_price, status, 
         special_requests, created_at, updated_at
```

### Activities Table
```sql
columns: id, name, description, category, price, 
         max_participants, duration, image_url, created_at
```

---

## 🔒 Security Features

✅ **Implemented:**
- Password hashing with bcrypt (cost: 12)
- SQL injection prevention (prepared statements)
- CSRF protection ready
- Session management
- Secure token generation
- Password strength validation
- Input validation and sanitization
- Remember token encryption

⚠️ **To Implement in Production:**
- HTTPS/SSL encryption
- CSRF tokens
- Rate limiting
- Two-factor authentication
- Content Security Policy headers
- Environment variables for sensitive data
- Regular security audits

---

## 📱 Responsive Design

The application is fully responsive and tested for:
- Desktop (1440px and above)
- Tablets (768px to 1439px)
- Mobile devices (below 768px)
- Ultra-wide displays (2560px and above)

---

## 🧪 Testing

### User Registration Test
1. Click "Register Now" on home page
2. Fill in all required fields
3. Create password (minimum 8 characters)
4. Accept terms and conditions
5. Click "Create Account"

### Login Test
1. Use registered email and password
2. Click "Remember me" to test persistent login
3. Try "Forgot Password" for recovery flow
4. Use eye icon to verify password visibility toggle

### Dashboard Test
1. After successful login, navigate to dashboard
2. View profile section
3. Check quick action buttons
4. Test logout functionality

---

## 🎯 Next Steps / Future Enhancements

- [ ] Email verification system
- [ ] Payment gateway integration (Stripe, PayPal)
- [ ] Advanced search and filtering
- [ ] User review and rating system
- [ ] Real-time booking availability
- [ ] Mobile app development
- [ ] Admin panel for staff
- [ ] Analytics and reporting
- [ ] Multi-language support
- [ ] Video tours of rooms and activities
- [ ] Virtual reality resort preview

---

## 📞 Support

For issues, questions, or feature requests, contact:
- **Email:** support@yp4kresort.com
- **Phone:** +1 (555) 123-4567
- **Address:** 123 Resort Lane, Paradise City

---

## 📄 License

This project is proprietary to YP4K Resort. All rights reserved.

---

## 👨‍💻 Developer Notes

### API Endpoints Ready for Implementation
- `POST /php/login_process.php` - User login
- `POST /php/register_process.php` - User registration
- `POST /php/forgot_password_process.php` - Password recovery
- `POST /php/reset_password_process.php` - Reset password
- `GET /php/logout.php` - User logout

### Code Quality
- Object-oriented and procedural PHP mixed
- Clear variable naming
- Comprehensive error handling
- Input validation at all levels
- Security best practices

### Performance Optimization Tips
- Use database indexes (already created)
- Implement caching for activities and rooms
- Optimize images with responsive image formats
- Minify CSS and JavaScript
- Use CDN for Bootstrap and Bootstrap Icons

---

## 🤝 Contributing

Team members can contribute by:
1. Following the existing code style
2. Adding meaningful comments
3. Testing features thoroughly
4. Updating documentation
5. Reporting bugs with details

---

**Created with ❤️ for YP4K Resort**  
*Bringing luxury and adventure together since 2026*
