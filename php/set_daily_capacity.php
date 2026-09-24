<?php
session_start();
require_once '../phpAuthentication/db_config.php';

header('Content-Type: application/json');

$isAdmin = isset($_SESSION['admin_id']) || (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true);
$isStaff = isset($_SESSION['is_staff']) && $_SESSION['is_staff'] === true;

if (!$isAdmin && !$isStaff) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$conn->query("
    CREATE TABLE IF NOT EXISTS `daily_resort_capacity` (
        `target_date` date NOT NULL,
        `max_guests` int(11) NOT NULL,
        `updated_by` int(11) DEFAULT NULL,
        `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
        PRIMARY KEY (`target_date`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

$date = $_POST['date'] ?? '';
$capacity = isset($_POST['capacity']) ? intval($_POST['capacity']) : null;
$userId = $_SESSION['admin_id'] ?? ($_SESSION['staff_id'] ?? null);

if (empty($date) || $capacity === null || $capacity < 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit();
}

$today = date('Y-m-d');
if ($date <= $today) {
    echo json_encode(['success' => false, 'message' => 'Cannot set guest limits for today or past dates. Only tomorrow onwards.']);
    exit();
}

if ($capacity === 0) {
    // Treat 0 as deleting the override (fallback to default)
    $stmt = $conn->prepare("DELETE FROM daily_resort_capacity WHERE target_date = ?");
    $stmt->bind_param('s', $date);
    $stmt->execute();
} else {
    // Insert or update
    $stmt = $conn->prepare("
        INSERT INTO daily_resort_capacity (target_date, max_guests, updated_by) 
        VALUES (?, ?, ?) 
        ON DUPLICATE KEY UPDATE max_guests = VALUES(max_guests), updated_by = VALUES(updated_by)
    ");
    $stmt->bind_param('sii', $date, $capacity, $userId);
    $stmt->execute();
}

echo json_encode(['success' => true]);
