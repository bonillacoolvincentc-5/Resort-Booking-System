<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    http_response_code(401);
    exit(json_encode(['error' => 'Unauthorized']));
}
require_once __DIR__ . '/config.php';
admin_include('db_config.php');
global $conn;

header('Content-Type: application/json');

$notifications = [];

// Fetch latest bookings and users
$query = "
    (SELECT 
        'booking' as type,
        'New Booking' as title,
        CONCAT(b.full_name, ' - ', COALESCE(a.name, acc.name, 'Resort Booking')) as descr,
        b.created_at as created_at,
        b.id as reference_id
    FROM bookings b
    LEFT JOIN activities a ON b.activity_id = a.id
    LEFT JOIN accommodations acc ON b.accommodation_id = acc.id
    ORDER BY b.created_at DESC
    LIMIT 5)
    UNION ALL
    (SELECT 
        'user' as type,
        'New User' as title,
        CONCAT(u.first_name, ' ', u.last_name, ' registered') as descr,
        u.created_at as created_at,
        u.id as reference_id
    FROM users u
    ORDER BY u.created_at DESC
    LIMIT 5)
    UNION ALL
    (SELECT 
        'booking' as type,
        'Booking Cancelled' as title,
        CONCAT(b.full_name, ' cancelled their booking') as descr,
        bl.created_at as created_at,
        b.id as reference_id
    FROM booking_logs bl
    JOIN bookings b ON bl.booking_id = b.id
    JOIN booking_status bs ON bl.new_status_id = bs.id
    WHERE bs.name = 'cancelled' AND bl.changed_by = 'user'
    ORDER BY bl.created_at DESC
    LIMIT 5)
    ORDER BY created_at DESC
    LIMIT 8
";

$result = $conn->query($query);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        // Calculate time ago
        $timeAgo = 'Just now';
        if (!empty($row['created_at'])) {
            $diff = time() - strtotime($row['created_at']);
            if ($diff < 60) {
                $timeAgo = 'Just now';
            } elseif ($diff < 3600) {
                $mins = floor($diff / 60);
                $timeAgo = $mins . ' min' . ($mins > 1 ? 's' : '') . ' ago';
            } elseif ($diff < 86400) {
                $hrs = floor($diff / 3600);
                $timeAgo = $hrs . ' hr' . ($hrs > 1 ? 's' : '') . ' ago';
            } elseif ($diff < 604800) {
                $days = floor($diff / 86400);
                $timeAgo = $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
            } else {
                $timeAgo = date('M d, Y', strtotime($row['created_at']));
            }
        }

        $notifications[] = [
            'type' => $row['type'],
            'title' => $row['title'],
            'desc' => $row['descr'],
            'time' => $timeAgo,
            'timestamp' => $row['created_at'],
            'url' => ($row['type'] === 'booking') ? 'bookings.php' : 'users.php'
        ];
    }
}

echo json_encode([
    'status' => 'success',
    'notifications' => $notifications
]);
?>
