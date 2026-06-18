<?php
/**
 * Update Booking Status (Admin)
 * Allows admin to approve, reject, complete, or cancel bookings
 */

session_start();
require_once '../phpAuthentication/db_config.php';

header('Content-Type: application/json');

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'You must be logged in as admin to update booking status'
    ]);
    exit();
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        throw new Exception('Invalid request data');
    }
    
    $bookingId = $data['booking_id'] ?? null;
    $newStatus = $data['status'] ?? null;
    $notes = $data['notes'] ?? null;
    
    if (!$bookingId || !$newStatus) {
        throw new Exception('Booking ID and status are required');
    }
    
    // Validate status
    $validStatuses = ['pending_review', 'approved', 'rejected', 'completed', 'cancelled'];
    if (!in_array($newStatus, $validStatuses)) {
        throw new Exception('Invalid status');
    }
    
    // Get current booking status
    $stmt = $conn->prepare("SELECT status_id, user_id FROM bookings WHERE id = ?");
    $stmt->bind_param('i', $bookingId);
    $stmt->execute();
    $result = $stmt->get_result();
    $booking = $result->fetch_assoc();
    
    if (!$booking) {
        throw new Exception('Booking not found');
    }
    
    // Get new status ID
    $stmt = $conn->prepare("SELECT id FROM booking_status WHERE name = ?");
    $stmt->bind_param('s', $newStatus);
    $stmt->execute();
    $result = $stmt->get_result();
    $status = $result->fetch_assoc();
    
    if (!$status) {
        throw new Exception('Status not found');
    }
    
    $newStatusId = $status['id'];
    $oldStatusId = $booking['status_id'];
    
    // Update booking status
    $stmt = $conn->prepare("UPDATE bookings SET status_id = ?, updated_at = NOW() WHERE id = ?");
    $stmt->bind_param('ii', $newStatusId, $bookingId);
    $stmt->execute();
    
    // Log the status change
    logBookingChange($conn, $bookingId, $oldStatusId, $newStatusId, 'admin', $_SESSION['admin_id'], $notes);
    
    // If rejecting or cancelling, restore availability
    if (in_array($newStatus, ['rejected', 'cancelled'])) {
        restoreAvailability($conn, $bookingId);
    }
    
    echo json_encode([
        'success' => true,
        'message' => "Booking status updated to $newStatus"
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
 * Restore availability when booking is cancelled or rejected
 */
function restoreAvailability($conn, $bookingId) {
    // FIX #10: fetch bundle_id in addition to activity_id / accommodation_id
    $stmt = $conn->prepare("
        SELECT booking_type, activity_id, accommodation_id, bundle_id, check_in_date, check_in_time
        FROM bookings
        WHERE id = ?
    ");
    $stmt->bind_param('i', $bookingId);
    $stmt->execute();
    $result = $stmt->get_result();
    $booking = $result->fetch_assoc();

    if (!$booking) return;

    $itemType = $booking['booking_type'];

    // FIX #10: correctly resolve item ID for all three booking types
    switch ($itemType) {
        case 'activity':      $itemId = $booking['activity_id'];      break;
        case 'accommodation': $itemId = $booking['accommodation_id']; break;
        case 'bundle':        $itemId = $booking['bundle_id'];        break;
        default:              $itemId = null;
    }

    if (!$itemId) return; // nothing to restore

    $date     = $booking['check_in_date'];
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
