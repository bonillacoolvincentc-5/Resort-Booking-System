<?php
/**
 * Check Availability API
 * Checks availability for activities and accommodations
 */

require_once '../phpAuthentication/db_config.php';

header('Content-Type: application/json');

try {
    $type = $_GET['type'] ?? null;
    $itemId = $_GET['item_id'] ?? null;
    $date = $_GET['date'] ?? null;
    $timeIn = $_GET['time_in'] ?? null;
    $timeOut = $_GET['time_out'] ?? null;
    
    if (!$type || !$itemId || !$date) {
        throw new Exception('Missing required parameters');
    }
    
    if (!in_array($type, ['activity', 'accommodation', 'bundle'])) {
        throw new Exception('Invalid type parameter');
    }
    
    // Get item details
    if ($type === 'activity') {
        $stmt = $conn->prepare("SELECT capacity, name FROM activities WHERE id = ? AND is_active = 1");
        $stmt->bind_param('i', $itemId);
        $stmt->execute();
        $result = $stmt->get_result();
        $item = $result->fetch_assoc();
        
        if (!$item) {
            throw new Exception('Activity not found or inactive');
        }
    } else if ($type === 'accommodation') {
        $stmt = $conn->prepare("SELECT capacity, name FROM accommodations WHERE id = ? AND is_active = 1");
        $stmt->bind_param('i', $itemId);
        $stmt->execute();
        $result = $stmt->get_result();
        $item = $result->fetch_assoc();
        
        if (!$item) {
            throw new Exception('Accommodation not found or inactive');
        }
    } else if ($type === 'bundle') {
        $stmt = $conn->prepare("SELECT capacity, name FROM bundles WHERE id = ? AND is_active = 1");
        $stmt->bind_param('i', $itemId);
        $stmt->execute();
        $result = $stmt->get_result();
        $item = $result->fetch_assoc();
        
        if (!$item) {
            throw new Exception('Bundle not found or inactive');
        }
    }
    
    $itemColumn = $type === 'activity' ? 'activity_id' : ($type === 'accommodation' ? 'accommodation_id' : 'bundle_id');
    
    // Count existing bookings for the date
    $query = "
        SELECT COUNT(*) as count
        FROM bookings b
        WHERE b.booking_type = ?
        AND b.$itemColumn = ?
        AND (
            (b.check_out_date IS NOT NULL AND b.check_out_date != b.check_in_date AND b.check_in_date <= ? AND b.check_out_date > ?)
            OR
            ((b.check_out_date IS NULL OR b.check_out_date = b.check_in_date) AND b.check_in_date = ?)
        )
        AND b.status_id IN (SELECT id FROM booking_status WHERE name IN ('pending_review', 'approved', 'checked_in'))
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('sisss', $type, $itemId, $date, $date, $date);
    $stmt->execute();
    $result = $stmt->get_result();
    $countResult = $result->fetch_assoc();
    
    $bookedCount = (int)$countResult['count'];
    
    if ($type === 'accommodation') {
        $erQuery = "
            SELECT COUNT(*) as cnt
            FROM booking_extra_rooms er
            JOIN bookings b ON er.booking_id = b.id
            WHERE er.accommodation_id = ?
            AND (
                (er.check_out_date IS NOT NULL AND er.check_out_date != er.check_in_date AND er.check_in_date <= ? AND er.check_out_date > ?)
                OR
                ((er.check_out_date IS NULL OR er.check_out_date = er.check_in_date) AND er.check_in_date = ?)
            )
            AND b.status_id IN (SELECT id FROM booking_status WHERE name IN ('pending_review', 'approved', 'checked_in'))
        ";
        $erStmt = $conn->prepare($erQuery);
        $erStmt->bind_param('isss', $itemId, $date, $date, $date);
        $erStmt->execute();
        $bookedCount += (int)$erStmt->get_result()->fetch_assoc()['cnt'];
        $erStmt->close();
    }
    
    $availableSlots = $item['capacity'] - $bookedCount;
    $isAvailable = $availableSlots > 0;
    
    // Check blocked dates
    $blockedStmt = $conn->prepare("SELECT reason FROM blocked_dates WHERE blocked_date = ? LIMIT 1");
    $blockedStmt->bind_param('s', $date);
    $blockedStmt->execute();
    $blockedRes = $blockedStmt->get_result()->fetch_assoc();
    $blockedStmt->close();
    
    if ($blockedRes) {
        $isAvailable = false;
        $blockedReason = $blockedRes['reason'] ?: 'Blocked by admin';
    } else {
        $blockedReason = null;
    }

    echo json_encode([
        'success' => true,
        'available' => $isAvailable,
        'available_slots' => $availableSlots,
        'total_capacity' => $item['capacity'],
        'booked_count' => $bookedCount,
        'item_name' => $item['name'],
        'is_blocked' => !!$blockedRes,
        'block_reason' => $blockedReason
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
