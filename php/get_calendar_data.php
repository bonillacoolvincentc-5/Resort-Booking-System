<?php
/**
 * Calendar Data API (Admin)
 * Returns per-day booking counts, occupancy levels, and blocked dates for a month.
 */

session_start();
require_once '../phpAuthentication/db_config.php';

header('Content-Type: application/json');

$isAdmin = isset($_SESSION['admin_id']) || (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true);
$isStaff = isset($_SESSION['is_staff']) && $_SESSION['is_staff'] === true;

if (!$isAdmin && !$isStaff) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Ensure blocked_dates table exists
$conn->query("
    CREATE TABLE IF NOT EXISTS `blocked_dates` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `blocked_date` date NOT NULL,
        `reason` text DEFAULT NULL,
        `blocked_by` int(11) DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        PRIMARY KEY (`id`),
        UNIQUE KEY `unique_blocked_date` (`blocked_date`),
        KEY `idx_blocked_date` (`blocked_date`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

$year  = intval($_GET['year'] ?? date('Y'));
$month = intval($_GET['month'] ?? date('n'));

if ($month < 1 || $month > 12) {
    echo json_encode(['success' => false, 'message' => 'Invalid month']);
    exit();
}

// Daily capacity baseline for occupancy colour bands
$maxDailyCapacity = 15;

try {
    $startDate = sprintf('%04d-%02d-01', $year, $month);
    $endDate   = date('Y-m-t', strtotime($startDate));

    // Blocked dates in range
    $blockedStmt = $conn->prepare("
        SELECT blocked_date, reason
        FROM blocked_dates
        WHERE blocked_date BETWEEN ? AND ?
    ");
    $blockedStmt->bind_param('ss', $startDate, $endDate);
    $blockedStmt->execute();
    $blockedResult = $blockedStmt->get_result();

    $blockedDates = [];
    while ($row = $blockedResult->fetch_assoc()) {
        $blockedDates[$row['blocked_date']] = [
            'reason' => $row['reason']
        ];
    }

    // Custom capacities in range
    $capStmt = $conn->prepare("
        SELECT target_date, max_guests
        FROM daily_resort_capacity
        WHERE target_date BETWEEN ? AND ?
    ");
    if ($capStmt) {
        $capStmt->bind_param('ss', $startDate, $endDate);
        $capStmt->execute();
        $capResult = $capStmt->get_result();
        $customCaps = [];
        while ($row = $capResult->fetch_assoc()) {
            $customCaps[$row['target_date']] = intval($row['max_guests']);
        }
    } else {
        $customCaps = [];
    }

    // Bookings overlapping each day in the month
    $bookingStmt = $conn->prepare("
        SELECT
            b.id,
            b.booking_type,
            b.check_in_date,
            b.check_out_date,
            acc.type AS accommodation_type
        FROM bookings b
        LEFT JOIN booking_status bs ON b.status_id = bs.id
        LEFT JOIN accommodations acc ON b.accommodation_id = acc.id
        WHERE bs.name NOT IN ('rejected', 'cancelled')
          AND (
              (b.booking_type = 'activity' AND b.check_in_date BETWEEN ? AND ?)
              OR (
                  b.booking_type IN ('accommodation', 'bundle')
                  AND b.check_in_date <= ?
                  AND (b.check_out_date IS NULL OR b.check_out_date >= ?)
              )
          )
    ");
    $bookingStmt->bind_param('ssss', $startDate, $endDate, $endDate, $startDate);
    $bookingStmt->execute();
    $bookingResult = $bookingStmt->get_result();

    $days = [];
    $cursor = new DateTime($startDate);
    $end    = new DateTime($endDate);
    while ($cursor <= $end) {
        $dateKey = $cursor->format('Y-m-d');
        $days[$dateKey] = [
            'total_bookings' => 0,
            'rooms' => 0,
            'cottages' => 0,
            'activities' => 0,
            'daily_capacity' => $customCaps[$dateKey] ?? $maxDailyCapacity,
            'occupancy_level' => 'none',
            'is_blocked' => false,
            'block_reason' => null
        ];
        $cursor->modify('+1 day');
    }

    while ($row = $bookingResult->fetch_assoc()) {
        $checkIn  = $row['check_in_date'];
        $checkOut = $row['check_out_date'] ?: $checkIn;

        // For accommodations and bundles that span multiple days, the check-out day is not occupied
        if ($checkIn !== $checkOut && in_array($row['booking_type'], ['accommodation', 'bundle'])) {
            $toObj = new DateTime($checkOut);
            $toObj->modify('-1 day');
            $adjustedCheckOut = $toObj->format('Y-m-d');
        } else {
            $adjustedCheckOut = $checkOut;
        }

        $from = max($startDate, $checkIn);
        $to   = min($endDate, $adjustedCheckOut);

        $dayCursor = new DateTime($from);
        $dayEnd    = new DateTime($to);
        
        while ($dayCursor <= $dayEnd) {
            $dateKey = $dayCursor->format('Y-m-d');
            if (!isset($days[$dateKey])) {
                $dayCursor->modify('+1 day');
                continue;
            }

            $days[$dateKey]['total_bookings']++;

            if ($row['booking_type'] === 'activity') {
                $days[$dateKey]['activities']++;
            } elseif ($row['booking_type'] === 'accommodation') {
                if ($row['accommodation_type'] === 'cottage') {
                    $days[$dateKey]['cottages']++;
                } else {
                    $days[$dateKey]['rooms']++;
                }
            } elseif ($row['booking_type'] === 'bundle') {
                $days[$dateKey]['activities']++;
                $days[$dateKey]['rooms']++;
            }

            $dayCursor->modify('+1 day');
        }
    }

    foreach ($days as $dateKey => &$day) {
        if (isset($blockedDates[$dateKey])) {
            $day['is_blocked'] = true;
            $day['block_reason'] = $blockedDates[$dateKey]['reason'];
            $day['occupancy_level'] = 'blocked';
            continue;
        }

        $count = $day['total_bookings'];
        $dailyCap = $day['daily_capacity'];

        if ($count === 0) {
            $day['occupancy_level'] = 'none';
        } elseif ($dailyCap > 0 && $count / $dailyCap < 0.4) {
            $day['occupancy_level'] = 'low';
        } elseif ($dailyCap > 0 && $count / $dailyCap < 0.7) {
            $day['occupancy_level'] = 'moderate';
        } else {
            $day['occupancy_level'] = 'high';
        }
    }
    unset($day);

    // Services for bundle modal
    $activities = [];
    $actRes = $conn->query("SELECT id, name, price_per_hour FROM activities WHERE is_active = 1 ORDER BY name ASC");
    while ($r = $actRes->fetch_assoc()) {
        $activities[] = $r;
    }

    $accommodations = [];
    $accRes = $conn->query("SELECT id, name, type, price_per_night FROM accommodations WHERE is_active = 1 ORDER BY type ASC, name ASC");
    while ($r = $accRes->fetch_assoc()) {
        $accommodations[] = $r;
    }

    echo json_encode([
        'success' => true,
        'year' => $year,
        'month' => $month,
        'days' => $days,
        'max_daily_capacity' => $maxDailyCapacity,
        'services' => [
            'activities' => $activities,
            'accommodations' => $accommodations
        ]
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
