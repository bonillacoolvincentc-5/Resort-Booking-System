<?php
/**
 * Update Booking Status (Admin)
 * Handles "Approve First, Pay Later (2hr window)" transitions, verification, cancellations, and capacity.
 */

session_start();
require_once __DIR__ . '/../phpAuthentication/db_config.php';
require_once __DIR__ . '/check_expired_bookings.php';

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
    
    $bookingId = intval($data['booking_id'] ?? 0);
    $newStatus = trim($data['status'] ?? '');
    $notes = $data['notes'] ?? null;
    
    if (!$bookingId || !$newStatus) {
        throw new Exception('Booking ID and status are required');
    }
    
    // Validate status
    $validStatuses = ['pending_review', 'awaiting_payment', 'payment_verifying', 'approved', 'rejected', 'completed', 'cancelled'];
    if (!in_array($newStatus, $validStatuses)) {
        throw new Exception('Invalid status');
    }
    
    // Get current booking status and details
    $stmt = $conn->prepare("
        SELECT b.id, b.status_id, b.user_id, b.booking_reference, b.total_amount, 
               b.email AS booking_email, b.full_name AS booking_full_name,
               u.email AS user_email, u.first_name, u.last_name, bs.name AS current_status_name
        FROM bookings b
        LEFT JOIN users u ON b.user_id = u.id
        LEFT JOIN booking_status bs ON b.status_id = bs.id
        WHERE b.id = ?
    ");
    $stmt->bind_param('i', $bookingId);
    $stmt->execute();
    $booking = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if (!$booking) {
        throw new Exception('Booking not found');
    }

    $currentStatusName = $booking['current_status_name'];
    $oldStatusId = (int)$booking['status_id'];
    $recipientEmail = !empty($booking['booking_email']) ? $booking['booking_email'] : ($booking['user_email'] ?? '');
    $recipientName = !empty($booking['booking_full_name']) ? $booking['booking_full_name'] : trim(($booking['first_name'] ?? '') . ' ' . ($booking['last_name'] ?? ''));

    require_once __DIR__ . '/../phpAuthentication/email_helper.php';

    // ── Approval Logic: Approve First, Pay Later ──
    if ($newStatus === 'approved') {
        if ($currentStatusName === 'pending_review') {
            // First approval: transition to 'awaiting_payment' with 2-hour window
            $stStmt = $conn->prepare("SELECT id FROM booking_status WHERE name = 'awaiting_payment'");
            $stStmt->execute();
            $targetStatus = $stStmt->get_result()->fetch_assoc();
            $stStmt->close();

            if (!$targetStatus) throw new Exception('Status awaiting_payment not configured');
            $newStatusId = (int)$targetStatus['id'];

            // Set expires_at = NOW() + 2 hours
            $upStmt = $conn->prepare("
                UPDATE bookings 
                SET status_id = ?, 
                    expires_at = DATE_ADD(NOW(), INTERVAL 2 HOUR), 
                    updated_at = NOW() 
                WHERE id = ?
            ");
            $upStmt->bind_param('ii', $newStatusId, $bookingId);
            $upStmt->execute();
            $upStmt->close();

            // Fetch calculated expires_at
            $expStmt = $conn->prepare("SELECT expires_at FROM bookings WHERE id = ?");
            $expStmt->bind_param('i', $bookingId);
            $expStmt->execute();
            $expRow = $expStmt->get_result()->fetch_assoc();
            $expStmt->close();
            $expiresAt = $expRow['expires_at'] ?? date('Y-m-d H:i:s', time() + 7200);

            logBookingChange($conn, $bookingId, $oldStatusId, $newStatusId, 'admin', $_SESSION['admin_id'], 'Approved by admin — Awaiting guest payment (2hr window)');

            // Send 2-hour payment window email
            if (!empty($recipientEmail)) {
                EmailHelper::sendAwaitingPaymentEmail(
                    $recipientEmail,
                    $recipientName,
                    $booking['booking_reference'],
                    $booking['total_amount'],
                    $expiresAt
                );
            }

            echo json_encode([
                'success' => true,
                'message' => 'Booking approved! Guest notified to pay within 2 hours.',
                'new_status' => 'awaiting_payment',
                'expires_at' => $expiresAt
            ]);
            exit();

        } else {
            // Second approval (from payment_verifying or direct approval)
            $stStmt = $conn->prepare("SELECT id FROM booking_status WHERE name = 'approved'");
            $stStmt->execute();
            $targetStatus = $stStmt->get_result()->fetch_assoc();
            $stStmt->close();

            if (!$targetStatus) throw new Exception('Status approved not configured');
            $newStatusId = (int)$targetStatus['id'];

            $upStmt = $conn->prepare("
                UPDATE bookings 
                SET status_id = ?, 
                    payment_status = 'paid', 
                    expires_at = NULL, 
                    updated_at = NOW() 
                WHERE id = ?
            ");
            $upStmt->bind_param('ii', $newStatusId, $bookingId);
            $upStmt->execute();
            $upStmt->close();

            logBookingChange($conn, $bookingId, $oldStatusId, $newStatusId, 'admin', $_SESSION['admin_id'], $notes ?: 'Payment confirmed and reservation officially approved');

            if (!empty($recipientEmail)) {
                EmailHelper::sendBookingStatusEmail(
                    $recipientEmail,
                    $recipientName,
                    $booking['booking_reference'],
                    'approved',
                    $notes ?: 'Your payment has been verified and your booking is confirmed!'
                );
            }

            echo json_encode([
                'success' => true,
                'message' => 'Booking confirmed and marked as paid!',
                'new_status' => 'approved'
            ]);
            exit();
        }
    }

    // ── Rejection, Cancellation, or Other Statuses ──
    $stStmt = $conn->prepare("SELECT id FROM booking_status WHERE name = ?");
    $stStmt->bind_param('s', $newStatus);
    $stStmt->execute();
    $targetStatus = $stStmt->get_result()->fetch_assoc();
    $stStmt->close();

    if (!$targetStatus) {
        throw new Exception("Status '$newStatus' not found");
    }
    $newStatusId = (int)$targetStatus['id'];

    $upStmt = $conn->prepare("UPDATE bookings SET status_id = ?, updated_at = NOW() WHERE id = ?");
    $upStmt->bind_param('ii', $newStatusId, $bookingId);
    $upStmt->execute();
    $upStmt->close();

    logBookingChange($conn, $bookingId, $oldStatusId, $newStatusId, 'admin', $_SESSION['admin_id'], $notes);

    // If cancelling or rejecting, restore availability
    if (in_array($newStatus, ['rejected', 'cancelled'])) {
        restoreAvailability($conn, $bookingId);
    }

    // Email notification
    if (in_array($newStatus, ['rejected', 'cancelled']) && !empty($recipientEmail)) {
        EmailHelper::sendBookingStatusEmail(
            $recipientEmail,
            $recipientName,
            $booking['booking_reference'],
            $newStatus,
            $notes
        );
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
    $stmt->close();
}

/**
 * Restore availability when booking is cancelled or rejected
 */
function restoreAvailability($conn, $bookingId) {
    $stmt = $conn->prepare("
        SELECT booking_type, activity_id, accommodation_id, bundle_id, check_in_date, check_in_time
        FROM bookings
        WHERE id = ?
    ");
    $stmt->bind_param('i', $bookingId);
    $stmt->execute();
    $booking = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$booking) return;

    $itemType = $booking['booking_type'];
    switch ($itemType) {
        case 'activity':      $itemId = $booking['activity_id'];      break;
        case 'accommodation': $itemId = $booking['accommodation_id']; break;
        case 'bundle':        $itemId = $booking['bundle_id'];        break;
        default:              $itemId = null;
    }

    if (!$itemId) return;

    $date     = $booking['check_in_date'];
    $timeSlot = $booking['check_in_time'];
    
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
    
    $updStmt = $conn->prepare($query);
    $updStmt->bind_param($types, ...$params);
    $updStmt->execute();
    $updStmt->close();
}
