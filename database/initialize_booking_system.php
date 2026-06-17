<?php
/**
 * Initialize Booking System Database Tables
 * Run this file once to create all booking-related tables
 */

require_once '../phpAuthentication/db_config.php';

try {
    // Read and execute the SQL file
    $sqlFile = __DIR__ . '/booking_system.sql';
    if (!file_exists($sqlFile)) {
        die("Error: SQL file not found at $sqlFile");
    }
    
    $sql = file_get_contents($sqlFile);
    
    // Split SQL statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($statements as $statement) {
        if (!empty($statement) && !preg_match('/^--/', $statement)) {
            $conn->query($statement);
        }
    }
    
    echo "✓ Booking system database tables created successfully!<br>";
    echo "✓ All foreign keys and constraints have been set up.<br>";
    echo "✓ Default booking statuses have been inserted.<br>";
    
} catch (Exception $e) {
    die("Error initializing booking system: " . $e->getMessage());
}
?>
