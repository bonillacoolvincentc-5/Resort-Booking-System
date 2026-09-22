<?php
/**
 * Activity & Accommodation Availability Calendar Data API
 * Queries `activities`, `accommodations`, and `bookings` tables to calculate total booked status per date.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../phpAuthentication/db_config.php';

if (!headers_sent()) {
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
}

$itemId = intval($_GET['activity_id'] ?? $_GET['accommodation_id'] ?? $_GET['item_id'] ?? $_GET['package_id'] ?? 0);
$type   = trim($_GET['package_type'] ?? $_GET['type'] ?? '');
$year   = isset($_GET['year']) ? intval($_GET['year']) : (int)date('Y');
$month  = isset($_GET['month']) ? intval($_GET['month']) : (int)date('n');

if (!$itemId) {
    echo json_encode(['success' => false, 'message' => 'Package ID is required']);
    exit();
}

if ($month < 1 || $month > 12) {
    echo json_encode(['success' => false, 'message' => 'Invalid month']);
    exit();
}

try {
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
    }

    // Auto-detection fallback if type not supplied or not matched
    if (!$item) {
        $stmt = $conn->prepare("SELECT id, name, capacity FROM activities WHERE id = ? AND is_active = 1");
        $stmt->bind_param('i', $itemId);
        $stmt->execute();
        $item = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($item) {
            $packageType = 'activity';
        } else {
            $stmt = $conn->prepare("SELECT id, name, capacity FROM accommodations WHERE id = ? AND is_active = 1");
            $stmt->bind_param('i', $itemId);
            $stmt->execute();
            $item = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if ($item) {
                $packageType = 'accommodation';
            }
        }
    }

    if (!$item) {
        throw new Exception("Package item not found or inactive.");
    }

    // Capacity logic
    $capacity = ($packageType === 'accommodation') ? 1 : max(1, (int)$item['capacity']);
    $startDate = sprintf('%04d-%02d-01', $year, $month);
    $endDate   = date('Y-m-t', strtotime($startDate));

    // Initialize Calendar Days
    $days = [];
    $cursor = new DateTime($startDate);
    $end = new DateTime($endDate);

    while ($cursor <= $end) {
        $days[$cursor->format('Y-m-d')] = [
            'booked'    => 0,
            'capacity'  => $capacity,
            'available' => true,
            'status'    => 'available'
        ];
        $cursor->modify('+1 day');
    }

    // Blocked Dates Check
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
    $blkStmt = $conn->prepare("SELECT blocked_date FROM blocked_dates WHERE blocked_date BETWEEN ? AND ?");
    $blkStmt->bind_param('ss', $startDate, $endDate);
    $blkStmt->execute();
    $blkRes = $blkStmt->get_result();
    while ($row = $blkRes->fetch_assoc()) {
        if (isset($days[$row['blocked_date']])) {
            $days[$row['blocked_date']]['status'] = 'blocked';
            $days[$row['blocked_date']]['available'] = false;
        }
    }
    $blkStmt->close();

    // Query Bookings
    if ($packageType === 'accommodation') {
        $bookStmt = $conn->prepare("
            SELECT check_in_date, check_out_date
            FROM bookings
            WHERE booking_type = 'accommodation'
              AND accommodation_id = ?
              AND status_id IN (SELECT id FROM booking_status WHERE name IN ('pending_review', 'approved', 'checked_in'))
              AND check_in_date <= ?
              AND (check_out_date IS NULL OR check_out_date >= ?)
        ");
        $bookStmt->bind_param('iss', $itemId, $endDate, $startDate);
        $bookStmt->execute();
        $bookRes = $bookStmt->get_result();

        while ($row = $bookRes->fetch_assoc()) {
            $checkIn  = $row['check_in_date'];
            $checkOut = $row['check_out_date'] ?: $checkIn;
            $from = max($startDate, $checkIn);
            $to   = min($endDate, $checkOut);

            $dayCursor = new DateTime($from);
            $dayEnd    = new DateTime($to);
            if ($checkOut > $checkIn && $to === $checkOut) {
                $dayEnd->modify('-1 day');
            }

            while ($dayCursor <= $dayEnd) {
                $dKey = $dayCursor->format('Y-m-d');
                if (isset($days[$dKey]) && $days[$dKey]['status'] !== 'blocked') {
                    $days[$dKey]['booked']++;
                    if ($days[$dKey]['booked'] >= $capacity) {
                        $days[$dKey]['status'] = 'full';
                        $days[$dKey]['available'] = false;
                    }
                }
                $dayCursor->modify('+1 day');
            }
        }
        $bookStmt->close();
    } else {
        $bookStmt = $conn->prepare("
            SELECT check_in_date, COALESCE(SUM(number_of_guests), COUNT(*)) AS total_booked
            FROM bookings
            WHERE booking_type = 'activity'
              AND activity_id = ?
              AND status_id IN (SELECT id FROM booking_status WHERE name IN ('pending_review', 'approved', 'checked_in'))
              AND check_in_date BETWEEN ? AND ?
            GROUP BY check_in_date
        ");
        $bookStmt->bind_param('iss', $itemId, $startDate, $endDate);
        $bookStmt->execute();
        $bookRes = $bookStmt->get_result();

        while ($row = $bookRes->fetch_assoc()) {
            $d = $row['check_in_date'];
            if (isset($days[$d])) {
                $bookedGuests = (int)$row['total_booked'];
                $days[$d]['booked'] = $bookedGuests;
                if ($days[$d]['status'] !== 'blocked') {
                    if ($bookedGuests >= $capacity) {
                        $days[$d]['status'] = 'full';
                        $days[$d]['available'] = false;
                    } else if ($capacity - $bookedGuests <= 2 || $bookedGuests >= ($capacity / 2)) {
                        $days[$d]['status'] = 'limited';
                        $days[$d]['available'] = true;
                    } else {
                        $days[$d]['status'] = 'available';
                        $days[$d]['available'] = true;
                    }
                }
            }
        }
        $bookStmt->close();
    }

    echo json_encode([
        'success'       => true,
        'package_type'  => $packageType,
        'item_id'       => $itemId,
        'item_name'     => $item['name'],
        'capacity'      => $capacity,
        'year'          => $year,
        'month'         => $month,
        'days'          => $days
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
