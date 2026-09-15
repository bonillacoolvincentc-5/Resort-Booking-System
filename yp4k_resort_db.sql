-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 15, 2026 at 07:51 PM
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

--
-- Dumping data for table `accommodations`
--

INSERT INTO `accommodations` (`id`, `name`, `type`, `description`, `capacity`, `price_per_night`, `price_per_day`, `amenities`, `image_url`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'VIP ROOM', 'room', 'king size bed', 4, 2000.00, 4000.00, 'Testing, Testing, Testing', 'assets/uploads/accommodations/6a4d26cc483e0.jfif', 1, '2026-07-07 16:18:20', '2026-07-07 16:18:20'),
(2, 'Day Tour Kubo Non-Aircon Room (8 Hours)', 'cottage', 'Day tour cottage room. Good for 2 persons. Non-aircon Kubo. 8-hour stay. Extra hour P200. Cut-off 9PM. Walk-in only. Free: Pool, Gym, Basketball Court, Parking, WiFi, Common Bathroom, Playground. Plus daytour entrance fee. VAT excluded.', 2, 799.00, 799.00, 'Pool, Gym, Basketball Court, Parking, WiFi, Common Bathroom, Playground', NULL, 1, '2026-07-08 15:59:21', '2026-07-08 15:59:21'),
(3, 'Day Tour Kubo Non-Aircon Room (12 Hours)', 'cottage', 'Day tour cottage room. Good for 2 persons. Non-aircon Kubo. 12-hour stay. Cut-off 9PM. Walk-in only. Free: Pool, Gym, Basketball Court, Parking, WiFi, Common Bathroom, Playground. Plus daytour entrance fee. VAT excluded.', 2, 999.00, 999.00, 'Pool, Gym, Basketball Court, Parking, WiFi, Common Bathroom, Playground', NULL, 1, '2026-07-08 15:59:21', '2026-07-08 15:59:21'),
(4, 'Day Tour Kubo Aircon Room (8 Hours)', 'cottage', 'Day tour cottage room. Good for 2 persons. Aircon Kubo. 8-hour stay. Extra hour P200. Cut-off 9PM. Walk-in only. Free: Pool, Gym, Basketball Court, Parking, WiFi, Common Bathroom, Playground, Air Conditioning. Plus daytour entrance fee. VAT excluded.', 2, 999.00, 999.00, 'Pool, Gym, Basketball Court, Parking, WiFi, Common Bathroom, Playground, Air Conditioning', NULL, 1, '2026-07-08 15:59:21', '2026-07-08 15:59:21'),
(5, 'Day Tour Kubo Aircon Room (12 Hours)', 'cottage', 'Day tour cottage room. Good for 2 persons. Aircon Kubo. 12-hour stay. Cut-off 9PM. Walk-in only. Free: Pool, Gym, Basketball Court, Parking, WiFi, Common Bathroom, Playground, Air Conditioning. Plus daytour entrance fee. VAT excluded.', 2, 1499.00, 1499.00, 'Pool, Gym, Basketball Court, Parking, WiFi, Common Bathroom, Playground, Air Conditioning', NULL, 1, '2026-07-08 15:59:21', '2026-07-08 15:59:21'),
(6, 'Umbrella Cottage (8 Pax+)', 'cottage', 'Outdoor umbrella cottage. Good for 8 pax and up. Includes table and chairs. Plus entrance fee. Free access: Gym, Kids Playground, Pool, Basketball Court, Shower, Griller, Pool Bench. VAT excluded.', 8, 500.00, 500.00, 'Table and Chairs, Gym, Kids Playground, Swimming Pool, Basketball Court, Shower, Griller, Pool Bench', NULL, 1, '2026-07-08 15:59:21', '2026-07-08 15:59:21'),
(7, 'Small Awning Cottage (10 Pax+)', 'cottage', 'Small awning cottage. Good for 10 pax and up. Includes table and chairs. Plus entrance fee. VAT excluded.', 10, 700.00, 700.00, 'Table and Chairs, Gym, Kids Playground, Swimming Pool, Basketball Court, Shower, Griller', NULL, 1, '2026-07-08 15:59:21', '2026-07-08 15:59:21'),
(8, 'Kubo Cottage (15 Pax+)', 'cottage', 'Kubo cottage. Good for 15 pax and up. Includes table and chairs. Plus entrance fee. VAT excluded.', 15, 1200.00, 1200.00, 'Table and Chairs, Gym, Kids Playground, Swimming Pool, Basketball Court, Shower, Griller', NULL, 1, '2026-07-08 15:59:21', '2026-07-08 15:59:21'),
(9, 'Big Kubo Cottage (20 Pax+)', 'cottage', 'Big kubo cottage. Good for 20 pax and up. Includes table and chairs. Plus entrance fee. VAT excluded.', 20, 1500.00, 1500.00, 'Table and Chairs, Gym, Kids Playground, Swimming Pool, Basketball Court, Shower, Griller', NULL, 1, '2026-07-08 15:59:21', '2026-07-08 15:59:21'),
(10, 'Pavilion Cottage (25 Pax+)', 'cottage', 'Pavilion cottage. Good for 25 pax and up. Includes table and chairs. Daytime rate. Plus entrance fee. VAT excluded.', 25, 1500.00, 1500.00, 'Table and Chairs, Gym, Kids Playground, Swimming Pool, Basketball Court, Shower, Griller', NULL, 1, '2026-07-08 15:59:21', '2026-07-08 15:59:21'),
(11, 'Private Big Pavilion - Daytime (35 Pax+)', 'cottage', 'Private big pavilion. Good for 35 pax and up. Includes table and chairs. Daytime rate. Plus entrance fee. VAT excluded.', 35, 2000.00, 2000.00, 'Table and Chairs, Gym, Kids Playground, Swimming Pool, Basketball Court, Shower, Griller', NULL, 1, '2026-07-08 15:59:21', '2026-07-08 15:59:21'),
(12, 'Private Big Pavilion - 3PM Onwards (35 Pax+)', 'cottage', 'Private big pavilion. Good for 35 pax and up. Includes table and chairs. 3PM onwards rate. Plus entrance fee. VAT excluded.', 35, 2500.00, 2500.00, 'Table and Chairs, Gym, Kids Playground, Swimming Pool, Basketball Court, Shower, Griller', NULL, 1, '2026-07-08 15:59:21', '2026-07-08 15:59:21'),
(13, 'Private Big Kubo - Daytime (50 Pax+)', 'cottage', 'Private big kubo cottage. Good for 50 pax and up. Includes table and chairs. Daytime rate. Plus entrance fee. VAT excluded.', 50, 3000.00, 3000.00, 'Table and Chairs, Gym, Kids Playground, Swimming Pool, Basketball Court, Shower, Griller', NULL, 1, '2026-07-08 15:59:21', '2026-07-08 15:59:21'),
(14, 'Private Big Kubo - 3PM Onwards (50 Pax+)', 'cottage', 'Private big kubo cottage. Good for 50 pax and up. Includes table and chairs. 3PM onwards rate. Plus entrance fee. VAT excluded.', 50, 4000.00, 4000.00, 'Table and Chairs, Gym, Kids Playground, Swimming Pool, Basketball Court, Shower, Griller', NULL, 1, '2026-07-08 15:59:21', '2026-07-08 15:59:21'),
(15, 'Kubo Room Non-Aircon (Transient)', 'room', 'Transient Kubo non-aircon room. Max 2 persons only. P1,499 per night. Plus P500 excess per person. Amenities: Swimming Pool, Karaoke and Sounds, Free Parking, Strong WiFi, Genset Backup, Playground. VAT excluded.', 2, 1499.00, 1499.00, 'Swimming Pool, Karaoke and Sounds, Free Parking, Strong WiFi, Genset Backup, Playground', NULL, 1, '2026-07-08 15:59:21', '2026-07-08 15:59:21'),
(16, 'Kubo Room Aircon (Transient)', 'room', 'Transient Kubo aircon room. Max 2 persons only. P2,499 per night. Plus P500 excess per person. Amenities: Swimming Pool, Karaoke and Sounds, Free Parking, Strong WiFi, Genset Backup, Air Conditioning, Playground. VAT excluded.', 2, 2499.00, 2499.00, 'Swimming Pool, Karaoke and Sounds, Free Parking, Strong WiFi, Genset Backup, Air Conditioning, Playground', NULL, 1, '2026-07-08 15:59:21', '2026-07-08 15:59:21'),
(17, 'Regular Room', 'room', 'Transient regular room. Up to 8 pax bed capacity. 4 persons: P3,999 (max 8 persons). Plus P400 excess per person. Amenities: Swimming Pool, Karaoke and Sounds, Free Parking, Strong WiFi, Genset Backup. VAT excluded.', 8, 3999.00, 3999.00, 'Swimming Pool, Karaoke and Sounds, Free Parking, Strong WiFi, Genset Backup, Playground', NULL, 1, '2026-07-08 15:59:21', '2026-07-08 15:59:21'),
(18, 'Dome Tent Room VIP', 'room', 'Transient VIP Dome Tent room. Max 2-8 persons. VIP 2 persons: P3,499 (max 4 persons). Regular Dome max 8 persons. Plus P500 excess per person. VAT excluded.', 4, 3499.00, 3499.00, 'Swimming Pool, Karaoke and Sounds, Free Parking, Strong WiFi, Genset Backup', NULL, 1, '2026-07-08 15:59:21', '2026-07-08 15:59:21'),
(19, 'VIP Master Bedroom', 'room', 'Transient VIP Master Bedroom. Up to 6 pax bed capacity. 2 persons: P3,999 (max 6 persons). Plus P500 excess per person. Amenities: Swimming Pool, Karaoke and Sounds, Free Parking, Strong WiFi, Genset Backup, Air Conditioning. VAT excluded.', 6, 3999.00, 3999.00, 'Swimming Pool, Karaoke and Sounds, Free Parking, Strong WiFi, Genset Backup, Air Conditioning', NULL, 1, '2026-07-08 15:59:21', '2026-07-08 15:59:21'),
(20, 'Regular Family Room', 'room', 'Transient regular family room. Up to 15 pax bed capacity. 10 persons: P5,999 (max 15 persons). Plus P400 excess per person. Amenities: Swimming Pool, Karaoke and Sounds, Free Parking, Strong WiFi, Genset Backup. VAT excluded.', 15, 5999.00, 5999.00, 'Swimming Pool, Karaoke and Sounds, Free Parking, Strong WiFi, Genset Backup, Playground', NULL, 1, '2026-07-08 15:59:21', '2026-07-08 15:59:21'),
(21, 'Regular Barkada Room', 'room', 'Transient regular barkada room. Up to 22 pax bed capacity. 10 persons: P6,499 (max 22 persons). Plus P400 excess per person. Amenities: Swimming Pool, Karaoke and Sounds, Free Parking, Strong WiFi, Genset Backup. VAT excluded.', 22, 6499.00, 6499.00, 'Swimming Pool, Karaoke and Sounds, Free Parking, Strong WiFi, Genset Backup, Playground', NULL, 1, '2026-07-08 15:59:21', '2026-07-08 15:59:21'),
(22, 'Grand Villa Room', 'stay-in', 'Transient VIP Grand Villa Room. Up to 50 pax bed capacity. 15 persons: P11,999 | 25 persons: P15,999 | 35 persons: P19,999 | Unli Pax: P24,999 (max 50). Plus P500 excess per person. Amenities: Swimming Pool, Karaoke and Sounds, Free Parking, Strong WiFi, Air Conditioning, Genset Backup. VAT excluded.', 50, 11999.00, 11999.00, 'Swimming Pool, Karaoke and Sounds, Free Parking, Strong WiFi, Air Conditioning, Genset Backup', NULL, 1, '2026-07-08 15:59:21', '2026-07-08 15:59:21'),
(23, 'Mega Family Room', 'stay-in', 'Transient VIP Mega Family Room. Up to 24 pax bed capacity. 10 persons: P7,999 | 20 persons: P10,999 (max 24). Plus P500 excess per person. Amenities: Swimming Pool, Karaoke and Sounds, Free Parking, Strong WiFi, Air Conditioning, Genset Backup. VAT excluded.', 24, 7999.00, 7999.00, 'Swimming Pool, Karaoke and Sounds, Free Parking, Strong WiFi, Air Conditioning, Genset Backup', NULL, 1, '2026-07-08 15:59:21', '2026-07-08 15:59:21'),
(24, 'Mega Barkada Room', 'stay-in', 'Transient VIP Mega Barkada Room. Up to 50 pax bed capacity. 15 persons: P9,000 | 30 persons: P14,999 | 45 persons: P19,999 (max 50). Plus P500 excess per person. Amenities: Swimming Pool, Karaoke and Sounds, Free Parking, Strong WiFi, Air Conditioning, Genset Backup. VAT excluded.', 50, 9000.00, 9000.00, 'Swimming Pool, Karaoke and Sounds, Free Parking, Strong WiFi, Air Conditioning, Genset Backup', NULL, 1, '2026-07-08 15:59:21', '2026-07-08 15:59:21'),
(25, 'Family Private Villa', 'stay-in', 'Transient VIP Family Private Villa. 2 Rooms and Solo Pool. Up to 20 pax bed capacity. 15 persons: P13,999 (max 20). Plus P650 excess per person. Amenities: Private Pool, 2 Aircon Rooms, Swimming Pool, Karaoke and Sounds, Free Parking, Strong WiFi, Genset Backup. VAT excluded.', 20, 13999.00, 13999.00, 'Private Pool, 2 Aircon Rooms, Swimming Pool, Karaoke and Sounds, Free Parking, Strong WiFi, Genset Backup', NULL, 1, '2026-07-08 15:59:21', '2026-07-08 15:59:21'),
(26, 'Barkada Private Villa', 'stay-in', 'Transient VIP Barkada Private Villa. 4 Rooms and Solo Pool. Up to 40 pax bed capacity. 20 persons: P15,999 | 30 persons: P19,999 | Unli Pax: P24,999 (max 40). Plus P650 excess per person. Amenities: Private Pool, 4 Aircon Rooms, Swimming Pool, Karaoke and Sounds, Free Parking, Strong WiFi, Genset Backup. VAT excluded.', 40, 15999.00, 15999.00, 'Private Pool, 4 Aircon Rooms, Swimming Pool, Karaoke and Sounds, Free Parking, Strong WiFi, Genset Backup', NULL, 1, '2026-07-08 15:59:21', '2026-07-08 15:59:21'),
(27, 'Private Grand Villa', 'stay-in', 'Transient VIP Private Grand Villa. 3 Rooms and Solo Pool. Up to 75 pax bed capacity. 30 persons: P19,999 | 40 persons: P24,999 | Unli Pax: P29,999 (max 75). Plus P650 excess per person. Amenities: Private Pool, 3 Aircon Rooms, Swimming Pool, Karaoke and Sounds, Free Parking, Strong WiFi, Genset Backup. VAT excluded.', 75, 19999.00, 19999.00, 'Private Pool, 3 Aircon Rooms, Swimming Pool, Karaoke and Sounds, Free Parking, Strong WiFi, Genset Backup', NULL, 1, '2026-07-08 15:59:21', '2026-07-08 15:59:21');

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
  `image_url` varchar(500) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activities`
--

INSERT INTO `activities` (`id`, `name`, `slug`, `description`, `price_per_hour`, `price_type`, `min_duration_hours`, `max_duration_hours`, `capacity`, `image_url`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Billiards', 'billiards', 'Premium billiards tables with expert coaching available for all skill levels', 200.00, 'flat_rate', 1.00, 4.00, 8, 'assets/uploads/activities/6aa21fc8a8369.webp', 1, '2026-06-18 14:06:52', '2026-09-10 03:11:04'),
(6, 'Kids Playground', 'playground', 'Safe and fun playground area with activities for children of all ages', 10.00, 'flat_rate', 1.00, 4.00, 25, 'assets/Images/Playground.jpg', 1, '2026-06-18 14:06:52', '2026-09-10 02:29:59'),
(7, 'Fitness Gym', 'fitness-gym', 'YP4K Warrior Fitness Gym. Walk-in: P150/day, P200 from 3PM. Annual: P11,999 (22+), P9,999 (under 21). Monthly: P2,499 (22+), P1,999 (under 21). Membership access card P200. Includes access to basketball court, pool, and playground.', 150.00, 'flat_rate', 1.00, 8.00, 30, 'assets/uploads/activities/6aa2221ab75ed.jpg', 1, '2026-07-08 15:59:21', '2026-09-10 03:20:58'),
(8, 'Swimming Pool - Morning Daytour (8AM-3PM)', 'swimming-pool-morning-daytour-8am-3pm-', 'Daytour swimming pool access. Morning rate 8AM-3PM. Adults: P150. Kids/Senior: P120. Transient guests and gym members: FREE. VAT excluded, non-refundable.', 150.00, 'flat_rate', 1.00, 7.00, 50, 'assets/uploads/activities/6aa2222a768e3.jpg', 1, '2026-07-08 15:59:21', '2026-09-10 03:21:14'),
(9, 'Swimming Pool - Afternoon Daytour (3PM-9PM)', 'swimming-pool-afternoon-daytour-3pm-9pm-', 'Daytour swimming pool access. Afternoon rate 3PM-9PM. Adults: P200. Kids/Senior: P150. VAT excluded, non-refundable.', 200.00, 'flat_rate', 1.00, 6.00, 50, 'assets/uploads/activities/6aa222343fe1f.jpg', 1, '2026-07-08 15:59:21', '2026-09-10 03:21:24'),
(10, 'ATV Short Ride - Direct Bolo Beach Tour', 'atv-short-ride-direct-bolo-beach-tour', 'YP4K ATV and UTV Sports. Short ride 1 hour. Direct Bolo Beach Tour. P1,500 per ATV. Tour guide P1,500 mandatory. Destinations: Sabangan, Telbang, Tubuey Rd., Bolo Beach. VAT excluded.', 1500.00, 'per_head', 1.00, 1.00, 10, 'assets/uploads/activities/6aa221779df1e.jpg', 1, '2026-07-08 15:59:21', '2026-09-10 14:51:37'),
(11, 'ATV Long Ride - Mountain and Bolo Beach Tour', 'atv-long-ride-mountain-and-bolo-beach-tour', 'YP4K ATV and UTV Sports. Long ride almost 2 hours. Mountain and Bolo Beach Tour. P2,000 per ATV. Tour guide P1,500 mandatory. VAT excluded.', 1000.00, 'flat_rate', 2.00, 2.00, 10, 'assets/uploads/activities/6aa2216cf4013.jpg', 1, '2026-07-08 15:59:21', '2026-09-10 03:18:05'),
(12, 'Basketball Court - Flood Light Rental (Guest)', 'basketball-court-flood-light-rental-guest-', 'Open Basketball Court at Resort-2 Venue. All guests: P500/hour flood light rental. Solar light: FREE. Morning/afternoon play: FREE. Strictly time-scheduled. Please register first.', 500.00, 'flat_rate', 1.00, 4.00, 30, 'assets/uploads/activities/6aa22248936e7.jpg', 1, '2026-07-08 15:59:21', '2026-09-10 03:21:44'),
(13, 'Basketball Court - Flood Light Rental (Walk-In)', 'basketball-court-flood-light-rental-walk-in-', 'Open Basketball Court at Resort-2 Venue. Non-guest/Walk-in: P700 first hour flood light, P500 succeeding hour. Solar light: P300 first hour, P200 succeeding. 30 pax max free entrance. Ball rental P200/schedule.', 700.00, 'flat_rate', 1.00, 4.00, 30, 'assets/uploads/activities/6aa222509b438.jpg', 1, '2026-07-08 15:59:21', '2026-09-10 03:21:52'),
(14, 'Pickleball Court - Private Rental (Walk-In)', 'pickleball-court-private-rental-walk-in', 'Sports Central YP4K. Soft opening special rates. Mon-Sun 4PM-9PM. Walk-in non-guest private rental: P350/hour. Open Play: P150. Paddle pair P150, Paddle walk-in P200, Pusun Launcher P300/hour. 2 courts only. Full payment upon reservation.', 350.00, 'flat_rate', 1.00, 5.00, 10, NULL, 1, '2026-07-08 15:59:21', '2026-07-08 15:59:21'),
(15, 'Pickleball Court - Private Rental (Guest/Member)', 'pickleball-court-private-rental-guest-member', 'Sports Central YP4K. Soft opening special rates. Mon-Sun 4PM-9PM. Guest/member private rental: P250/hour. Open Play: P100 (4PM-9PM). 2 courts only. Full payment upon reservation.', 250.00, 'flat_rate', 1.00, 5.00, 10, NULL, 1, '2026-07-08 15:59:21', '2026-07-08 15:59:21'),
(16, 'Videoke Rental', 'videoke-rental', 'Videoke rental at YP4K Villa Berde Resort. P1,500 flat rate per session.', 750.00, 'flat_rate', 1.00, 4.00, 50, NULL, 1, '2026-07-08 15:59:21', '2026-07-08 15:59:21');

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
(1, 'Saiyon', '', 'Madarame', '', 'rhainearia@gmail.com', '09563695447', '$2y$10$l6ZJu.OEwCf454micPxxXO0pnHtUZyMXmJlrI8a/uZLGYl.Y8TcKO', 0, '696bc6d7cec17df661ed8408600c4bf7c2e081c2f8ff0ab97b7794dab79a97f0', NULL, NULL, NULL, NULL, '2026-03-26 16:56:37', '2026-03-26 16:56:37', 'admin', NULL, NULL, NULL, NULL, NULL),
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
  `payment_proof` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `booking_reference`, `user_id`, `booking_type`, `activity_id`, `accommodation_id`, `bundle_id`, `status_id`, `full_name`, `email`, `phone_number`, `check_in_date`, `check_in_time`, `check_out_date`, `check_out_time`, `number_of_days`, `number_of_guests`, `special_requests`, `total_amount`, `payment_status`, `payment_method`, `booking_source`, `guest_name`, `guest_contact`, `created_at`, `updated_at`, `payment_proof`) VALUES
(1, 'REF-2026-0001', 1, 'activity', 1, NULL, NULL, 4, 'Cool Vincent Bonilla', 'bonillacoolvincentc@gmail.com', '09563695447', '2026-07-10', '14:00:00', '2026-07-10', '16:00:00', 1, 4, 'Need 2 extra cue sticks and chalk.', 50.00, 'paid', 'gcash', 'online', NULL, NULL, '2026-07-02 18:27:04', '2026-09-09 14:20:07', NULL),
(2, 'BK202607098766', 1, 'bundle', NULL, NULL, 16, 3, 'Cool Vincent Bonilla', 'bonillacoolvincentc@gmail.com', 'N/A', '2026-07-29', NULL, '2026-07-30', NULL, 1, 2, '', 20999.00, 'pending', 'gcash', 'online', NULL, NULL, '2026-07-09 15:24:27', '2026-09-09 12:03:39', 'assets/uploads/payment_proofs/proof_1_1783610667.jfif'),
(3, 'BK202607092423', 1, 'activity', NULL, NULL, NULL, 3, 'Cool Vincent Bonilla', 'bonillacoolvincentc@gmail.com', '09563695447', '2026-07-09', '13:00:00', NULL, '17:00:00', 1, 1, '', 180.00, 'pending', 'gcash', 'online', NULL, NULL, '2026-07-09 15:46:38', '2026-09-09 12:03:34', NULL),
(4, 'BK202607091287', 1, 'activity', 13, NULL, NULL, 3, 'Cool Vincent Bonilla', 'bonillacoolvincentc@gmail.com', '09563695447', '2026-07-09', '13:49:00', NULL, '17:49:00', 1, 30, '', 2800.00, 'pending', 'gcash', 'online', NULL, NULL, '2026-07-09 16:16:08', '2026-09-09 12:03:29', 'assets/uploads/payment_proofs/proof_1_1783613768.png'),
(5, 'BK202607105542', 1, 'bundle', NULL, NULL, 5, 3, 'Cool Vincent Bonilla', 'bonillacoolvincentc@gmail.com', 'N/A', '2026-07-21', NULL, '2026-07-22', NULL, 1, 2, '', 39999.00, 'pending', 'gcash', 'online', NULL, NULL, '2026-07-10 01:25:45', '2026-09-09 12:03:26', 'assets/uploads/payment_proofs/proof_1_1783646745.jpg'),
(6, 'BK202607109394', 1, 'bundle', NULL, NULL, 11, 3, 'Cool Vincent Bonilla', 'bonillacoolvincentc@gmail.com', 'N/A', '2026-07-20', NULL, '2026-07-21', NULL, 1, 20, '', 69999.00, 'pending', 'gcash', 'online', NULL, NULL, '2026-07-10 02:52:53', '2026-09-09 12:03:21', 'assets/uploads/payment_proofs/proof_1_1783651973.jpg'),
(7, 'BK202607109533', 1, 'bundle', NULL, NULL, 15, 3, 'Cool Vincent Bonilla', 'bonillacoolvincentc@gmail.com', 'N/A', '2026-07-30', NULL, '2026-07-31', NULL, 1, 2, '', 18499.00, 'pending', 'gcash', 'online', NULL, NULL, '2026-07-10 02:54:52', '2026-09-09 12:03:15', 'assets/uploads/payment_proofs/proof_1_1783652092.jpg'),
(8, 'BK202607103601', 1, 'activity', 16, NULL, NULL, 3, 'Cool Vincent Bonilla', 'bonillacoolvincentc@gmail.com', '09563695447', '2026-07-10', '07:58:00', NULL, '12:00:00', 1, 5, '', 3025.00, 'pending', 'gcash', 'online', NULL, NULL, '2026-07-10 02:59:56', '2026-09-09 12:02:55', 'assets/uploads/payment_proofs/proof_1_1783652396.jpg'),
(9, 'BK202609112348', 1, 'bundle', NULL, NULL, 5, 1, 'Cool Vincent Bonilla', 'bonillacoolvincentc@gmail.com', 'N/A', '2026-09-14', '00:00:00', '2026-09-15', '00:00:00', 1, 1, '', 39999.00, 'pending', 'gcash', 'online', NULL, NULL, '2026-09-11 01:00:24', '2026-09-11 01:00:24', 'assets/uploads/payment_proofs/proof_1_1789088424.jpg'),
(10, 'BK202609113914', 1, 'activity', 11, NULL, NULL, 4, 'Cool Vincent Bonilla', 'bonillacoolvincentc@gmail.com', '09563695447', '2026-09-12', '09:30:00', NULL, '10:30:00', 1, 2, 'Test', 1000.00, 'pending', 'gcash', 'online', NULL, NULL, '2026-09-11 01:32:02', '2026-09-14 15:02:05', 'assets/uploads/payment_proofs/proof_1_1789090322.webp');

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
(18, 10, 1, 2, 'admin', 1, 'Booking approved by admin', '2026-09-11 01:50:05');

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
(6, 'checked_in', 'Guest has checked in (walk-in reservation)', '#6366f1', 1, '2026-09-01 14:23:21');

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
  `start_time` time DEFAULT NULL,
  `duration_hours` int(11) DEFAULT NULL,
  `amenities` text DEFAULT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bundles`
--

INSERT INTO `bundles` (`id`, `name`, `slug`, `description`, `price`, `capacity`, `start_time`, `duration_hours`, `amenities`, `image_url`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Testing Bundle', 'testing-bundle', 'This is just a testing bundle', 1000.00, 12, NULL, NULL, '0', 'assets/uploads/bundles/bundle_6a4686b366c35.jpg', 1, '2026-07-02 15:41:39', '2026-07-02 15:41:39'),
(2, 'Daytour Private Villa Event Place (Unli Pax - Free Entrance)', 'daytour-private-villa-event-place-unli-pax-free-entrance', 'Daytour Swimming Private Villa Event Place. 8HRS Stay. Good for 100 PAX. UNLI PAX Guest. FREE Entrance. Includes: Private Swimming Pool, 2 Aircon Rooms (20 Pax), Function Hall Non-Aircon (P2,000 for Aircon), Billiards Table, Videoke, Pavilion, CR/Shower, Bench Pool Area, Griller/Gas/Stove, Outdoor Kitchen, Solo Parking, Free WiFi, Table and Chairs, Lights and Sounds, Big Fans, Big Awning Area. Additional guest entrance: P9,999. Night rate: Kids P120, Adult P180. Genset Backup. Cut off 9PM. VAT excluded.', 14999.00, 100, NULL, NULL, 'Private Swimming Pool, 2 Aircon Rooms (20 Pax), Function Hall, Billiards Table, Videoke, Pavilion, CR/Shower, Bench Pool Area, Griller, Outdoor Kitchen, Solo Parking, Free WiFi, Table and Chairs, Lights and Sounds, Big Fans, Big Awning Area, Genset Backup', NULL, 1, '2026-07-08 15:59:21', '2026-07-08 15:59:21'),
(3, 'Outdoor Big Kubo Event Place (Unli Pax - Free Entrance)', 'outdoor-big-kubo-event-place-unli-pax-free-entrance', 'Outdoor Big Kubo Event Place. Good for 100 PAX and up. UNLI PAX Free Entrance. Includes: 2 Kubo Rooms Non-Aircon, 3 Small Kubo Cottages, Table and Chairs, Outdoor Kitchen, Projector Screen, Videoke, Projector Lamp, Shower/CR, Sound and Lights System. Free access: Basketball Court, Kids Playground, Parking, Griller/Kitchen, Fitness Gym, Swimming Pool. Best for: Birthdays, Seminars, Reunions, Parties, Weddings, Team Building. VAT excluded, non-refundable.', 19999.00, 100, NULL, NULL, '2 Kubo Rooms Non-Aircon, 3 Small Kubo Cottages, Table and Chairs, Outdoor Kitchen, Projector Screen, Videoke, Projector Lamp, Shower/CR, Sound and Lights System, Basketball Court, Kids Playground, Parking, Griller/Kitchen, Fitness Gym, Swimming Pool', NULL, 1, '2026-07-08 15:59:21', '2026-07-08 15:59:21'),
(4, 'Daytour Private Resort 2 - 6HRS Daytime (Unli Pax)', 'daytour-private-resort-2-6hrs-daytime-unli-pax', 'Daytour Private Resort 2 Rental (UNLIPAX). For exclusive use only. 6 Hours daytime. Unli Pax guest, cut-off 9PM. With 4 Cogon Kubo Rooms for celebrant. Includes: Resort 5,000sqm lot area, Private Kubo, 2 Rooms Kubo, Private Pavillion, Pavillion Area, Whole Basketball Court, Big Pool, Big Stage, Videoke Area, Table and Chairs, Kids Playground, CR/Shower/Hot Shower, Projector Big Screen, Free Parking. All payments non-refundable. VAT excluded.', 29999.00, 200, NULL, NULL, 'Resort 5000sqm Lot, Private Kubo, 2 Rooms Kubo, Private Pavillion, Pavillion, Whole Basketball Court, Big Pool, Big Stage, Videoke, Table and Chairs, Kids Playground, CR/Shower/Hot Shower, Projector Big Screen, Free Parking, 4 Cogon Kubo Rooms', NULL, 1, '2026-07-08 15:59:21', '2026-07-08 15:59:21'),
(5, 'Daytour Private Resort 2 - 6HRS 3PM Onwards (Unli Pax)', 'daytour-private-resort-2-6hrs-3pm-onwards-unli-pax', 'Daytour Private Resort 2 Rental (UNLIPAX). For exclusive use only. 6 Hours from 3PM onwards. Unli Pax guest, cut-off 9PM. Same inclusions as daytime package. All payments non-refundable. VAT excluded.', 39999.00, 200, NULL, NULL, 'Resort 5000sqm Lot, Private Kubo, 2 Rooms Kubo, Private Pavillion, Pavillion, Whole Basketball Court, Big Pool, Big Stage, Videoke, Table and Chairs, Kids Playground, CR/Shower/Hot Shower, Projector Big Screen, Free Parking, 4 Cogon Kubo Rooms', NULL, 1, '2026-07-08 15:59:21', '2026-07-08 15:59:21'),
(6, 'Daytour Private Resort 2 - 10HRS Full Day (Unli Pax)', 'daytour-private-resort-2-10hrs-full-day-unli-pax', 'Daytour Private Resort 2 Rental (UNLIPAX). For exclusive use only. 10 Hours full day rental. Unli Pax guest, cut-off 9PM. Same inclusions as other packages. All payments non-refundable. VAT excluded.', 49999.00, 200, NULL, NULL, 'Resort 5000sqm Lot, Private Kubo, 2 Rooms Kubo, Private Pavillion, Pavillion, Whole Basketball Court, Big Pool, Big Stage, Videoke, Table and Chairs, Kids Playground, CR/Shower/Hot Shower, Projector Big Screen, Free Parking, 4 Cogon Kubo Rooms', NULL, 1, '2026-07-08 15:59:21', '2026-07-08 15:59:21'),
(7, 'Overnight Team Building - 100 Pax (1 Night)', 'overnight-team-building-100-pax-1-night', 'Overnight Team Building Package. 22HRS Stay. 2PM Check-in / 12NN Checkout. 100 PAX capacity. 7 Rooms: 3 VIP Master Bedroom, 2 Regular Room, 1 VIP Mega Family Room, 1 VIP Grand Villa. Includes: Solo Large Pool, Large Pool, Solo Billiards, Videoke, Picnic Area, Pickle Ball Court, WiFi, Tables and Chairs, Outdoor Kitchen, Griller, Mini Gym, Parking, Playground. Non-solo access: Common Big Kubo Cottage, Whole Basketball Court, Big Playground, Big Stage, Outdoor/Indoor Gym, Common XL Big Pool. VAT excluded.', 49999.00, 100, NULL, NULL, '3 VIP Master Bedroom, 2 Regular Room, 1 VIP Mega Family Room, 1 VIP Grand Villa, Solo Large Pool, Large Pool, Solo Billiards, Videoke, Picnic Area, Pickle Ball Court, WiFi, Tables and Chairs, Outdoor Kitchen, Griller, Mini Gym, Parking, Playground', NULL, 1, '2026-07-08 15:59:21', '2026-07-08 15:59:21'),
(8, 'Overnight Team Building - 100 Pax (2 Nights)', 'overnight-team-building-100-pax-2-nights', 'Overnight Team Building Package. 2 Nights. 100 PAX. 7 Rooms. For 2 nights: Free Hundred Islands Tour (tourism fees excluded). Same room inclusions as 1-night package. VAT excluded.', 99999.00, 100, NULL, NULL, '3 VIP Master Bedroom, 2 Regular Room, 1 VIP Mega Family Room, 1 VIP Grand Villa, Solo Large Pool, Large Pool, Solo Billiards, Videoke, Picnic Area, Pickle Ball Court, WiFi, Tables and Chairs, Outdoor Kitchen, Griller, Mini Gym, Parking, Playground, Free Hundred Islands Tour', NULL, 1, '2026-07-08 15:59:21', '2026-07-08 15:59:21'),
(9, 'Overnight Team Building - 150 Pax (1 Night)', 'overnight-team-building-150-pax-1-night', 'Overnight Team Building Package. 22HRS Stay. 150 PAX capacity. 9 Rooms: 3 VIP Master Bedroom, 1 VIP Grand Villa, 1 VIP Mega Family Room, 2 Regular Room, 1 Regular Family Room, 1 Regular Barkada Room. VAT excluded.', 59999.00, 150, NULL, NULL, '3 VIP Master Bedroom, 1 VIP Grand Villa, 1 VIP Mega Family Room, 2 Regular Room, 1 Regular Family Room, 1 Regular Barkada Room, Solo Large Pool, Large Pool, Solo Billiards, Videoke, Picnic Area, Pickle Ball Court, WiFi, Tables and Chairs, Outdoor Kitchen, Griller, Mini Gym, Parking, Playground', NULL, 1, '2026-07-08 15:59:21', '2026-07-08 15:59:21'),
(10, 'Overnight Team Building - 150 Pax (2 Nights)', 'overnight-team-building-150-pax-2-nights', 'Overnight Team Building Package. 2 Nights. 150 PAX. 9 Rooms. For 2 nights: Free Hundred Islands Tour (tourism fees excluded). VAT excluded.', 119999.00, 150, NULL, NULL, '3 VIP Master Bedroom, 1 VIP Grand Villa, 1 VIP Mega Family Room, 2 Regular Room, 1 Regular Family Room, 1 Regular Barkada Room, Solo Large Pool, Large Pool, Solo Billiards, Videoke, Picnic Area, Pickle Ball Court, WiFi, Tables and Chairs, Outdoor Kitchen, Griller, Mini Gym, Parking, Playground, Free Hundred Islands Tour', NULL, 1, '2026-07-08 15:59:21', '2026-07-08 15:59:21'),
(11, 'Overnight Team Building - Unli Pax 220 (1 Night)', 'overnight-team-building-unli-pax-220-1-night', 'Overnight Team Building Package. 22HRS Stay. Unli Pax up to 220 PAX bed capacity. 12 Rooms: 4 VIP Master Bedroom, 1 VIP Grand Villa, 1 VIP Mega Barkada Room, 1 VIP Mega Family Room, 2 Regular Room, 1 Regular Family Room, 1 Regular Barkada Room, Conference Function Hall, One Solo Pool, Billiard Table. VAT excluded.', 69999.00, 220, NULL, NULL, '4 VIP Master Bedroom, 1 VIP Grand Villa, 1 VIP Mega Barkada Room, 1 VIP Mega Family Room, 2 Regular Room, 1 Regular Family Room, 1 Regular Barkada Room, Conference Function Hall, Solo Pool, Billiard Table, Solo Large Pool, Large Pool, Videoke, Picnic Area, Pickle Ball Court, WiFi, Tables and Chairs, Outdoor Kitchen, Griller, Mini Gym, Parking, Playground', NULL, 1, '2026-07-08 15:59:21', '2026-07-08 15:59:21'),
(12, 'Overnight Team Building - Unli Pax 220 (2 Nights)', 'overnight-team-building-unli-pax-220-2-nights', 'Overnight Team Building Package. 2 Nights. Unli Pax up to 220 PAX. 12 Rooms. For 2 nights: Free Hundred Islands Tour up to 200 pax (tourism fees excluded). VAT excluded.', 139999.00, 220, NULL, NULL, '4 VIP Master Bedroom, 1 VIP Grand Villa, 1 VIP Mega Barkada Room, 1 VIP Mega Family Room, 2 Regular Room, 1 Regular Family Room, 1 Regular Barkada Room, Conference Function Hall, Solo Pool, Billiard Table, Videoke, Picnic Area, Pickle Ball Court, WiFi, Tables and Chairs, Outdoor Kitchen, Griller, Mini Gym, Parking, Playground, Free Hundred Islands Tour (up to 200 pax)', NULL, 1, '2026-07-08 15:59:21', '2026-07-08 15:59:21'),
(13, 'Hundred Islands Tour Pkg - VIP Dome Tent Room 2 Pax (1N/2D)', 'hundred-islands-tour-pkg-vip-dome-tent-room-2-pax-1n-2d', 'Hundred Islands Tour Package 1 Night/2 Days. VIP Dome Tent Room. Up to 2-4 bed capacity. 2 Persons (max 4, plus P700/person). Includes: Room Accommodation, Boat Rental/Life Vest, Tourism Environmental Fees, Shuttle Service, Exclusive Hundred Island Tour. Day 1: 7AM Registration, 8AM Port Terminal, 2-3PM Back to Resort, 2PM Check-in. Day 2: 12NN Checkout. Kids/Toddler NOT FREE. VAT excluded.', 5499.00, 4, NULL, NULL, 'VIP Dome Tent Room, Boat Rental, Life Vest, Tourism Environmental Fees, Shuttle Service, Exclusive Hundred Island Tour, Swimming Pool, Free Parking, Strong WiFi', NULL, 1, '2026-07-08 15:59:21', '2026-07-08 15:59:21'),
(14, 'Hundred Islands Tour Pkg - VIP Master Bedroom 2 Pax (1N/2D)', 'hundred-islands-tour-pkg-vip-master-bedroom-2-pax-1n-2d', 'Hundred Islands Tour Package 1 Night/2 Days. VIP Master Bedroom. Up to 6 pax bed capacity. 2 Persons (max 6, plus P700/person). Includes: Room Accommodation, Boat Rental/Life Vest, Tourism Environmental Fees, Shuttle Service, Exclusive Hundred Island Tour. VAT excluded.', 6499.00, 6, NULL, NULL, 'VIP Master Bedroom, Boat Rental, Life Vest, Tourism Environmental Fees, Shuttle Service, Exclusive Hundred Island Tour, Swimming Pool, Free Parking, Strong WiFi', NULL, 1, '2026-07-08 15:59:21', '2026-07-08 15:59:21'),
(15, 'Hundred Islands Tour Pkg - Family Private Villa 15 Pax (1N/2D)', 'hundred-islands-tour-pkg-family-private-villa-15-pax-1n-2d', 'Hundred Islands Tour Package 1 Night/2 Days. VIP Family Private Villa. 2 Rooms and Solo Pool. Up to 20 pax bed capacity. 15 Persons (max 20, plus P800/person). Includes: Room Accommodation, Boat Rental/Life Vest, Tourism Environmental Fees, Shuttle Service, Exclusive Hundred Island Tour. VAT excluded.', 18499.00, 20, NULL, NULL, 'Family Private Villa with Private Pool, 2 Aircon Rooms, Boat Rental, Life Vest, Tourism Environmental Fees, Shuttle Service, Exclusive Hundred Island Tour, Swimming Pool, Free Parking, Strong WiFi', NULL, 1, '2026-07-08 15:59:21', '2026-07-08 15:59:21'),
(16, 'Hundred Islands Tour Pkg - Barkada Private Villa 20 Pax (1N/2D)', 'hundred-islands-tour-pkg-barkada-private-villa-20-pax-1n-2d', 'Hundred Islands Tour Package 1 Night/2 Days. VIP Barkada Private Villa. 4 Rooms and Solo Pool. Up to 40 pax bed capacity. 20 Persons (max 40, plus P800/person). Includes: Room Accommodation, Boat Rental/Life Vest, Tourism Environmental Fees, Shuttle Service, Exclusive Hundred Island Tour. VAT excluded.', 20999.00, 40, NULL, NULL, 'Barkada Private Villa with Private Pool, 4 Rooms, Boat Rental, Life Vest, Tourism Environmental Fees, Shuttle Service, Exclusive Hundred Island Tour, Swimming Pool, Free Parking, Strong WiFi', NULL, 1, '2026-07-08 15:59:21', '2026-07-08 15:59:21'),
(17, 'Hundred Islands Tour Pkg - Mega Family Room 10 Pax (1N/2D)', 'hundred-islands-tour-pkg-mega-family-room-10-pax-1n-2d', 'Hundred Islands Tour Package 1 Night/2 Days. VIP Mega Family Room. Up to 24 pax bed capacity. 10 Persons (max 24, plus P700/person). Includes: Room Accommodation, Boat Rental/Life Vest, Tourism Environmental Fees, Shuttle Service, Exclusive Hundred Island Tour. VAT excluded.', 10999.00, 24, NULL, NULL, 'Mega Family Room, Boat Rental, Life Vest, Tourism Environmental Fees, Shuttle Service, Exclusive Hundred Island Tour, Swimming Pool, Free Parking, Strong WiFi', NULL, 1, '2026-07-08 15:59:21', '2026-07-08 15:59:21'),
(18, 'Hundred Islands Tour Pkg - Private Grand Villa 30 Pax (1N/2D)', 'hundred-islands-tour-pkg-private-grand-villa-30-pax-1n-2d', 'Hundred Islands Tour Package 1 Night/2 Days. VIP Private Grand Villa. 3 Rooms and Solo Pool. Up to 75 pax bed capacity. 30 Persons (max 75, plus P800/person). Includes: Room Accommodation, Boat Rental/Life Vest, Tourism Environmental Fees, Shuttle Service, Exclusive Hundred Island Tour. VAT excluded.', 26499.00, 75, NULL, NULL, 'Private Grand Villa with Private Pool, 3 Aircon Rooms, Boat Rental, Life Vest, Tourism Environmental Fees, Shuttle Service, Exclusive Hundred Island Tour, Swimming Pool, Free Parking, Strong WiFi', NULL, 1, '2026-07-08 15:59:21', '2026-07-08 15:59:21'),
(19, 'Hundred Islands Tour Pkg - Mega Barkada Room 15 Pax (1N/2D)', 'hundred-islands-tour-pkg-mega-barkada-room-15-pax-1n-2d', 'Hundred Islands Tour Package 1 Night/2 Days. VIP Mega Barkada Room. Up to 50 pax bed capacity. 15 Persons (max 50, plus P700/person). Includes: Room Accommodation, Boat Rental/Life Vest, Tourism Environmental Fees, Shuttle Service, Exclusive Hundred Island Tour. VAT excluded.', 13499.00, 50, NULL, NULL, 'Mega Barkada Room, Boat Rental, Life Vest, Tourism Environmental Fees, Shuttle Service, Exclusive Hundred Island Tour, Swimming Pool, Free Parking, Strong WiFi', NULL, 1, '2026-07-08 15:59:21', '2026-07-08 15:59:21'),
(20, 'Hundred Islands Tour Pkg - Grand Villa Room 20 Pax (1N/2D)', 'hundred-islands-tour-pkg-grand-villa-room-20-pax-1n-2d', 'Hundred Islands Tour Package 1 Night/2 Days. VIP Grand Villa Room. Up to 50 pax bed capacity. 20 Persons (max 50, plus P700/person). Includes: Room Accommodation, Boat Rental/Life Vest, Tourism Environmental Fees, Shuttle Service, Exclusive Hundred Island Tour. VAT excluded.', 17999.00, 50, NULL, NULL, 'Grand Villa Room, Boat Rental, Life Vest, Tourism Environmental Fees, Shuttle Service, Exclusive Hundred Island Tour, Swimming Pool, Free Parking, Strong WiFi', NULL, 1, '2026-07-08 15:59:21', '2026-07-08 15:59:21');

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
  ADD KEY `fk_bookings_bundle` (`bundle_id`),
  ADD KEY `idx_booking_source` (`booking_source`);

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
-- Indexes for table `promotions`
--
ALTER TABLE `promotions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_is_active` (`is_active`);

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
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `booking_logs`
--
ALTER TABLE `booking_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `booking_status`
--
ALTER TABLE `booking_status`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

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
-- AUTO_INCREMENT for table `promotions`
--
ALTER TABLE `promotions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

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
  ADD CONSTRAINT `fk_bookings_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

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
