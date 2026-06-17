-- YP4K Resort Booking System Database Schema
-- Created: May 20, 2026
-- Description: Complete booking system with accommodations, activities, availability tracking

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- Table: accommodations
-- Stores room, cottage, and stay-in accommodation details
CREATE TABLE IF NOT EXISTS `accommodations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `type` enum('room','cottage','stay-in') NOT NULL,
  `description` text,
  `capacity` int(11) NOT NULL DEFAULT 1,
  `price_per_night` decimal(10,2) NOT NULL,
  `price_per_day` decimal(10,2) NOT NULL,
  `amenities` text,
  `image_url` varchar(500) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_type` (`type`),
  KEY `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: activities
-- Stores activity details (Billiards, Basketball, Swimming Pool, etc.)
CREATE TABLE IF NOT EXISTS `activities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text,
  `price_per_hour` decimal(10,2) NOT NULL,
  `min_duration_hours` decimal(4,2) NOT NULL DEFAULT 1.0,
  `max_duration_hours` decimal(4,2) NOT NULL DEFAULT 4.0,
  `capacity` int(11) NOT NULL DEFAULT 10,
  `icon` varchar(50) DEFAULT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: booking_status
-- Defines booking status types
CREATE TABLE IF NOT EXISTS `booking_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` text,
  `color` varchar(20) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default booking statuses
INSERT INTO `booking_status` (`name`, `description`, `color`) VALUES
('pending_review', 'Booking is pending admin review', '#f59e0b'),
('approved', 'Booking has been approved', '#10b981'),
('rejected', 'Booking has been rejected', '#ef4444'),
('completed', 'Booking has been completed', '#3b82f6'),
('cancelled', 'Booking has been cancelled', '#64748b');

-- Table: bookings
-- Main booking table storing all booking information
CREATE TABLE IF NOT EXISTS `bookings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_reference` varchar(20) NOT NULL,
  `user_id` int(11) NOT NULL,
  `booking_type` enum('activity','accommodation') NOT NULL,
  `activity_id` int(11) DEFAULT NULL,
  `accommodation_id` int(11) DEFAULT NULL,
  `status_id` int(11) NOT NULL DEFAULT 1,
  
  -- Booking details
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone_number` varchar(20) NOT NULL,
  `check_in_date` date NOT NULL,
  `check_in_time` time DEFAULT NULL,
  `check_out_date` date DEFAULT NULL,
  `check_out_time` time DEFAULT NULL,
  `number_of_days` int(11) DEFAULT NULL,
  `number_of_guests` int(11) NOT NULL DEFAULT 1,
  `special_requests` text DEFAULT NULL,
  
  -- Payment details
  `total_amount` decimal(10,2) NOT NULL,
  `payment_status` enum('pending','paid','refunded') DEFAULT 'pending',
  `payment_method` varchar(50) DEFAULT 'gcash',
  
  -- Timestamps
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  
  PRIMARY KEY (`id`),
  UNIQUE KEY `booking_reference` (`booking_reference`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_booking_type` (`booking_type`),
  KEY `idx_activity_id` (`activity_id`),
  KEY `idx_accommodation_id` (`accommodation_id`),
  KEY `idx_status_id` (`status_id`),
  KEY `idx_check_in_date` (`check_in_date`),
  KEY `idx_check_out_date` (`check_out_date`),
  KEY `idx_payment_status` (`payment_status`),
  KEY `idx_created_at` (`created_at`),
  
  CONSTRAINT `fk_bookings_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_bookings_activity` FOREIGN KEY (`activity_id`) REFERENCES `activities` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_bookings_accommodation` FOREIGN KEY (`accommodation_id`) REFERENCES `accommodations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_bookings_status` FOREIGN KEY (`status_id`) REFERENCES `booking_status` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: payment_receipts
-- Stores uploaded payment receipt images
CREATE TABLE IF NOT EXISTS `payment_receipts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_size` bigint(20) NOT NULL,
  `file_type` varchar(50) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_booking_id` (`booking_id`),
  CONSTRAINT `fk_receipts_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: availability
-- Tracks availability of activities and accommodations
CREATE TABLE IF NOT EXISTS `availability` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_type` enum('activity','accommodation') NOT NULL,
  `item_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `time_slot` time DEFAULT NULL,
  `available_slots` int(11) NOT NULL,
  `total_slots` int(11) NOT NULL,
  `is_available` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_availability` (`item_type`, `item_id`, `date`, `time_slot`),
  KEY `idx_date` (`date`),
  KEY `idx_item_type_id` (`item_type`, `item_id`),
  KEY `idx_is_available` (`is_available`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: booking_logs
-- Logs all booking status changes
CREATE TABLE IF NOT EXISTS `booking_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_id` int(11) NOT NULL,
  `old_status_id` int(11) DEFAULT NULL,
  `new_status_id` int(11) NOT NULL,
  `changed_by` enum('user','admin','system') NOT NULL,
  `changed_by_id` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_booking_id` (`booking_id`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `fk_logs_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_logs_old_status` FOREIGN KEY (`old_status_id`) REFERENCES `booking_status` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_logs_new_status` FOREIGN KEY (`new_status_id`) REFERENCES `booking_status` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: activity_availability_schedule
-- Pre-defined availability schedule for activities
CREATE TABLE IF NOT EXISTS `activity_availability_schedule` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `activity_id` int(11) NOT NULL,
  `day_of_week` enum('monday','tuesday','wednesday','thursday','friday','saturday','sunday') NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `max_concurrent_bookings` int(11) NOT NULL DEFAULT 1,
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_activity_id` (`activity_id`),
  KEY `idx_day_of_week` (`day_of_week`),
  CONSTRAINT `fk_schedule_activity` FOREIGN KEY (`activity_id`) REFERENCES `activities` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

COMMIT;
