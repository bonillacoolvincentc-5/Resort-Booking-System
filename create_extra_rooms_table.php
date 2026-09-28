<?php
require_once 'c:/xampp/htdocs/YP4K_Resort/phpAuthentication/db_config.php';
global $conn;

$sql = "
CREATE TABLE IF NOT EXISTS `booking_extra_rooms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_id` int(11) NOT NULL,
  `accommodation_id` int(11) NOT NULL,
  `num_guests` int(11) NOT NULL DEFAULT 1,
  `check_in_date` date NOT NULL,
  `check_out_date` date NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_booking_id` (`booking_id`),
  KEY `fk_accommodation_id` (`accommodation_id`),
  CONSTRAINT `fk_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

if ($conn->query($sql) === TRUE) {
    echo "Table booking_extra_rooms created successfully";
} else {
    echo "Error creating table: " . $conn->error;
}
?>
