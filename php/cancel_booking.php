<?php
/**
 * Cancel Booking (User)
 * Allows users to cancel their own pending bookings
 */

session_start();
require_once '../phpAuthentication/db_config.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'You must be logged in to cancel a booking'
    ]);
    exit();
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        throw new Exception('Invalid request data');
    }
    
    $bookingId = $data['booking_id'] ?? null;
    
    if (!$bookingId) {
        throw new Exception('Booking ID is required');
    }
    
    // Get booking details
    $stmt = $conn->prepare("
        SELECT b.id, b.user_id, b.status_id, b.booking_type, b.activity_id, b.accommodation_id, b.check_in_date, b.check_in_time
        FROM bookings b
        WHERE b.id = ?
    ");
    $stmt->bind_param('i', $bookingId);
    $stmt->execute();
    $result = $stmt->get_result();
    $booking = $result->fetch_assoc();
    
    if (!$booking) {
        throw new Exception('Booking not found');
    }
    
    // Check if booking belongs to user
    if ($booking['user_id'] != $_SESSION['user_id']) {
        throw new Exception('You can only cancel your own bookings');
    }
    
    // Check if booking can be cancelled (only pending_review or approved)
    $stmt = $conn->prepare("SELECT name FROM booking_status WHERE id = ?");
    $stmt->bind_param('i', $booking['status_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $status = $result->fetch_assoc();
    
    if (!in_array($status['name'], ['pending_review', 'approved'])) {
        throw new Exception('This booking cannot be cancelled');
    }
    
    // Get cancelled status ID
    $stmt = $conn->prepare("SELECT id FROM booking_status WHERE name = 'cancelled'");
    $stmt->execute();
    $result = $stmt->get_result();
    $cancelledStatus = $result->fetch_assoc();
    
    // Update booking status
    $stmt = $conn->prepare("UPDATE bookings SET status_id = ?, updated_at = NOW() WHERE id = ?");
    $stmt->bind_param('ii', $cancelledStatus['id'], $bookingId);
    $stmt->execute();
    
    // Log the status change
    logBookingChange($conn, $bookingId, $booking['status_id'], $cancelledStatus['id'], 'user', $_SESSION['user_id'], 'User cancelled booking');
    
    // Restore availability
    restoreAvailability($conn, $booking);
    
    echo json_encode([
        'success' => true,
        'message' => 'Booking cancelled successfully'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

/**
 * Log booking status change
 */
function logBookingChange($conn, $bookingId, $oldStatusId, $newStatusId, $changedBy, $changedById, $notes = null) {
    $stmt = $conn->prepare("
        INSERT INTO booking_logs (booking_id, old_status_id, new_status_id, changed_by, changed_by_id, notes)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param('iiisis', $bookingId, $oldStatusId, $newStatusId, $changedBy, $changedById, $notes);
    $stmt->execute();
}

/**
 * Restore availability when booking is cancelled
 */
function restoreAvailability($conn, $booking) {
    $itemType = $booking['booking_type'];
    $itemId = $itemType === 'activity' ? $booking['activity_id'] : $booking['accommodation_id'];
    $date = $booking['check_in_date'];
    $timeSlot = $booking['check_in_time'];
    
    // Update availability
    $query = "
        UPDATE availability
        SET available_slots = available_slots + 1,
            is_available = 1,
            updated_at = NOW()
        WHERE item_type = ? AND item_id = ? AND date = ?
    ";
    
    $params = [$itemType, $itemId, $date];
    $types = 'sis';
    
    if ($timeSlot) {
        $query .= " AND time_slot = ?";
        $params[] = $timeSlot;
        $types .= 's';
    }
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
}
?>
