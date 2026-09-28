<?php
/*
 * Logout Process
 * YP4K Resort - Booking & Trail Guide System
 */

session_start();

// Log admin/staff logout
if (isset($_SESSION['admin_id'])) {
    require_once 'db_config.php';
    $logRole = isset($_SESSION['is_staff']) && $_SESSION['is_staff'] === true ? 'staff' : 'admin';
    $logUserId = $_SESSION['admin_id'];
    $logAction = 'logout';
    $logDesc = ucfirst($logRole) . " logged out.";
    $logStmt = $conn->prepare("INSERT INTO activity_logs (user_id, role, action_type, description) VALUES (?, ?, ?, ?)");
    $logStmt->bind_param('isss', $logUserId, $logRole, $logAction, $logDesc);
    $logStmt->execute();
    $logStmt->close();
}

// Destroy session
session_destroy();

// Clear remember token cookie if exists
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/', '', true, true);
}

// Redirect to home page
header("Location: ../index.html?message=" . urlencode("You have been logged out successfully."));
exit();
?>
