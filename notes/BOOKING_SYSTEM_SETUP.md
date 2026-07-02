# YP4K Resort Booking System - Setup Instructions

## Overview
This document provides step-by-step instructions to set up and initialize the new backend booking system for YP4K Resort.

## Prerequisites
- XAMPP/WAMP server running
- MySQL database `yp4k_resort_db` already configured
- Existing authentication system working
- PHP 7.4 or higher

## Files Created

### Database Files
- `database/booking_system.sql` - Complete database schema for booking system
- `database/initialize_booking_system.php` - Script to create all database tables
- `database/seed_activities.php` - Script to populate activities table
- `database/seed_accommodations.php` - Script to populate accommodations table

### Backend PHP Files
- `php/create_booking.php` - Handles booking creation
- `php/upload_receipt.php` - Handles GCash receipt image uploads
- `php/get_activities.php` - API to fetch activities
- `php/get_accommodations.php` - API to fetch accommodations
- `php/check_availability.php` - API to check availability
- `php/get_user_bookings.php` - API to fetch user bookings
- `php/get_all_bookings.php` - API to fetch all bookings (admin)
- `php/get_booking_details.php` - API to fetch booking details
- `php/update_booking_status.php` - API to update booking status (admin)
- `php/cancel_booking.php` - API to cancel booking (user)

### Updated UI Files
- `User_Dashboard/create-booking.php` - Now loads activities from database
- `User_Dashboard/my-bookings.php` - Now displays real bookings from database

## Setup Steps

### Step 1: Initialize Database Tables
Run the initialization script to create all booking-related tables:

```
http://localhost/YP4K_Resort/database/initialize_booking_system.php
```

This will create the following tables:
- `accommodations` - Rooms, cottages, and stay-in accommodations
- `activities` - Resort activities (Billiards, Basketball, Swimming Pool, etc.)
- `booking_status` - Booking status types (pending_review, approved, rejected, completed, cancelled)
- `bookings` - Main booking records
- `payment_receipts` - Uploaded GCash receipt images
- `availability` - Availability tracking
- `booking_logs` - Booking status change history
- `activity_availability_schedule` - Activity availability schedules

### Step 2: Seed Activities
Populate the activities table with resort activities:

```
http://localhost/YP4K_Resort/database/seed_activities.php
```

This will add:
- Billiards - $25/hour
- Basketball Court - $25/hour
- Swimming Pool - $15/hour
- Fitness Gym - $20/hour
- Adventure Rides - $45/hour
- Kids Playground - $10/hour

### Step 3: Seed Accommodations
Populate the accommodations table with resort accommodations:

```
http://localhost/YP4K_Resort/database/seed_accommodations.php
```

This will add:
- Standard Room - $1,500/night (2 guests)
- Deluxe Room - $2,500/night (3 guests)
- Family Suite - $4,500/night (6 guests)
- Small Cottage - $2,000/night (4 guests)
- Large Cottage - $3,500/night (8 guests)
- VIP Stay-In Villa - $8,000/night (10 guests)

### Step 4: Create Uploads Directory
Create the directory for storing uploaded receipt images:

```
mkdir uploads/receipts
```

Or manually create:
- `uploads/` folder in project root
- `receipts/` folder inside uploads

### Step 5: Test the Booking System

#### Test User Booking Flow:
1. Login as a regular user
2. Navigate to User Dashboard → Create Booking
3. Select an activity from the dynamically loaded list
4. Choose date and time
5. Submit the booking
6. Verify booking appears in My Bookings page
7. Check that status is "Pending Review"

#### Test Admin Management Flow:
1. Login as admin
2. Navigate to Admin Dashboard → Bookings
3. View all pending bookings
4. Approve or reject bookings
5. View uploaded receipts
6. Check booking history/logs

## Database Schema Overview

### Key Tables and Relationships

**bookings** - Main booking table
- Links to users (user_id)
- Links to activities (activity_id) OR accommodations (accommodation_id)
- Links to booking_status (status_id)
- Stores booking details, dates, payment info

**booking_status** - Status types
- pending_review (default)
- approved
- rejected
- completed
- cancelled

**availability** - Tracks availability
- Prevents double bookings
- Shows available slots per date/time

**booking_logs** - Audit trail
- Records all status changes
- Tracks who made changes (admin/user/system)

## API Endpoints

### Public/User APIs
- `POST /php/create_booking.php` - Create new booking
- `POST /php/upload_receipt.php` - Upload payment receipt
- `GET /php/get_activities.php` - Get all activities
- `GET /php/get_accommodations.php` - Get all accommodations
- `GET /php/check_availability.php` - Check availability
- `GET /php/get_user_bookings.php` - Get user's bookings
- `GET /php/get_booking_details.php` - Get booking details
- `POST /php/cancel_booking.php` - Cancel booking

### Admin APIs
- `GET /php/get_all_bookings.php` - Get all bookings (with filters)
- `POST /php/update_booking_status.php` - Update booking status

## Features Implemented

### User Features
- ✅ Activity booking with real-time availability checking
- ✅ Accommodation booking (rooms, cottages, stay-in)
- ✅ Automatic price calculation based on duration/days
- ✅ GCash receipt upload for payment verification
- ✅ Booking status tracking (Pending, Approved, Rejected, Completed, Cancelled)
- ✅ View booking history and details
- ✅ Cancel pending bookings
- ✅ Download booking tickets (for approved bookings)

### Admin Features
- ✅ View all bookings with filtering
- ✅ View uploaded payment receipts
- ✅ Approve/reject bookings
- ✅ Mark bookings as completed
- ✅ Cancel bookings
- ✅ View booking history and audit logs
- ✅ Statistics dashboard

### System Features
- ✅ Automatic availability checking
- ✅ Prevents double bookings
- ✅ Secure image upload handling
- ✅ Database-driven data (no more mock data)
- ✅ Foreign key relationships for data integrity
- ✅ Comprehensive error handling

## Important Notes

### UI Preservation
All existing UI designs, layouts, colors, and styling have been preserved exactly as requested. Only the backend functionality has been integrated.

### Authentication
The booking system uses the existing authentication system. Users must be logged in to create bookings. Admins must be logged in to manage bookings.

### Availability System
The availability system automatically:
- Checks for existing bookings before allowing new ones
- Updates available slots when bookings are created
- Restores availability when bookings are cancelled/rejected
- Prevents overbooking

### Payment Receipts
- Uploaded to `uploads/receipts/` directory
- Validated for file type (JPG, PNG, GIF)
- Limited to 5MB max size
- Stored securely with database reference

## Troubleshooting

### Activities not loading
- Ensure database tables are initialized
- Run seed_activities.php script
- Check browser console for JavaScript errors

### Booking creation fails
- Check that user is logged in
- Verify database connection
- Check availability for selected date/time
- Review PHP error logs

### Image upload fails
- Ensure `uploads/receipts/` directory exists
- Check directory permissions (should be writable)
- Verify file size is under 5MB
- Check file type is supported

## Next Steps

After setup is complete:
1. Test the complete booking flow end-to-end
2. Verify admin panel functionality
3. Test availability checking with multiple bookings
4. Verify receipt upload and display
5. Test booking status changes and notifications

## Support

For issues or questions:
1. Check PHP error logs in XAMPP
2. Verify database tables exist and have data
3. Check file permissions for uploads directory
4. Review browser console for JavaScript errors
