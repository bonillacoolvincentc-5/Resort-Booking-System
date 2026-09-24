<?php
/**
 * Bundle & Package Availability API
 * Returns the availability status of a bundle, accommodation, or activity for a specific month and year.
 * Statuses: available (Green), limited (Yellow), full (Red), blocked (Gray)
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../phpAuthentication/db_config.php';

if (!headers_sent()) {
    header('Content-Type: application/json');
}

// Get parameters
$itemId = isset($_GET['item_id']) ? intval($_GET['item_id']) : (isset($_GET['bundle_id']) ? intval($_GET['bundle_id']) : 0);
$type = isset($_GET['type']) ? trim($_GET['type']) : (isset($_GET['package_type']) ? trim($_GET['package_type']) : '');
$year = isset($_GET['year']) ? intval($_GET['year']) : (int)date('Y');
$month = isset($_GET['month']) ? intval($_GET['month']) : (int)date('n');

if (!$itemId) {
    echo json_encode(['success' => false, 'message' => 'Item/Bundle ID is required']);
    exit();
}

if ($month < 1 || $month > 12) {
    echo json_encode(['success' => false, 'message' => 'Invalid month']);
    exit();
}

try {
    // Determine package type if not explicitly provided or if provided type not found
    $item = null;
    $packageType = $type;

    if ($packageType === 'accommodation') {
        $stmt = $conn->prepare("SELECT id, name, capacity FROM accommodations WHERE id = ? AND is_active = 1");
        $stmt->bind_param('i', $itemId);
        $stmt->execute();
        $item = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    } elseif ($packageType === 'activity') {
        $stmt = $conn->prepare("SELECT id, name, capacity FROM activities WHERE id = ? AND is_active = 1");
        $stmt->bind_param('i', $itemId);
        $stmt->execute();
        $item = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    } elseif ($packageType === 'bundle') {
        $stmt = $conn->prepare("SELECT id, name, capacity, daily_booking_limit FROM bundles WHERE id = ? AND is_active = 1");
        $stmt->bind_param('i', $itemId);
        $stmt->execute();
        $item = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }

    // Auto-detection fallback if type was not supplied or item not found in specified type
    if (!$item) {
        // Try bundles first
        $stmt = $conn->prepare("SELECT id, name, capacity, daily_booking_limit FROM bundles WHERE id = ? AND is_active = 1");
        $stmt->bind_param('i', $itemId);
        $stmt->execute();
        $item = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($item) {
            $packageType = 'bundle';
        } else {
            // Try accommodations
            $stmt = $conn->prepare("SELECT id, name, capacity FROM accommodations WHERE id = ? AND is_active = 1");
            $stmt->bind_param('i', $itemId);
            $stmt->execute();
            $item = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if ($item) {
                $packageType = 'accommodation';
            } else {
                // Try activities
                $stmt = $conn->prepare("SELECT id, name, capacity FROM activities WHERE id = ? AND is_active = 1");
                $stmt->bind_param('i', $itemId);
                $stmt->execute();
                $item = $stmt->get_result()->fetch_assoc();
                $stmt->close();
                if ($item) {
                    $packageType = 'activity';
                }
            }
        }
    }

    if (!$item) {
        throw new Exception("Package item not found or inactive.");
    }

    // Capacity logic
    if ($packageType === 'accommodation') {
        // Accommodations use daily_booking_limit as their capacity (number of units available)
        $totalCapacity = isset($item['daily_booking_limit']) ? max(1, intval($item['daily_booking_limit'])) : 1;
        $itemColumn = 'accommodation_id';
    } elseif ($packageType === 'activity') {
        $totalCapacity = max(1, intval($item['capacity']));
        $itemColumn = 'activity_id';
    } else {
        // Bundles use the daily booking limit
        $totalCapacity = max(1, intval($item['daily_booking_limit']));
        $itemColumn = 'bundle_id';
    }

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
    $conn->query("
        CREATE TABLE IF NOT EXISTS `blocked_dates` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `blocked_date` date NOT NULL,
            `reason` text DEFAULT NULL,
            `blocked_by` int(11) DEFAULT NULL,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            PRIMARY KEY (`id`),
            UNIQUE KEY `unique_blocked_date` (`blocked_date`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
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
    $blockedStmt->close();

    // 2.5 Fetch Global Daily Capacities
    $capStmt = $conn->prepare("SELECT target_date, max_guests FROM daily_resort_capacity WHERE target_date BETWEEN ? AND ?");
    $capStmt->bind_param('ss', $startDate, $endDate);
    $capStmt->execute();
    $capRes = $capStmt->get_result();
    $customCaps = [];
    while ($row = $capRes->fetch_assoc()) {
        $customCaps[$row['target_date']] = (int)$row['max_guests'];
    }
    $capStmt->close();

    if (!empty($customCaps)) {
        $globalBookStmt = $conn->prepare("
            SELECT check_in_date, SUM(number_of_guests) AS total_guests
            FROM bookings
            WHERE status_id IN (SELECT id FROM booking_status WHERE name IN ('pending_review', 'approved', 'checked_in'))
              AND check_in_date BETWEEN ? AND ?
            GROUP BY check_in_date
        ");
        $globalBookStmt->bind_param('ss', $startDate, $endDate);
        $globalBookStmt->execute();
        $globalBookRes = $globalBookStmt->get_result();
        while ($row = $globalBookRes->fetch_assoc()) {
            $d = $row['check_in_date'];
            $totalG = (int)$row['total_guests'];
            if (isset($customCaps[$d]) && $totalG >= $customCaps[$d]) {
                if (isset($days[$d]) && $days[$d]['status'] !== 'blocked') {
                    $days[$d]['status'] = 'full';
                    $days[$d]['global_full'] = true;
                }
            }
        }
        $globalBookStmt->close();
    }

    // 3. Fetch Bookings overlapping this month for this item
    $bookingStmt = $conn->prepare("
        SELECT check_in_date, check_out_date 
        FROM bookings 
        WHERE booking_type = ? 
          AND $itemColumn = ? 
          AND status_id IN (SELECT id FROM booking_status WHERE name IN ('pending_review', 'approved', 'checked_in'))
          AND check_in_date <= ? 
          AND (check_out_date IS NULL OR check_out_date >= ?)
    ");
    $bookingStmt->bind_param('siss', $packageType, $itemId, $endDate, $startDate);
    $bookingStmt->execute();
    $bookingRes = $bookingStmt->get_result();

    while ($row = $bookingRes->fetch_assoc()) {
        $checkIn = $row['check_in_date'];
        $checkOut = $row['check_out_date'] ?: $checkIn;

        $from = max($startDate, $checkIn);
        $to = min($endDate, $checkOut);

        $dayCursor = new DateTime($from);
        $dayEnd = new DateTime($to);

        // If multi-day stay and checking out on or after this date, departure day morning check-out frees up the date
        if ($checkOut > $checkIn && $to === $checkOut) {
            $dayEnd->modify('-1 day');
        }

        while ($dayCursor <= $dayEnd) {
            $dateKey = $dayCursor->format('Y-m-d');
            if (isset($days[$dateKey]) && $days[$dateKey]['status'] !== 'blocked') {
                $days[$dateKey]['booked_count']++;
            }
            $dayCursor->modify('+1 day');
        }
    }
    $bookingStmt->close();

    // 4. Determine status based on capacity
    foreach ($days as $dateKey => &$day) {
        if ($day['status'] === 'blocked') {
            continue;
        }
        if (!empty($day['global_full'])) {
            $day['status'] = 'full';
            continue;
        }

        $count = $day['booked_count'];
        if ($count >= $totalCapacity) {
            $day['status'] = 'full';
        } else if ($packageType !== 'accommodation' && $count > 0 && ($totalCapacity - $count) <= 2) {
            // If only 1 or 2 slots left, mark as limited
            $day['status'] = 'limited';
        } else if ($packageType !== 'accommodation' && $count > 0 && $count >= ($totalCapacity / 2)) {
            // Also mark as limited if half full
            $day['status'] = 'limited';
        } else {
            $day['status'] = 'available';
        }

        unset($day['booked_count']);
    }
    unset($day);

    echo json_encode([
        'success'        => true,
        'package_type'   => $packageType,
        'item_id'        => $itemId,
        'item_name'      => $item['name'],
        'year'           => $year,
        'month'          => $month,
        'total_capacity' => $totalCapacity,
        'days'           => $days
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
