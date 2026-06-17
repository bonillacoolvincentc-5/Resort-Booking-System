<?php
/*
 * Database Configuration File
 * YP4K Resort - Booking & Trail Guide System
 */

// Database connection parameters
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'yp4k_resort_db');

// Create connection
global $conn;
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8
$conn->set_charset("utf8mb4");

// Optional: Enable error reporting (disable in production)
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

?>
