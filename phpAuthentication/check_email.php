<?php
/*
 * Check Email Availability Endpoint
 * YP4K Resort - Booking & Trail Guide System
 * Checks in real-time whether an email already exists in users or admins
 */

ob_start();
header('Content-Type: application/json');

require_once __DIR__ . '/db_config.php';
require_once __DIR__ . '/blocked_email_helper.php';

$email = trim($_GET['email'] ?? $_POST['email'] ?? '');

if (empty($email)) {
    ob_clean();
    echo json_encode([
        'success' => false,
        'message' => 'Email address is required.'
    ]);
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    ob_clean();
    echo json_encode([
        'success' => false,
        'valid_format' => false,
        'message' => 'Please enter a valid email format.'
    ]);
    exit();
}

try {
    $blocked = is_email_blocked($conn, $email);

    // Check in users table
    $stmtUsers = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
    $stmtUsers->bind_param("s", $email);
    $stmtUsers->execute();
    $resultUsers = $stmtUsers->get_result();
    $userExists = ($resultUsers->num_rows > 0);
    $stmtUsers->close();

    // Check in admins table
    $adminExists = false;
    $stmtAdmins = $conn->prepare("SELECT id FROM admins WHERE email = ? LIMIT 1");
    if ($stmtAdmins) {
        $stmtAdmins->bind_param("s", $email);
        $stmtAdmins->execute();
        $resultAdmins = $stmtAdmins->get_result();
        $adminExists = ($resultAdmins->num_rows > 0);
        $stmtAdmins->close();
    }

    $exists = $userExists || $adminExists;

    ob_clean();
    echo json_encode([
        'success' => true,
        'valid_format' => true,
        'blocked' => $blocked,
        'exists' => $exists || $blocked,
        'available' => !$exists && !$blocked,
        'message' => $blocked
            ? blocked_email_message('register')
            : ($exists ? 'This email is already registered.' : 'Email is available.')
    ]);
} catch (Throwable $e) {
    error_log("Check email error: " . $e->getMessage());
    ob_clean();
    echo json_encode([
        'success' => false,
        'message' => 'Unable to verify email availability at this time.'
    ]);
}
