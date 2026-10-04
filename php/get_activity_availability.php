<?php
/**
 * Activity daily availability
 * GET ?date=YYYY-MM-DD  ->  remaining spots (daily booking limit minus what is already booked) per activity
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../phpAuthentication/db_config.php';
require_once __DIR__ . '/check_expired_bookings.php';
require_once __DIR__ . '/activity_daily_usage.php';

if (!headers_sent()) {
    header('Content-Type: application/json');
}

$date = $_GET['date'] ?? '';
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    echo json_encode(['success' => false, 'message' => 'A valid date (YYYY-MM-DD) is required.']);
    exit();
}

try {
    echo json_encode([
        'success'    => true,
        'date'       => $date,
        'activities' => getActivityDailyUsage($conn, $date),
    ]);
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => 'Could not load availability.']);
}
