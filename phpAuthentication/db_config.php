<?php
/*
 * Database Configuration File
 * YP4K Resort - Booking & Trail Guide System
 */

// TEMPORARY: Enable error display to catch the 500 Error reason!
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Safe .env parsing for free hosts that disable parse_ini_file
function safe_parse_env($path) {
    if (!file_exists($path)) return [];
    $env = [];
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $env[trim($name)] = trim($value, " \t\n\r\0\x0B\"'");
        }
    }
    return $env;
}

$env_path = dirname(__DIR__) . '/.env';
$env = function_exists('parse_ini_file') ? @parse_ini_file($env_path) : safe_parse_env($env_path);
if ($env === false) $env = safe_parse_env($env_path);

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
