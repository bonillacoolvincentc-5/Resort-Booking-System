-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 06, 2026 at 04:30 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `yp4k_resort_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `accommodations`
--

CREATE TABLE `accommodations` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` enum('room','cottage','stay-in') NOT NULL,
  `description` text DEFAULT NULL,
  `capacity` int(11) NOT NULL DEFAULT 1,
  `price_per_night` decimal(10,2) NOT NULL,
  `price_per_day` decimal(10,2) NOT NULL,
  `amenities` text DEFAULT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `activities`
--

CREATE TABLE `activities` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price_per_hour` decimal(10,2) NOT NULL,
  `min_duration_hours` decimal(4,2) NOT NULL DEFAULT 1.00,
  `max_duration_hours` decimal(4,2) NOT NULL DEFAULT 4.00,
  `capacity` int(11) NOT NULL DEFAULT 10,
  `image_url` varchar(500) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activities`
--

INSERT INTO `activities` (`id`, `name`, `slug`, `description`, `price_per_hour`, `min_duration_hours`, `max_duration_hours`, `capacity`, `image_url`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Billiards', 'billiards', 'Premium billiards tables with expert coaching available for all skill levels', 25.00, 1.00, 4.00, 8, 'assets/Images/BilliardsHDreal.jpg', 1, '2026-06-18 14:06:52', '2026-06-18 14:06:52'),
(2, 'Basketball Court', 'basketball', 'State-of-the-art indoor and outdoor courts for basketball enthusiasts', 25.00, 1.00, 4.00, 20, 'assets/Images/BasketballCourt.jpg', 1, '2026-06-18 14:06:52', '2026-06-18 14:06:52'),
(3, 'Swimming Pool', 'swimming', 'Olympic-sized pools with crystal clear water and professional lifeguards', 15.00, 1.00, 4.00, 50, 'assets/Images/Pool.jpg', 1, '2026-06-18 14:06:52', '2026-06-18 14:06:52'),
(4, 'Fitness Gym', 'gym', 'Modern equipment with personal trainers and group fitness classes', 20.00, 1.00, 3.00, 30, 'assets/Images/Gym.jfif', 1, '2026-06-18 14:06:52', '2026-06-18 14:06:52'),
(5, 'Adventure Rides', 'adventure', 'Thrilling adventure rides and zip lines for adrenaline seekers', 45.00, 2.00, 4.00, 10, 'assets/Images/ADV.jpg', 1, '2026-06-18 14:06:52', '2026-06-18 14:06:52'),
(6, 'Kids Playground', 'playground', 'Safe and fun playground area with activities for children of all ages', 10.00, 1.00, 4.00, 25, 'assets/Images/Playground.jpg', 1, '2026-06-18 14:06:52', '2026-06-18 14:06:52');

-- --------------------------------------------------------

--
-- Table structure for table `activity_availability_schedule`
--

CREATE TABLE `activity_availability_schedule` (
  `id` int(11) NOT NULL,
  `activity_id` int(11) NOT NULL,
  `day_of_week` enum('monday','tuesday','wednesday','thursday','friday','saturday','sunday') NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `max_concurrent_bookings` int(11) NOT NULL DEFAULT 1,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) NOT NULL,
  `suffix` varchar(50) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `phone_number` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `verification_token` varchar(255) DEFAULT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_token_expiry` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(255) DEFAULT NULL,
  `token_expiry` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `first_name`, `middle_name`, `last_name`, `suffix`, `email`, `phone_number`, `password`, `is_verified`, `verification_token`, `reset_token`, `reset_token_expiry`, `remember_token`, `token_expiry`, `created_at`, `updated_at`) VALUES
(1, 'Saiyon', '', 'Madarame', '', 'rhainearia@gmail.com', '09563695447', '$2y$10$l6ZJu.OEwCf454micPxxXO0pnHtUZyMXmJlrI8a/uZLGYl.Y8TcKO', 0, '696bc6d7cec17df661ed8408600c4bf7c2e081c2f8ff0ab97b7794dab79a97f0', NULL, NULL, NULL, NULL, '2026-03-26 16:56:37', '2026-03-26 16:56:37');

-- --------------------------------------------------------

--
-- Table structure for table `availability`
--

CREATE TABLE `availability` (
  `id` int(11) NOT NULL,
  `item_type` enum('activity','accommodation','bundle') NOT NULL,
  `item_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `time_slot` time DEFAULT NULL,
  `available_slots` int(11) NOT NULL,
  `total_slots` int(11) NOT NULL,
  `is_available` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `blocked_dates`
--

CREATE TABLE `blocked_dates` (
  `id` int(11) NOT NULL,
  `blocked_date` date NOT NULL,
  `reason` text DEFAULT NULL,
  `blocked_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `blocked_dates`
--

INSERT INTO `blocked_dates` (`id`, `blocked_date`, `reason`, `blocked_by`, `created_at`) VALUES
(1, '2026-07-15', 'Testing', 1, '2026-07-02 16:06:37');

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `booking_reference` varchar(20) NOT NULL,
  `user_id` int(11) NOT NULL,
  `booking_type` enum('activity','accommodation','bundle') NOT NULL,
  `activity_id` int(11) DEFAULT NULL,
  `accommodation_id` int(11) DEFAULT NULL,
  `bundle_id` int(11) DEFAULT NULL,
  `status_id` int(11) NOT NULL DEFAULT 1,
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
  `total_amount` decimal(10,2) NOT NULL,
  `payment_status` enum('pending','paid','refunded') DEFAULT 'pending',
  `payment_method` varchar(50) DEFAULT 'gcash',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `booking_reference`, `user_id`, `booking_type`, `activity_id`, `accommodation_id`, `bundle_id`, `status_id`, `full_name`, `email`, `phone_number`, `check_in_date`, `check_in_time`, `check_out_date`, `check_out_time`, `number_of_days`, `number_of_guests`, `special_requests`, `total_amount`, `payment_status`, `payment_method`, `created_at`, `updated_at`) VALUES
(1, 'REF-2026-0001', 1, 'activity', 1, NULL, NULL, 1, 'Cool Vincent Bonilla', 'bonillacoolvincentc@gmail.com', '09563695447', '2026-07-10', '14:00:00', '2026-07-10', '16:00:00', 1, 4, 'Need 2 extra cue sticks and chalk.', 50.00, 'paid', 'gcash', '2026-07-02 18:27:04', '2026-07-02 18:27:04');

-- --------------------------------------------------------

--
-- Table structure for table `booking_logs`
--

CREATE TABLE `booking_logs` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `old_status_id` int(11) DEFAULT NULL,
  `new_status_id` int(11) NOT NULL,
  `changed_by` enum('user','admin','system') NOT NULL,
  `changed_by_id` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `booking_status`
--

CREATE TABLE `booking_status` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `color` varchar(20) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `booking_status`
--

INSERT INTO `booking_status` (`id`, `name`, `description`, `color`, `is_active`, `created_at`) VALUES
(1, 'pending_review', 'Booking is pending admin review', '#f59e0b', 1, '2026-05-29 02:03:38'),
(2, 'approved', 'Booking has been approved', '#10b981', 1, '2026-05-29 02:03:38'),
(3, 'rejected', 'Booking has been rejected', '#ef4444', 1, '2026-05-29 02:03:38'),
(4, 'completed', 'Booking has been completed', '#3b82f6', 1, '2026-05-29 02:03:38'),
(5, 'cancelled', 'Booking has been cancelled', '#64748b', 1, '2026-05-29 02:03:38');

-- --------------------------------------------------------

--
-- Table structure for table `bundles`
--

CREATE TABLE `bundles` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `capacity` int(11) NOT NULL DEFAULT 1,
  `amenities` text DEFAULT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bundles`
--

INSERT INTO `bundles` (`id`, `name`, `slug`, `description`, `price`, `capacity`, `amenities`, `image_url`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Testing Bundle', 'testing-bundle', 'This is just a testing bundle', 1000.00, 12, '0', 'assets/uploads/bundles/bundle_6a4686b366c35.jpg', 1, '2026-07-02 15:41:39', '2026-07-02 15:41:39');

-- --------------------------------------------------------

--
-- Table structure for table `bundle_accommodations`
--

CREATE TABLE `bundle_accommodations` (
  `id` int(11) NOT NULL,
  `bundle_id` int(11) NOT NULL,
  `accommodation_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bundle_activities`
--

CREATE TABLE `bundle_activities` (
  `id` int(11) NOT NULL,
  `bundle_id` int(11) NOT NULL,
  `activity_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bundle_activities`
--

INSERT INTO `bundle_activities` (`id`, `bundle_id`, `activity_id`) VALUES
(1, 1, 2),
(2, 1, 1),
(3, 1, 4),
(4, 1, 6),
(5, 1, 3);

-- --------------------------------------------------------

--
-- Table structure for table `login_logs`
--

CREATE TABLE `login_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_type` enum('user','admin') NOT NULL,
  `email` varchar(255) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `login_time` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `otp_verifications`
--

CREATE TABLE `otp_verifications` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `otp` varchar(6) NOT NULL,
  `user_type` enum('user','admin') NOT NULL,
  `user_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`user_data`)),
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `attempts` int(11) DEFAULT 0,
  `last_resend` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `otp_verifications`
--

INSERT INTO `otp_verifications` (`id`, `email`, `otp`, `user_type`, `user_data`, `expires_at`, `attempts`, `last_resend`, `created_at`) VALUES
(1, 'bonillacoolvincentc@gmail.com', '288166', 'user', '{\"first_name\":\"Cool Vincent\",\"middle_name\":\"\",\"last_name\":\"Bonilla\",\"suffix\":\"\",\"email\":\"bonillacoolvincentc@gmail.com\",\"phone_number\":\"09563695447\",\"password\":\"Saiyon05\",\"region\":\"010000000\",\"province\":\"015500000\",\"city_municipality\":\"015516000\",\"barangay\":\"015516007\"}', '2026-03-26 16:32:31', 1, NULL, '2026-03-26 16:32:09'),
(3, 'bonillacoolvincentc@gmail.com', '288166', 'user', '{\"first_name\":\"Cool Vincent\",\"middle_name\":\"\",\"last_name\":\"Bonilla\",\"suffix\":\"\",\"email\":\"bonillacoolvincentc@gmail.com\",\"phone_number\":\"09563695447\",\"password\":\"Saiyon05\",\"region\":\"010000000\",\"province\":\"015500000\",\"city_municipality\":\"015516000\",\"barangay\":\"015516007\"}', '2026-03-27 00:37:09', 0, NULL, '2026-03-26 16:32:09'),
(4, 'bonillacoolvincentc@gmail.com', '288166', 'user', '{\"first_name\":\"Cool Vincent\",\"middle_name\":\"\",\"last_name\":\"Bonilla\",\"suffix\":\"\",\"email\":\"bonillacoolvincentc@gmail.com\",\"phone_number\":\"09563695447\",\"password\":\"Saiyon05\",\"region\":\"010000000\",\"province\":\"015500000\",\"city_municipality\":\"015516000\",\"barangay\":\"015516007\"}', '2026-03-27 00:37:09', 0, NULL, '2026-03-26 16:32:09'),
(5, 'bonillacoolvincentc@gmail.com', '288166', 'user', '{\"first_name\":\"Cool Vincent\",\"middle_name\":\"\",\"last_name\":\"Bonilla\",\"suffix\":\"\",\"email\":\"bonillacoolvincentc@gmail.com\",\"phone_number\":\"09563695447\",\"password\":\"Saiyon05\",\"region\":\"010000000\",\"province\":\"015500000\",\"city_municipality\":\"015516000\",\"barangay\":\"015516007\"}', '2026-03-27 00:37:09', 0, NULL, '2026-03-26 16:32:09'),
(6, 'bonillacoolvincentc@gmail.com', '288166', 'user', '{\"first_name\":\"Cool Vincent\",\"middle_name\":\"\",\"last_name\":\"Bonilla\",\"suffix\":\"\",\"email\":\"bonillacoolvincentc@gmail.com\",\"phone_number\":\"09563695447\",\"password\":\"Saiyon05\",\"region\":\"010000000\",\"province\":\"015500000\",\"city_municipality\":\"015516000\",\"barangay\":\"015516007\"}', '2026-03-27 00:37:09', 0, NULL, '2026-03-26 16:32:09'),
(7, 'bonillacoolvincentc@gmail.com', '288166', 'user', '{\"first_name\":\"Cool Vincent\",\"middle_name\":\"\",\"last_name\":\"Bonilla\",\"suffix\":\"\",\"email\":\"bonillacoolvincentc@gmail.com\",\"phone_number\":\"09563695447\",\"password\":\"Saiyon05\",\"region\":\"010000000\",\"province\":\"015500000\",\"city_municipality\":\"015516000\",\"barangay\":\"015516007\"}', '2026-03-27 00:37:09', 0, NULL, '2026-03-26 16:32:09'),
(8, 'bonillacoolvincentc@gmail.com', '288166', 'user', '{\"first_name\":\"Cool Vincent\",\"middle_name\":\"\",\"last_name\":\"Bonilla\",\"suffix\":\"\",\"email\":\"bonillacoolvincentc@gmail.com\",\"phone_number\":\"09563695447\",\"password\":\"Saiyon05\",\"region\":\"010000000\",\"province\":\"015500000\",\"city_municipality\":\"015516000\",\"barangay\":\"015516007\"}', '2026-03-27 00:37:09', 0, NULL, '2026-03-26 16:32:09'),
(9, 'bonillacoolvincentc@gmail.com', '967589', 'user', '{\"first_name\":\"Cool Vincent\",\"middle_name\":\"\",\"last_name\":\"Bonilla\",\"suffix\":\"\",\"email\":\"bonillacoolvincentc@gmail.com\",\"phone_number\":\"09563695447\",\"password\":\"Saiyon05\",\"region\":\"010000000\",\"province\":\"015500000\",\"city_municipality\":\"015516000\",\"barangay\":\"015516007\"}', '2026-03-26 16:32:01', 1, NULL, '2026-03-26 16:31:37'),
(10, 'rhainearia@gmail.com', '726275', 'admin', '{\"first_name\":\"Cool Vincent\",\"middle_name\":\"\",\"last_name\":\"Bonilla\",\"suffix\":\"\",\"email\":\"rhainearia@gmail.com\",\"phone_number\":\"09563695447\",\"password\":\"Admin321\"}', '2026-03-26 16:49:57', 1, NULL, '2026-03-26 16:49:24'),
(11, 'rhainearia@gmail.com', '118287', 'user', '{}', '2026-03-27 00:55:21', 0, NULL, '2026-03-26 16:50:21');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `user_type` enum('user','admin') NOT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_used` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_attempts`
--

CREATE TABLE `password_reset_attempts` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `attempted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_logs`
--

CREATE TABLE `password_reset_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_type` enum('user','admin') NOT NULL,
  `email` varchar(255) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `reset_time` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_receipts`
--

CREATE TABLE `payment_receipts` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_size` bigint(20) NOT NULL,
  `file_type` varchar(50) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) NOT NULL,
  `suffix` varchar(50) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `phone_number` varchar(20) NOT NULL,
  `region` varchar(100) NOT NULL,
  `province` varchar(100) NOT NULL,
  `city_municipality` varchar(100) NOT NULL,
  `barangay` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `verification_token` varchar(255) DEFAULT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_token_expiry` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(255) DEFAULT NULL,
  `token_expiry` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `address` varchar(255) DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `gender` varchar(50) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `middle_name`, `last_name`, `suffix`, `email`, `phone_number`, `region`, `province`, `city_municipality`, `barangay`, `password`, `is_verified`, `verification_token`, `reset_token`, `reset_token_expiry`, `remember_token`, `token_expiry`, `created_at`, `updated_at`, `address`, `birthdate`, `gender`, `bio`, `avatar`) VALUES
(1, 'Cool Vincent', '', 'Bonilla', '', 'bonillacoolvincentc@gmail.com', '09563695447', 'Ilocos Region', 'Pangasinan', 'Burgos', 'Poblacion', '$2y$10$9smp40N.ZWhZ4dWxMSJ3xOADbAkMeNHu.R3GeJlQXieumXHrm.5IO', 0, '12f4c66db811caa1c2eae16aed9efb0e6f4ebb0c9f0c54a1a1ec8fb527b5ddff', NULL, NULL, 'd2d47f89587c1dbfd7f7d33c88302d56b2c70fed9fe1ab2454b0c9b5ee7c3b9c', '2026-08-01 15:42:05', '2026-03-26 16:32:38', '2026-07-02 17:25:27', 'Poblacion, Burgos, Pangasinan, Ilocos Region', NULL, NULL, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accommodations`
--
ALTER TABLE `accommodations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_type` (`type`),
  ADD KEY `idx_is_active` (`is_active`);

--
-- Indexes for table `activities`
--
ALTER TABLE `activities`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_is_active` (`is_active`);

--
-- Indexes for table `activity_availability_schedule`
--
ALTER TABLE `activity_availability_schedule`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_activity_id` (`activity_id`),
  ADD KEY `idx_day_of_week` (`day_of_week`);

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `phone_number` (`phone_number`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_phone` (`phone_number`),
  ADD KEY `idx_verification_token` (`verification_token`),
  ADD KEY `idx_reset_token` (`reset_token`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `availability`
--
ALTER TABLE `availability`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_availability` (`item_type`,`item_id`,`date`,`time_slot`),
  ADD KEY `idx_date` (`date`),
  ADD KEY `idx_item_type_id` (`item_type`,`item_id`),
  ADD KEY `idx_is_available` (`is_available`);

--
-- Indexes for table `blocked_dates`
--
ALTER TABLE `blocked_dates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_blocked_date` (`blocked_date`),
  ADD KEY `idx_blocked_date` (`blocked_date`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `booking_reference` (`booking_reference`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_booking_type` (`booking_type`),
  ADD KEY `idx_activity_id` (`activity_id`),
  ADD KEY `idx_accommodation_id` (`accommodation_id`),
  ADD KEY `idx_status_id` (`status_id`),
  ADD KEY `idx_check_in_date` (`check_in_date`),
  ADD KEY `idx_check_out_date` (`check_out_date`),
  ADD KEY `idx_payment_status` (`payment_status`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `fk_bookings_bundle` (`bundle_id`);

--
-- Indexes for table `booking_logs`
--
ALTER TABLE `booking_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_booking_id` (`booking_id`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `fk_logs_old_status` (`old_status_id`),
  ADD KEY `fk_logs_new_status` (`new_status_id`);

--
-- Indexes for table `booking_status`
--
ALTER TABLE `booking_status`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `bundles`
--
ALTER TABLE `bundles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `bundle_accommodations`
--
ALTER TABLE `bundle_accommodations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bundle_id` (`bundle_id`),
  ADD KEY `accommodation_id` (`accommodation_id`);

--
-- Indexes for table `bundle_activities`
--
ALTER TABLE `bundle_activities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bundle_id` (`bundle_id`),
  ADD KEY `activity_id` (`activity_id`);

--
-- Indexes for table `login_logs`
--
ALTER TABLE `login_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_login_time` (`login_time`),
  ADD KEY `idx_user_type` (`user_type`);

--
-- Indexes for table `otp_verifications`
--
ALTER TABLE `otp_verifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_otp` (`otp`),
  ADD KEY `idx_expires_at` (`expires_at`),
  ADD KEY `idx_user_type` (`user_type`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_token` (`token`),
  ADD KEY `idx_expires_at` (`expires_at`),
  ADD KEY `idx_user_type` (`user_type`);

--
-- Indexes for table `password_reset_attempts`
--
ALTER TABLE `password_reset_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_ip_address` (`ip_address`),
  ADD KEY `idx_attempted_at` (`attempted_at`);

--
-- Indexes for table `password_reset_logs`
--
ALTER TABLE `password_reset_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_reset_time` (`reset_time`),
  ADD KEY `idx_user_type` (`user_type`);

--
-- Indexes for table `payment_receipts`
--
ALTER TABLE `payment_receipts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_booking_id` (`booking_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `phone_number` (`phone_number`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_phone` (`phone_number`),
  ADD KEY `idx_verification_token` (`verification_token`),
  ADD KEY `idx_reset_token` (`reset_token`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `accommodations`
--
ALTER TABLE `accommodations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `activities`
--
ALTER TABLE `activities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `activity_availability_schedule`
--
ALTER TABLE `activity_availability_schedule`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `availability`
--
ALTER TABLE `availability`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `blocked_dates`
--
ALTER TABLE `blocked_dates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `booking_logs`
--
ALTER TABLE `booking_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `booking_status`
--
ALTER TABLE `booking_status`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `bundles`
--
ALTER TABLE `bundles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `bundle_accommodations`
--
ALTER TABLE `bundle_accommodations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bundle_activities`
--
ALTER TABLE `bundle_activities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `login_logs`
--
ALTER TABLE `login_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `otp_verifications`
--
ALTER TABLE `otp_verifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `password_reset_attempts`
--
ALTER TABLE `password_reset_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `password_reset_logs`
--
ALTER TABLE `password_reset_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment_receipts`
--
ALTER TABLE `payment_receipts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_availability_schedule`
--
ALTER TABLE `activity_availability_schedule`
  ADD CONSTRAINT `fk_schedule_activity` FOREIGN KEY (`activity_id`) REFERENCES `activities` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `fk_bookings_accommodation` FOREIGN KEY (`accommodation_id`) REFERENCES `accommodations` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_bookings_activity` FOREIGN KEY (`activity_id`) REFERENCES `activities` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_bookings_bundle` FOREIGN KEY (`bundle_id`) REFERENCES `bundles` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_bookings_status` FOREIGN KEY (`status_id`) REFERENCES `booking_status` (`id`),
  ADD CONSTRAINT `fk_bookings_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `booking_logs`
--
ALTER TABLE `booking_logs`
  ADD CONSTRAINT `fk_logs_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_logs_new_status` FOREIGN KEY (`new_status_id`) REFERENCES `booking_status` (`id`),
  ADD CONSTRAINT `fk_logs_old_status` FOREIGN KEY (`old_status_id`) REFERENCES `booking_status` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `payment_receipts`
--
ALTER TABLE `payment_receipts`
  ADD CONSTRAINT `fk_receipts_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
