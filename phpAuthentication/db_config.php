<?php
/*
 * Database Configuration File
 * YP4K Resort - Booking & Trail Guide System
 */

// Read from .env
$env_path = dirname(__DIR__) . '/.env';
$env = file_exists($env_path) ? parse_ini_file($env_path) : [];

// Database connection parameters
define('DB_HOST', $env['DB_HOST'] ?? 'localhost');
define('DB_USER', $env['DB_USER'] ?? 'root');
define('DB_PASS', $env['DB_PASS'] ?? '');
define('DB_NAME', $env['DB_NAME'] ?? 'yp4k_resort_db');

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
