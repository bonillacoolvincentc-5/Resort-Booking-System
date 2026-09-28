<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized. Please log in.']);
    exit();
}

require_once '../phpAuthentication/db_config.php';
require_once '../phpAuthentication/blocked_email_helper.php';

$userId = $_SESSION['user_id'];
kick_blocked_user_session($conn, true);
$currentPassword = $_POST['current_password'] ?? '';
$newPassword = $_POST['new_password'] ?? '';

if (empty($currentPassword) || empty($newPassword)) {
    echo json_encode(['success' => false, 'message' => 'Please provide both current and new passwords.']);
    exit();
}

if (strlen($newPassword) < 6) {
    echo json_encode(['success' => false, 'message' => 'New password must be at least 6 characters long.']);
    exit();
}

// Fetch current password hash from DB
$stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    echo json_encode(['success' => false, 'message' => 'User account not found.']);
    exit();
}

if (!password_verify($currentPassword, $user['password'])) {
    echo json_encode(['success' => false, 'message' => 'Incorrect current password!']);
    exit();
}

// Hash new password and save
$newHashed = password_hash($newPassword, PASSWORD_DEFAULT);
$updateStmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
$updateStmt->bind_param("si", $newHashed, $userId);

if ($updateStmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Password changed successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update password in database.']);
}

$updateStmt->close();
$conn->close();
?>
