<?php
/**
 * Get Booking Details API
 * Returns detailed information about a specific booking
 */

session_start();
require_once '../phpAuthentication/db_config.php';

header('Content-Type: application/json');

// Check if user is logged in (user or admin)
$isAdmin = isset($_SESSION['admin_id']);
$isUser = isset($_SESSION['user_id']);

if (!$isAdmin && !$isUser) {
    echo json_encode([
        'success' => false,
        'message' => 'You must be logged in to view booking details'
    ]);
    exit();
}

try {
    $bookingId = $_GET['booking_id'] ?? null;
    
    if (!$bookingId) {
        throw new Exception('Booking ID is required');
    }
    
    $query = "
        SELECT 
            b.*,
            bs.name as status_name,
            bs.description as status_description,
            bs.color as status_color,
            u.name as user_name,
            u.email as user_email,
            u.phone_number as user_phone,
            CASE 
                WHEN b.booking_type = 'activity' THEN a.name
                ELSE acc.name
            END as item_name,
            CASE 
                WHEN b.booking_type = 'activity' THEN a.description
                ELSE acc.description
            END as item_description,
            CASE 
                WHEN b.booking_type = 'activity' THEN a.icon
                ELSE NULL
            END as item_icon,
            CASE 
                WHEN b.booking_type = 'activity' THEN a.image_url
                ELSE acc.image_url
            END as item_image_url,
            pr.file_name as receipt_file_name,
            pr.file_path as receipt_file_path,
            pr.file_size as receipt_file_size,
            pr.uploaded_at as receipt_uploaded_at
        FROM bookings b
        LEFT JOIN booking_status bs ON b.status_id = bs.id
        LEFT JOIN users u ON b.user_id = u.id
        LEFT JOIN activities a ON b.activity_id = a.id
        LEFT JOIN accommodations acc ON b.accommodation_id = acc.id
        LEFT JOIN payment_receipts pr ON b.id = pr.booking_id
        WHERE b.id = ?
    ";
    
    // If user (not admin), only show their own bookings
    if (!$isAdmin && $isUser) {
        $query .= " AND b.user_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('ii', $bookingId, $_SESSION['user_id']);
    } else {
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $bookingId);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $booking = $result->fetch_assoc();
    
    if (!$booking) {
        throw new Exception('Booking not found');
    }
    
    // Get booking logs
    $logQuery = "
        SELECT 
            bl.*,
            bs_old.name as old_status_name,
            bs_new.name as new_status_name,
            CASE 
                WHEN bl.changed_by = 'admin' THEN (SELECT name FROM admins WHERE id = bl.changed_by_id)
                WHEN bl.changed_by = 'user' THEN (SELECT name FROM users WHERE id = bl.changed_by_id)
                ELSE 'System'
            END as changed_by_name
        FROM booking_logs bl
        LEFT JOIN booking_status bs_old ON bl.old_status_id = bs_old.id
        LEFT JOIN booking_status bs_new ON bl.new_status_id = bs_new.id
        WHERE bl.booking_id = ?
        ORDER BY bl.created_at ASC
    ";
    
    $stmt = $conn->prepare($logQuery);
    $stmt->bind_param('i', $bookingId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $logs = [];
    while ($row = $result->fetch_assoc()) {
        $logs[] = $row;
    }
    
    $booking['logs'] = $logs;
    
    echo json_encode([
        'success' => true,
        'booking' => $booking
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
