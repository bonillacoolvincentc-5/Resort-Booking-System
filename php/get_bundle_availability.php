<?php
/**
 * Bundle Availability API
 * Returns the availability status of a bundle for a specific month and year.
 * Statuses: available (Green), limited (Yellow), full (Red), blocked (Gray)
 */

session_start();
require_once '../phpAuthentication/db_config.php';

header('Content-Type: application/json');

// Get parameters
$bundleId = isset($_GET['bundle_id']) ? intval($_GET['bundle_id']) : null;
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$month = isset($_GET['month']) ? intval($_GET['month']) : date('n');

if (!$bundleId) {
    echo json_encode(['success' => false, 'message' => 'Bundle ID is required']);
    exit();
}

if ($month < 1 || $month > 12) {
    echo json_encode(['success' => false, 'message' => 'Invalid month']);
    exit();
}

try {
    // 1. Get Bundle Capacity
    $stmt = $conn->prepare("SELECT capacity FROM bundles WHERE id = ? AND is_active = 1");
    $stmt->bind_param('i', $bundleId);
    $stmt->execute();
    $result = $stmt->get_result();
    $bundle = $result->fetch_assoc();

    if (!$bundle) {
        throw new Exception("Bundle not found or inactive.");
    }
    
    $totalCapacity = $bundle['capacity'];

    // Define dates
    $startDate = sprintf('%04d-%02d-01', $year, $month);
    $endDate = date('Y-m-t', strtotime($startDate));

    // Initialize calendar days array
    $days = [];
    $cursor = new DateTime($startDate);
    $end = new DateTime($endDate);
    
    while ($cursor <= $end) {
        $days[$cursor->format('Y-m-d')] = [
            'booked_count' => 0,
            'status' => 'available' // default
        ];
        $cursor->modify('+1 day');
    }

    // 2. Fetch Blocked Dates
    $blockedStmt = $conn->prepare("
        SELECT blocked_date 
        FROM blocked_dates 
        WHERE blocked_date BETWEEN ? AND ?
    ");
    $blockedStmt->bind_param('ss', $startDate, $endDate);
    $blockedStmt->execute();
    $blockedRes = $blockedStmt->get_result();

    while ($row = $blockedRes->fetch_assoc()) {
        if (isset($days[$row['blocked_date']])) {
            $days[$row['blocked_date']]['status'] = 'blocked';
        }
    }

    // 3. Fetch Bookings overlapping this month for this bundle
    $bookingStmt = $conn->prepare("
        SELECT check_in_date, check_out_date 
        FROM bookings 
        WHERE booking_type = 'bundle' 
          AND bundle_id = ? 
          AND status_id IN (SELECT id FROM booking_status WHERE name IN ('pending_review', 'approved'))
          AND check_in_date <= ? 
          AND (check_out_date IS NULL OR check_out_date >= ?)
    ");
    $bookingStmt->bind_param('iss', $bundleId, $endDate, $startDate);
    $bookingStmt->execute();
    $bookingRes = $bookingStmt->get_result();

    while ($row = $bookingRes->fetch_assoc()) {
        $checkIn = $row['check_in_date'];
        $checkOut = $row['check_out_date'] ?: $checkIn;

        $from = max($startDate, $checkIn);
        $to = min($endDate, $checkOut);

        $dayCursor = new DateTime($from);
        $dayEnd = new DateTime($to);
        
        while ($dayCursor <= $dayEnd) {
            $dateKey = $dayCursor->format('Y-m-d');
            if (isset($days[$dateKey]) && $days[$dateKey]['status'] !== 'blocked') {
                $days[$dateKey]['booked_count']++;
            }
            $dayCursor->modify('+1 day');
        }
    }

    // 4. Determine status based on capacity
    foreach ($days as $dateKey => &$day) {
        if ($day['status'] === 'blocked') {
            continue;
        }

        $count = $day['booked_count'];
        if ($count >= $totalCapacity) {
            $day['status'] = 'full';
        } else if ($count > 0 && ($totalCapacity - $count) <= 2) { 
            // If only 1 or 2 slots left, mark as limited
            $day['status'] = 'limited';
        } else if ($count > 0 && $count >= ($totalCapacity / 2)) {
            // Also mark as limited if half full
            $day['status'] = 'limited';
        } else {
            $day['status'] = 'available';
        }
        
        // Remove count to prevent admin info leakage
        unset($day['booked_count']);
    }
    unset($day);

    echo json_encode([
        'success' => true,
        'year' => $year,
        'month' => $month,
        'total_capacity' => $totalCapacity,
        'days' => $days
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
