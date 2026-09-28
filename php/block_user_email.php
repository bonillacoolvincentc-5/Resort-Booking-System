<?php
/**
 * YP4K Resort - Block / Unblock User Email
 * Handles admin block and unblock actions from User Management.
 */
session_start();
require_once '../phpAuthentication/db_config.php';
require_once '../phpAuthentication/blocked_email_helper.php';

header('Content-Type: application/json');

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Invalid input data.']);
    exit();
}

$action = strtolower(trim($input['action'] ?? ''));
$userId = intval($input['user_id'] ?? 0);

if (!in_array($action, ['block', 'unblock'], true) || $userId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid block request.']);
    exit();
}

try {
    ensure_blocked_emails_table($conn);

    $stmt = $conn->prepare("SELECT id, email FROM users WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'User not found.']);
        exit();
    }

    $email = normalize_blocked_email($user['email']);
    if ($email === '') {
        echo json_encode(['success' => false, 'message' => 'User email is missing.']);
        exit();
    }

    $blockedBy = intval($_SESSION['admin_id'] ?? 0);

    if ($action === 'block') {
        $insert = $conn->prepare("
            INSERT INTO blocked_emails (email, user_id, reason, blocked_by)
            VALUES (?, ?, 'Blocked by admin', ?)
            ON DUPLICATE KEY UPDATE user_id = VALUES(user_id), blocked_by = VALUES(blocked_by)
        ");
        $insert->bind_param("sii", $email, $userId, $blockedBy);
        $insert->execute();
        $insert->close();

        echo json_encode(['success' => true, 'message' => 'Email blocked successfully.']);
        exit();
    }

    $delete = $conn->prepare("DELETE FROM blocked_emails WHERE email = ?");
    $delete->bind_param("s", $email);
    $delete->execute();
    $delete->close();

    echo json_encode(['success' => true, 'message' => 'Email unblocked successfully.']);
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to update block status.']);
}
