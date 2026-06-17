<?php
/**
 * Get User Bookings API
 * Returns bookings for the logged-in user
 */

session_start();
require_once '../phpAuthentication/db_config.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'You must be logged in to view bookings'
    ]);
    exit();
}

try {
    $userId = $_SESSION['user_id'];
    $statusFilter = $_GET['status'] ?? null;
    
    $query = "
        SELECT 
            b.id,
            b.booking_reference,
            b.booking_type,
            b.activity_id,
            b.accommodation_id,
            b.full_name,
            b.email,
            b.phone_number,
            b.check_in_date,
            b.check_in_time,
            b.check_out_date,
            b.check_out_time,
            b.number_of_days,
            b.number_of_guests,
            b.special_requests,
            b.total_amount,
            b.payment_status,
            b.created_at,
            bs.name as status_name,
            bs.description as status_description,
            bs.color as status_color,
            CASE 
                WHEN b.booking_type = 'activity' THEN a.name
                ELSE acc.name
            END as item_name,
            CASE 
                WHEN b.booking_type = 'activity' THEN a.icon
                ELSE NULL
            END as item_icon
        FROM bookings b
        LEFT JOIN booking_status bs ON b.status_id = bs.id
        LEFT JOIN activities a ON b.activity_id = a.id
        LEFT JOIN accommodations acc ON b.accommodation_id = acc.id
        WHERE b.user_id = ?
    ";
    
    $params = [$userId];
    $types = 'i';
    
    if ($statusFilter) {
        $query .= " AND bs.name = ?";
        $params[] = $statusFilter;
        $types .= 's';
    }
    
    $query .= " ORDER BY b.created_at DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $bookings = [];
    while ($row = $result->fetch_assoc()) {
        $bookings[] = $row;
    }
    
    // Get booking counts by status
    $statsQuery = "
        SELECT 
            bs.name as status,
            COUNT(*) as count
        FROM bookings b
        JOIN booking_status bs ON b.status_id = bs.id
        WHERE b.user_id = ?
        GROUP BY bs.name
    ";
    
    $stmt = $conn->prepare($statsQuery);
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $stats = [];
    while ($row = $result->fetch_assoc()) {
        $stats[$row['status']] = $row['count'];
    }
    
    echo json_encode([
        'success' => true,
        'bookings' => $bookings,
        'stats' => $stats
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
