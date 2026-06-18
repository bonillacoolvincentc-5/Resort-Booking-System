<?php
/**
 * Block / Unblock Date API (Admin)
 */

session_start();
require_once '../phpAuthentication/db_config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id']) && (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true)) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$conn->query("
    CREATE TABLE IF NOT EXISTS `blocked_dates` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `blocked_date` date NOT NULL,
        `reason` text DEFAULT NULL,
        `blocked_by` int(11) DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        PRIMARY KEY (`id`),
        UNIQUE KEY `unique_blocked_date` (`blocked_date`),
        KEY `idx_blocked_date` (`blocked_date`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$action = trim($_POST['action'] ?? 'block');
$date   = trim($_POST['date'] ?? '');
$reason = trim($_POST['reason'] ?? '');

if (!$date || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    echo json_encode(['success' => false, 'message' => 'Valid date is required']);
    exit();
}

$adminId = intval($_SESSION['admin_id'] ?? 0);

try {
    if ($action === 'unblock') {
        $stmt = $conn->prepare("DELETE FROM blocked_dates WHERE blocked_date = ?");
        $stmt->bind_param('s', $date);
        $stmt->execute();
        echo json_encode(['success' => true, 'message' => 'Date unblocked successfully']);
        exit();
    }

    if ($reason === '') {
        echo json_encode(['success' => false, 'message' => 'Reason is required to block a date']);
        exit();
    }

    $stmt = $conn->prepare("
        INSERT INTO blocked_dates (blocked_date, reason, blocked_by)
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE reason = VALUES(reason), blocked_by = VALUES(blocked_by)
    ");
    $stmt->bind_param('ssi', $date, $reason, $adminId);
    $stmt->execute();

    echo json_encode(['success' => true, 'message' => 'Date blocked successfully']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
