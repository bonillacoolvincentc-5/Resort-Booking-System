<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

admin_include('db_config.php');
global $conn;

$range = strtolower(trim($_GET['range'] ?? 'week'));
if (!in_array($range, ['week', 'month', 'year'], true)) {
    $range = 'week';
}

$series = [];
$labels = [];

try {
    if ($range === 'year') {
        for ($i = 11; $i >= 0; $i--) {
            $dt = new DateTime('first day of this month');
            $dt->modify("-$i months");
            $key = $dt->format('Y-m');
            $series[$key] = 0;
            $labels[$key] = $dt->format('M Y');
        }
        $stmt = $conn->prepare("
            SELECT DATE_FORMAT(created_at, '%Y-%m') AS period, COUNT(*) AS cnt
            FROM bookings
            WHERE created_at >= DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 11 MONTH), '%Y-%m-01')
            GROUP BY period
        ");
    } elseif ($range === 'month') {
        for ($i = 29; $i >= 0; $i--) {
            $dt = new DateTime("-$i days");
            $key = $dt->format('Y-m-d');
            $series[$key] = 0;
            $labels[$key] = $dt->format('M j');
        }
        $stmt = $conn->prepare("
            SELECT DATE(created_at) AS period, COUNT(*) AS cnt
            FROM bookings
            WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 29 DAY)
            GROUP BY DATE(created_at)
        ");
    } else {
        for ($i = 6; $i >= 0; $i--) {
            $dt = new DateTime("-$i days");
            $key = $dt->format('Y-m-d');
            $series[$key] = 0;
            $labels[$key] = $dt->format('D j');
        }
        $stmt = $conn->prepare("
            SELECT DATE(created_at) AS period, COUNT(*) AS cnt
            FROM bookings
            WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
            GROUP BY DATE(created_at)
        ");
    }

    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $period = $row['period'];
        if (array_key_exists($period, $series)) {
            $series[$period] = (int) $row['cnt'];
        }
    }

    echo json_encode([
        'success' => true,
        'range'   => $range,
        'labels'  => array_values($labels),
        'data'    => array_values($series)
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to load bookings overview']);
}
