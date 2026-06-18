<?php
/**
 * Update Booking (Admin)
 * Allows admin to edit basic booking fields:
 *   full_name, check_in_date, check_in_time, payment_method,
 *   item_id (activity/accommodation/bundle), special_requests
 */

session_start();
require_once '../phpAuthentication/db_config.php';

header('Content-Type: application/json');

// Guard: admin only
if (!isset($_SESSION['admin_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized: Admin login required'
    ]);
    exit();
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        throw new Exception('Invalid request data');
    }

    $bookingId = intval($data['booking_id'] ?? 0);
    if (!$bookingId) {
        throw new Exception('Booking ID is required');
    }

    // Validate required fields
    $fullName     = trim($data['full_name']       ?? '');
    $checkInDate  = trim($data['check_in_date']   ?? '');
    $checkInTime  = $data['check_in_time']        ?? null;
    $paymentMethod = trim($data['payment_method'] ?? '');
    $itemId       = intval($data['item_id']       ?? 0);
    $specialReqs  = trim($data['special_requests'] ?? '');

    if (!$fullName || !$checkInDate) {
        throw new Exception('Customer name and check-in date are required');
    }

    // Fetch the booking to know which type it is
    $stmt = $conn->prepare("SELECT booking_type FROM bookings WHERE id = ?");
    $stmt->bind_param('i', $bookingId);
    $stmt->execute();
    $booking = $stmt->get_result()->fetch_assoc();

    if (!$booking) {
        throw new Exception('Booking not found');
    }

    $bookingType = $booking['booking_type'];

    // Build the item column to update (if an item was selected)
    $itemColSql = '';
    if ($itemId > 0) {
        $itemColMap = [
            'activity'      => 'activity_id',
            'accommodation' => 'accommodation_id',
            'bundle'        => 'bundle_id',
        ];
        if (isset($itemColMap[$bookingType])) {
            $col = $itemColMap[$bookingType];
            // Verify the item exists and is active
            $tableName = $bookingType === 'activity' ? 'activities'
                : ($bookingType === 'accommodation'  ? 'accommodations' : 'bundles');
            $chk = $conn->prepare("SELECT id FROM {$tableName} WHERE id = ? AND is_active = 1");
            $chk->bind_param('i', $itemId);
            $chk->execute();
            if (!$chk->get_result()->fetch_assoc()) {
                throw new Exception('Selected item not found or is inactive');
            }
            $itemColSql = ", {$col} = ?";
        }
    }

    // Perform update
    if ($itemColSql) {
        $sql  = "UPDATE bookings
                 SET full_name = ?, check_in_date = ?, check_in_time = ?,
                     payment_method = ?, special_requests = ?,
                     updated_at = NOW()
                     {$itemColSql}
                 WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sssssii',
            $fullName, $checkInDate, $checkInTime,
            $paymentMethod, $specialReqs,
            $itemId, $bookingId
        );
    } else {
        $sql  = "UPDATE bookings
                 SET full_name = ?, check_in_date = ?, check_in_time = ?,
                     payment_method = ?, special_requests = ?,
                     updated_at = NOW()
                 WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sssssi',
            $fullName, $checkInDate, $checkInTime,
            $paymentMethod, $specialReqs,
            $bookingId
        );
    }

    $stmt->execute();

    if ($conn->affected_rows === 0) {
        // Still success — data was identical, no rows changed
    }

    // Log the edit
    $stmt = $conn->prepare("
        INSERT INTO booking_logs (booking_id, old_status_id, new_status_id, changed_by, changed_by_id, notes)
        SELECT id, status_id, status_id, 'admin', ?, 'Booking details edited by admin'
        FROM bookings WHERE id = ?
    ");
    $stmt->bind_param('ii', $_SESSION['admin_id'], $bookingId);
    $stmt->execute();

    echo json_encode([
        'success' => true,
        'message' => 'Booking updated successfully'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
