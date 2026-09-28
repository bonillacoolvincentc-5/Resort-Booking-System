-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 28, 2026 at 08:59 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

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
  `type` varchar(50) NOT NULL DEFAULT 'room',
  `tour_type` varchar(50) NOT NULL DEFAULT 'overnight,day_tour',
  `description` text DEFAULT NULL,
  `base_capacity` int(11) NOT NULL DEFAULT 1,
  `capacity` int(11) NOT NULL DEFAULT 1,
  `daily_booking_limit` int(11) NOT NULL DEFAULT 1,
  `extra_person_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `price_per_night` decimal(10,2) NOT NULL,
  `price_per_day` decimal(10,2) NOT NULL,
  `amenities` text DEFAULT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `check_in_time` time DEFAULT '14:00:00',
  `check_out_time` time DEFAULT '12:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `accommodations`
--

INSERT INTO `accommodations` (`id`, `name`, `type`, `tour_type`, `description`, `base_capacity`, `capacity`, `daily_booking_limit`, `extra_person_fee`, `price_per_night`, `price_per_day`, `amenities`, `image_url`, `is_active`, `created_at`, `updated_at`, `check_in_time`, `check_out_time`) VALUES
(2, 'Day Tour Kubo Non-Aircon Room (8 Hours)', 'cottage', 'day_tour', 'Day tour cottage room. Good for 2 persons. Non-aircon Kubo. 8-hour stay. Cut-off 9PM. Free: Pool, Parking, WiFi, Common Bathroom, Playground. Plus daytour entrance fee. VAT excluded.', 2, 2, 1, 0.00, 0.00, 799.00, 'Pool, Gym, Basketball Court, Parking, WiFi, Common Bathroom, Playground', 'assets/uploads/accommodations/6abab3c2a0a8a.webp', 1, '2026-07-08 15:59:21', '2026-09-28 18:36:50', '14:00:00', '12:00:00'),
(3, 'Day Tour Kubo Non-Aircon Room (12 Hours)', 'cottage', 'day_tour', 'Day tour cottage room. Good for 2 persons. Non-aircon Kubo. 12-hour stay. Cut-off 9PM. Free: Pool, Parking, WiFi, Common Bathroom, Playground. Plus daytour entrance fee. VAT excluded.', 2, 6, 5, 250.00, 0.00, 999.00, 'Pool, Gym, Basketball Court, Parking, WiFi, Common Bathroom, Playground', 'assets/uploads/accommodations/6abab3cfb41bf.webp', 1, '2026-07-08 15:59:21', '2026-09-28 18:37:03', '14:00:00', '12:00:00'),
(4, 'Day Tour Kubo Aircon Room (8 Hours)', 'cottage', 'day_tour', 'Day tour cottage room. Good for 2 persons. Aircon Kubo. 8-hour stay. Extra hour P200. Cut-off 9PM. Walk-in only. Free: Pool, Gym, Basketball Court, Parking, WiFi, Common Bathroom, Playground, Air Conditioning. Plus daytour entrance fee. VAT excluded.', 2, 2, 1, 0.00, 999.00, 999.00, 'Pool, Gym, Basketball Court, Parking, WiFi, Common Bathroom, Playground, Air Conditioning', 'assets/uploads/accommodations/6abab3db706ef.webp', 1, '2026-07-08 15:59:21', '2026-09-28 18:37:15', '14:00:00', '12:00:00'),
(5, 'Day Tour Kubo Aircon Room (12 Hours)', 'cottage', 'day_tour', 'Day tour cottage room. Good for 2 persons. Aircon Kubo. 12-hour stay. Cut-off 9PM. Free: Pool, Parking, WiFi, Common Bathroom, Playground, Air Conditioning. Plus daytour entrance fee. VAT excluded.', 2, 6, 2, 250.00, 0.00, 1499.00, 'Pool, Gym, Basketball Court, Parking, WiFi, Common Bathroom, Playground, Air Conditioning', 'assets/uploads/accommodations/6abab3e895788.webp', 1, '2026-07-08 15:59:21', '2026-09-28 18:37:28', '14:00:00', '12:00:00'),
(6, 'Umbrella Cottage (8 Pax+)', 'cottage', 'day_tour', 'Outdoor umbrella cottage. Good for 8 pax and up. Includes table and chairs. Plus entrance fee. Free access: Gym, Kids Playground, Pool, Basketball Court, Shower, Griller, Pool Bench. VAT excluded.', 8, 8, 5, 0.00, 0.00, 500.00, 'Table and Chairs, Gym, Kids Playground, Swimming Pool, Basketball Court, Shower, Griller, Pool Bench', 'assets/uploads/accommodations/6abab0e6aab9c.jpg', 1, '2026-07-08 15:59:21', '2026-09-28 18:24:38', '14:00:00', '12:00:00'),
(7, 'Small Awning Cottage (10 Pax+)', 'cottage', 'day_tour', 'Small awning cottage. Good for 10 pax and up. Includes table and chairs. Plus entrance fee. VAT excluded.', 10, 10, 1, 0.00, 700.00, 700.00, 'Table and Chairs, Gym, Kids Playground, Swimming Pool, Basketball Court, Shower, Griller', 'assets/uploads/accommodations/6abab109b559e.webp', 1, '2026-07-08 15:59:21', '2026-09-28 18:25:13', '14:00:00', '12:00:00'),
(8, 'Kubo Cottage (15 Pax+)', 'cottage', 'day_tour', 'Kubo cottage. Good for 15 pax and up. Includes table and chairs. Plus entrance fee. VAT excluded.', 15, 15, 1, 0.00, 1200.00, 1200.00, 'Table and Chairs, Gym, Kids Playground, Swimming Pool, Basketball Court, Shower, Griller', 'assets/uploads/accommodations/6abab11aab5be.webp', 1, '2026-07-08 15:59:21', '2026-09-28 18:25:30', '14:00:00', '12:00:00'),
(9, 'Big Kubo Cottage (20 Pax+)', 'cottage', 'day_tour', 'Big kubo cottage. Good for 20 pax and up. Includes table and chairs. Plus entrance fee. VAT excluded.', 20, 20, 5, 0.00, 1500.00, 1500.00, 'Table and Chairs, Gym, Kids Playground, Swimming Pool, Basketball Court, Shower, Griller', 'assets/uploads/accommodations/6abab13519f09.webp', 1, '2026-07-08 15:59:21', '2026-09-28 18:25:57', '14:00:00', '12:00:00'),
(10, 'Pavilion Cottage (25 Pax+)', 'cottage', 'day_tour', 'Pavilion cottage. Good for 25 pax and up. Includes table and chairs. Daytime rate. Plus entrance fee. VAT excluded.', 25, 25, 1, 0.00, 1500.00, 1500.00, 'Table and Chairs, Gym, Kids Playground, Swimming Pool, Basketball Court, Shower, Griller', 'assets/uploads/accommodations/6abab1847b86a.webp', 1, '2026-07-08 15:59:21', '2026-09-28 18:27:16', '14:00:00', '12:00:00'),
(11, 'Private Big Pavilion - Daytime (35 Pax+)', 'cottage', 'day_tour', 'Private big pavilion. Good for 35 pax and up. Includes table and chairs. Daytime rate. Plus entrance fee. VAT excluded.', 35, 35, 1, 0.00, 2000.00, 2000.00, 'Table and Chairs, Gym, Kids Playground, Swimming Pool, Basketball Court, Shower, Griller', 'assets/uploads/accommodations/6abab1a50088b.webp', 1, '2026-07-08 15:59:21', '2026-09-28 18:27:49', '14:00:00', '12:00:00'),
(12, 'Private Big Pavilion - 3PM Onwards (35 Pax+)', 'cottage', 'day_tour', 'Private big pavilion. Good for 35 pax and up. Includes table and chairs. 3PM onwards rate. Plus entrance fee. VAT excluded.', 35, 35, 1, 0.00, 2500.00, 2500.00, 'Table and Chairs, Gym, Kids Playground, Swimming Pool, Basketball Court, Shower, Griller', 'assets/uploads/accommodations/6abab1b03d20a.webp', 1, '2026-07-08 15:59:21', '2026-09-28 18:28:00', '14:00:00', '12:00:00'),
(13, 'Private Big Kubo - Daytime (50 Pax+)', 'cottage', 'day_tour', 'Private big kubo cottage. Good for 50 pax and up. Includes table and chairs. Daytime rate. Plus entrance fee. VAT excluded.', 50, 50, 1, 0.00, 3000.00, 3000.00, 'Table and Chairs, Gym, Kids Playground, Swimming Pool, Basketball Court, Shower, Griller', 'assets/uploads/accommodations/6abab1c7ad369.webp', 1, '2026-07-08 15:59:21', '2026-09-28 18:28:23', '14:00:00', '12:00:00'),
(14, 'Private Big Kubo - 3PM Onwards (50 Pax+)', 'cottage', 'day_tour', 'Private big kubo cottage. Good for 50 pax and up. Includes table and chairs. 3PM onwards rate. Plus entrance fee. VAT excluded.', 50, 50, 5, 0.00, 4000.00, 4000.00, 'Table and Chairs, Gym, Kids Playground, Swimming Pool, Basketball Court, Shower, Griller', 'assets/uploads/accommodations/6abab1d3b08e1.webp', 1, '2026-07-08 15:59:21', '2026-09-28 18:28:35', '14:00:00', '12:00:00'),
(15, 'Kubo Room Non-Aircon (Transient)', 'room', 'overnight', 'Transient Kubo non-aircon room. Max 2 persons only. P1,499 per night. Plus P500 excess per person. Amenities: Swimming Pool, Karaoke and Sounds, Free Parking, Strong WiFi, Genset Backup, Playground. VAT excluded.', 2, 2, 1, 500.00, 1499.00, 1499.00, 'Swimming Pool, Karaoke and Sounds, Free Parking, Strong WiFi, Genset Backup, Playground', 'assets/uploads/accommodations/6abab3f50a4c5.webp', 1, '2026-07-08 15:59:21', '2026-09-28 18:37:41', '14:00:00', '12:00:00'),
(16, 'Kubo Room Aircon (Transient)', 'room', 'overnight', 'Transient Kubo aircon room. Max 2 persons only. P2,499 per night. Plus P500 excess per person. Amenities: Swimming Pool, Karaoke and Sounds, Free Parking, Strong WiFi, Genset Backup, Air Conditioning, Playground. VAT excluded.', 2, 2, 1, 500.00, 2499.00, 2499.00, 'Swimming Pool, Karaoke and Sounds, Free Parking, Strong WiFi, Genset Backup, Air Conditioning, Playground', 'assets/uploads/accommodations/6abab3fd655ba.webp', 1, '2026-07-08 15:59:21', '2026-09-28 18:37:49', '14:00:00', '12:00:00'),
(17, 'Regular Room', 'room', 'overnight', 'Transient regular room. Up to 8 pax bed capacity. 4 persons: P3,999 (max 8 persons). Plus P400 excess per person. Amenities: Swimming Pool, Karaoke and Sounds, Free Parking, Strong WiFi, Genset Backup. VAT excluded.', 4, 8, 1, 400.00, 3999.00, 3999.00, 'Swimming Pool, Karaoke and Sounds, Free Parking, Strong WiFi, Genset Backup, Playground', 'assets/uploads/accommodations/6abab29d742d3.jpg', 1, '2026-07-08 15:59:21', '2026-09-28 18:31:57', '18:00:00', '16:00:00'),
(18, 'Dome Tent Room VIP', 'room', 'overnight', 'Transient VIP Dome Tent room. Max 2-8 persons. VIP 2 persons: P3,499 (max 4 persons). Regular Dome max 8 persons. Plus P500 excess per person. VAT excluded.', 2, 4, 1, 500.00, 3499.00, 3499.00, 'Swimming Pool, Karaoke and Sounds, Free Parking, Strong WiFi, Genset Backup', NULL, 1, '2026-07-08 15:59:21', '2026-09-28 18:34:03', '14:00:00', '12:00:00'),
(19, 'VIP Master Bedroom', 'room', 'overnight', 'Transient VIP Master Bedroom. Up to 6 pax bed capacity. 2 persons: P3,999 (max 6 persons). Plus P500 excess per person. Amenities: Swimming Pool, Karaoke and Sounds, Free Parking, Strong WiFi, Genset Backup, Air Conditioning. VAT excluded.', 2, 6, 1, 500.00, 3999.00, 3999.00, 'Swimming Pool, Karaoke and Sounds, Free Parking, Strong WiFi, Genset Backup, Air Conditioning', 'assets/uploads/accommodations/6abab287e92a0.webp', 1, '2026-07-08 15:59:21', '2026-09-28 18:31:35', '14:00:00', '12:00:00'),
(26, 'Barkada Private Villa', 'room', 'overnight', 'Transient VIP Barkada Private Villa. 4 Rooms and Solo Pool. Up to 40 pax bed capacity. 20 persons: P15,999 | 30 persons: P19,999 | Unli Pax: P24,999 (max 40). Plus P650 excess per person. Amenities: Private Pool, 4 Aircon Rooms, Swimming Pool, Karaoke and Sounds, Free Parking, Strong WiFi, Genset Backup. VAT excluded.', 20, 40, 1, 650.00, 15999.00, 15999.00, 'Private Pool, 4 Aircon Rooms, Swimming Pool, Karaoke and Sounds, Free Parking, Strong WiFi, Genset Backup', 'assets/uploads/accommodations/6abab377f2184.webp', 1, '2026-07-08 15:59:21', '2026-09-28 18:35:36', '14:00:00', '12:00:00'),
(27, 'Private Grand Villa', 'room', 'overnight', 'Transient VIP Private Grand Villa. 3 Rooms and Solo Pool. Up to 75 pax bed capacity. 30 persons: P19,999 | 40 persons: P24,999 | Unli Pax: P29,999 (max 75). Plus P650 excess per person. Amenities: Private Pool, 3 Aircon Rooms, Swimming Pool, Karaoke and Sounds, Free Parking, Strong WiFi, Genset Backup. VAT excluded.', 30, 75, 1, 650.00, 19999.00, 19999.00, 'Private Pool, 3 Aircon Rooms, Swimming Pool, Karaoke and Sounds, Free Parking, Strong WiFi, Genset Backup', 'assets/uploads/accommodations/6abab382bfa40.webp', 1, '2026-07-08 15:59:21', '2026-09-28 18:35:46', '14:00:00', '12:00:00');

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
  `price_type` enum('per_head','per_hour','flat_rate') NOT NULL DEFAULT 'flat_rate',
  `min_duration_hours` decimal(4,2) NOT NULL DEFAULT 1.00,
  `max_duration_hours` decimal(4,2) NOT NULL DEFAULT 4.00,
  `capacity` int(11) NOT NULL DEFAULT 10,
  `daily_booking_limit` int(11) NOT NULL DEFAULT 10,
  `image_url` varchar(500) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activities`
--

INSERT INTO `activities` (`id`, `name`, `slug`, `description`, `price_per_hour`, `price_type`, `min_duration_hours`, `max_duration_hours`, `capacity`, `daily_booking_limit`, `image_url`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Billiards', 'billiards', 'Premium billiards tables with expert coaching available for all skill levels', 200.00, 'flat_rate', 1.00, 4.00, 8, 10, 'assets/uploads/activities/6aa21fc8a8369.webp', 1, '2026-06-18 14:06:52', '2026-09-24 12:35:32'),
(10, 'ATV Short Ride - Direct Bolo Beach Tour', 'atv-short-ride-direct-bolo-beach-tour', 'YP4K ATV and UTV Sports. Short ride 1 hour. Direct Bolo Beach Tour. P1,500 per ATV. Tour guide P1,500 mandatory. Destinations: Sabangan, Telbang, Tubuey Rd., Bolo Beach. VAT excluded.', 1500.00, 'per_head', 1.00, 1.00, 10, 10, 'assets/uploads/activities/6aa221779df1e.jpg', 1, '2026-07-08 15:59:21', '2026-09-10 14:51:37'),
(11, 'ATV Long Ride - Mountain and Bolo Beach Tour', 'atv-long-ride-mountain-and-bolo-beach-tour', 'YP4K ATV and UTV Sports. Long ride almost 2 hours. Mountain and Bolo Beach Tour. P2,000 per ATV. Tour guide P1,500 mandatory. VAT excluded.', 3000.00, 'per_head', 2.00, 2.00, 10, 10, 'assets/uploads/activities/6aa2216cf4013.jpg', 1, '2026-07-08 15:59:21', '2026-09-23 12:59:47'),
(12, 'Basketball Court - Flood Light Rental (Guest)', 'basketball-court-flood-light-rental-guest-', 'Open Basketball Court at Resort-2 Venue. All guests: P500/hour flood light rental. Solar light: FREE. Morning/afternoon play: FREE. Strictly time-scheduled. Please register first.', 500.00, 'flat_rate', 1.00, 4.00, 30, 10, 'assets/uploads/activities/6aa22248936e7.jpg', 1, '2026-07-08 15:59:21', '2026-09-10 03:21:44'),
(15, 'Pickleball Court - Private Rental (Guest/Member)', 'pickleball-court-private-rental-guest-member-', 'Sports Central YP4K. Soft opening special rates. Mon-Sun 4PM-9PM. Guest/member private rental: P250/hour. Open Play: P100 (4PM-9PM). 2 courts only. Full payment upon reservation.', 250.00, 'flat_rate', 1.00, 5.00, 10, 10, 'assets/uploads/activities/6abaaacfba620.webp', 1, '2026-07-08 15:59:21', '2026-09-28 17:58:39'),
(16, 'Videoke Rental', 'videoke-rental', 'Videoke rental at YP4K Villa Berde Resort. P1,500 flat rate per session.', 750.00, 'flat_rate', 1.00, 4.00, 50, 10, 'assets/uploads/activities/6abaaae7975dd.jpg', 1, '2026-07-08 15:59:21', '2026-09-28 17:59:03');

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
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `role` varchar(50) DEFAULT NULL,
  `action_type` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `role`, `action_type`, `description`, `created_at`) VALUES
(1, 1, 'admin', 'logout', 'Admin logged out.', '2026-09-28 00:23:08'),
(2, 1, 'admin', 'login', 'Admin logged in.', '2026-09-28 00:23:22'),
(3, 1, 'admin', 'walk_in', 'Walk-in booking #WI-2026-21E5CC created for Feliciano Nando (accommodation).', '2026-09-28 00:56:50'),
(4, 1, 'admin', 'login', 'Admin logged in.', '2026-09-28 03:16:50'),
(5, 1, 'admin', 'logout', 'Admin logged out.', '2026-09-28 03:17:28'),
(6, 2, 'staff', 'login', 'Staff logged in.', '2026-09-28 03:17:38'),
(7, 2, 'staff', 'logout', 'Staff logged out.', '2026-09-28 04:39:11'),
(8, 1, 'admin', 'login', 'Admin logged in.', '2026-09-28 04:39:20'),
(9, 1, 'admin', 'logout', 'Admin logged out.', '2026-09-28 04:48:09'),
(10, 1, 'admin', 'login', 'Admin logged in.', '2026-09-28 04:56:26'),
(11, 1, 'admin', 'status_update', 'Booking #29 status updated to awaiting_payment.', '2026-09-28 04:57:00'),
(12, 1, 'admin', 'status_update', 'Booking #28 status updated to awaiting_payment.', '2026-09-28 04:57:05'),
(13, 1, 'admin', 'status_update', 'Booking #28 status updated to approved.', '2026-09-28 04:57:08'),
(14, 1, 'admin', 'logout', 'Admin logged out.', '2026-09-28 04:57:36'),
(15, 1, 'admin', 'login', 'Admin logged in.', '2026-09-28 05:00:38'),
(16, 1, 'admin', 'status_update', 'Booking #29 status updated to approved.', '2026-09-28 05:01:20'),
(17, 1, 'admin', 'logout', 'Admin logged out.', '2026-09-28 05:07:31'),
(18, 1, 'admin', 'login', 'Admin logged in.', '2026-09-28 05:07:52'),
(19, 1, 'admin', 'edit_accommodation', 'Edited accommodation: Day Tour Kubo Aircon Room (8 Hours) (ID: 4).', '2026-09-28 05:09:18'),
(20, 1, 'admin', 'logout', 'Admin logged out.', '2026-09-28 05:09:26'),
(21, 1, 'admin', 'login', 'Admin logged in.', '2026-09-28 05:19:15'),
(22, 1, 'admin', 'status_update', 'Booking #30 status updated to awaiting_payment.', '2026-09-28 05:19:26'),
(23, 1, 'admin', 'logout', 'Admin logged out.', '2026-09-28 05:20:30'),
(24, 1, 'admin', 'login', 'Admin logged in.', '2026-09-28 05:24:04'),
(25, 1, 'admin', 'status_update', 'Booking #30 status updated to cancelled.', '2026-09-28 05:26:07'),
(26, 1, 'admin', 'logout', 'Admin logged out.', '2026-09-28 05:26:24'),
(27, 1, 'admin', 'login', 'Admin logged in.', '2026-09-28 05:28:09'),
(28, 1, 'admin', 'logout', 'Admin logged out.', '2026-09-28 05:28:24'),
(29, 1, 'admin', 'login', 'Admin logged in.', '2026-09-28 06:18:17'),
(30, 1, 'admin', 'logout', 'Admin logged out.', '2026-09-28 06:50:37'),
(31, 2, 'staff', 'login', 'Staff logged in.', '2026-09-28 06:50:50'),
(32, 2, 'staff', 'logout', 'Staff logged out.', '2026-09-28 06:54:54'),
(33, 1, 'admin', 'login', 'Admin logged in.', '2026-09-28 09:44:28'),
(34, 1, 'admin', 'logout', 'Admin logged out.', '2026-09-28 09:45:28'),
(35, 1, 'admin', 'login', 'Admin logged in.', '2026-09-28 10:03:01'),
(36, 1, 'admin', 'edit_bundle', 'Edited bundle: Daytour Private Resort 2 - 6HRS Daytime (Unli Pax) (ID: 4).', '2026-09-28 10:06:47'),
(37, 1, 'admin', 'edit_bundle', 'Edited bundle: Daytour Private Resort 2 - 6HRS 3PM Onwards (Unli Pax) (ID: 5).', '2026-09-28 10:06:59'),
(38, 1, 'admin', 'edit_bundle', 'Edited bundle: Private Resort 2 - 10HRS Full Day (ID: 6).', '2026-09-28 10:07:04'),
(39, 1, 'admin', 'logout', 'Admin logged out.', '2026-09-28 10:07:12'),
(40, 1, 'admin', 'login', 'Admin logged in.', '2026-09-28 11:03:56'),
(41, 1, 'admin', 'status_update', 'Booking #31 status updated to awaiting_payment.', '2026-09-28 11:06:47'),
(42, 1, 'admin', 'logout', 'Admin logged out.', '2026-09-28 11:06:57'),
(43, 1, 'admin', 'login', 'Admin logged in.', '2026-09-28 11:08:32'),
(44, 1, 'admin', 'edit_accommodation', 'Edited accommodation: Day Tour Kubo Non-Aircon Room (8 Hours) (ID: 2).', '2026-09-28 11:34:14'),
(45, 1, 'admin', 'edit_accommodation', 'Edited accommodation: Day Tour Kubo Non-Aircon Room (8 Hours) (ID: 2).', '2026-09-28 11:35:23'),
(46, 1, 'admin', 'edit_accommodation', 'Edited accommodation: Day Tour Kubo Non-Aircon Room (12 Hours) (ID: 3).', '2026-09-28 12:14:14'),
(47, 1, 'admin', 'logout', 'Admin logged out.', '2026-09-28 12:18:43'),
(48, 1, 'admin', 'login', 'Admin logged in.', '2026-09-28 12:31:19'),
(49, 1, 'admin', 'edit_accommodation', 'Edited accommodation: Day Tour Kubo Non-Aircon Room (12 Hours) (ID: 3).', '2026-09-28 12:31:58'),
(50, 1, 'admin', 'logout', 'Admin logged out.', '2026-09-28 12:32:01'),
(51, 1, 'admin', 'login', 'Admin logged in.', '2026-09-28 12:36:17'),
(52, 1, 'admin', 'edit_accommodation', 'Edited accommodation: Day Tour Kubo Aircon Room (12 Hours) (ID: 5).', '2026-09-28 12:37:35'),
(53, 1, 'admin', 'logout', 'Admin logged out.', '2026-09-28 12:37:50'),
(54, 1, 'admin', 'login', 'Admin logged in.', '2026-09-28 13:00:57'),
(55, 1, 'admin', 'logout', 'Admin logged out.', '2026-09-28 13:08:30'),
(56, 1, 'admin', 'login', 'Admin logged in.', '2026-09-28 13:41:37'),
(57, 1, 'admin', 'status_update', 'Booking #32 status updated to awaiting_payment.', '2026-09-28 13:41:50'),
(58, 1, 'admin', 'logout', 'Admin logged out.', '2026-09-28 13:42:19'),
(59, 2, 'staff', 'login', 'Staff logged in.', '2026-09-28 13:42:51'),
(60, 2, 'staff', 'logout', 'Staff logged out.', '2026-09-28 13:44:23'),
(61, 1, 'admin', 'login', 'Admin logged in.', '2026-09-28 13:45:46'),
(62, 1, 'admin', 'status_update', 'Booking #32 status updated to approved.', '2026-09-28 13:47:31'),
(63, 1, 'admin', 'logout', 'Admin logged out.', '2026-09-28 13:48:29'),
(64, 2, 'staff', 'login', 'Staff logged in.', '2026-09-28 13:48:48'),
(65, 2, 'staff', 'logout', 'Staff logged out.', '2026-09-28 14:02:57'),
(66, 1, 'admin', 'login', 'Admin logged in.', '2026-09-28 14:03:12'),
(67, 1, 'admin', 'logout', 'Admin logged out.', '2026-09-28 15:09:39'),
(68, 1, 'admin', 'login', 'Admin logged in.', '2026-09-28 15:10:34'),
(69, 1, 'admin', 'edit_accommodation', 'Edited accommodation: Umbrella Cottage (8 Pax+) (ID: 6).', '2026-09-28 15:11:09'),
(70, 1, 'admin', 'logout', 'Admin logged out.', '2026-09-28 15:11:13'),
(71, 1, 'admin', 'login', 'Admin logged in.', '2026-09-28 15:11:47'),
(72, 1, 'admin', 'edit_accommodation', 'Edited accommodation: Private Big Kubo - 3PM Onwards (50 Pax+) (ID: 14).', '2026-09-28 15:12:31'),
(73, 1, 'admin', 'logout', 'Admin logged out.', '2026-09-28 15:12:34'),
(74, 1, 'admin', 'login', 'Admin logged in.', '2026-09-28 15:14:08'),
(75, 1, 'admin', 'status_update', 'Booking #33 status updated to awaiting_payment.', '2026-09-28 15:14:13'),
(76, 1, 'admin', 'logout', 'Admin logged out.', '2026-09-28 15:14:48'),
(77, 1, 'admin', 'login', 'Admin logged in.', '2026-09-28 15:25:40'),
(78, 1, 'admin', 'status_update', 'Booking #34 status updated to awaiting_payment.', '2026-09-28 15:25:45'),
(79, 1, 'admin', 'logout', 'Admin logged out.', '2026-09-28 15:26:08'),
(80, 1, 'admin', 'login', 'Admin logged in.', '2026-09-28 15:28:22'),
(81, 1, 'admin', 'status_update', 'Booking #35 status updated to awaiting_payment.', '2026-09-28 15:29:56'),
(82, 1, 'admin', 'logout', 'Admin logged out.', '2026-09-28 15:30:14'),
(83, 1, 'admin', 'login', 'Admin logged in.', '2026-09-28 15:33:03'),
(84, 1, 'admin', 'status_update', 'Booking #36 status updated to awaiting_payment.', '2026-09-28 15:33:06'),
(85, 1, 'admin', 'logout', 'Admin logged out.', '2026-09-28 15:33:17'),
(86, 1, 'admin', 'login', 'Admin logged in.', '2026-09-28 15:45:54'),
(87, 1, 'admin', 'status_update', 'Booking #37 status updated to awaiting_payment.', '2026-09-28 15:45:58'),
(88, 1, 'admin', 'logout', 'Admin logged out.', '2026-09-28 15:46:07'),
(89, 1, 'admin', 'login', 'Admin logged in.', '2026-09-28 15:47:59'),
(90, 1, 'admin', 'status_update', 'Booking #38 status updated to awaiting_payment.', '2026-09-28 15:48:03'),
(91, 1, 'admin', 'logout', 'Admin logged out.', '2026-09-28 15:48:35'),
(92, 2, 'staff', 'login', 'Staff logged in.', '2026-09-28 15:49:22'),
(93, 2, 'staff', 'logout', 'Staff logged out.', '2026-09-28 15:52:31'),
(94, 1, 'admin', 'login', 'Admin logged in.', '2026-09-28 15:52:48'),
(95, 1, 'admin', 'logout', 'Admin logged out.', '2026-09-28 17:07:12'),
(96, 1, 'admin', 'login', 'Admin logged in.', '2026-09-28 17:27:36'),
(97, 1, 'admin', 'edit_accommodation', 'Edited accommodation: Regular Room (ID: 17).', '2026-09-28 17:33:30'),
(98, 1, 'admin', 'logout', 'Admin logged out.', '2026-09-28 17:34:20'),
(99, 1, 'admin', 'login', 'Admin logged in.', '2026-09-28 17:35:10'),
(100, 1, 'admin', 'edit_activity', 'Edited activity: Pickleball Court - Private Rental (Guest/Member) (ID: 15).', '2026-09-28 17:58:39'),
(101, 1, 'admin', 'edit_activity', 'Edited activity: Videoke Rental (ID: 16).', '2026-09-28 17:59:03'),
(102, 1, 'admin', 'edit_accommodation', 'Edited accommodation: Day Tour Kubo Non-Aircon Room (8 Hours) (ID: 2).', '2026-09-28 18:00:02'),
(103, 1, 'admin', 'edit_accommodation', 'Edited accommodation: Day Tour Kubo Non-Aircon Room (8 Hours) (ID: 2).', '2026-09-28 18:00:21'),
(104, 1, 'admin', 'login', 'Admin logged in.', '2026-09-28 18:16:20'),
(105, 1, 'admin', 'edit_accommodation', 'Edited accommodation: Day Tour Kubo Non-Aircon Room (12 Hours) (ID: 3).', '2026-09-28 18:16:50'),
(106, 1, 'admin', 'edit_accommodation', 'Edited accommodation: Day Tour Kubo Aircon Room (8 Hours) (ID: 4).', '2026-09-28 18:17:05'),
(107, 1, 'admin', 'edit_accommodation', 'Edited accommodation: Day Tour Kubo Aircon Room (12 Hours) (ID: 5).', '2026-09-28 18:17:21'),
(108, 1, 'admin', 'edit_accommodation', 'Edited accommodation: Kubo Cottage (15 Pax+) (ID: 8).', '2026-09-28 18:23:17'),
(109, 1, 'admin', 'edit_accommodation', 'Edited accommodation: Umbrella Cottage (8 Pax+) (ID: 6).', '2026-09-28 18:24:38'),
(110, 1, 'admin', 'edit_accommodation', 'Edited accommodation: Small Awning Cottage (10 Pax+) (ID: 7).', '2026-09-28 18:25:13'),
(111, 1, 'admin', 'edit_accommodation', 'Edited accommodation: Kubo Cottage (15 Pax+) (ID: 8).', '2026-09-28 18:25:30'),
(112, 1, 'admin', 'edit_accommodation', 'Edited accommodation: Big Kubo Cottage (20 Pax+) (ID: 9).', '2026-09-28 18:25:57'),
(113, 1, 'admin', 'edit_accommodation', 'Edited accommodation: Pavilion Cottage (25 Pax+) (ID: 10).', '2026-09-28 18:27:16'),
(114, 1, 'admin', 'edit_accommodation', 'Edited accommodation: Private Big Pavilion - Daytime (35 Pax+) (ID: 11).', '2026-09-28 18:27:49'),
(115, 1, 'admin', 'edit_accommodation', 'Edited accommodation: Private Big Pavilion - 3PM Onwards (35 Pax+) (ID: 12).', '2026-09-28 18:28:00'),
(116, 1, 'admin', 'edit_accommodation', 'Edited accommodation: Private Big Kubo - Daytime (50 Pax+) (ID: 13).', '2026-09-28 18:28:23'),
(117, 1, 'admin', 'edit_accommodation', 'Edited accommodation: Private Big Kubo - 3PM Onwards (50 Pax+) (ID: 14).', '2026-09-28 18:28:35'),
(118, 1, 'admin', 'edit_accommodation', 'Edited accommodation: Kubo Room Non-Aircon (Transient) (ID: 15).', '2026-09-28 18:28:55'),
(119, 1, 'admin', 'edit_accommodation', 'Edited accommodation: Kubo Room Aircon (Transient) (ID: 16).', '2026-09-28 18:29:05'),
(120, 1, 'admin', 'edit_accommodation', 'Edited accommodation: Regular Room (ID: 17).', '2026-09-28 18:31:14'),
(121, 1, 'admin', 'edit_accommodation', 'Edited accommodation: VIP Master Bedroom (ID: 19).', '2026-09-28 18:31:35'),
(122, 1, 'admin', 'edit_accommodation', 'Edited accommodation: Regular Room (ID: 17).', '2026-09-28 18:31:57'),
(123, 1, 'admin', 'delete_accommodation', 'Deleted accommodation: VIP ROOM.', '2026-09-28 18:33:18'),
(124, 1, 'admin', 'delete_accommodation', 'Deleted accommodation: Family Private Villa.', '2026-09-28 18:33:37'),
(125, 1, 'admin', 'delete_accommodation', 'Deleted accommodation: Mega Barkada Room.', '2026-09-28 18:33:41'),
(126, 1, 'admin', 'delete_accommodation', 'Deleted accommodation: Mega Family Room.', '2026-09-28 18:33:44'),
(127, 1, 'admin', 'delete_accommodation', 'Deleted accommodation: Regular Barkada Room.', '2026-09-28 18:33:49'),
(128, 1, 'admin', 'toggle_accommodation', 'Changed accommodation (ID: 18) status to Inactive.', '2026-09-28 18:34:01'),
(129, 1, 'admin', 'toggle_accommodation', 'Changed accommodation (ID: 18) status to Active.', '2026-09-28 18:34:03'),
(130, 1, 'admin', 'delete_accommodation', 'Deleted accommodation: Regular Family Room.', '2026-09-28 18:34:11'),
(131, 1, 'admin', 'delete_accommodation', 'Deleted accommodation: Grand Villa Room.', '2026-09-28 18:34:24'),
(132, 1, 'admin', 'edit_accommodation', 'Edited accommodation: Barkada Private Villa (ID: 26).', '2026-09-28 18:35:36'),
(133, 1, 'admin', 'edit_accommodation', 'Edited accommodation: Private Grand Villa (ID: 27).', '2026-09-28 18:35:46'),
(134, 1, 'admin', 'edit_accommodation', 'Edited accommodation: Day Tour Kubo Non-Aircon Room (8 Hours) (ID: 2).', '2026-09-28 18:36:50'),
(135, 1, 'admin', 'edit_accommodation', 'Edited accommodation: Day Tour Kubo Non-Aircon Room (12 Hours) (ID: 3).', '2026-09-28 18:37:03'),
(136, 1, 'admin', 'edit_accommodation', 'Edited accommodation: Day Tour Kubo Aircon Room (8 Hours) (ID: 4).', '2026-09-28 18:37:15'),
(137, 1, 'admin', 'edit_accommodation', 'Edited accommodation: Day Tour Kubo Aircon Room (12 Hours) (ID: 5).', '2026-09-28 18:37:28'),
(138, 1, 'admin', 'edit_accommodation', 'Edited accommodation: Kubo Room Non-Aircon (Transient) (ID: 15).', '2026-09-28 18:37:41'),
(139, 1, 'admin', 'edit_accommodation', 'Edited accommodation: Kubo Room Aircon (Transient) (ID: 16).', '2026-09-28 18:37:49'),
(140, 1, 'admin', 'status_update', 'Booking #15 status updated to cancelled.', '2026-09-28 18:49:22');

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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `role` enum('admin','staff') NOT NULL DEFAULT 'admin',
  `address` varchar(255) DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `gender` varchar(50) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `first_name`, `middle_name`, `last_name`, `suffix`, `email`, `phone_number`, `password`, `is_verified`, `verification_token`, `reset_token`, `reset_token_expiry`, `remember_token`, `token_expiry`, `created_at`, `updated_at`, `role`, `address`, `birthdate`, `gender`, `bio`, `avatar`) VALUES
(1, 'Saiyon', '', 'Madarame', '', 'rhainearia@gmail.com', '09563695447', '$2y$10$ACwsnFAOshGieC504ZKCVeic3OmsGiHfnHCyut6tS8ymY8Jl.mrQa', 0, '696bc6d7cec17df661ed8408600c4bf7c2e081c2f8ff0ab97b7794dab79a97f0', NULL, NULL, NULL, NULL, '2026-03-26 16:56:37', '2026-09-28 09:44:11', 'admin', NULL, NULL, NULL, NULL, NULL),
(2, 'Shion', NULL, 'Madarame', NULL, 'Shionmadarame@gmail.com', '09653765434', '$2y$10$hFjTTnFQptcIFilzpxTAd.01oZ0zkGRzmV.flRkHJ87X2Np/aY7oK', 1, NULL, NULL, NULL, NULL, NULL, '2026-09-15 17:10:16', '2026-09-15 17:10:16', 'staff', NULL, NULL, NULL, NULL, NULL);

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

--
-- Dumping data for table `availability`
--

INSERT INTO `availability` (`id`, `item_type`, `item_id`, `date`, `time_slot`, `available_slots`, `total_slots`, `is_available`, `created_at`, `updated_at`) VALUES
(1, 'activity', 5, '2026-07-09', '00:00:13', 9, 10, 1, '2026-07-09 15:46:38', '2026-07-09 15:46:38');

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
-- Table structure for table `blocked_emails`
--

CREATE TABLE `blocked_emails` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `blocked_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `blocked_emails`
--

INSERT INTO `blocked_emails` (`id`, `email`, `user_id`, `reason`, `blocked_by`, `created_at`) VALUES
(2, 'pranksimithel@gmail.com', 2, 'Blocked by admin', 1, '2026-09-28 18:46:14');

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `booking_reference` varchar(20) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
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
  `booking_source` enum('online','walk_in') NOT NULL DEFAULT 'online',
  `guest_name` varchar(255) DEFAULT NULL,
  `guest_contact` varchar(30) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `payment_proof` varchar(500) DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `booking_reference`, `user_id`, `booking_type`, `activity_id`, `accommodation_id`, `bundle_id`, `status_id`, `full_name`, `email`, `phone_number`, `check_in_date`, `check_in_time`, `check_out_date`, `check_out_time`, `number_of_days`, `number_of_guests`, `special_requests`, `total_amount`, `payment_status`, `payment_method`, `booking_source`, `guest_name`, `guest_contact`, `created_at`, `updated_at`, `payment_proof`, `expires_at`) VALUES
(1, 'REF-2026-0001', 1, 'activity', 1, NULL, NULL, 4, 'Cool Vincent Bonilla', 'bonillacoolvincentc@gmail.com', '09563695447', '2026-07-10', '14:00:00', '2026-07-10', '16:00:00', 1, 4, 'Need 2 extra cue sticks and chalk.', 50.00, 'paid', 'gcash', 'online', NULL, NULL, '2026-07-02 18:27:04', '2026-09-09 14:20:07', NULL, NULL),
(2, 'BK202607098766', 1, 'bundle', NULL, NULL, NULL, 3, 'Cool Vincent Bonilla', 'bonillacoolvincentc@gmail.com', '09563695447', '2026-07-29', NULL, '2026-07-30', NULL, 1, 2, '', 20999.00, 'pending', 'gcash', 'online', NULL, NULL, '2026-07-09 15:24:27', '2026-09-22 16:41:24', 'assets/uploads/payment_proofs/proof_1_1783610667.jfif', NULL),
(3, 'BK202607092423', 1, 'activity', NULL, NULL, NULL, 3, 'Cool Vincent Bonilla', 'bonillacoolvincentc@gmail.com', '09563695447', '2026-07-09', '13:00:00', NULL, '17:00:00', 1, 1, '', 180.00, 'pending', 'gcash', 'online', NULL, NULL, '2026-07-09 15:46:38', '2026-09-09 12:03:34', NULL, NULL),
(4, 'BK202607091287', 1, 'activity', NULL, NULL, NULL, 3, 'Cool Vincent Bonilla', 'bonillacoolvincentc@gmail.com', '09563695447', '2026-07-09', '13:49:00', NULL, '17:49:00', 1, 30, '', 2800.00, 'pending', 'gcash', 'online', NULL, NULL, '2026-07-09 16:16:08', '2026-09-09 12:03:29', 'assets/uploads/payment_proofs/proof_1_1783613768.png', NULL),
(5, 'BK202607105542', 1, 'bundle', NULL, NULL, 5, 3, 'Cool Vincent Bonilla', 'bonillacoolvincentc@gmail.com', '09563695447', '2026-07-21', NULL, '2026-07-22', NULL, 1, 2, '', 39999.00, 'pending', 'gcash', 'online', NULL, NULL, '2026-07-10 01:25:45', '2026-09-22 16:41:24', 'assets/uploads/payment_proofs/proof_1_1783646745.jpg', NULL),
(6, 'BK202607109394', 1, 'bundle', NULL, NULL, NULL, 3, 'Cool Vincent Bonilla', 'bonillacoolvincentc@gmail.com', '09563695447', '2026-07-20', NULL, '2026-07-21', NULL, 1, 20, '', 69999.00, 'pending', 'gcash', 'online', NULL, NULL, '2026-07-10 02:52:53', '2026-09-22 16:41:24', 'assets/uploads/payment_proofs/proof_1_1783651973.jpg', NULL),
(7, 'BK202607109533', 1, 'bundle', NULL, NULL, NULL, 3, 'Cool Vincent Bonilla', 'bonillacoolvincentc@gmail.com', '09563695447', '2026-07-30', NULL, '2026-07-31', NULL, 1, 2, '', 18499.00, 'pending', 'gcash', 'online', NULL, NULL, '2026-07-10 02:54:52', '2026-09-22 16:41:24', 'assets/uploads/payment_proofs/proof_1_1783652092.jpg', NULL),
(8, 'BK202607103601', 1, 'activity', 16, NULL, NULL, 3, 'Cool Vincent Bonilla', 'bonillacoolvincentc@gmail.com', '09563695447', '2026-07-10', '07:58:00', NULL, '12:00:00', 1, 5, '', 3025.00, 'pending', 'gcash', 'online', NULL, NULL, '2026-07-10 02:59:56', '2026-09-09 12:02:55', 'assets/uploads/payment_proofs/proof_1_1783652396.jpg', NULL),
(9, 'BK202609112348', 1, 'bundle', NULL, NULL, 5, 4, 'Cool Vincent Bonilla', 'bonillacoolvincentc@gmail.com', '09563695447', '2026-09-14', '00:00:00', '2026-09-15', '00:00:00', 1, 1, '', 39999.00, 'pending', 'gcash', 'online', NULL, NULL, '2026-09-11 01:00:24', '2026-09-22 18:11:55', 'assets/uploads/payment_proofs/proof_1_1789088424.jpg', NULL),
(10, 'BK202609113914', 1, 'activity', 11, NULL, NULL, 4, 'Cool Vincent Bonilla', 'bonillacoolvincentc@gmail.com', '09563695447', '2026-09-12', '09:30:00', NULL, '10:30:00', 1, 2, 'Test', 1000.00, 'pending', 'gcash', 'online', NULL, NULL, '2026-09-11 01:32:02', '2026-09-14 15:02:05', 'assets/uploads/payment_proofs/proof_1_1789090322.webp', NULL),
(11, 'BK202609236318', 1, 'accommodation', NULL, 18, NULL, 3, 'Cool Vincent Bonilla', 'bonillacoolvincentc@gmail.com', '09563695447', '2026-09-23', NULL, '2026-09-25', NULL, 2, 4, '[Add-on Activities]: Billiards, Basketball Court - Flood Light Rental (Guest), Pickleball Court - Private Rental (Guest/Member), Videoke Rental', 10698.00, 'pending', 'gcash', 'online', NULL, NULL, '2026-09-23 02:44:36', '2026-09-23 02:50:58', 'assets/uploads/payment_proofs/proof_1_1790131476.webp', NULL),
(12, 'BK202609238215', 1, 'accommodation', NULL, 27, NULL, 3, 'Cool Vincent Bonilla', 'bonillacoolvincentc@gmail.com', '09563695447', '2026-09-24', NULL, '2026-09-30', NULL, 6, 75, '[Add-on Activities]: Billiards, Basketball Court - Flood Light Rental (Guest), Pickleball Court - Private Rental (Guest/Member), Videoke Rental', 297194.00, 'pending', 'gcash', 'online', NULL, NULL, '2026-09-23 06:55:13', '2026-09-23 07:00:02', 'assets/uploads/payment_proofs/proof_1_1790146513.webp', NULL),
(13, 'BK202609264537', 1, 'accommodation', NULL, 9, NULL, 3, 'Cool Vincent Bonilla', 'bonillacoolvincentc@gmail.com', '09563695447', '2026-09-30', NULL, NULL, NULL, 1, 1, '[Add-on Activities]: Billiards, ATV Short Ride - Direct Bolo Beach Tour (Qty: 1), ATV Long Ride - Mountain and Bolo Beach Tour (Qty: 1), Basketball Court - Flood Light Rental (Guest), Pickleball Court - Private Rental (Guest/Member), Videoke Rental', 7700.00, 'pending', 'gcash', 'online', NULL, NULL, '2026-09-26 15:04:08', '2026-09-27 06:16:30', 'assets/uploads/payment_proofs/proof_1_1790435048.webp', NULL),
(14, 'BK202609260386', 1, 'accommodation', NULL, 9, NULL, 3, 'Cool Vincent Bonilla', 'bonillacoolvincentc@gmail.com', '09563695447', '2026-09-30', NULL, NULL, NULL, 1, 1, '[Add-on Activities]: ATV Long Ride - Mountain and Bolo Beach Tour (Qty: 1)', 4500.00, 'pending', 'gcash', 'online', NULL, NULL, '2026-09-26 15:10:15', '2026-09-27 06:16:15', 'assets/uploads/payment_proofs/proof_1_1790435415.webp', NULL),
(15, 'BK202609265181', 1, 'accommodation', NULL, 5, NULL, 5, 'Cool Vincent Bonilla', 'bonillacoolvincentc@gmail.com', '09563695447', '2026-09-30', NULL, NULL, NULL, 1, 2, '[Add-on Activities]: ATV Long Ride - Mountain and Bolo Beach Tour (Qty: 1)', 4499.00, 'pending', 'gcash', 'online', NULL, NULL, '2026-09-26 15:10:50', '2026-09-28 18:49:22', 'assets/uploads/payment_proofs/proof_1_1790435450.webp', NULL),
(16, 'BK202609276217', 1, 'accommodation', NULL, 9, NULL, 5, 'Cool Vincent Bonilla', 'bonillacoolvincentc@gmail.com', '09563695447', '2026-09-28', NULL, NULL, NULL, 1, 1, '[Add-on Activities]: Billiards, Basketball Court - Flood Light Rental (Guest), Pickleball Court - Private Rental (Guest/Member), Videoke Rental', 3200.00, 'pending', 'gcash', 'online', NULL, NULL, '2026-09-27 08:39:53', '2026-09-27 12:58:07', 'assets/uploads/payment_proofs/proof_1_1790498393.webp', NULL),
(17, 'BK202609270968', 1, 'accommodation', NULL, 5, NULL, 2, 'Cool Vincent Bonilla', 'bonillacoolvincentc@gmail.com', '09563695447', '2026-09-28', NULL, NULL, NULL, 1, 1, '', 1499.00, 'paid', 'gcash', 'online', NULL, NULL, '2026-09-27 10:20:32', '2026-09-27 12:57:43', 'assets/uploads/payment_proofs/receipt_17_1790511028.jpg', NULL),
(18, 'BK202609274905', 1, 'accommodation', NULL, 26, NULL, 5, 'Cool Vincent Bonilla', 'bonillacoolvincentc@gmail.com', '09563695447', '2026-10-01', NULL, '2026-10-03', NULL, 2, 40, '', 57998.00, 'pending', 'gcash', 'online', NULL, NULL, '2026-09-27 13:06:48', '2026-09-27 15:10:01', NULL, '2026-09-27 11:07:46'),
(19, 'BK202609272922', 1, 'accommodation', NULL, 9, NULL, 3, 'Cool Vincent Bonilla', 'bonillacoolvincentc@gmail.com', '09563695447', '2026-09-28', NULL, NULL, NULL, 1, 20, '', 1500.00, 'pending', 'gcash', 'online', NULL, NULL, '2026-09-27 13:29:49', '2026-09-27 13:31:45', NULL, NULL),
(20, 'BK202609272922-EXT1', 1, 'accommodation', NULL, 9, NULL, 3, 'Cool Vincent Bonilla', 'bonillacoolvincentc@gmail.com', '09563695447', '2026-09-29', NULL, '2026-09-30', NULL, 1, 20, 'Extra Room (Part of Booking BK202609272922)', 0.00, 'pending', 'gcash', 'online', NULL, NULL, '2026-09-27 13:29:49', '2026-09-27 13:32:03', NULL, NULL),
(21, 'BK202609272562', 1, 'accommodation', NULL, 9, NULL, 5, 'Cool Vincent Bonilla', 'bonillacoolvincentc@gmail.com', '09563695447', '2026-09-30', NULL, NULL, NULL, 1, 1, '', 1500.00, 'pending', 'gcash', 'online', NULL, NULL, '2026-09-27 13:54:17', '2026-09-28 00:56:54', NULL, '2026-09-27 11:54:40'),
(22, 'BK202609274061', 1, 'accommodation', NULL, 9, NULL, 3, 'Cool Vincent Bonilla', 'bonillacoolvincentc@gmail.com', '09563695447', '2026-09-30', NULL, NULL, NULL, 1, 20, '', 1500.00, 'pending', 'gcash', 'online', NULL, NULL, '2026-09-27 14:03:01', '2026-09-27 14:04:44', NULL, NULL),
(23, 'BK202609274061-EXT1', 1, 'accommodation', NULL, 9, NULL, 3, 'Cool Vincent Bonilla', 'bonillacoolvincentc@gmail.com', '09563695447', '2026-09-30', NULL, '2026-10-01', NULL, 1, 20, 'Extra Room (Part of Booking BK202609274061)', 0.00, 'pending', 'gcash', 'online', NULL, NULL, '2026-09-27 14:03:01', '2026-09-27 14:04:54', NULL, NULL),
(24, 'BK202609274061-EXT2', 1, 'accommodation', NULL, 9, NULL, 3, 'Cool Vincent Bonilla', 'bonillacoolvincentc@gmail.com', '09563695447', '2026-09-30', NULL, '2026-10-01', NULL, 1, 20, 'Extra Room (Part of Booking BK202609274061)', 0.00, 'pending', 'gcash', 'online', NULL, NULL, '2026-09-27 14:03:01', '2026-09-27 14:05:02', NULL, NULL),
(25, 'BK202609274061-EXT3', 1, 'accommodation', NULL, 9, NULL, 3, 'Cool Vincent Bonilla', 'bonillacoolvincentc@gmail.com', '09563695447', '2026-09-30', NULL, '2026-10-01', NULL, 1, 20, 'Extra Room (Part of Booking BK202609274061)', 0.00, 'pending', 'gcash', 'online', NULL, NULL, '2026-09-27 14:03:01', '2026-09-27 14:05:11', NULL, NULL),
(26, 'BK202609274061-EXT4', 1, 'accommodation', NULL, 9, NULL, 3, 'Cool Vincent Bonilla', 'bonillacoolvincentc@gmail.com', '09563695447', '2026-09-30', NULL, '2026-10-01', NULL, 1, 20, 'Extra Room (Part of Booking BK202609274061)', 0.00, 'pending', 'gcash', 'online', NULL, NULL, '2026-09-27 14:03:01', '2026-09-27 14:05:26', NULL, NULL),
(27, 'WI-2026-21E5CC', NULL, 'accommodation', NULL, 5, NULL, 6, 'Feliciano Nando', '', '09952544624', '2026-09-28', '08:00:00', '2026-09-28', '19:00:00', 0, 1, NULL, 1499.00, 'paid', 'cash', 'walk_in', 'Feliciano Nando', '09952544624', '2026-09-28 00:56:50', '2026-09-28 00:56:50', NULL, NULL),
(28, 'BK202609281581', 1, 'accommodation', NULL, 5, NULL, 2, 'Cool Vincent Bonilla', 'bonillacoolvincentc@gmail.com', '09563695447', '2026-09-29', NULL, NULL, NULL, 1, 2, '', 1499.00, 'paid', 'gcash', 'online', NULL, NULL, '2026-09-28 04:55:48', '2026-09-28 04:57:08', NULL, NULL),
(29, 'BK202609281581-EXT1', 1, 'accommodation', NULL, 9, NULL, 2, 'Cool Vincent Bonilla', 'bonillacoolvincentc@gmail.com', '09563695447', '2026-09-29', NULL, '2026-09-30', NULL, 1, 20, 'Extra Room (Part of Booking BK202609281581)', 0.00, 'paid', 'gcash', 'online', NULL, NULL, '2026-09-28 04:55:48', '2026-09-28 05:01:20', 'assets/uploads/payment_proofs/receipt_29_1790571616.jpg', NULL),
(30, 'BK202609287727', 1, 'accommodation', NULL, 18, NULL, 5, 'Cool Vincent Bonilla', 'bonillacoolvincentc@gmail.com', '09563695447', '2026-10-01', NULL, '2026-10-31', NULL, 30, 4, '', 134970.00, 'pending', 'gcash', 'online', NULL, NULL, '2026-09-28 05:18:56', '2026-09-28 05:26:07', 'assets/uploads/payment_proofs/receipt_30_1790573025.jpg', '2026-09-28 03:19:26'),
(31, 'BK202609284552', 1, 'accommodation', NULL, 5, NULL, 5, 'Cool Vincent Bonilla', 'bonillacoolvincentc@gmail.com', '09563695447', '2026-10-01', NULL, NULL, NULL, 1, 2, '[Additional Rooms]:\n- Day Tour Kubo Aircon Room (8 Hours) (Guests: 2, Dates: 2026-10-01 to 2026-10-02, Price: ₱999.00)', 2498.00, 'pending', 'gcash', 'online', NULL, NULL, '2026-09-28 11:03:30', '2026-09-28 13:08:55', NULL, '2026-09-28 09:06:47'),
(32, 'BK202609282864', 1, 'accommodation', NULL, 3, NULL, 2, 'Cool Vincent Bonilla', 'bonillacoolvincentc@gmail.com', '09563695447', '2026-09-30', '09:00:00', NULL, '21:00:00', 1, 4, '[Add-on Activities]: Billiards, Basketball Court - Flood Light Rental (Guest), Pickleball Court - Private Rental (Guest/Member), Videoke Rental', 3199.00, 'paid', 'gcash', 'online', NULL, NULL, '2026-09-28 13:41:23', '2026-09-28 13:47:31', 'assets/uploads/payment_proofs/receipt_32_1790603120.jpg', NULL),
(33, 'BK202609282911', 1, 'accommodation', NULL, 6, NULL, 5, 'Cool Vincent Bonilla', 'bonillacoolvincentc@gmail.com', '09563695447', '2026-09-29', NULL, NULL, NULL, 1, 8, '[Additional Rooms]:\n- Private Big Kubo - 3PM Onwards (50 Pax+) (Guests: 8, Dates: 2026-09-29 to 2026-09-30, Price: ₱4,000.00)', 4500.00, 'pending', 'gcash', 'online', NULL, NULL, '2026-09-28 15:13:55', '2026-09-28 17:18:46', NULL, '2026-09-28 13:14:13'),
(34, 'BK202609280473', 1, 'accommodation', NULL, 6, NULL, 5, 'Cool Vincent Bonilla', 'bonillacoolvincentc@gmail.com', '09563695447', '2026-09-30', NULL, NULL, NULL, 1, 1, '[Additional Rooms]:\n- Private Big Kubo - 3PM Onwards (50 Pax+) (Guests: 50, Dates: 2026-09-30 to 2026-10-01, Price: ₱4,000.00)', 4500.00, 'pending', 'gcash', 'online', NULL, NULL, '2026-09-28 15:25:29', '2026-09-28 17:26:06', NULL, '2026-09-28 13:25:45'),
(35, 'BK202609285766', 1, 'accommodation', NULL, 9, NULL, 5, 'Cool Vincent Bonilla', 'bonillacoolvincentc@gmail.com', '09563695447', '2026-09-30', NULL, NULL, NULL, 1, 1, '[Additional Rooms]:\n- Day Tour Kubo Aircon Room (12 Hours) (Guests: 1, Dates: 2026-09-30 to 2026-10-01, Price: ₱1,499.00)', 2999.00, 'pending', 'gcash', 'online', NULL, NULL, '2026-09-28 15:28:01', '2026-09-28 17:33:37', NULL, '2026-09-28 13:29:56'),
(36, 'BK202609284455', 1, 'accommodation', NULL, 5, NULL, 5, 'Cool Vincent Bonilla', 'bonillacoolvincentc@gmail.com', '09563695447', '2026-10-08', NULL, NULL, NULL, 1, 6, '[Additional Rooms]:\n- Big Kubo Cottage (20 Pax+) (Guests: 6, Dates: 2026-10-08 to 2026-10-09, Price: ₱1,500.00)', 3999.00, 'pending', 'gcash', 'online', NULL, NULL, '2026-09-28 15:32:50', '2026-09-28 17:33:37', NULL, '2026-09-28 13:33:06'),
(37, 'BK202609280338', 1, 'accommodation', NULL, 9, NULL, 5, 'Cool Vincent Bonilla', 'bonillacoolvincentc@gmail.com', '09563695447', '2026-10-15', NULL, NULL, NULL, 1, 20, '[Additional Rooms]:\n- Day Tour Kubo Aircon Room (12 Hours) (Guests: 20, Date: 2026-10-15, Price: ₱5,999.00)', 7499.00, 'pending', 'gcash', 'online', NULL, NULL, '2026-09-28 15:45:43', '2026-09-28 18:49:12', NULL, '2026-09-28 13:45:58'),
(38, 'BK202609286277', 1, 'accommodation', NULL, 26, NULL, 5, 'Cool Vincent Bonilla', 'bonillacoolvincentc@gmail.com', '09563695447', '2026-10-08', NULL, '2026-10-10', NULL, 2, 40, '[Additional Rooms]:\n- Family Private Villa (Guests: 40, Dates: 2026-10-08 to 2026-10-10, Price: ₱30,249.00)', 88247.00, 'pending', 'gcash', 'online', NULL, NULL, '2026-09-28 15:47:42', '2026-09-28 18:49:12', NULL, '2026-09-28 13:48:03'),
(39, 'WI-2026-2456C8', NULL, 'accommodation', NULL, 3, NULL, 6, 'Joshua Fernandez', '', '09876475647', '2026-10-30', '07:00:00', '2026-10-30', '19:00:00', 0, 6, '[Additional Rooms]:\n- Kubo Cottage (15 Pax+) (Guests: 15, Price: ₱1,200.00)', 1200.00, 'paid', 'cash', 'walk_in', 'Joshua Fernandez', '09876475647', '2026-09-28 16:00:50', '2026-09-28 16:00:50', NULL, NULL),
(40, 'WI-2026-23032E', NULL, 'accommodation', NULL, 2, NULL, 6, 'Joshua Fernandez', '', '09875468763', '2026-10-30', '08:00:00', '2026-10-30', '16:00:00', 0, 2, 'Extra Cusion\n\n[Additional Rooms]:\n- Kubo Cottage (15 Pax+) (Guests: 15, Price: ₱1,200.00)', 1200.00, 'paid', 'cash', 'walk_in', 'Joshua Fernandez', '09875468763', '2026-09-28 16:07:14', '2026-09-28 16:07:14', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `booking_extra_rooms`
--

CREATE TABLE `booking_extra_rooms` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `accommodation_id` int(11) NOT NULL,
  `num_guests` int(11) NOT NULL DEFAULT 1,
  `check_in_date` date NOT NULL,
  `check_out_date` date DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `booking_extra_rooms`
--

INSERT INTO `booking_extra_rooms` (`id`, `booking_id`, `accommodation_id`, `num_guests`, `check_in_date`, `check_out_date`, `price`, `created_at`) VALUES
(1, 34, 14, 50, '2026-09-30', '2026-10-01', 4000.00, '2026-09-28 15:25:29'),
(2, 35, 5, 1, '2026-09-30', '2026-10-01', 1499.00, '2026-09-28 15:28:01'),
(3, 36, 9, 6, '2026-10-08', '2026-10-09', 1500.00, '2026-09-28 15:32:50'),
(4, 37, 5, 20, '2026-10-15', NULL, 5999.00, '2026-09-28 15:45:43'),
(5, 38, 25, 40, '2026-10-08', '2026-10-10', 30249.00, '2026-09-28 15:47:42'),
(6, 39, 8, 15, '2026-10-30', '2026-10-30', 1200.00, '2026-09-28 16:00:50'),
(7, 40, 8, 15, '2026-10-30', '2026-10-30', 1200.00, '2026-09-28 16:07:14');

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

--
-- Dumping data for table `booking_logs`
--

INSERT INTO `booking_logs` (`id`, `booking_id`, `old_status_id`, `new_status_id`, `changed_by`, `changed_by_id`, `notes`, `created_at`) VALUES
(1, 1, 1, 2, 'admin', 1, 'Booking approved by admin', '2026-07-07 16:04:43'),
(2, 2, NULL, 1, 'user', 1, 'Booking created with GCash payment proof', '2026-07-09 15:24:27'),
(3, 3, NULL, 1, 'user', 1, 'Booking created', '2026-07-09 15:46:38'),
(4, 4, NULL, 1, 'user', 1, 'Booking created with GCash payment proof', '2026-07-09 16:16:08'),
(5, 5, NULL, 1, 'user', 1, 'Booking created with GCash payment proof', '2026-07-10 01:25:46'),
(6, 6, NULL, 1, 'user', 1, 'Booking created with GCash payment proof', '2026-07-10 02:52:53'),
(7, 7, NULL, 1, 'user', 1, 'Booking created with GCash payment proof', '2026-07-10 02:54:52'),
(8, 8, NULL, 1, 'user', 1, 'Booking created with GCash payment proof', '2026-07-10 02:59:56'),
(9, 8, 1, 3, 'admin', 1, 'No gcash proof', '2026-09-09 12:02:55'),
(10, 7, 1, 3, 'admin', 1, 'No gcash proof', '2026-09-09 12:03:15'),
(11, 6, 1, 3, 'admin', 1, 'No gcash proof', '2026-09-09 12:03:21'),
(12, 5, 1, 3, 'admin', 1, 'No gcash proof', '2026-09-09 12:03:26'),
(13, 4, 1, 3, 'admin', 1, 'No gcash proof', '2026-09-09 12:03:29'),
(14, 3, 1, 3, 'admin', 1, 'No gcash proof', '2026-09-09 12:03:34'),
(15, 2, 1, 3, 'admin', 1, 'No gcash proof', '2026-09-09 12:03:39'),
(16, 9, NULL, 1, 'user', 1, 'Booking created with GCash payment proof', '2026-09-11 01:00:24'),
(17, 10, NULL, 1, 'user', 1, 'Booking created with GCash payment proof', '2026-09-11 01:32:02'),
(18, 10, 1, 2, 'admin', 1, 'Booking approved by admin', '2026-09-11 01:50:05'),
(19, 9, 1, 2, 'admin', 1, 'Booking approved by admin', '2026-09-22 18:11:48'),
(20, 11, NULL, 1, 'user', 1, 'Booking created with GCash payment proof', '2026-09-23 02:44:36'),
(21, 11, 1, 3, 'admin', 1, 'Not enough', '2026-09-23 02:50:58'),
(22, 12, NULL, 1, 'user', 1, 'Booking created with GCash payment proof', '2026-09-23 06:55:13'),
(23, 12, 1, 3, 'admin', 1, 'rejected', '2026-09-23 07:00:02'),
(24, 13, NULL, 1, 'user', 1, 'Booking created with GCash payment proof', '2026-09-26 15:04:08'),
(25, 14, NULL, 1, 'user', 1, 'Booking created with GCash payment proof', '2026-09-26 15:10:15'),
(26, 15, NULL, 1, 'user', 1, 'Booking created with GCash payment proof', '2026-09-26 15:10:50'),
(27, 14, 1, 3, 'admin', 1, 'Kulang ang bayad', '2026-09-27 06:16:15'),
(28, 13, 1, 3, 'admin', 1, 'Kulang ang bayad', '2026-09-27 06:16:30'),
(29, 15, 1, 2, 'admin', 1, 'Booking approved by admin', '2026-09-27 07:50:44'),
(30, 16, NULL, 1, 'user', 1, 'Booking created with GCash payment proof', '2026-09-27 08:39:53'),
(31, 16, 1, 2, 'admin', 1, 'Booking approved by admin', '2026-09-27 08:40:22'),
(32, 17, NULL, 1, 'user', 1, 'Booking request submitted (pending admin review)', '2026-09-27 10:20:32'),
(33, 17, 1, 7, 'admin', 1, 'Approved by admin — Awaiting guest payment (2hr window)', '2026-09-27 10:22:49'),
(34, 17, 7, 8, 'user', 1, 'Payment receipt submitted by guest for verification', '2026-09-27 12:10:28'),
(35, 17, 8, 2, 'admin', 1, 'Payment verified and approved by admin', '2026-09-27 12:57:43'),
(36, 16, 2, 5, 'admin', 1, 'Cancelled by admin', '2026-09-27 12:58:07'),
(37, 18, NULL, 1, 'user', 1, 'Booking request submitted (pending admin review)', '2026-09-27 13:06:48'),
(38, 18, 1, 7, 'admin', 1, 'Approved by admin — Awaiting guest payment (2hr window)', '2026-09-27 13:07:46'),
(39, 19, NULL, 1, 'user', 1, 'Booking request submitted (pending admin review)', '2026-09-27 13:29:49'),
(40, 19, 1, 3, 'admin', 1, 'Not enogh', '2026-09-27 13:31:45'),
(41, 20, 1, 3, 'admin', 1, 'Full slot,  you\'re too late', '2026-09-27 13:32:03'),
(42, 21, NULL, 1, 'user', 1, 'Booking request submitted (pending admin review)', '2026-09-27 13:54:17'),
(43, 21, 1, 7, 'admin', 1, 'Approved by admin — Awaiting guest payment (2hr window)', '2026-09-27 13:54:40'),
(44, 22, NULL, 1, 'user', 1, 'Booking request submitted (pending admin review)', '2026-09-27 14:03:01'),
(45, 22, 1, 3, 'admin', 1, 'sdsd', '2026-09-27 14:04:44'),
(46, 23, 1, 3, 'admin', 1, 'fsdfsd', '2026-09-27 14:04:54'),
(47, 24, 1, 3, 'admin', 1, 'fsdfds', '2026-09-27 14:05:02'),
(48, 25, 1, 3, 'admin', 1, 'fsdfs', '2026-09-27 14:05:11'),
(49, 26, 1, 3, 'admin', 1, 'sdasdasdasdas', '2026-09-27 14:05:26'),
(50, 18, 7, 5, 'system', NULL, 'Auto-cancelled: 2-hour payment window expired', '2026-09-27 15:10:01'),
(51, 27, NULL, 6, 'admin', 1, 'Walk-in reservation created by admin.', '2026-09-28 00:56:50'),
(52, 21, 7, 5, 'system', NULL, 'Auto-cancelled: 2-hour payment window expired', '2026-09-28 00:56:54'),
(53, 28, NULL, 1, 'user', 1, 'Booking request submitted (pending admin review)', '2026-09-28 04:55:48'),
(54, 29, 1, 7, 'admin', 1, 'Approved by admin — Awaiting guest payment (2hr window)', '2026-09-28 04:57:00'),
(55, 28, 1, 7, 'admin', 1, 'Approved by admin — Awaiting guest payment (2hr window)', '2026-09-28 04:57:05'),
(56, 28, 7, 2, 'admin', 1, 'Booking approved by admin', '2026-09-28 04:57:08'),
(57, 29, 7, 8, 'user', 1, 'Payment receipt submitted by guest for verification', '2026-09-28 05:00:16'),
(58, 29, 8, 2, 'admin', 1, 'Payment verified and approved by admin', '2026-09-28 05:01:20'),
(59, 30, NULL, 1, 'user', 1, 'Booking request submitted (pending admin review)', '2026-09-28 05:18:56'),
(60, 30, 1, 7, 'admin', 1, 'Approved by admin — Awaiting guest payment (2hr window)', '2026-09-28 05:19:26'),
(61, 30, 7, 8, 'user', 1, 'Payment receipt submitted by guest for verification', '2026-09-28 05:23:45'),
(62, 30, 8, 5, 'admin', 1, 'kulang', '2026-09-28 05:26:07'),
(63, 31, NULL, 1, 'user', 1, 'Booking request submitted (pending admin review)', '2026-09-28 11:03:30'),
(64, 31, 1, 7, 'admin', 1, 'Approved by admin — Awaiting guest payment (2hr window)', '2026-09-28 11:06:47'),
(65, 31, 7, 5, 'system', NULL, 'Auto-cancelled: 2-hour payment window expired', '2026-09-28 13:08:55'),
(66, 32, NULL, 1, 'user', 1, 'Booking request submitted (pending admin review)', '2026-09-28 13:41:23'),
(67, 32, 1, 7, 'admin', 1, 'Approved by admin — Awaiting guest payment (2hr window)', '2026-09-28 13:41:50'),
(68, 32, 7, 8, 'user', 1, 'Payment receipt submitted by guest for verification', '2026-09-28 13:45:20'),
(69, 32, 8, 2, 'admin', 1, 'Payment verified and approved by admin', '2026-09-28 13:47:31'),
(70, 33, NULL, 1, 'user', 1, 'Booking request submitted (pending admin review)', '2026-09-28 15:13:55'),
(71, 33, 1, 7, 'admin', 1, 'Approved by admin — Awaiting guest payment (2hr window)', '2026-09-28 15:14:13'),
(72, 34, NULL, 1, 'user', 1, 'Booking request submitted (pending admin review)', '2026-09-28 15:25:29'),
(73, 34, 1, 7, 'admin', 1, 'Approved by admin — Awaiting guest payment (2hr window)', '2026-09-28 15:25:45'),
(74, 35, NULL, 1, 'user', 1, 'Booking request submitted (pending admin review)', '2026-09-28 15:28:01'),
(75, 35, 1, 7, 'admin', 1, 'Approved by admin — Awaiting guest payment (2hr window)', '2026-09-28 15:29:56'),
(76, 36, NULL, 1, 'user', 1, 'Booking request submitted (pending admin review)', '2026-09-28 15:32:50'),
(77, 36, 1, 7, 'admin', 1, 'Approved by admin — Awaiting guest payment (2hr window)', '2026-09-28 15:33:06'),
(78, 37, NULL, 1, 'user', 1, 'Booking request submitted (pending admin review)', '2026-09-28 15:45:43'),
(79, 37, 1, 7, 'admin', 1, 'Approved by admin — Awaiting guest payment (2hr window)', '2026-09-28 15:45:58'),
(80, 38, NULL, 1, 'user', 1, 'Booking request submitted (pending admin review)', '2026-09-28 15:47:42'),
(81, 38, 1, 7, 'admin', 1, 'Approved by admin — Awaiting guest payment (2hr window)', '2026-09-28 15:48:03'),
(82, 39, NULL, 6, 'admin', 1, 'Walk-in reservation created by admin.', '2026-09-28 16:00:50'),
(83, 40, NULL, 6, 'admin', 1, 'Walk-in reservation created by admin.', '2026-09-28 16:07:14'),
(84, 33, 7, 5, 'system', NULL, 'Auto-cancelled: 2-hour payment window expired', '2026-09-28 17:18:46'),
(85, 34, 7, 5, 'system', NULL, 'Auto-cancelled: 2-hour payment window expired', '2026-09-28 17:26:06'),
(86, 35, 7, 5, 'system', NULL, 'Auto-cancelled: 2-hour payment window expired', '2026-09-28 17:33:37'),
(87, 36, 7, 5, 'system', NULL, 'Auto-cancelled: 2-hour payment window expired', '2026-09-28 17:33:37'),
(88, 37, 7, 5, 'system', NULL, 'Auto-cancelled: 2-hour payment window expired', '2026-09-28 18:49:12'),
(89, 38, 7, 5, 'system', NULL, 'Auto-cancelled: 2-hour payment window expired', '2026-09-28 18:49:12'),
(90, 15, 2, 5, 'admin', 1, 'gg', '2026-09-28 18:49:22');

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
(5, 'cancelled', 'Booking has been cancelled', '#64748b', 1, '2026-05-29 02:03:38'),
(6, 'checked_in', 'Guest has checked in (walk-in reservation)', '#6366f1', 1, '2026-09-01 14:23:21'),
(7, 'awaiting_payment', 'Awaiting payment confirmation within 2 hours', '#f59e0b', 1, '2026-09-27 09:21:21'),
(8, 'payment_verifying', 'Payment receipt uploaded and awaiting admin verification', '#8b5cf6', 1, '2026-09-27 09:21:21');

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
  `daily_booking_limit` int(11) NOT NULL DEFAULT 10,
  `start_time` time DEFAULT NULL,
  `duration_hours` int(11) DEFAULT NULL,
  `bundle_type` varchar(20) DEFAULT 'overnight',
  `amenities` text DEFAULT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `check_in_time` time DEFAULT '14:00:00',
  `check_out_time` time DEFAULT '12:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bundles`
--

INSERT INTO `bundles` (`id`, `name`, `slug`, `description`, `price`, `capacity`, `daily_booking_limit`, `start_time`, `duration_hours`, `bundle_type`, `amenities`, `image_url`, `is_active`, `created_at`, `updated_at`, `check_in_time`, `check_out_time`) VALUES
(4, 'Daytour Private Resort 2 - 6HRS Daytime (Unli Pax)', 'daytour-private-resort-2-6hrs-daytime-unli-pax-', 'Daytour Private Resort 2 Rental (UNLIPAX). For exclusive use only. 6 Hours daytime. Unli Pax guest, cut-off 9PM. With 4 Cogon Kubo Rooms for celebrant. Includes: Resort 5,000sqm lot area, Private Kubo, 2 Rooms Kubo, Private Pavillion, Pavillion Area, Whole Basketball Court, Big Pool, Big Stage, Videoke Area, Table and Chairs, Kids Playground, CR/Shower/Hot Shower, Projector Big Screen, Free Parking. All payments non-refundable. VAT excluded.', 29999.00, 100, 1, '08:00:00', 6, 'day_tour', 'Resort 5000sqm Lot, Private Kubo, 2 Rooms Kubo, Private Pavillion, Pavillion, Whole Basketball Court, Big Pool, Big Stage, Videoke, Table and Chairs, Kids Playground, CR/Shower/Hot Shower, Projector Big Screen, Free Parking, 4 Cogon Kubo Rooms', NULL, 1, '2026-07-08 15:59:21', '2026-09-28 10:06:47', '14:00:00', '12:00:00'),
(5, 'Daytour Private Resort 2 - 6HRS 3PM Onwards (Unli Pax)', 'daytour-private-resort-2-6hrs-3pm-onwards-unli-pax-', 'Daytour Private Resort 2 Rental (UNLIPAX). For exclusive use only. 6 Hours from 3PM onwards. Unli Pax guest, cut-off 9PM. Same inclusions as daytime package. All payments non-refundable. VAT excluded.', 39999.00, 200, 1, '15:00:01', 6, 'day_tour', 'Resort 5000sqm Lot, Private Kubo, 2 Rooms Kubo, Private Pavillion, Pavillion, Whole Basketball Court, Big Pool, Big Stage, Videoke, Table and Chairs, Kids Playground, CR/Shower/Hot Shower, Projector Big Screen, Free Parking, 4 Cogon Kubo Rooms', NULL, 1, '2026-07-08 15:59:21', '2026-09-28 10:06:59', '14:00:00', '12:00:00'),
(6, 'Private Resort 2 - 10HRS Full Day', 'private-resort-2-10hrs-full-day', 'Daytour Private Resort 2 Rental (UNLIPAX). For exclusive use only. 10 Hours full day rental. Unli Pax guest, cut-off 9PM. Same inclusions as other packages. All payments non-refundable. VAT excluded.', 49999.00, 200, 1, '09:00:22', 10, 'day_tour', 'Resort 5000sqm Lot, Private Kubo, 2 Rooms Kubo, Private Pavillion, Pavillion, Whole Basketball Court, Big Pool, Big Stage, Videoke, Table and Chairs, Kids Playground, CR/Shower/Hot Shower, Projector Big Screen, Free Parking, 4 Cogon Kubo Rooms', NULL, 1, '2026-07-08 15:59:21', '2026-09-28 10:07:04', '14:00:00', '12:00:00');

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

-- --------------------------------------------------------

--
-- Table structure for table `daily_resort_capacity`
--

CREATE TABLE `daily_resort_capacity` (
  `target_date` date NOT NULL,
  `max_guests` int(11) NOT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `daily_resort_capacity`
--

INSERT INTO `daily_resort_capacity` (`target_date`, `max_guests`, `updated_by`, `updated_at`) VALUES
('2026-09-25', 6, 1, '2026-09-24 15:16:06'),
('2026-09-26', 5, 1, '2026-09-24 14:57:59'),
('2026-10-15', 5, 1, '2026-09-24 15:19:48'),
('2026-10-27', 3, 1, '2026-09-24 15:26:04');

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
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp(),
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
(11, 'rhainearia@gmail.com', '118287', 'user', '{}', '2026-03-27 00:55:21', 0, NULL, '2026-03-26 16:50:21'),
(13, 'pranksimithel@gmail.com', '604831', 'user', '{\"first_name\":\"Fernando\",\"middle_name\":\"\",\"last_name\":\"Estrada\",\"suffix\":\"I\",\"email\":\"pranksimithel@gmail.com\",\"phone_number\":\"09234567896\",\"password\":\"Fernando#08\",\"region\":\"MIMAROPA Region\",\"province\":\"Romblon\",\"city_municipality\":\"San Andres\",\"barangay\":\"Pag-Alad\"}', '2026-09-26 17:27:18', 1, NULL, '2026-09-26 17:26:46'),
(14, 'pranksimithel@gmail.com', '601056', 'user', '{\"first_name\":\"Fernando\",\"middle_name\":\"\",\"last_name\":\"Estrada\",\"suffix\":\"I\",\"email\":\"pranksimithel@gmail.com\",\"phone_number\":\"09234567896\",\"password\":\"Fernando#08\",\"region\":\"Western Visayas\",\"province\":\"Iloilo\",\"city_municipality\":\"Alimodian\",\"barangay\":\"Ba-ong\"}', '2026-09-26 23:48:03', 2, NULL, '2026-09-26 17:43:03');

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
-- Table structure for table `password_reset_otps`
--

CREATE TABLE `password_reset_otps` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `otp` varchar(6) NOT NULL,
  `user_type` varchar(20) NOT NULL DEFAULT 'user',
  `attempts` int(11) NOT NULL DEFAULT 0,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_reset_otps`
--

INSERT INTO `password_reset_otps` (`id`, `email`, `otp`, `user_type`, `attempts`, `expires_at`, `created_at`) VALUES
(6, 'pranksimithel@gmail.com', '179667', 'user', 0, '2026-09-28 11:47:35', '2026-09-28 09:42:35');

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

--
-- Dumping data for table `payment_receipts`
--

INSERT INTO `payment_receipts` (`id`, `booking_id`, `file_name`, `file_path`, `file_size`, `file_type`, `uploaded_at`) VALUES
(1, 17, 'receipt_17.jpg', 'assets/uploads/payment_proofs/receipt_17_1790511028.jpg', 7357, 'image/jpeg', '2026-09-27 12:10:28'),
(2, 29, 'receipt_29.jpg', 'assets/uploads/payment_proofs/receipt_29_1790571616.jpg', 78760, 'image/jpeg', '2026-09-28 05:00:16'),
(3, 30, 'receipt_30.jpg', 'assets/uploads/payment_proofs/receipt_30_1790573025.jpg', 78760, 'image/jpeg', '2026-09-28 05:23:45'),
(4, 32, 'receipt_32.jpg', 'assets/uploads/payment_proofs/receipt_32_1790603120.jpg', 78760, 'image/jpeg', '2026-09-28 13:45:20');

-- --------------------------------------------------------

--
-- Table structure for table `promotions`
--

CREATE TABLE `promotions` (
  `id` int(10) UNSIGNED NOT NULL,
  `tagline` varchar(255) NOT NULL DEFAULT '',
  `headline` varchar(255) NOT NULL DEFAULT '',
  `event_name` varchar(255) NOT NULL DEFAULT '',
  `price` varchar(100) NOT NULL DEFAULT '',
  `pass_type` varchar(255) NOT NULL DEFAULT '',
  `footer_text` text NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `room_inventory`
--

CREATE TABLE `room_inventory` (
  `id` int(11) NOT NULL,
  `date` date NOT NULL,
  `room_id` int(11) NOT NULL,
  `booked_count` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `time_slots`
--

CREATE TABLE `time_slots` (
  `id` int(11) NOT NULL,
  `activity_id` int(11) DEFAULT NULL,
  `accommodation_id` int(11) DEFAULT NULL,
  `label` varchar(80) DEFAULT NULL COMMENT 'Optional friendly name, e.g. "Morning Session"',
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `time_slots`
--

INSERT INTO `time_slots` (`id`, `activity_id`, `accommodation_id`, `label`, `start_time`, `end_time`, `is_active`, `created_at`) VALUES
(1, NULL, NULL, 'Morning Session', '08:00:00', '12:00:00', 1, '2026-09-21 14:54:11'),
(2, NULL, NULL, 'Afternoon Session', '13:00:00', '17:00:00', 1, '2026-09-21 14:54:11'),
(3, NULL, NULL, 'Full Day', '08:00:00', '17:00:00', 1, '2026-09-21 14:54:11'),
(46, NULL, 2, '', '07:00:00', '15:00:00', 1, '2026-09-28 18:36:50'),
(47, NULL, 2, '', '08:00:00', '16:00:00', 1, '2026-09-28 18:36:50'),
(48, NULL, 2, '', '09:00:00', '17:00:00', 1, '2026-09-28 18:36:50'),
(49, NULL, 2, '', '10:00:00', '18:00:00', 1, '2026-09-28 18:36:50'),
(50, NULL, 2, '', '11:00:00', '19:00:00', 1, '2026-09-28 18:36:50'),
(51, NULL, 2, '', '00:00:00', '20:00:00', 1, '2026-09-28 18:36:50'),
(52, NULL, 2, '', '13:00:00', '21:00:00', 1, '2026-09-28 18:36:50'),
(53, NULL, 3, '', '07:00:00', '19:00:00', 1, '2026-09-28 18:37:03'),
(54, NULL, 3, '', '08:00:00', '20:00:00', 1, '2026-09-28 18:37:03'),
(55, NULL, 3, '', '09:00:00', '21:00:00', 1, '2026-09-28 18:37:03'),
(56, NULL, 4, '', '09:00:00', '20:00:00', 1, '2026-09-28 18:37:15'),
(57, NULL, 4, '', '09:00:00', '21:00:00', 1, '2026-09-28 18:37:15');

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
  `avatar` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `middle_name`, `last_name`, `suffix`, `email`, `phone_number`, `region`, `province`, `city_municipality`, `barangay`, `password`, `is_verified`, `verification_token`, `reset_token`, `reset_token_expiry`, `remember_token`, `token_expiry`, `created_at`, `updated_at`, `address`, `birthdate`, `avatar`) VALUES
(1, 'Cool Vincent', '', 'Bonilla', '', 'bonillacoolvincentc@gmail.com', '09563695447', 'Ilocos Region', 'Pangasinan', 'Burgos', 'Poblacion', '$2y$10$aWnh9/BOr9pzjjkmAIlVHO480u4N9L.ljy21oCEsFaie2iMj3XwVa', 0, '12f4c66db811caa1c2eae16aed9efb0e6f4ebb0c9f0c54a1a1ec8fb527b5ddff', NULL, NULL, 'd2d47f89587c1dbfd7f7d33c88302d56b2c70fed9fe1ab2454b0c9b5ee7c3b9c', '2026-08-01 15:42:05', '2026-03-26 16:32:38', '2026-09-27 06:09:03', 'Poblacion, Burgos, Pangasinan, Ilocos Region', NULL, NULL),
(2, 'Fernando', '', 'Estrada', 'Jr.', 'pranksimithel@gmail.com', '09564472345', 'Ilocos Region', 'Pangasinan', 'Binmaley', 'Canaoalan', '$2y$10$/1TYKg6LmbsCvG/ZkT6aNOuvR58dPYXynHW2ZJRTkH0VLJ5HochG6', 1, 'dcf67f225d7ed331ce20ff3e504a46cc56496f6a2d8b69c26a09c472d3301571', NULL, NULL, NULL, NULL, '2026-09-26 17:36:37', '2026-09-26 17:36:37', 'Canaoalan, Binmaley, Pangasinan, Ilocos Region', NULL, NULL),
(5, 'Saiyon', '', 'Mayuzumi', 'Jr.', 'coolegoness@gmail.com', '09876545677', 'Central Luzon', 'Nueva Ecija', 'Licab', 'Tabing Ilog', '$2y$10$Ulv86L6SNKtIOJQWAUyQqODAT21yjR31X1o0CIiW2.SUsEdDqUC1a', 1, '512371279dddb8255023cbbafb9d7203513790af5d6a94b3bf44637c315d8a40', NULL, NULL, NULL, NULL, '2026-09-26 18:21:45', '2026-09-26 18:21:45', 'Tabing Ilog, Licab, Nueva Ecija, Central Luzon', NULL, NULL);

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
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`);

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
-- Indexes for table `blocked_emails`
--
ALTER TABLE `blocked_emails`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_blocked_email` (`email`);

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
  ADD KEY `fk_bookings_bundle` (`bundle_id`),
  ADD KEY `idx_booking_source` (`booking_source`);

--
-- Indexes for table `booking_extra_rooms`
--
ALTER TABLE `booking_extra_rooms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_booking_id` (`booking_id`),
  ADD KEY `fk_accommodation_id` (`accommodation_id`);

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
-- Indexes for table `daily_resort_capacity`
--
ALTER TABLE `daily_resort_capacity`
  ADD PRIMARY KEY (`target_date`);

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
-- Indexes for table `password_reset_otps`
--
ALTER TABLE `password_reset_otps`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_email` (`email`);

--
-- Indexes for table `payment_receipts`
--
ALTER TABLE `payment_receipts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_booking_id` (`booking_id`);

--
-- Indexes for table `promotions`
--
ALTER TABLE `promotions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_is_active` (`is_active`);

--
-- Indexes for table `room_inventory`
--
ALTER TABLE `room_inventory`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_room_date` (`room_id`,`date`);

--
-- Indexes for table `time_slots`
--
ALTER TABLE `time_slots`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_activity_id` (`activity_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `activities`
--
ALTER TABLE `activities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `activity_availability_schedule`
--
ALTER TABLE `activity_availability_schedule`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=141;

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `availability`
--
ALTER TABLE `availability`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `blocked_dates`
--
ALTER TABLE `blocked_dates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `blocked_emails`
--
ALTER TABLE `blocked_emails`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `booking_extra_rooms`
--
ALTER TABLE `booking_extra_rooms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `booking_logs`
--
ALTER TABLE `booking_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=91;

--
-- AUTO_INCREMENT for table `booking_status`
--
ALTER TABLE `booking_status`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `bundles`
--
ALTER TABLE `bundles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

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
-- AUTO_INCREMENT for table `password_reset_otps`
--
ALTER TABLE `password_reset_otps`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `payment_receipts`
--
ALTER TABLE `payment_receipts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `promotions`
--
ALTER TABLE `promotions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `room_inventory`
--
ALTER TABLE `room_inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `time_slots`
--
ALTER TABLE `time_slots`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

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
  ADD CONSTRAINT `fk_bookings_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `booking_extra_rooms`
--
ALTER TABLE `booking_extra_rooms`
  ADD CONSTRAINT `fk_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE;

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
