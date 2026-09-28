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
$firstName = trim($_POST['first_name'] ?? '');
$lastName = trim($_POST['last_name'] ?? '');
$phone = trim($_POST['phone_number'] ?? '');
$address = trim($_POST['address'] ?? '');
$birthdate = !empty($_POST['birthdate']) ? $_POST['birthdate'] : null;

if (empty($firstName) || empty($lastName)) {
    echo json_encode(['success' => false, 'message' => 'First Name and Last Name are required.']);
    exit();
}

// Update database
$stmt = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, phone_number = ?, address = ?, birthdate = ? WHERE id = ?");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    exit();
}

$stmt->bind_param("sssssi", $firstName, $lastName, $phone, $address, $birthdate, $userId);

if ($stmt->execute()) {
    $_SESSION['user_name'] = trim($firstName . ' ' . $lastName);
    echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update profile: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
