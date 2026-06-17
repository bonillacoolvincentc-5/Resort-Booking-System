<?php
/*
 * Logout Process
 * YP4K Resort - Booking & Trail Guide System
 */

session_start();

// Destroy session
session_destroy();

// Clear remember token cookie if exists
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/', '', true, true);
}

// Redirect to home page
header("Location: ../index-new.html?message=" . urlencode("You have been logged out successfully."));
exit();
?>
